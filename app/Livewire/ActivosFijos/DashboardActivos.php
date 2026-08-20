<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetAssignment;
use App\Models\InventoryMovement;

class DashboardActivos extends Component
{
    public function render()
    {
        $totalArticulos = Asset::count();
        $disponibles = Asset::where('estado', 'disponible')->count();
        $asignados = Asset::where('estado', 'asignado')->count();
        $enMantenimiento = Asset::where('estado', 'en_mantenimiento')->count();
        $deteriorados = Asset::where('estado', 'deteriorado')->count();
        $perdidos = Asset::where('estado', 'perdido')->count();
        $dadosDeBaja = Asset::where('estado', 'dado_de_baja')->count();

        // Calcular Valor Total del Inventario
        $valorTotal = Asset::where('estado', '!=', 'dado_de_baja')->get()->sum(function ($asset) {
            if ($asset->tipo_control === 'cantidad') {
                return (float)$asset->existencia * (float)$asset->costo_promedio_ppp;
            }
            return (float)$asset->costo_adquisicion;
        });

        $ultimosMovimientos = InventoryMovement::with(['asset', 'user', 'responsable'])
            ->latest('fecha_movimiento')
            ->latest('id')
            ->take(6)
            ->get();

        $asignacionesRecientes = AssetAssignment::with(['asset', 'responsable'])
            ->latest('fecha_asignacion')
            ->take(5)
            ->get();

        $categorias = AssetCategory::withCount('assets')->get();

        return view('livewire.activos-fijos.dashboard-activos', [
            'totalArticulos' => $totalArticulos,
            'disponibles' => $disponibles,
            'asignados' => $asignados,
            'enMantenimiento' => $enMantenimiento,
            'deteriorados' => $deteriorados,
            'perdidos' => $perdidos,
            'dadosDeBaja' => $dadosDeBaja,
            'valorTotal' => $valorTotal,
            'ultimosMovimientos' => $ultimosMovimientos,
            'asignacionesRecientes' => $asignacionesRecientes,
            'categorias' => $categorias,
        ])->layout('components.layouts.app', ['title' => 'Dashboard - Activos Fijos']);
    }
}
