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

    // Modal de Pagos del Cliente por Evento
    public $modalHistorialPagosOpen = false;
    public $contratoHistorial = null;
    public $pagosHistorial = [];
    public $resumenFinanciero = [];

    // Modal de Comisiones de Músicos y Personal por Evento
    public $modalComisionesOpen = false;
    public $eventoComisiones = null;
    public $resumenComisiones = [];

    // Modal para Desmarcar / Revertir Comisión
    public $modalDesmarcarOpen = false;
    public $desmarcarParticipanteId = null;
    public $desmarcarParticipante = null;
    public $observacionDesmarcar = '';

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

    public function abrirHistorialPagos($id)
    {
        $this->contratoHistorial = Contrato::with(['cliente', 'servicio', 'evento', 'pagos.user'])->findOrFail($id);

        $montoTotal = (float)$this->contratoHistorial->monto_total;
        
        $pagos = Pago::where(function ($q) {
            $q->where('contrato_id', $this->contratoHistorial->id);
            if ($this->contratoHistorial->evento_id) {
                $q->orWhere('evento_id', $this->contratoHistorial->evento_id);
            }
        })
        ->with('user')
        ->orderBy('fecha_pago', 'asc')
        ->get();

        $totalAbonado = (float)$pagos->where('estado', 'Valido')->sum('monto');
        $saldoPendiente = max(0.0, $montoTotal - $totalAbonado);

        $this->pagosHistorial = $pagos;
        $this->resumenFinanciero = [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'saldo_pendiente' => $saldoPendiente,
            'numero_contrato' => $this->contratoHistorial->numero_contrato,
            'codigo_evento' => $this->contratoHistorial->evento?->codigo_evento ?? 'S/E',
            'estado_contrato' => $this->contratoHistorial->estado,
            'codigo_verificacion' => $this->contratoHistorial->codigo_verificacion,
            'cliente_id' => $this->contratoHistorial->cliente_id,
        ];

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Consultar Pagos de Contrato',
            "Se consultó el historial de pagos y contrato N° {$this->contratoHistorial->numero_contrato} (Evento: {$this->resumenFinanciero['codigo_evento']}, Cliente: " . ($this->contratoHistorial->cliente->nombre_completo ?? 'N/A') . ")"
        );

        $this->modalHistorialPagosOpen = true;
    }

    public function cerrarHistorialPagos()
    {
        $this->modalHistorialPagosOpen = false;
        $this->contratoHistorial = null;
        $this->pagosHistorial = [];
        $this->resumenFinanciero = [];
    }

    public function abrirComisiones($contratoId)
    {
        $contrato = Contrato::with(['evento.cliente', 'evento.servicio', 'evento.contrato', 'evento.participantes.persona', 'evento.distribuciones'])->findOrFail($contratoId);

        if (!$contrato->evento) {
            session()->flash('error', 'Este contrato no tiene un evento vinculado.');
            return;
        }

        $this->eventoComisiones = $contrato->evento;
        $this->calcularResumenComisiones();

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Consultar Comisiones desde Contrato',
            "Se consultaron comisiones de músicos del contrato {$contrato->numero_contrato} (Evento: {$this->eventoComisiones->codigo_evento})"
        );

        $this->modalComisionesOpen = true;
    }

    public function calcularResumenComisiones()
    {
        if (!$this->eventoComisiones) return;

        $montoEvento = $this->eventoComisiones->contrato 
            ? (float)$this->eventoComisiones->contrato->monto_total 
            : (float)($this->eventoComisiones->monto_total ?? 0);

        $participantes = $this->eventoComisiones->participantes;
        $totalComisiones = (float)$participantes->sum('monto_asignado');

        // Sumar como pagado solo los que están en estado Distribuido
        $totalPagado = (float)$participantes->where('estado_distribucion', 'Distribuido')->sum('monto_asignado');
        $totalPendiente = max(0.0, $totalComisiones - $totalPagado);
        $margenGrupo = $montoEvento - $totalComisiones;

        $token = \App\Services\Security\DocumentToken::encodeId($this->eventoComisiones->id, 'evento');

        $this->resumenComisiones = [
            'monto_evento' => $montoEvento,
            'total_comisiones' => $totalComisiones,
            'total_pagado' => $totalPagado,
            'total_pendiente' => $totalPendiente,
            'margen_grupo' => $margenGrupo,
            'token' => $token,
            'count_participantes' => $participantes->count(),
            'count_pagados' => $participantes->where('estado_distribucion', 'Distribuido')->count(),
            'count_pendientes' => $participantes->where('estado_distribucion', '!=', 'Distribuido')->count(),
        ];
    }

    public function solicitarDesmarcarComision($participanteId)
    {
        $this->desmarcarParticipante = \App\Models\EventoParticipante::with(['persona', 'pagosValidos'])->findOrFail($participanteId);
        $this->desmarcarParticipanteId = $this->desmarcarParticipante->id;
        $this->observacionDesmarcar = '';
        $this->resetValidation('observacionDesmarcar');
        $this->modalDesmarcarOpen = true;
    }

    public function cerrarModalDesmarcar()
    {
        $this->modalDesmarcarOpen = false;
        $this->desmarcarParticipanteId = null;
        $this->desmarcarParticipante = null;
        $this->observacionDesmarcar = '';
        $this->resetValidation('observacionDesmarcar');
    }

    public function confirmarDesmarcarComision()
    {
        $this->validate([
            'observacionDesmarcar' => 'required|string|min:4|max:500',
        ], [
            'observacionDesmarcar.required' => 'Debe ingresar una observación o motivo para desmarcar la comisión.',
            'observacionDesmarcar.min' => 'El motivo debe tener al menos 4 caracteres.',
            'observacionDesmarcar.max' => 'El motivo no debe exceder 500 caracteres.',
        ]);

        $part = \App\Models\EventoParticipante::with(['persona', 'pagosValidos'])->findOrFail($this->desmarcarParticipanteId);

        \Illuminate\Support\Facades\DB::transaction(function () use ($part) {
            // Revertir todos los pagos válidos de este participante en este evento
            $pagos = \App\Models\PagoParticipante::where('evento_participante_id', $part->id)
                ->where('estado', 'Valido')
                ->get();

            $montoRevertido = 0;
            foreach ($pagos as $p) {
                $p->update([
                    'estado' => 'Revertido',
                    'motivo_reversion' => trim($this->observacionDesmarcar),
                    'fecha_reversion' => now(),
                    'revertido_por' => Auth::id(),
                ]);
                $montoRevertido += (float)$p->monto;
            }

            // Cambiar estado a Pendiente en EventoParticipante y DistribucionEconomica
            $part->update(['estado_distribucion' => 'Pendiente']);

            \App\Models\DistribucionEconomica::where('evento_participante_id', $part->id)
                ->update(['estado' => 'Pendiente']);

            $personaNombre = $part->persona->nombre_completo ?? 'Participante';
            $eventoCodigo = $this->eventoComisiones->codigo_evento ?? 'Evento';
            $contratoRef = $this->eventoComisiones->contrato?->numero_contrato 
                ? " (Contrato: {$this->eventoComisiones->contrato->numero_contrato})" 
                : '';

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Desmarcar Comisión Músico',
                "Se desmarcó comisión y revirtió pago de {$personaNombre} en evento {$eventoCodigo}{$contratoRef}. Comisión: Bs. " . number_format($part->monto_asignado, 2) . " (Monto revertido en pagos: Bs. " . number_format($montoRevertido, 2) . "). Observación: " . trim($this->observacionDesmarcar)
            );
        });

        $personaNombre = $part->persona->nombre_completo ?? 'Participante';
        $this->cerrarModalDesmarcar();

        // Recargar evento y actualizar resumen de comisiones
        $this->eventoComisiones = Evento::with(['cliente', 'servicio', 'contrato', 'participantes.persona', 'participantes.pagos', 'distribuciones'])->findOrFail($this->eventoComisiones->id);
        $this->calcularResumenComisiones();

        session()->flash('comision_success', "La comisión de {$personaNombre} ha sido desmarcada y su pago fue revertido exitosamente.");
    }

    public function marcarComisionPagada($participanteId)
    {
        $part = \App\Models\EventoParticipante::with(['persona', 'pagosValidos'])->findOrFail($participanteId);

        \Illuminate\Support\Facades\DB::transaction(function () use ($part) {
            $part->update(['estado_distribucion' => 'Distribuido']);

            // Sincronizar o crear distribucion_economica
            $dist = \App\Models\DistribucionEconomica::where('evento_participante_id', $part->id)->first();
            if ($dist) {
                $dist->update(['estado' => 'Distribuido']);
            } else {
                $dist = \App\Models\DistribucionEconomica::create([
                    'evento_id' => $part->evento_id,
                    'contrato_id' => optional($this->eventoComisiones->contrato)->id,
                    'persona_id' => $part->persona_id,
                    'evento_participante_id' => $part->id,
                    'funcion' => $part->funcion,
                    'concepto' => $part->concepto_pago,
                    'monto' => $part->monto_asignado,
                    'fecha_distribucion' => now(),
                    'estado' => 'Distribuido',
                    'user_id' => Auth::id(),
                ]);
            }

            // Sincronizar pagos_participantes si no tiene pagos válidos
            $pagadoPrevio = (float)$part->pagosValidos()->sum('monto');
            $saldoRestante = max(0.0, (float)$part->monto_asignado - $pagadoPrevio);
            if ($saldoRestante > 0) {
                \App\Models\PagoParticipante::create([
                    'evento_participante_id' => $part->id,
                    'distribucion_economica_id' => $dist->id,
                    'evento_id' => $part->evento_id,
                    'persona_id' => $part->persona_id,
                    'monto' => $saldoRestante,
                    'metodo_pago' => 'Efectivo',
                    'glosa' => "Liquidación de {$part->concepto_pago} a {$part->persona->nombre_completo}",
                    'observaciones' => 'Marcado como pagado desde módulo de comisiones',
                    'fecha_pago' => now(),
                    'estado' => 'Valido',
                    'user_id' => Auth::id(),
                ]);
            }

            $personaNombre = $part->persona->nombre_completo ?? 'Participante';
            $eventoCodigo = $this->eventoComisiones->codigo_evento ?? 'Evento';
            $contratoRef = $this->eventoComisiones->contrato?->numero_contrato 
                ? " (Contrato: {$this->eventoComisiones->contrato->numero_contrato})" 
                : '';

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Marcar Comisión Pagada',
                "Se marcó como pagada la comisión de {$personaNombre} por Bs. " . number_format($part->monto_asignado, 2) . " en el evento {$eventoCodigo}{$contratoRef}."
            );
        });

        $personaNombre = $part->persona->nombre_completo ?? 'Participante';
        $this->eventoComisiones = Evento::with(['cliente', 'servicio', 'contrato', 'participantes.persona', 'participantes.pagos', 'distribuciones'])->findOrFail($this->eventoComisiones->id);
        $this->calcularResumenComisiones();

        session()->flash('comision_success', "La comisión de {$personaNombre} fue marcada como pagada correctamente.");
    }

    public function cambiarEstadoComision($participanteId)
    {
        $part = \App\Models\EventoParticipante::findOrFail($participanteId);
        if ($part->estado_distribucion === 'Distribuido') {
            $this->solicitarDesmarcarComision($participanteId);
        } else {
            $this->marcarComisionPagada($participanteId);
        }
    }

    public function cerrarComisiones()
    {
        $this->modalComisionesOpen = false;
        $this->eventoComisiones = null;
        $this->resumenComisiones = [];
        $this->cerrarModalDesmarcar();
    }

    public function render()
    {
        $query = Contrato::with(['cliente', 'servicio', 'evento.participantes.persona', 'pagos']);

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
