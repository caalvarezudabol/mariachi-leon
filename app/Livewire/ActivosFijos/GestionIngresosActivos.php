<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\InventoryMovement;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionIngresosActivos extends Component
{
    use WithPagination, Auditable;

    public $asset_id = '';
    public $fecha_movimiento = '';
    public $motivo = 'compra';
    public $cantidad = 1;
    public $costo_unitario = 0;
    public $documento_referencia = '';
    public $observaciones = '';

    public $modalOpen = false;

    protected function rules()
    {
        return [
            'asset_id' => 'required|exists:assets,id',
            'fecha_movimiento' => 'required|date',
            'motivo' => 'required|in:compra,donacion,devolucion,reposicion,ajuste_positivo',
            'cantidad' => 'required|numeric|gt:0',
            'costo_unitario' => 'required|numeric|min:0',
            'documento_referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'asset_id.required' => 'Seleccione un artículo para el ingreso.',
        'fecha_movimiento.required' => 'Ingrese la fecha del movimiento.',
        'cantidad.gt' => 'La cantidad ingresada debe ser mayor a cero.',
        'costo_unitario.min' => 'El costo unitario no puede ser negativo.',
    ];

    public function abrirModal()
    {
        $this->reset(['asset_id', 'fecha_movimiento', 'motivo', 'cantidad', 'costo_unitario', 'documento_referencia', 'observaciones']);
        $this->fecha_movimiento = date('Y-m-d H:i');
        $this->motivo = 'compra';
        $this->cantidad = 1;
        $this->costo_unitario = 0;
        $this->modalOpen = true;
    }

    public function updatedAssetId($value)
    {
        if ($value) {
            $asset = Asset::find($value);
            if ($asset) {
                $this->costo_unitario = ($asset->tipo_control === 'cantidad' && $asset->costo_promedio_ppp > 0)
                    ? $asset->costo_promedio_ppp
                    : $asset->costo_adquisicion;
            }
        }
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            $asset = Asset::lockForUpdate()->findOrFail($this->asset_id);

            $cantidadAnterior = (float)$asset->existencia;
            $cantidadIngreso = (float)$this->cantidad;
            $costoUnitarioIngreso = (float)$this->costo_unitario;

            $cantidadNuevoSaldo = $cantidadAnterior + $cantidadIngreso;

            // Lógica PPP (Precio Promedio Ponderado) para artículos por cantidad
            if ($asset->tipo_control === 'cantidad') {
                $costoPppAnterior = (float)$asset->costo_promedio_ppp;
                $valorInventarioAnterior = $cantidadAnterior * $costoPppAnterior;
                $valorNuevoIngreso = $cantidadIngreso * $costoUnitarioIngreso;

                $nuevoPpp = ($cantidadNuevoSaldo > 0)
                    ? ($valorInventarioAnterior + $valorNuevoIngreso) / $cantidadNuevoSaldo
                    : $costoUnitarioIngreso;

                $asset->costo_promedio_ppp = $nuevoPpp;
            } else {
                $nuevoPpp = (float)$asset->costo_adquisicion;
            }

            // Actualizar existencia del activo
            $asset->existencia = $cantidadNuevoSaldo;
            if ($asset->estado === 'dado_de_baja' && $cantidadNuevoSaldo > 0) {
                $asset->estado = 'disponible';
            }
            $asset->save();

            // Registrar movimiento en el Kardex
            InventoryMovement::create([
                'asset_id' => $asset->id,
                'user_id' => Auth::id(),
                'fecha_movimiento' => $this->fecha_movimiento,
                'tipo_movimiento' => 'entrada',
                'motivo' => $this->motivo,
                'cantidad' => $cantidadIngreso,
                'costo_unitario' => $costoUnitarioIngreso,
                'costo_total' => $cantidadIngreso * $costoUnitarioIngreso,
                'cantidad_saldo' => $cantidadNuevoSaldo,
                'costo_ppp_saldo' => $nuevoPpp,
                'valor_total_saldo' => $cantidadNuevoSaldo * $nuevoPpp,
                'documento_referencia' => $this->documento_referencia,
                'observaciones' => $this->observaciones,
            ]);

            $this->registrarAuditoria('Activos Fijos', 'Registrar Ingreso', 'Ingreso de ' . $cantidadIngreso . ' und. para activo ' . $asset->codigo);
        });

        session()->flash('success', 'Ingreso de activo y movimiento en Kardex registrados correctamente.');
        $this->modalOpen = false;
    }

    public function render()
    {
        $ingresos = InventoryMovement::with(['asset', 'user', 'responsable'])
            ->where('tipo_movimiento', 'entrada')
            ->latest('fecha_movimiento')
            ->latest('id')
            ->paginate(10);

        $articulos = Asset::orderBy('nombre', 'asc')->get();

        return view('livewire.activos-fijos.gestion-ingresos-activos', [
            'ingresos' => $ingresos,
            'articulos' => $articulos,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Ingresos - Activos Fijos']);
    }
}
