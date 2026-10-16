<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Pago;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;

class GestionEventos extends Component
{
    use WithPagination, Auditable;

    public $evento_id = null;
    public $codigo_evento = '';
    public $cliente_id = '';
    public $servicio_id = '';
    public $contacto_evento = '';
    public $telefono_contacto = '';
    public $fecha_evento = '';
    public $hora_evento = '';
    public $duracion_horas = 1.00;
    public $direccion_evento = '';
    public $latitud = null;
    public $longitud = null;
    public $estado = 'Pendiente';
    public $observaciones = '';

    public $search = '';

    #[Url(as: 'cliente_id')]
    public $cliente_filtro = '';

    public $estado_filtro = '';
    public $modalOpen = false;
    public $modalMapOpen = false;
    public $eventoMapa = null;
    public $isEdit = false;

    // Propiedades para modal de Historial de Pagos
    public $modalHistorialPagosOpen = false;
    public $eventoHistorial = null;
    public $pagosHistorial = [];
    public $resumenFinanciero = [];

    // Propiedades para modal de Comisiones y Liquidación a Músicos / Personal
    public $modalComisionesOpen = false;
    public $eventoComisiones = null;
    public $resumenComisiones = [];

    // Modal para Desmarcar / Revertir Comisión
    public $modalDesmarcarOpen = false;
    public $desmarcarParticipanteId = null;
    public $desmarcarParticipante = null;
    public $observacionDesmarcar = '';

    public function mount()
    {
        if (request()->has('cliente_id')) {
            $param = request()->get('cliente_id');
            $decoded = \App\Services\Security\DocumentToken::decodeId($param, 'cliente');
            $this->cliente_filtro = $decoded ?: $param;
        }
    }

    protected function rules()
    {
        return [
            'codigo_evento' => 'required|string|max:50|unique:eventos,codigo_evento,' . ($this->evento_id ?? 'NULL') . ',id',
            'cliente_id' => 'required|exists:clientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'contacto_evento' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:50',
            'fecha_evento' => 'required|date',
            'hora_evento' => 'required',
            'duracion_horas' => 'required|numeric|min:0.5',
            'direccion_evento' => 'required|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'estado' => 'required|in:Pendiente,Cotizado,Reservado,Confirmado,Realizado,Cancelado,Finalizado',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'cliente_id.required' => 'Seleccione un cliente para el evento.',
        'servicio_id.required' => 'Seleccione el servicio a contratar.',
        'fecha_evento.required' => 'La fecha del evento es obligatoria.',
        'hora_evento.required' => 'La hora del evento es obligatoria.',
        'direccion_evento.required' => 'Ingrese la dirección completa del evento.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClienteFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function generarCodigoAutomatico()
    {
        $last = Evento::orderBy('id', 'desc')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        return 'EVT-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    public function abrirModal()
    {
        $this->reset([
            'evento_id', 'codigo_evento', 'cliente_id', 'servicio_id',
            'contacto_evento', 'telefono_contacto', 'fecha_evento', 'hora_evento',
            'duracion_horas', 'direccion_evento', 'latitud', 'longitud', 'observaciones', 'isEdit'
        ]);
        $this->codigo_evento = $this->generarCodigoAutomatico();
        $this->fecha_evento = date('Y-m-d');
        $this->hora_evento = '20:00';
        $this->duracion_horas = 1.00;
        $this->estado = 'Pendiente';
        $this->modalOpen = true;
    }

    public function seleccionarCliente($id)
    {
        $this->cliente_id = $id;
        $this->updatedClienteId($id);
    }

    public function updatedClienteId($value)
    {
        if ($value) {
            $cliente = Cliente::find($value);
            if ($cliente) {
                $this->contacto_evento = $cliente->nombre_completo;
                $this->telefono_contacto = $cliente->telefono ?: $cliente->whatsapp;
                if ($cliente->direccion) {
                    $this->direccion_evento = $cliente->direccion;
                    $this->dispatch('cliente-seleccionado', direccion: $cliente->direccion);
                }
            }
        }
    }

    public function editar($id)
    {
        $e = Evento::findOrFail($id);
        $this->evento_id = $e->id;
        $this->codigo_evento = $e->codigo_evento;
        $this->cliente_id = $e->cliente_id;
        $this->servicio_id = $e->servicio_id;
        $this->contacto_evento = $e->contacto_evento;
        $this->telefono_contacto = $e->telefono_contacto;
        $this->fecha_evento = $e->fecha_evento ? $e->fecha_evento->format('Y-m-d') : '';
        $this->hora_evento = $e->hora_evento;
        $this->duracion_horas = $e->duracion_horas;
        $this->direccion_evento = $e->direccion_evento;
        $this->latitud = $e->latitud;
        $this->longitud = $e->longitud;
        $this->estado = $e->estado;
        $this->observaciones = $e->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function verMapa($id)
    {
        $this->eventoMapa = Evento::with(['cliente', 'servicio'])->findOrFail($id);
        $this->modalMapOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $e = Evento::findOrFail($this->evento_id);
            $e->update([
                'codigo_evento' => strtoupper(trim($this->codigo_evento)),
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'contacto_evento' => trim($this->contacto_evento),
                'telefono_contacto' => trim($this->telefono_contacto),
                'fecha_evento' => $this->fecha_evento,
                'hora_evento' => $this->hora_evento,
                'duracion_horas' => $this->duracion_horas,
                'direccion_evento' => trim($this->direccion_evento),
                'latitud' => $this->latitud ?: null,
                'longitud' => $this->longitud ?: null,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Editar Evento', 'Se actualizó el evento ' . $e->codigo_evento);
            session()->flash('success', 'Evento actualizado correctamente.');
        } else {
            $e = Evento::create([
                'codigo_evento' => strtoupper(trim($this->codigo_evento)),
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'contacto_evento' => trim($this->contacto_evento),
                'telefono_contacto' => trim($this->telefono_contacto),
                'fecha_evento' => $this->fecha_evento,
                'hora_evento' => $this->hora_evento,
                'duracion_horas' => $this->duracion_horas,
                'direccion_evento' => trim($this->direccion_evento),
                'latitud' => $this->latitud ?: null,
                'longitud' => $this->longitud ?: null,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
                'user_id' => Auth::id(),
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Crear Evento', 'Se registró el evento ' . $e->codigo_evento);
            session()->flash('success', 'Evento registrado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $e = Evento::findOrFail($id);
        $e->update(['estado' => $nuevoEstado]);
        $this->registrarAuditoria('Gestión Comercial', 'Cambiar Estado Evento', 'Se cambió estado de evento ' . $e->codigo_evento . ' a ' . $nuevoEstado);
        session()->flash('success', 'Estado del evento ' . $e->codigo_evento . ' actualizado a ' . $nuevoEstado . '.');
    }

    public function abrirHistorialPagos($id)
    {
        $this->eventoHistorial = Evento::with(['cliente', 'servicio', 'contrato'])->findOrFail($id);

        $contrato = $this->eventoHistorial->contrato;
        $montoTotal = $contrato ? (float)$contrato->monto_total : 0;

        $pagos = Pago::where(function ($q) use ($id, $contrato) {
            $q->where('evento_id', $id);
            if ($contrato) {
                $q->orWhere('contrato_id', $contrato->id);
            }
        })
        ->with('user')
        ->orderBy('fecha_pago', 'asc')
        ->get();

        $totalAbonado = (float)$pagos->where('estado', 'Valido')->sum('monto');
        $saldoPendiente = max(0, $montoTotal - $totalAbonado);

        $this->pagosHistorial = $pagos;
        $this->resumenFinanciero = [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'saldo_pendiente' => $saldoPendiente,
            'tiene_contrato' => !empty($contrato),
            'numero_contrato' => $contrato?->numero_contrato,
            'estado_contrato' => $contrato?->estado,
            'contrato_id' => $contrato?->id,
        ];

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Consultar Historial de Pagos Evento',
            "Se consultó el historial de pagos del evento {$this->eventoHistorial->codigo_evento} (Cliente: " . ($this->eventoHistorial->cliente->nombre_completo ?? 'N/A') . ")"
        );

        $this->modalHistorialPagosOpen = true;
    }

    public function cerrarHistorialPagos()
    {
        $this->modalHistorialPagosOpen = false;
        $this->eventoHistorial = null;
        $this->pagosHistorial = [];
        $this->resumenFinanciero = [];
    }

    public function abrirComisiones($eventoId)
    {
        $this->eventoComisiones = Evento::with(['cliente', 'servicio', 'contrato', 'participantes.persona', 'distribuciones'])->findOrFail($eventoId);
        $this->calcularResumenComisiones();
        
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Consultar Comisiones Evento',
            "Se consultó la planilla de comisiones a músicos y personal del evento {$this->eventoComisiones->codigo_evento}"
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
        $query = Evento::with(['cliente', 'servicio', 'user', 'contrato.pagos', 'pagos', 'participantes.persona']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo_evento', 'like', '%' . $this->search . '%')
                  ->orWhere('contacto_evento', 'like', '%' . $this->search . '%')
                  ->orWhere('direccion_evento', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function($c) {
                      $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('servicio', function($s) {
                      $s->where('nombre', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->cliente_filtro) {
            $query->where('cliente_id', $this->cliente_filtro);
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $eventos = $query->orderBy('fecha_evento', 'desc')->orderBy('hora_evento', 'asc')->paginate(10);
        $clientes = Cliente::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('livewire.comercial.gestion-eventos', [
            'eventos' => $eventos,
            'clientes' => $clientes,
            'servicios' => $servicios,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Eventos - Mariachi León Guanajuato']);
    }
}
