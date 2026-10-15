<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pago;
use App\Models\Contrato;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
    public $estado_filtro = '';
    public $modalOpen = false;

    // Modal de Anulación
    public $modalAnularOpen = false;
    public $pagoIdAnular = null;
    public $pagoParaAnular = null;
    public $motivoAnulacion = '';
    public $ultimoRecibo = null;

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

            // Registrar Pago (Inmutable)
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
                'codigo_verificacion' => Str::random(40),
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            // Actualizar saldos del contrato
            $nuevoTotalPagado = (float)$contrato->total_pagado + $montoPago;
            $nuevoSaldo = max(0, (float)$contrato->monto_total - $nuevoTotalPagado);
            $nuevoEstado = $contrato->estado;
            if (in_array($contrato->estado, ['Borrador', 'Pendiente de Confirmacion'])) {
                $nuevoEstado = 'Confirmado';
            }

            $contrato->update([
                'total_pagado' => $nuevoTotalPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoEstado,
            ]);

            if ($contrato->evento && $contrato->evento->estado === 'Reservado' && $nuevoTotalPagado > 0) {
                $contrato->evento->update(['estado' => 'Confirmado']);
            }

            $this->registrarAuditoria('Gestión Comercial', 'Registrar Pago Cliente', 'Se registró el pago recibo N° ' . $pago->numero_recibo . ' por Bs ' . number_format($montoPago, 2) . ' para el contrato N° ' . $contrato->numero_contrato);

            $this->ultimoRecibo = [
                'id' => $pago->id,
                'numero' => $pago->numero_recibo,
                'monto' => $pago->monto,
            ];

            session()->flash('success_pago', 'Pago ' . $pago->numero_recibo . ' registrado exitosamente por Bs ' . number_format($montoPago, 2) . '.');
            $this->modalOpen = false;
        });
    }

    public function abrirModalAnular($id)
    {
        $user = Auth::user();
        if (!$user->can('gestionar-pagos') && !$user->hasRole('Administrador')) {
            session()->flash('error', 'No tiene los permisos requeridos (gestionar-pagos) para anular pagos.');
            return;
        }

        $pago = Pago::with(['evento', 'contrato.evento', 'cliente'])->findOrFail($id);

        if ($pago->estado === 'Anulado') {
            session()->flash('error', 'Este pago ya se encuentra anulado.');
            return;
        }

        $evento = $pago->evento ?: ($pago->contrato ? $pago->contrato->evento : null);
        if ($evento && in_array($evento->estado, ['Realizado', 'Finalizado', 'Concluido'])) {
            session()->flash('error', 'No se puede anular el pago porque el evento (' . $evento->codigo_evento . ') ya ha sido concluido/realizado.');
            return;
        }

        $this->pagoIdAnular = $pago->id;
        $this->pagoParaAnular = $pago;
        $this->motivoAnulacion = '';
        $this->modalAnularOpen = true;
    }

    public function confirmarAnulacion()
    {
        $user = Auth::user();
        if (!$user->can('gestionar-pagos') && !$user->hasRole('Administrador')) {
            session()->flash('error', 'No tiene los permisos requeridos para anular pagos.');
            $this->modalAnularOpen = false;
            return;
        }

        $this->validate([
            'motivoAnulacion' => 'required|string|min:5|max:255',
        ], [
            'motivoAnulacion.required' => 'Debe ingresar obligatoriamente un motivo para anular el pago.',
            'motivoAnulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        DB::transaction(function () {
            $pago = Pago::lockForUpdate()->findOrFail($this->pagoIdAnular);

            $evento = $pago->evento ?: ($pago->contrato ? $pago->contrato->evento : null);
            if ($evento && in_array($evento->estado, ['Realizado', 'Finalizado', 'Concluido'])) {
                session()->flash('error', 'No se puede anular el pago porque el evento ya concluyó.');
                $this->modalAnularOpen = false;
                return;
            }

            $pago->update([
                'estado' => 'Anulado',
                'motivo_anulacion' => trim($this->motivoAnulacion),
                'fecha_anulacion' => now(),
                'anulado_por' => Auth::id(),
            ]);

            // Revertir saldos en el contrato
            $contrato = $pago->contrato;
            if ($contrato) {
                $nuevoTotalPagado = max(0, (float)$contrato->total_pagado - (float)$pago->monto);
                $nuevoSaldo = min((float)$contrato->monto_total, (float)$contrato->saldo_pendiente + (float)$pago->monto);
                $nuevoEstado = $contrato->estado;
                if ($nuevoTotalPagado <= 0 && $contrato->estado === 'Confirmado') {
                    $nuevoEstado = 'Pendiente de Confirmacion';
                }

                $contrato->update([
                    'total_pagado' => $nuevoTotalPagado,
                    'saldo_pendiente' => $nuevoSaldo,
                    'estado' => $nuevoEstado,
                ]);
            }

            // Registrar en Bitácora del sistema
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Anular Pago Cliente',
                'Se anuló el recibo de pago N° ' . $pago->numero_recibo . ' por Bs ' . number_format($pago->monto, 2) . ' del cliente ' . ($pago->cliente ? $pago->cliente->nombre_completo : 'N/A') . '. Motivo: ' . $this->motivoAnulacion . '. Anulado por: ' . Auth::user()->name
            );

            session()->flash('success', 'El pago ' . $pago->numero_recibo . ' ha sido anulado correctamente y los saldos del contrato fueron revertidos.');
            $this->modalAnularOpen = false;
        });
    }

    public function render()
    {
        $query = Pago::with(['contrato', 'cliente', 'evento', 'user', 'anuladoPor']);

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

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
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
