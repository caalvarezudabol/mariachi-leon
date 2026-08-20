<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\MusicoPersonal;
use App\Models\AssetDisposal;
use App\Models\InventoryMovement;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionBajasActivos extends Component
{
    use WithPagination, Auditable;

    public $asset_id = '';
    public $fecha_baja = '';
    public $cantidad = 1;
    public $motivo = 'obsolescencia';
    public $responsable_id = '';
    public $observaciones = '';

    public $modalOpen = false;

    protected function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'fecha_baja' => 'required|date',
            'cantidad' => 'required|numeric|gt:0',
            'motivo' => 'required|in:deterioro,perdida,obsolescencia,dano_irreparable,desuso,otro',
            'responsable_id' => 'nullable|exists:musicos_personal,id',
            'observaciones' => 'required|string|min:5',
        ];
    }

    protected $messages = [
        'asset_id.required' => 'Seleccione el artículo para dar de baja.',
        'fecha_baja.required' => 'Ingrese la fecha de baja.',
        'observaciones.required' => 'Debe ingresar la justificación / observación de la baja (mínimo 5 caracteres).',
    ];

    public function abrirModal()
    {
        $this->reset(['asset_id', 'fecha_baja', 'cantidad', 'motivo', 'responsable_id', 'observaciones']);
        $this->fecha_baja = date('Y-m-d');
        $this->motivo = 'obsolescencia';
        $this->cantidad = 1;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        $assetCheck = Asset::find($this->asset_id);
        if ($assetCheck && (float)$this->cantidad > (float)$assetCheck->existencia) {
            $this->addError('cantidad', 'Error: La cantidad a dar de baja (' . $this->cantidad . ') supera la existencia actual (' . number_format($assetCheck->existencia, 2) . ').');
            return;
        }

        DB::transaction(function () {
            $asset = Asset::lockForUpdate()->findOrFail($this->asset_id);

            $cantidadAnterior = (float)$asset->existencia;
            $cantidadBaja = (float)$this->cantidad;
            $cantidadNuevoSaldo = max(0, $cantidadAnterior - $cantidadBaja);

            $costoPppVigente = ($asset->tipo_control === 'cantidad')
                ? (float)$asset->costo_promedio_ppp
                : (float)$asset->costo_adquisicion;

            // Actualizar estado del activo sin borrarlo físicamente
            $asset->existencia = $cantidadNuevoSaldo;
            if ($cantidadNuevoSaldo == 0 || $asset->tipo_control === 'individual') {
                $asset->estado = 'dado_de_baja';
                $asset->responsable_id = null;
            }
            $asset->save();

            // Crear registro de Baja
            $disposal = AssetDisposal::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'responsable_id' => $this->responsable_id ?: null,
                'fecha_baja' => $this->fecha_baja,
                'cantidad' => $cantidadBaja,
                'motivo' => $this->motivo,
                'observaciones' => $this->observaciones,
            ]);

            // Registrar movimiento de salida en el Kardex
            InventoryMovement::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'fecha_movimiento' => $this->fecha_baja . ' ' . date('H:i:s'),
                'tipo_movimiento' => 'salida',
                'motivo' => 'baja',
                'cantidad' => $cantidadBaja,
                'costo_unitario' => $costoPppVigente,
                'costo_total' => $cantidadBaja * $costoPppVigente,
                'cantidad_saldo' => $cantidadNuevoSaldo,
                'costo_ppp_saldo' => $costoPppVigente,
                'valor_total_saldo' => $cantidadNuevoSaldo * $costoPppVigente,
                'responsable_id' => $this->responsable_id ?: null,
                'documento_referencia' => 'BAJA-' . str_pad($disposal->id, 5, '0', STR_PAD_LEFT),
                'observaciones' => 'Baja por ' . str_replace('_', ' ', $this->motivo) . ': ' . $this->observaciones,
            ]);

            $this->registrarAuditoria('Activos Fijos', 'Registrar Baja', 'Baja de ' . $cantidadBaja . ' und. del activo ' . $asset->codigo);
        });

        session()->flash('success', 'Baja de activo registrada correctamente. Se conserva el registro e historial en el Kardex.');
        $this->modalOpen = false;
    }

    public function render()
    {
        $bajas = AssetDisposal::with(['asset', 'user', 'responsable'])
            ->latest('fecha_baja')
            ->latest('id')
            ->paginate(10);

        $articulos = Asset::where('existencia', '>', 0)
            ->where('estado', '!=', 'dado_de_baja')
            ->orderBy('nombre', 'asc')
            ->get();

        $responsables = MusicoPersonal::orderBy('nombre_completo', 'asc')->get();

        return view('livewire.activos-fijos.gestion-bajas-activos', [
            'bajas' => $bajas,
            'articulos' => $articulos,
            'responsables' => $responsables,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Bajas - Activos Fijos']);
    }
}
