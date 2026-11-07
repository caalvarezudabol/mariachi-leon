<?php

namespace App\Livewire\Finanzas;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Evento;
use App\Models\Servicio;
use App\Services\Finanzas\FinanzasService;
use App\Traits\Auditable;

class DashboardFinanciero extends Component
{
    use Auditable;

    public $fecha_inicio;
    public $fecha_fin;
    public $periodo_preset = 'este_mes';
    public $cliente_id = '';
    public $evento_id = '';
    public $servicio_id = '';

    public function mount()
    {
        $this->aplicarPreset('este_mes');

        $this->registrarAuditoria(
            'Gestión Financiera',
            'Consultar Dashboard Financiero',
            'Se consultó el dashboard financiero administrativo con período: ' . $this->periodo_preset
        );
    }

    public function aplicarPreset($preset)
    {
        $this->periodo_preset = $preset;

        switch ($preset) {
            case 'este_mes':
                $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
                $this->fecha_fin = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'mes_anterior':
                $this->fecha_inicio = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fecha_fin = now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'este_ano':
                $this->fecha_inicio = now()->startOfYear()->format('Y-m-d');
                $this->fecha_fin = now()->endOfYear()->format('Y-m-d');
                break;
            case 'todo':
                $this->fecha_inicio = null;
                $this->fecha_fin = null;
                break;
            case 'personalizado':
                // Conserva los valores actuales
                break;
        }
    }

    // Si se edita una fecha a mano, el período pasa a "personalizado"
    public function updated($property)
    {
        if (in_array($property, ['fecha_inicio', 'fecha_fin'])) {
            $this->periodo_preset = 'personalizado';
        }
    }

    public function limpiarFiltros()
    {
        $this->cliente_id = '';
        $this->evento_id = '';
        $this->servicio_id = '';
        $this->aplicarPreset('este_mes');
    }

    public function render()
    {
        // Los indicadores se calculan en cada render, ya no viajan como datos públicos al navegador
        $filtros = [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'cliente_id' => $this->cliente_id ?: null,
            'evento_id' => $this->evento_id ?: null,
            'servicio_id' => $this->servicio_id ?: null,
            'metodo_pago' => null,
        ];

        $kpis = FinanzasService::calcularKPIs($filtros);
        $graficos = FinanzasService::obtenerDatosGraficos($filtros);

        $clientes = Cliente::orderBy('nombre_completo', 'asc')->get(['id', 'nombre_completo']);
        $eventos = Evento::with('cliente:id,nombre_completo')
            ->orderBy('fecha_evento', 'desc')
            ->limit(50)
            ->get(['id', 'codigo_evento', 'cliente_id']);
        $servicios = Servicio::where('activo', true)->orderBy('nombre', 'asc')->get(['id', 'nombre']);

        return view('livewire.finanzas.dashboard-financiero', [
            'kpis' => $kpis,
            'graficos' => $graficos,
            'clientes' => $clientes,
            'eventos' => $eventos,
            'servicios' => $servicios,
        ])->layout('components.layouts.app', [
            'title' => 'Dashboard Financiero - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}
