<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pago;
use App\Models\Contrato;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionPagos extends Component
{
    use WithPagination, Auditable;

    public $pago_id = null;
    public $numero_recibo = '';
    public $contrato_id = '';
    public $fecha_pago = '';
    public $monto = 0.00;
    public $metodo_pago = 'EFECTIVO';
    public $comprobante_referencia = '';
    public $observaciones = '';

    public $search = '';
    public $metodo_filtro = '';
    public $modalOpen = false;

    protected function rules()
    {
        return [
            'numero_recibo' => 'required|string|max:50|unique:pagos,numero_recibo,' . ($this->pago_id ?? 'NULL') . ',id',
            'contrato_id' => 'required|exists:contratos,id',
            'fecha_pago' => 'required',
            'monto' => 'required|numeric|gt:0',
            'metodo_pago' => 'required|in:EFECTIVO,QR,TRANSFERENCIA',
            'comprobante_referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function generarNumeroAutomatico()
    {
        $last = Pago::orderBy('id', 'desc')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        return 'REC-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    public function abrirModal()
    {
        $this->reset(['pago_id', 'numero_recibo', 'contrato_id', 'monto', 'comprobante_referencia', 'observaciones']);
        $this->numero_recibo = $this->generarNumeroAutomatico();
        $this->fecha_pago = date('Y-m-d H:i');
        $this->metodo_pago = 'EFECTIVO';
        $this->modalOpen = true;
    }

    public function updatedContratoId($value)
    {
        if ($value) {
            $contrato = Contrato::find($value);
            if ($contrato) {
                // Sugiere por defecto el saldo pendiente como monto a pagar
                $this->monto = $contrato->saldo_pendiente;
            }
        }
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            $contrato = Contrato::lockForUpdate()->findOrFail($this->contrato_id);
            $montoPago = (float)$this->monto;

            if ($montoPago > (float)$contrato->saldo_pendiente) {
                $this->addError('monto', 'El monto a pagar (Bs ' . number_format($montoPago, 2) . ') no puede ser mayor al saldo pendiente del contrato (Bs ' . number_format($contrato->saldo_pendiente, 2) . ').');
                return;
            }

            // Registrar Pago
            $pago = Pago::create([
                'numero_recibo' => strtoupper(trim($this->numero_recibo)),
                'contrato_id' => $contrato->id,
                'cliente_id' => $contrato->cliente_id,
                'evento_id' => $contrato->evento_id,
                'fecha_pago' => $this->fecha_pago,
                'monto' => $montoPago,
                'metodo_pago' => $this->metodo_pago,
                'comprobante_referencia' => trim($this->comprobante_referencia),
                'observaciones' => trim($this->observaciones),
                'user_id' => Auth::id(),
            ]);

            // Actualizar saldos del contrato
            $nuevoTotalPagado = (float)$contrato->total_pagado + $montoPago;
            $nuevoSaldo = max(0, (float)$contrato->monto_total - $nuevoTotalPagado);

            $contrato->update([
                'total_pagado' => $nuevoTotalPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => ($nuevoSaldo <= 0) ? 'Confirmado' : $contrato->estado,
            ]);

            $this->registrarAuditoria('Gestión Comercial', 'Registrar Pago Cliente', 'Se registró el pago recibo N° ' . $pago->numero_recibo . ' por Bs ' . number_format($montoPago, 2) . ' para el contrato N° ' . $contrato->numero_contrato);

            session()->flash('success', 'Pago de cliente registrado exitosamente.');
            $this->modalOpen = false;
        });
    }

    public function render()
    {
        $query = Pago::with(['contrato', 'cliente', 'evento', 'user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_recibo', 'like', '%' . $this->search . '%')
                  ->orWhere('comprobante_referencia', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function($c) {
                      $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('contrato', function($ct) {
                      $ct->where('numero_contrato', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->metodo_filtro) {
            $query->where('metodo_pago', $this->metodo_filtro);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')->paginate(10);
        $contratosPendientes = Contrato::with(['cliente', 'evento'])
            ->where('saldo_pendiente', '>', 0)
            ->orderBy('numero_contrato', 'asc')
            ->get();

        return view('livewire.comercial.gestion-pagos', [
            'pagos' => $pagos,
            'contratosPendientes' => $contratosPendientes,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Pagos - Mariachi León Guanajuato']);
    }
}
