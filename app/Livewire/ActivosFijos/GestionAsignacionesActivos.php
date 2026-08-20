<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\MusicoPersonal;
use App\Models\AssetAssignment;
use App\Models\InventoryMovement;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionAsignacionesActivos extends Component
{
    use WithPagination, Auditable;

    public $asset_id = '';
    public $responsable_id = '';
    public $fecha_asignacion = '';
    public $cantidad = 1;
    public $condicion_entrega = 'Bueno';
    public $observaciones = '';

    public $modalOpen = false;

    protected function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'responsable_id' => 'required|exists:musicos_personal,id',
            'fecha_asignacion' => 'required|date',
            'cantidad' => 'required|numeric|gt:0',
            'condicion_entrega' => 'required|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'asset_id.required' => 'Seleccione el artículo a asignar.',
        'responsable_id.required' => 'Seleccione el Músico / Personal responsable.',
        'fecha_asignacion.required' => 'Ingrese la fecha de asignación.',
        'cantidad.gt' => 'La cantidad debe ser mayor a cero.',
    ];

    public function abrirModal()
    {
        $this->reset(['asset_id', 'responsable_id', 'fecha_asignacion', 'cantidad', 'condicion_entrega', 'observaciones']);
        $this->fecha_asignacion = date('Y-m-d H:i');
        $this->condicion_entrega = 'Bueno';
        $this->cantidad = 1;

        // Autoseleccionar a Juan Pérez si existe
        $juan = MusicoPersonal::where('nombre_completo', 'Juan Pérez')->first();
        if ($juan) {
            $this->responsable_id = $juan->id;
        }

        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        $assetCheck = Asset::find($this->asset_id);
        if ($assetCheck && (float)$this->cantidad > (float)$assetCheck->existencia) {
            $this->addError('cantidad', 'Error: La cantidad solicitada (' . $this->cantidad . ') supera la existencia disponible (' . number_format($assetCheck->existencia, 2) . ').');
            return;
        }

        DB::transaction(function () {
            $asset = Asset::lockForUpdate()->findOrFail($this->asset_id);
            $responsable = MusicoPersonal::findOrFail($this->responsable_id);

            $cantidadAnterior = (float)$asset->existencia;
            $cantidadAsignada = (float)$this->cantidad;
            $cantidadNuevoSaldo = $cantidadAnterior - $cantidadAsignada;

            $costoPppVigente = ($asset->tipo_control === 'cantidad')
                ? (float)$asset->costo_promedio_ppp
                : (float)$asset->costo_adquisicion;

            // Actualizar estado y responsable del activo
            $asset->existencia = $cantidadNuevoSaldo;
            if ($asset->tipo_control === 'individual') {
                $asset->estado = 'asignado';
            }
            $asset->responsable_id = $responsable->id;
            $asset->save();

            // Crear registro de Asignación
            $assignment = AssetAssignment::create([
                'asset_id' => $asset->id,
                'responsable_id' => $responsable->id,
                'user_id' => Auth::id(),
                'fecha_asignacion' => $this->fecha_asignacion,
                'cantidad' => $cantidadAsignada,
                'condicion_entrega' => $this->condicion_entrega,
                'observaciones' => $this->observaciones,
                'estado' => 'activo',
            ]);

            // Registrar movimiento de Kardex
            InventoryMovement::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'fecha_movimiento' => $this->fecha_asignacion,
                'tipo_movimiento' => 'salida',
                'motivo' => 'asignacion',
                'cantidad' => $cantidadAsignada,
                'costo_unitario' => $costoPppVigente,
                'costo_total' => $cantidadAsignada * $costoPppVigente,
                'cantidad_saldo' => $cantidadNuevoSaldo,
                'costo_ppp_saldo' => $costoPppVigente,
                'valor_total_saldo' => $cantidadNuevoSaldo * $costoPppVigente,
                'responsable_id' => $responsable->id,
                'documento_referencia' => 'ASIG-' . str_pad($assignment->id, 5, '0', STR_PAD_LEFT),
                'observaciones' => 'Asignado a: ' . $responsable->nombre_completo,
            ]);

            $this->registrarAuditoria('Activos Fijos', 'Asignar Activo', 'Se asignó ' . $asset->codigo . ' a ' . $responsable->nombre_completo);
        });

        session()->flash('success', 'Asignación de activo registrada exitosamente.');
        $this->modalOpen = false;
    }

    public function render()
    {
        $asignaciones = AssetAssignment::with(['asset', 'responsable', 'user', 'returnRecord'])
            ->latest('fecha_asignacion')
            ->latest('id')
            ->paginate(10);

        $articulos = Asset::where('existencia', '>', 0)
            ->where('estado', '!=', 'dado_de_baja')
            ->orderBy('nombre', 'asc')
            ->get();

        $responsables = MusicoPersonal::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();

        return view('livewire.activos-fijos.gestion-asignaciones-activos', [
            'asignaciones' => $asignaciones,
            'articulos' => $articulos,
            'responsables' => $responsables,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Asignaciones - Activos Fijos']);
    }
}
