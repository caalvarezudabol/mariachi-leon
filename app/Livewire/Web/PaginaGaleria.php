<?php

namespace App\Livewire\Web;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GaleriaItem;

class PaginaGaleria extends Component
{
    use WithPagination;

    public $mes_filtro = '';
    public $anio_filtro = '';
    public $tipo_filtro = '';
    public $categoria_filtro = '';

    public function updatedMesFiltro()
    {
        $this->resetPage();
    }

    public function updatedAnioFiltro()
    {
        $this->resetPage();
    }

    public function updatedTipoFiltro()
    {
        $this->resetPage();
    }

    public function updatedCategoriaFiltro()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['mes_filtro', 'anio_filtro', 'tipo_filtro', 'categoria_filtro']);
        $this->resetPage();
    }

    public function render()
    {
        $query = GaleriaItem::query();

        if ($this->tipo_filtro) {
            $query->where('tipo', $this->tipo_filtro);
        }

        if ($this->categoria_filtro) {
            $query->where('categoria', $this->categoria_filtro);
        }

        if ($this->mes_filtro && $this->anio_filtro) {
            $query->whereYear('fecha_evento', $this->anio_filtro)
                  ->whereMonth('fecha_evento', $this->mes_filtro);
        } elseif ($this->anio_filtro) {
            $query->whereYear('fecha_evento', $this->anio_filtro);
        }

        // Muestra las últimas 15 publicaciones por defecto
        $items = $query->latest('fecha_evento')->latest('id')->paginate(15);

        // Años y meses disponibles para el calendario de galerías
        $anios = GaleriaItem::selectRaw('YEAR(fecha_evento) as anio')
            ->whereNotNull('fecha_evento')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        return view('livewire.web.pagina-galeria', [
            'items' => $items,
            'anios' => $anios,
        ])->layout('components.layouts.web', ['title' => 'Galería de Fotos & Videos - Mariachi León']);
    }
}
