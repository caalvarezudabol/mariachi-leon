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
    public $search_articulo = '';
    public $dropdown_open = false;

    public $user_id = '';
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $tipo_movimiento = '';
    public $motivo = '';

    public function updatingSearchArticulo()
    {
        $this->dropdown_open = true;
    }

    public function updatedSearchArticulo()
    {
        if (trim($this->search_articulo) === '') {
            $this->asset_id = '';
            $this->resetPage();
        }
    }

    public function abrirDropdown()
    {
        $this->dropdown_open = true;
    }

    public function cerrarDropdown()
    {
        $this->dropdown_open = false;
    }

    public function seleccionarArticulo($id)
    {
        $art = Asset::find($id);
        if ($art) {
            $this->asset_id = $art->id;
            $this->search_articulo = '[' . $art->codigo . '] ' . $art->nombre;
        } else {
            $this->asset_id = '';
            $this->search_articulo = '';
        }
        $this->dropdown_open = false;
        $this->resetPage();
    }

    public function limpiarArticulo()
    {
        $this->asset_id = '';
        $this->search_articulo = '';
        $this->dropdown_open = false;
        $this->resetPage();
    }

    public function updatingUserId() { $this->resetPage(); }
    public function updatingFechaInicio() { $this->resetPage(); }
    public function updatingFechaFin() { $this->resetPage(); }
    public function updatingTipoMovimiento() { $this->resetPage(); }
    public function updatingMotivo() { $this->resetPage(); }

    public function resetFiltros()
    {
        $this->reset(['asset_id', 'search_articulo', 'dropdown_open', 'user_id', 'fecha_inicio', 'fecha_fin', 'tipo_movimiento', 'motivo']);
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

        // Búsqueda en tiempo real para el selector desplegable
        $articulosQuery = Asset::query();
        if (!empty($this->search_articulo) && !$this->asset_id) {
            $term = '%' . trim($this->search_articulo) . '%';
            $articulosQuery->where(function($q) use ($term) {
                $q->where('nombre', 'like', $term)
                  ->orWhere('codigo', 'like', $term)
                  ->orWhere('marca', 'like', $term)
                  ->orWhere('modelo', 'like', $term);
            });
        }
        $articulos = $articulosQuery->orderBy('nombre', 'asc')->take(20)->get();

        $usuarios = User::orderBy('name', 'asc')->get();

        return view('livewire.activos-fijos.gestion-kardex-activos', [
            'movimientos' => $movimientos,
            'articulos' => $articulos,
            'usuarios' => $usuarios,
        ])->layout('components.layouts.app', ['title' => 'Kardex Valorizado (PPP) - Activos Fijos']);
    }
}
