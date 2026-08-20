<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;
use App\Models\InventoryMovement;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionDevolucionesActivos extends Component
{
    use WithPagination, Auditable;

    public $asset_assignment_id = '';
    public $fecha_devolucion = '';
    public $condicion_recepcion = 'Bueno';
    public $observaciones = '';

    public $modalOpen = false;

    protected function rules()
    {
        return [
            'asset_assignment_id' => 'required|exists:asset_assignments,id',
            'fecha_devolucion' => 'required|date',
            'condicion_recepcion' => 'required|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'asset_assignment_id.required' => 'Seleccione la asignación que se devuelve.',
        'fecha_devolucion.required' => 'Ingrese la fecha de devolución.',
    ];

    public function abrirModal()
    {
        $this->reset(['asset_assignment_id', 'fecha_devolucion', 'condicion_recepcion', 'observaciones']);
        $this->fecha_devolucion = date('Y-m-d H:i');
        $this->condicion_recepcion = 'Bueno';
        $this->modalOpen = true;
    }

    public function registrarDevolucionDirecta($assignmentId)
    {
        $this->asset_assignment_id = $assignmentId;
        $this->fecha_devolucion = date('Y-m-d H:i');
        $this->condicion_recepcion = 'Bueno';
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            $assignment = AssetAssignment::lockForUpdate()->findOrFail($this->asset_assignment_id);

            if ($assignment->estado === 'devuelto') {
                throw new \Exception('Esta asignación ya fue devuelta previamente.');
            }

            $asset = Asset::lockForUpdate()->findOrFail($assignment->asset_id);

            $cantidadDevuelta = (float)$assignment->cantidad;
            $cantidadAnterior = (float)$asset->existencia;
            $cantidadNuevoSaldo = $cantidadAnterior + $cantidadDevuelta;

            $costoPppVigente = ($asset->tipo_control === 'cantidad')
                ? (float)$asset->costo_promedio_ppp
                : (float)$asset->costo_adquisicion;

            // Actualizar estado del activo
            $asset->existencia = $cantidadNuevoSaldo;
            if ($asset->tipo_control === 'individual') {
                $asset->estado = 'disponible';
                $asset->responsable_id = null;
            }
            $asset->save();

            // Marcar asignación como devuelta
            $assignment->estado = 'devuelto';
            $assignment->save();

            // Crear registro de Devolución
            $returnRecord = AssetReturn::create([
                'asset_assignment_id' => $assignment->id,
                'asset_id' => $asset->id,
                'responsable_id' => $assignment->responsable_id,
                'user_id' => Auth::id(),
                'fecha_devolucion' => $this->fecha_devolucion,
                'cantidad' => $cantidadDevuelta,
                'condicion_recepcion' => $this->condicion_recepcion,
                'observaciones' => $this->observaciones,
            ]);

            // Registrar movimiento en el Kardex
            InventoryMovement::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'fecha_movimiento' => $this->fecha_devolucion,
                'tipo_movimiento' => 'entrada',
                'motivo' => 'devolucion',
                'cantidad' => $cantidadDevuelta,
                'costo_unitario' => $costoPppVigente,
                'costo_total' => $cantidadDevuelta * $costoPppVigente,
                'cantidad_saldo' => $cantidadNuevoSaldo,
                'costo_ppp_saldo' => $costoPppVigente,
                'valor_total_saldo' => $cantidadNuevoSaldo * $costoPppVigente,
                'responsable_id' => $assignment->responsable_id,
                'documento_referencia' => 'DEV-' . str_pad($returnRecord->id, 5, '0', STR_PAD_LEFT),
                'observaciones' => 'Devuelto por: ' . ($assignment->responsable->nombre_completo ?? 'Músico'),
            ]);

            $this->registrarAuditoria('Activos Fijos', 'Registrar Devolución', 'Devolución de activo ' . $asset->codigo . ' por parte de ' . ($assignment->responsable->nombre_completo ?? 'Músico'));
        });

        session()->flash('success', 'Devolución de activo registrada correctamente en el sistema.');
        $this->modalOpen = false;
    }

    public function render()
    {
        $devoluciones = AssetReturn::with(['asset', 'responsable', 'user', 'assignment'])
            ->latest('fecha_devolucion')
            ->latest('id')
            ->paginate(10);

        $asignacionesActivas = AssetAssignment::with(['asset', 'responsable'])
            ->where('estado', 'activo')
            ->latest('fecha_asignacion')
            ->get();

        return view('livewire.activos-fijos.gestion-devoluciones-activos', [
            'devoluciones' => $devoluciones,
            'asignacionesActivas' => $asignacionesActivas,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Devoluciones - Activos Fijos']);
    }
}
