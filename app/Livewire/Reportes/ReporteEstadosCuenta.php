<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\Empresa;
use App\Traits\Auditable;
use App\Services\Security\DocumentToken;

class ReporteEstadosCuenta extends Component
{
    use Auditable;

    public $modalidad = 'contrato'; // 'contrato' o 'cliente'
    public $cliente_id = '';
    public $contrato_id = '';
    public $search_cliente = '';

    public function mount()
    {
        // Si hay clientes, seleccionar el primero por defecto o dejar en blanco
        $primerCliente = Cliente::has('contratos')->first();
        if ($primerCliente) {
            $this->cliente_id = $primerCliente->id;
            $primerContrato = Contrato::where('cliente_id', $primerCliente->id)->orderBy('fecha_contrato', 'desc')->first();
            if ($primerContrato) {
                $this->contrato_id = $primerContrato->id;
            }
        }

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Consultar Estados de Cuenta',
            'El usuario accedió al módulo interactivo de estados de cuenta de clientes y contratos.'
        );
    }

    public function updatedModalidad()
    {
        if ($this->modalidad === 'contrato' && $this->cliente_id && !$this->contrato_id) {
            $primerContrato = Contrato::where('cliente_id', $this->cliente_id)->orderBy('fecha_contrato', 'desc')->first();
            $this->contrato_id = $primerContrato ? $primerContrato->id : '';
        }
    }

    public function updatedClienteId($val)
    {
        if ($val) {
            $primerContrato = Contrato::where('cliente_id', $val)->orderBy('fecha_contrato', 'desc')->first();
            $this->contrato_id = $primerContrato ? $primerContrato->id : '';
        } else {
            $this->contrato_id = '';
        }
    }

    public function render()
    {
        $clientesQuery = Cliente::withCount('contratos')->orderBy('nombre_completo');
        if ($this->search_cliente) {
            $clientesQuery->where('nombre_completo', 'like', '%' . $this->search_cliente . '%')
                          ->orWhere('ci_nit', 'like', '%' . $this->search_cliente . '%');
        }
        $clientes = $clientesQuery->get();

        $contratosCliente = collect();
        if ($this->cliente_id) {
            $contratosCliente = Contrato::with('servicio', 'evento')
                ->where('cliente_id', $this->cliente_id)
                ->orderBy('fecha_contrato', 'desc')
                ->get();
        }

        $clienteSeleccionado = $this->cliente_id ? Cliente::find($this->cliente_id) : null;
        $contratoSeleccionado = null;
        $pagos = collect();
        $resumenFinanciero = [
            'total_contratado' => 0.0,
            'total_cobrado' => 0.0,
            'saldo_pendiente' => 0.0,
            'estado_liquidacion' => 'Sin datos',
            'cantidad_pagos' => 0,
        ];

        $tokenCliente = $clienteSeleccionado ? DocumentToken::encodeId($clienteSeleccionado->id, 'cliente') : null;
        $tokenContrato = null;

        if ($this->modalidad === 'contrato' && $this->contrato_id) {
            $contratoSeleccionado = Contrato::with(['cliente', 'servicio', 'evento', 'pagos.user'])
                ->find($this->contrato_id);

            if ($contratoSeleccionado) {
                $tokenContrato = DocumentToken::encodeId($contratoSeleccionado->id, 'contrato');
                $montoContratado = (float)$contratoSeleccionado->monto_total;
                $pagosValidos = $contratoSeleccionado->pagos->where('estado', 'Valido');
                $totalCobrado = (float)$pagosValidos->sum('monto');
                $saldo = max(0.0, $montoContratado - $totalCobrado);

                $resumenFinanciero = [
                    'total_contratado' => $montoContratado,
                    'total_cobrado' => $totalCobrado,
                    'saldo_pendiente' => $saldo,
                    'estado_liquidacion' => $saldo <= 0.01 ? 'Liquidado (Completamente Pagado)' : 'Con Saldo Pendiente',
                    'cantidad_pagos' => $pagosValidos->count(),
                ];

                // Pagos ordenados cronológicamente con cálculo de saldo acumulado
                $saldoAcumulado = $montoContratado;
                $pagosOrdenados = $contratoSeleccionado->pagos->sortBy('fecha_pago')->values();
                $pagosConSaldo = collect();

                foreach ($pagosOrdenados as $pago) {
                    if ($pago->estado === 'Valido') {
                        $saldoAcumulado = max(0.0, $saldoAcumulado - (float)$pago->monto);
                    }
                    $pago->saldo_posterior = $saldoAcumulado;
                    $pagosConSaldo->push($pago);
                }

                $pagos = $pagosConSaldo;
            }
        } elseif ($this->modalidad === 'cliente' && $clienteSeleccionado) {
            $todosContratos = Contrato::with('pagos')->where('cliente_id', $clienteSeleccionado->id)->get();
            $totalContratado = (float)$todosContratos->sum('monto_total');
            $todosPagos = Pago::with(['contrato', 'user'])
                ->where('cliente_id', $clienteSeleccionado->id)
                ->orderBy('fecha_pago', 'asc')
                ->get();

            $totalCobrado = (float)$todosPagos->where('estado', 'Valido')->sum('monto');
            $saldo = max(0.0, $totalContratado - $totalCobrado);

            $resumenFinanciero = [
                'total_contratado' => $totalContratado,
                'total_cobrado' => $totalCobrado,
                'saldo_pendiente' => $saldo,
                'estado_liquidacion' => $saldo <= 0.01 ? 'Liquidado Totalmente' : 'Con Saldo Pendiente Global',
                'cantidad_pagos' => $todosPagos->where('estado', 'Valido')->count(),
            ];

            $saldoAcumulado = $totalContratado;
            $pagosConSaldo = collect();
            foreach ($todosPagos as $pago) {
                if ($pago->estado === 'Valido') {
                    $saldoAcumulado = max(0.0, $saldoAcumulado - (float)$pago->monto);
                }
                $pago->saldo_posterior = $saldoAcumulado;
                $pagosConSaldo->push($pago);
            }

            $pagos = $pagosConSaldo;
        }

        $empresa = Empresa::obtener();

        return view('livewire.reportes.reporte-estados-cuenta', [
            'clientes' => $clientes,
            'contratosCliente' => $contratosCliente,
            'clienteSeleccionado' => $clienteSeleccionado,
            'contratoSeleccionado' => $contratoSeleccionado,
            'pagos' => $pagos,
            'resumenFinanciero' => $resumenFinanciero,
            'empresa' => $empresa,
            'tokenCliente' => $tokenCliente,
            'tokenContrato' => $tokenContrato,
        ])->layout('components.layouts.app', ['title' => 'Estados de Cuenta - Mariachi León Guanajuato']);
    }
}
