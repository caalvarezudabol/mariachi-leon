<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Contrato;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Cotizacion;
use App\Models\Pago;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;

class GestionContratos extends Component
{
    use WithPagination, Auditable;

    public $contrato_id = null;
    public $numero_contrato = '';
    public $evento_id = '';
    public $cliente_id = '';
    public $servicio_id = '';
    public $cotizacion_id = '';
    public $fecha_contrato = '';
    public $hora_contrato = '';
    public $monto_total = 0.00;
    public $pago_inicial = 0.00;
    public $total_pagado = 0.00;
    public $saldo_pendiente = 0.00;
    public $estado = 'Pendiente de Confirmacion';
    public $observaciones = '';

    public $search = '';
    public $estado_filtro = '';
    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'numero_contrato' => 'required|string|max:50|unique:contratos,numero_contrato,' . ($this->contrato_id ?? 'NULL') . ',id',
            'evento_id' => 'required|exists:eventos,id',
            'fecha_contrato' => 'required|date',
            'monto_total' => 'required|numeric|min:0',
            'pago_inicial' => 'required|numeric|min:0',
            'estado' => 'required|in:Borrador,Pendiente de Confirmacion,Confirmado,En Ejecucion,Realizado,Cancelado,Finalizado',
            'observaciones' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function generarNumeroAutomatico()
    {
        $last = Contrato::orderBy('id', 'desc')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        return 'CTR-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    public function abrirModal()
    {
        $this->reset([
            'contrato_id', 'numero_contrato', 'evento_id', 'cliente_id', 'servicio_id', 'cotizacion_id',
            'monto_total', 'pago_inicial', 'total_pagado', 'saldo_pendiente', 'observaciones', 'isEdit'
        ]);
        $this->numero_contrato = $this->generarNumeroAutomatico();
        $this->fecha_contrato = date('Y-m-d');
        $this->hora_contrato = date('H:i');
        $this->estado = 'Pendiente de Confirmacion';
        $this->modalOpen = true;
    }

    public function updatedEventoId($value)
    {
        if ($value) {
            $evento = Evento::with(['cliente', 'servicio', 'cotizaciones'])->find($value);
            if ($evento) {
                $this->cliente_id = $evento->cliente_id;
                $this->servicio_id = $evento->servicio_id;
                
                // Si el evento tiene cotización aceptada, la vincula
                $cot = $evento->cotizaciones()->where('estado', 'Aceptada')->first();
                if ($cot) {
                    $this->cotizacion_id = $cot->id;
                    $this->monto_total = $cot->monto_total;
                } else {
                    $this->monto_total = $evento->servicio ? $evento->servicio->precio_base : 0;
                }

                $this->recalcularSaldos();
            }
        }
    }

    public function updatedMontoTotal()
    {
        $this->recalcularSaldos();
    }

    public function updatedPagoInicial()
    {
        $this->recalcularSaldos();
    }

    public function recalcularSaldos()
    {
        $mt = (float)$this->monto_total;
        $pi = (float)$this->pago_inicial;
        
        // No permitir pago inicial mayor al monto total
        if ($pi > $mt) {
            $pi = $mt;
            $this->pago_inicial = $pi;
        }

        $this->total_pagado = $pi;
        $this->saldo_pendiente = max(0, $mt - $pi);
    }

    public function editar($id)
    {
        $c = Contrato::findOrFail($id);
        $this->contrato_id = $c->id;
        $this->numero_contrato = $c->numero_contrato;
        $this->evento_id = $c->evento_id;
        $this->cliente_id = $c->cliente_id;
        $this->servicio_id = $c->servicio_id;
        $this->cotizacion_id = $c->cotizacion_id;
        $this->fecha_contrato = $c->fecha_contrato ? $c->fecha_contrato->format('Y-m-d') : '';
        $this->hora_contrato = $c->hora_contrato;
        $this->monto_total = $c->monto_total;
        $this->pago_inicial = $c->pago_inicial;
        $this->total_pagado = $c->total_pagado;
        $this->saldo_pendiente = $c->saldo_pendiente;
        $this->estado = $c->estado;
        $this->observaciones = $c->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $c = Contrato::findOrFail($this->contrato_id);
            $c->update([
                'numero_contrato' => strtoupper(trim($this->numero_contrato)),
                'evento_id' => $this->evento_id,
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'cotizacion_id' => $this->cotizacion_id ?: null,
                'fecha_contrato' => $this->fecha_contrato,
                'hora_contrato' => $this->hora_contrato,
                'monto_total' => $this->monto_total,
                'pago_inicial' => $this->pago_inicial,
                'total_pagado' => $this->total_pagado,
                'saldo_pendiente' => $this->saldo_pendiente,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Editar Contrato', 'Se actualizó el contrato N° ' . $c->numero_contrato);
            session()->flash('success', 'Contrato actualizado correctamente.');
        } else {
            $c = Contrato::create([
                'numero_contrato' => strtoupper(trim($this->numero_contrato)),
                'evento_id' => $this->evento_id,
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'cotizacion_id' => $this->cotizacion_id ?: null,
                'fecha_contrato' => $this->fecha_contrato,
                'hora_contrato' => $this->hora_contrato,
                'monto_total' => $this->monto_total,
                'pago_inicial' => $this->pago_inicial,
                'total_pagado' => $this->total_pagado,
                'saldo_pendiente' => $this->saldo_pendiente,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
                'user_id' => Auth::id(),
            ]);

            // Si se especificó un pago inicial mayor a cero, genera el primer recibo de pago automáticamente
            if ((float)$this->pago_inicial > 0) {
                $lastPago = Pago::orderBy('id', 'desc')->first();
                $nextRec = $lastPago ? ($lastPago->id + 1) : 1;
                $numRecibo = 'REC-' . str_pad($nextRec, 5, '0', STR_PAD_LEFT);

                Pago::create([
                    'numero_recibo' => $numRecibo,
                    'contrato_id' => $c->id,
                    'cliente_id' => $c->cliente_id,
                    'evento_id' => $c->evento_id,
                    'fecha_pago' => date('Y-m-d H:i:s'),
                    'monto' => $this->pago_inicial,
                    'metodo_pago' => 'EFECTIVO',
                    'observaciones' => 'Pago inicial / Seña al firmar el contrato ' . $c->numero_contrato,
                    'user_id' => Auth::id(),
                ]);
            }

            // Actualiza el evento a Confirmado o Reservado
            $evento = Evento::find($this->evento_id);
            if ($evento) {
                $evento->update(['estado' => 'Confirmado']);
            }

            $this->registrarAuditoria('Gestión Comercial', 'Crear Contrato', 'Se creó el contrato N° ' . $c->numero_contrato);
            session()->flash('success', 'Contrato registrado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function render()
    {
        $query = Contrato::with(['cliente', 'servicio', 'evento', 'pagos']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_contrato', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function($c) {
                      $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('evento', function($e) {
                      $e->where('codigo_evento', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $contratos = $query->orderBy('fecha_contrato', 'desc')->paginate(10);
        $eventos = Evento::with(['cliente', 'servicio'])->orderBy('fecha_evento', 'desc')->get();

        return view('livewire.comercial.gestion-contratos', [
            'contratos' => $contratos,
            'eventos' => $eventos,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Contratos - Mariachi León Guanajuato']);
    }
}
