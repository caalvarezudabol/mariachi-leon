<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\MusicoPersonal;
use App\Models\InventoryMovement;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionEgresosActivos extends Component
{
    use WithPagination, Auditable;

    public $asset_id = '';
    public $fecha_movimiento = '';
    public $motivo = 'prestamo';
    public $cantidad = 1;
    public $responsable_id = '';
    public $documento_referencia = '';
    public $observaciones = '';

    public $modalOpen = false;

    protected function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'fecha_movimiento' => 'required|date',
            'motivo' => 'required|in:prestamo,perdida,deterioro,transferencia,ajuste_negativo',
            'cantidad' => 'required|numeric|gt:0',
            'responsable_id' => 'nullable|exists:musicos_personal,id',
            'documento_referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'asset_id.required' => 'Seleccione un artículo para el egreso.',
        'fecha_movimiento.required' => 'Ingrese la fecha del egreso.',
        'cantidad.gt' => 'La cantidad debe ser un monto positivo.',
    ];

    public function abrirModal()
    {
        $this->reset(['asset_id', 'fecha_movimiento', 'motivo', 'cantidad', 'responsable_id', 'documento_referencia', 'observaciones']);
        $this->fecha_movimiento = date('Y-m-d H:i');
        $this->motivo = 'prestamo';
        $this->cantidad = 1;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        $assetCheck = Asset::find($this->asset_id);
        if ($assetCheck && (float)$this->cantidad > (float)$assetCheck->existencia) {
            $this->addError('cantidad', 'Error: La cantidad a egresar (' . $this->cantidad . ') supera la existencia disponible actual (' . number_format($assetCheck->existencia, 2) . ').');
            return;
        }

        DB::transaction(function () {
            $asset = Asset::lockForUpdate()->findOrFail($this->asset_id);

            $cantidadAnterior = (float)$asset->existencia;
            $cantidadEgreso = (float)$this->cantidad;

            if ($cantidadEgreso > $cantidadAnterior) {
                throw new \Exception('No se pueden egresar más unidades de las disponibles en inventario.');
            }

            $cantidadNuevoSaldo = $cantidadAnterior - $cantidadEgreso;

            // PPP vigente no cambia con las salidas
            $costoPppVigente = ($asset->tipo_control === 'cantidad')
                ? (float)$asset->costo_promedio_ppp
                : (float)$asset->costo_adquisicion;

            // Actualizar existencia del activo
            $asset->existencia = $cantidadNuevoSaldo;
            $asset->save();

            // Registrar movimiento de salida en el Kardex
            InventoryMovement::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'fecha_movimiento' => $this->fecha_movimiento,
                'tipo_movimiento' => 'salida',
                'motivo' => $this->motivo,
                'cantidad' => $cantidadEgreso,
                'costo_unitario' => $costoPppVigente,
                'costo_total' => $cantidadEgreso * $costoPppVigente,
                'cantidad_saldo' => $cantidadNuevoSaldo,
                'costo_ppp_saldo' => $costoPppVigente,
                'valor_total_saldo' => $cantidadNuevoSaldo * $costoPppVigente,
                'responsable_id' => $this->responsable_id ?: null,
                'documento_referencia' => $this->documento_referencia,
                'observaciones' => $this->observaciones,
            ]);

            $this->registrarAuditoria('Activos Fijos', 'Registrar Egreso', 'Egreso de ' . $cantidadEgreso . ' und. para activo ' . $asset->codigo);
        });

        session()->flash('success', 'Egreso de inventario registrado correctamente en el Kardex.');
        $this->modalOpen = false;
    }

    public function render()
    {
        $egresos = InventoryMovement::with(['asset', 'user', 'responsable'])
            ->where('tipo_movimiento', 'salida')
            ->whereNotIn('motivo', ['asignacion', 'baja'])
            ->latest('fecha_movimiento')
            ->latest('id')
            ->paginate(10);

        $articulos = Asset::where('existencia', '>', 0)->orderBy('nombre', 'asc')->get();
        $responsables = MusicoPersonal::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();

        return view('livewire.activos-fijos.gestion-egresos-activos', [
            'egresos' => $egresos,
            'articulos' => $articulos,
            'responsables' => $responsables,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Egresos - Activos Fijos']);
    }
}
