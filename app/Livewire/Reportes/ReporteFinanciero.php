<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Evento;
use App\Models\Servicio;
use App\Models\MovimientoFinanciero;
use App\Services\Finanzas\FinanzasService;
use App\Traits\Auditable;

class ReporteFinanciero extends Component
{
    use Auditable;

    public $tipo_balance = 'consolidado'; // 'consolidado', 'por_evento', 'gastos_operativos'
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $cliente_id = '';
    public $evento_id = '';
    public $servicio_id = '';
    public $periodo_preset = 'este_mes';

    public function mount()
    {
        $this->aplicarPreset('este_mes');

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Consultar Reporte Financiero y Balances',
            'El usuario accedió al reporte financiero administrativo y balances.'
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
                $this->fecha_inicio = '';
                $this->fecha_fin = '';
                break;
        }
    }

    public function updatedTipoBalance()
    {
        $this->registrarAuditoria(
            'Informes y Reportes',
            'Cambiar Tipo de Balance',
            "Cambio de vista a balance: {$this->tipo_balance}."
        );
    }

    public function render()
    {
        $filtros = [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'cliente_id' => $this->cliente_id,
            'evento_id' => $this->evento_id,
            'servicio_id' => $this->servicio_id,
        ];

        // 1. KPIs Generales con fórmulas oficiales
        $kpis = FinanzasService::calcularKPIs($filtros);

        // 2. Desglose detallado por evento si está en modo 'por_evento'
        $balancesEventos = collect();
        if ($this->tipo_balance === 'por_evento') {
            $qEventos = Evento::with(['cliente', 'servicio', 'contrato.pagos', 'participantes.pagos', 'gastosValidos']);

            if ($this->fecha_inicio) $qEventos->whereDate('fecha_evento', '>=', $this->fecha_inicio);
            if ($this->fecha_fin) $qEventos->whereDate('fecha_evento', '<=', $this->fecha_fin);
            if ($this->cliente_id) $qEventos->where('cliente_id', $this->cliente_id);
            if ($this->evento_id) $qEventos->where('id', $this->evento_id);
            if ($this->servicio_id) $qEventos->where('servicio_id', $this->servicio_id);

            $eventos = $qEventos->orderBy('fecha_evento', 'desc')->get();

            foreach ($eventos as $ev) {
                $montoContratado = $ev->contrato ? (float)$ev->contrato->monto_total : 0.0;
                $totalCobrado = $ev->contrato ? (float)$ev->contrato->pagos->where('estado', 'Valido')->sum('monto') : 0.0;
                $totalAsignado = (float)$ev->participantes->sum('monto_asignado');
                $totalPagadoPart = 0.0;
                foreach ($ev->participantes as $part) {
                    $totalPagadoPart += (float)$part->pagos->where('estado', 'Valido')->sum('monto');
                }
                $gastosEvento = (float)$ev->gastosValidos->sum('monto');
                $balanceBruto = $totalCobrado - $totalAsignado;
                $balanceNeto = $totalCobrado - ($totalAsignado + $gastosEvento);

                $balancesEventos->push([
                    'id' => $ev->id,
                    'codigo_evento' => $ev->codigo_evento,
                    'fecha_evento' => $ev->fecha_evento,
                    'cliente' => $ev->cliente->nombre_completo ?? 'Cliente S/N',
                    'servicio' => $ev->servicio->nombre ?? 'Estándar',
                    'monto_contratado' => $montoContratado,
                    'total_cobrado' => $totalCobrado,
                    'total_asignado' => $totalAsignado,
                    'total_pagado_part' => $totalPagadoPart,
                    'gastos_evento' => $gastosEvento,
                    'balance_bruto' => $balanceBruto,
                    'balance_neto' => $balanceNeto,
                ]);
            }
        }

        // 3. Gastos operativos desglosados si está en modo 'gastos_operativos'
        $gastosOperativos = collect();
        $gastosPorCategoria = collect();
        if ($this->tipo_balance === 'gastos_operativos' || $this->tipo_balance === 'consolidado') {
            $qGastos = MovimientoFinanciero::valido()->operativo()->egresos();
            if ($this->fecha_inicio) $qGastos->where('fecha', '>=', $this->fecha_inicio);
            if ($this->fecha_fin) $qGastos->where('fecha', '<=', $this->fecha_fin);

            $gastosOperativos = (clone $qGastos)->orderBy('fecha', 'desc')->get();

            $gastosPorCategoria = (clone $qGastos)
                ->selectRaw('categoria, count(*) as cantidad, SUM(monto) as total')
                ->groupBy('categoria')
                ->orderByDesc('total')
                ->get();
        }

        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $eventosLista = Evento::orderBy('fecha_evento', 'desc')->limit(60)->get(['id', 'codigo_evento']);
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.reportes.reporte-financiero', [
            'kpis' => $kpis,
            'balancesEventos' => $balancesEventos,
            'gastosOperativos' => $gastosOperativos,
            'gastosPorCategoria' => $gastosPorCategoria,
            'clientes' => $clientes,
            'eventosLista' => $eventosLista,
            'servicios' => $servicios,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'cliente_id' => $this->cliente_id,
            'evento_id' => $this->evento_id,
            'servicio_id' => $this->servicio_id,
            'tipo_balance' => $this->tipo_balance,
            'periodo_preset' => $this->periodo_preset,
        ])->layout('components.layouts.app', ['title' => 'Reporte Financiero & Balances - Mariachi León Guanajuato']);
    }
}
