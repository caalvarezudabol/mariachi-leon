<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\User;
use App\Models\InventoryMovement;

class GestionKardexActivos extends Component
{
    use WithPagination;

    public $asset_id = '';
    public $user_id = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $tipo_movimiento = '';
    public $motivo = '';

    public function updatingAssetId() { $this->resetPage(); }
    public function updatingUserId() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }
    public function updatingTipoMovimiento() { $this->resetPage(); }
    public function updatingMotivo() { $this->resetPage(); }

    public function resetFiltros()
    {
        $this->reset(['asset_id', 'user_id', 'fecha_inicio', 'fecha_fin', 'tipo_movimiento', 'motivo']);
        $this->resetPage();
    }

    public function exportarPdf()
    {
        if (!$this->asset_id) {
            session()->flash('warning', 'Seleccione un producto para generar el Kardex en PDF.');
            return;
        }

        return redirect()->route('admin.activos-fijos.kardex.pdf', ['asset_id' => $this->asset_id]);
    }

    public function render()
    {
        $query = InventoryMovement::with(['asset', 'user', 'responsable']);

        if ($this->asset_id) {
            $query->where('asset_id', $this->asset_id);
        }

        if ($this->user_id) {
            $query->where('user_id', $this->user_id);
        }

        if ($this->fecha_inicio) {
            $query->whereDate('fecha_movimiento', '>=', $this->fecha_inicio);
        }

        if ($this->fecha_fin) {
            $query->whereDate('fecha_movimiento', '<=', $this->fecha_fin);
        }

        if ($this->tipo_movimiento) {
            $query->where('tipo_movimiento', $this->tipo_movimiento);
        }

        if ($this->motivo) {
            $query->where('motivo', $this->motivo);
        }

        $movimientos = $query->orderBy('fecha_movimiento', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $articulos = Asset::orderBy('nombre', 'asc')->get();
        $usuarios = User::orderBy('name', 'asc')->get();

        return view('livewire.activos-fijos.gestion-kardex-activos', [
            'movimientos' => $movimientos,
            'articulos' => $articulos,
            'usuarios' => $usuarios,
        ])->layout('components.layouts.app', ['title' => 'Kardex Valorizado (PPP) - Activos Fijos']);
    }
}
