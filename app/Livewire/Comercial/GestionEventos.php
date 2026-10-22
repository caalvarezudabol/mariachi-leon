<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Pago;
use App\Models\ServicioParticipante;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use App\Models\DistribucionEconomica;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    // =========================================================================
    // PLANTILLA Y SUGERENCIAS DE PARTICIPANTES EN EVENTOS
    // =========================================================================
    public $participantesSugeridos = [];
    public $servicio_anterior_id = null;
    public $modalCambioServicioOpen = false;
    public $servicioNuevoPendiente = null;

    // Sub-modal para agregar participantes adicionales (fuera de la plantilla)
    public $mostrarModalAgregarParticipante = false;
    public $nuevo_part_persona_id = '';
    public $nuevo_part_funcion = 'Músico';
    public $nuevo_part_concepto = 'Comisión por Evento';
    public $nuevo_part_tipo_calculo = 'monto_fijo';
    public $nuevo_part_valor = 0.00;
    public $nuevo_part_observaciones = '';

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
            'duracion_horas', 'direccion_evento', 'latitud', 'longitud', 'observaciones', 'isEdit',
            'participantesSugeridos', 'servicio_anterior_id', 'modalCambioServicioOpen',
            'servicioNuevoPendiente', 'mostrarModalAgregarParticipante'
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

    public function updatedServicioId($value)
    {
        if (empty($value)) {
            $this->participantesSugeridos = [];
            $this->servicio_anterior_id = null;
            return;
        }

        // Si ya teníamos un servicio y lista de participantes y el nuevo servicio es diferente:
        if ($this->servicio_anterior_id && $this->servicio_anterior_id != $value && count($this->participantesSugeridos) > 0) {
            $this->servicioNuevoPendiente = $value;
            $this->modalCambioServicioOpen = true;
        } else {
            $this->servicio_anterior_id = $value;
            $this->cargarPlantillaServicio($value);
        }
    }

    public function confirmarCambioServicio()
    {
        $this->servicio_id = $this->servicioNuevoPendiente;
        $this->servicio_anterior_id = $this->servicioNuevoPendiente;
        $this->cargarPlantillaServicio($this->servicio_id, true); // true = preserva los que ya tengan pagos
        $this->modalCambioServicioOpen = false;
        $this->servicioNuevoPendiente = null;
        session()->flash('participante_success', 'Plantilla del nuevo servicio cargada exitosamente.');
    }

    public function cancelarCambioServicio()
    {
        $this->servicio_id = $this->servicio_anterior_id;
        $this->modalCambioServicioOpen = false;
        $this->servicioNuevoPendiente = null;
    }

    public function cargarPlantillaServicio($servicioId, $preservePaid = false)
    {
        $srv = Servicio::with(['participantesActivos.persona'])->find($servicioId);
        if (!$srv) {
            if (!$preservePaid) $this->participantesSugeridos = [];
            return;
        }

        $precioBase = (float)$srv->precio_base;

        if ($preservePaid) {
            $preservados = array_filter($this->participantesSugeridos, function($item) {
                return !empty($item['tiene_pagos']);
            });
            $preservadosPersonaIds = array_column($preservados, 'persona_id');
            $nuevaLista = array_values($preservados);

            foreach ($srv->participantesActivos as $sp) {
                if (in_array($sp->persona_id, $preservadosPersonaIds)) {
                    continue;
                }
                $montoSugerido = $sp->calcularMontoSugerido($precioBase);
                $nuevaLista[] = [
                    'persona_id' => $sp->persona_id,
                    'nombre_completo' => $sp->persona->nombre_completo ?? 'N/A',
                    'ci_nit' => $sp->persona->ci_nit ?? '',
                    'funcion' => $sp->funcion,
                    'concepto_pago' => $sp->concepto_pago,
                    'tipo_calculo' => $sp->tipo_calculo,
                    'valor_sugerido' => (float)$sp->valor,
                    'monto_sugerido' => $montoSugerido,
                    'monto_asignado' => $montoSugerido,
                    'incluido' => true,
                    'origen' => 'plantilla',
                    'tiene_pagos' => false,
                    'total_pagado' => 0.0,
                    'evento_participante_id' => null,
                ];
            }
            $this->participantesSugeridos = $nuevaLista;
        } else {
            $this->participantesSugeridos = [];
            foreach ($srv->participantesActivos as $sp) {
                $montoSugerido = $sp->calcularMontoSugerido($precioBase);
                $this->participantesSugeridos[] = [
                    'persona_id' => $sp->persona_id,
                    'nombre_completo' => $sp->persona->nombre_completo ?? 'N/A',
                    'ci_nit' => $sp->persona->ci_nit ?? '',
                    'funcion' => $sp->funcion,
                    'concepto_pago' => $sp->concepto_pago,
                    'tipo_calculo' => $sp->tipo_calculo,
                    'valor_sugerido' => (float)$sp->valor,
                    'monto_sugerido' => $montoSugerido,
                    'monto_asignado' => $montoSugerido,
                    'incluido' => true,
                    'origen' => 'plantilla',
                    'tiene_pagos' => false,
                    'total_pagado' => 0.0,
                    'evento_participante_id' => null,
                ];
            }
        }
    }

    public function toggleIncluidoParticipante($index)
    {
        if (!isset($this->participantesSugeridos[$index])) return;

        $item = $this->participantesSugeridos[$index];
        if ($item['incluido'] && !empty($item['tiene_pagos'])) {
            session()->flash('participante_error', "No se puede excluir a {$item['nombre_completo']} porque ya registra pagos confirmados en este evento (Pagado: Bs. " . number_format($item['total_pagado'], 2) . ").");
            return;
        }

        $this->participantesSugeridos[$index]['incluido'] = !$this->participantesSugeridos[$index]['incluido'];
    }

    public function quitarParticipante($index)
    {
        if (!isset($this->participantesSugeridos[$index])) return;

        $item = $this->participantesSugeridos[$index];
        if (!empty($item['tiene_pagos'])) {
            session()->flash('participante_error', "No se puede quitar a {$item['nombre_completo']} porque ya registra pagos confirmados en este evento.");
            return;
        }

        if ($item['origen'] === 'adicional' && empty($item['evento_participante_id'])) {
            array_splice($this->participantesSugeridos, $index, 1);
        } else {
            $this->participantesSugeridos[$index]['incluido'] = false;
        }
    }

    public function abrirModalAgregarParticipante()
    {
        $this->reset([
            'nuevo_part_persona_id', 'nuevo_part_valor', 'nuevo_part_observaciones'
        ]);
        $this->nuevo_part_funcion = 'Músico';
        $this->nuevo_part_concepto = 'Comisión por Evento';
        $this->nuevo_part_tipo_calculo = 'monto_fijo';
        $this->nuevo_part_valor = 0.00;
        $this->mostrarModalAgregarParticipante = true;
    }

    public function cerrarModalAgregarParticipante()
    {
        $this->mostrarModalAgregarParticipante = false;
    }

    public function updatedNuevoPartPersonaId($value)
    {
        if ($value) {
            $persona = MusicoPersonal::find($value);
            if ($persona && $persona->tipo) {
                $this->nuevo_part_funcion = $persona->tipo;
            }
        }
    }

    public function agregarParticipanteAdicional()
    {
        $this->validate([
            'nuevo_part_persona_id' => 'required|exists:musicos_personal,id',
            'nuevo_part_funcion' => 'required|string|max:100',
            'nuevo_part_concepto' => 'required|string|max:100',
            'nuevo_part_tipo_calculo' => 'required|in:monto_fijo,porcentaje',
            'nuevo_part_valor' => 'required|numeric|min:0' . ($this->nuevo_part_tipo_calculo === 'porcentaje' ? '|max:100' : ''),
        ], [
            'nuevo_part_persona_id.required' => 'Seleccione una persona del catálogo.',
            'nuevo_part_valor.min' => 'El monto o porcentaje no puede ser negativo.',
        ]);

        $persona = MusicoPersonal::findOrFail($this->nuevo_part_persona_id);

        // Verificar si ya existe en la lista
        foreach ($this->participantesSugeridos as $idx => $item) {
            if ($item['persona_id'] == $persona->id) {
                if (!$item['incluido']) {
                    $this->participantesSugeridos[$idx]['incluido'] = true;
                    $this->participantesSugeridos[$idx]['funcion'] = $this->nuevo_part_funcion;
                    $this->participantesSugeridos[$idx]['concepto_pago'] = $this->nuevo_part_concepto;
                    $this->participantesSugeridos[$idx]['tipo_calculo'] = $this->nuevo_part_tipo_calculo;
                    $this->participantesSugeridos[$idx]['valor_sugerido'] = (float)$this->nuevo_part_valor;
                    $this->mostrarModalAgregarParticipante = false;
                    session()->flash('participante_success', "Se reincorporó a {$persona->nombre_completo} a la distribución.");
                    return;
                } else {
                    session()->flash('participante_error', "{$persona->nombre_completo} ya está incluido en la lista de participantes.");
                    return;
                }
            }
        }

        $srv = Servicio::find($this->servicio_id);
        $base = (float)($srv ? $srv->precio_base : 0);
        $monto = ($this->nuevo_part_tipo_calculo === 'porcentaje')
            ? round(($base * (float)$this->nuevo_part_valor) / 100, 2)
            : (float)$this->nuevo_part_valor;

        $this->participantesSugeridos[] = [
            'persona_id' => $persona->id,
            'nombre_completo' => $persona->nombre_completo,
            'ci_nit' => $persona->ci_nit ?? '',
            'funcion' => trim($this->nuevo_part_funcion),
            'concepto_pago' => trim($this->nuevo_part_concepto),
            'tipo_calculo' => $this->nuevo_part_tipo_calculo,
            'valor_sugerido' => (float)$this->nuevo_part_valor,
            'monto_sugerido' => $monto,
            'monto_asignado' => $monto,
            'incluido' => true,
            'origen' => 'adicional',
            'tiene_pagos' => false,
            'total_pagado' => 0.0,
            'evento_participante_id' => null,
        ];

        $this->mostrarModalAgregarParticipante = false;
        session()->flash('participante_success', "{$persona->nombre_completo} agregado a la distribución del evento.");
    }

    public function editar($id)
    {
        $e = Evento::findOrFail($id);
        $this->evento_id = $e->id;
        $this->codigo_evento = $e->codigo_evento;
        $this->cliente_id = $e->cliente_id;
        $this->servicio_id = $e->servicio_id;
        $this->servicio_anterior_id = $e->servicio_id;
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
        $this->participantesSugeridos = [];

        // Cargar participantes existentes si ya fueron asignados previamente
        $participantesExistentes = EventoParticipante::with(['persona', 'pagosValidos'])
            ->where('evento_id', $e->id)
            ->get();

        if ($participantesExistentes->count() > 0) {
            foreach ($participantesExistentes as $ep) {
                $pagado = (float)$ep->pagosValidos->sum('monto');
                $tienePagos = $pagado > 0;
                $this->participantesSugeridos[] = [
                    'persona_id' => $ep->persona_id,
                    'nombre_completo' => $ep->persona->nombre_completo ?? 'N/A',
                    'ci_nit' => $ep->persona->ci_nit ?? '',
                    'funcion' => $ep->funcion,
                    'concepto_pago' => $ep->concepto_pago,
                    'tipo_calculo' => $ep->tipo_calculo ?: 'monto_fijo',
                    'valor_sugerido' => (float)($ep->valor_sugerido ?? $ep->monto_asignado),
                    'monto_sugerido' => (float)($ep->valor_sugerido ?? $ep->monto_asignado),
                    'monto_asignado' => (float)$ep->monto_asignado,
                    'incluido' => true,
                    'origen' => 'existente',
                    'tiene_pagos' => $tienePagos,
                    'total_pagado' => $pagado,
                    'evento_participante_id' => $ep->id,
                ];
            }
        } else if ($e->servicio_id) {
            // Si no tenía participantes guardados, sugerir los de la plantilla del servicio
            $this->cargarPlantillaServicio($e->servicio_id);
        }

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

        // Validaciones financieras de participantes en el servidor
        foreach ($this->participantesSugeridos as $p) {
            if (!empty($p['incluido'])) {
                if (!is_numeric($p['monto_asignado']) || (float)$p['monto_asignado'] < 0) {
                    session()->flash('participante_error', "El monto asignado a {$p['nombre_completo']} no puede ser negativo.");
                    return;
                }
                if (!empty($p['tiene_pagos']) && (float)$p['monto_asignado'] < (float)$p['total_pagado']) {
                    session()->flash('participante_error', "El monto asignado a {$p['nombre_completo']} no puede ser inferior al total ya pagado (Mínimo: Bs. " . number_format($p['total_pagado'], 2) . ").");
                    return;
                }
            }
        }

        DB::transaction(function () {
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

            // Sincronizar participantes definitivos y distribución económica
            $totalDistribucion = 0;
            $countConfirmados = 0;

            foreach ($this->participantesSugeridos as $item) {
                if (!empty($item['incluido'])) {
                    $montoFinal = (float)$item['monto_asignado'];
                    $totalDistribucion += $montoFinal;
                    $countConfirmados++;

                    $estadoCalculado = 'Pendiente';
                    if (!empty($item['tiene_pagos'])) {
                        $saldo = max(0.0, $montoFinal - (float)$item['total_pagado']);
                        $estadoCalculado = ($saldo <= 0.001) ? 'Distribuido' : 'Pago Parcial';
                    }

                    $ep = EventoParticipante::updateOrCreate(
                        [
                            'evento_id' => $e->id,
                            'persona_id' => $item['persona_id'],
                        ],
                        [
                            'funcion' => $item['funcion'],
                            'concepto_pago' => $item['concepto_pago'],
                            'tipo_calculo' => $item['tipo_calculo'],
                            'valor_sugerido' => $item['valor_sugerido'],
                            'monto_asignado' => $montoFinal,
                            'estado_distribucion' => $estadoCalculado,
                            'user_id' => Auth::id(),
                        ]
                    );

                    DistribucionEconomica::updateOrCreate(
                        [
                            'evento_id' => $e->id,
                            'persona_id' => $item['persona_id'],
                        ],
                        [
                            'contrato_id' => optional($e->contrato)->id,
                            'evento_participante_id' => $ep->id,
                            'funcion' => $item['funcion'],
                            'concepto' => $item['concepto_pago'],
                            'monto' => $montoFinal,
                            'fecha_distribucion' => now(),
                            'estado' => $estadoCalculado,
                            'user_id' => Auth::id(),
                        ]
                    );
                } else {
                    // Si no está incluido y ya existía en la BD para este evento:
                    if (!empty($item['evento_participante_id'])) {
                        $ep = EventoParticipante::find($item['evento_participante_id']);
                        if ($ep) {
                            if ($ep->total_pagado > 0) {
                                throw new \Exception("No se puede excluir a {$item['nombre_completo']} porque ya registra pagos confirmados en este evento.");
                            }
                            DistribucionEconomica::where('evento_participante_id', $ep->id)->delete();
                            $ep->delete();
                        }
                    }
                }
            }

            if ($countConfirmados > 0) {
                $this->registrarAuditoria(
                    'Gestión Comercial',
                    'Confirmar Distribución Económica Evento',
                    "Se confirmó la distribución económica del evento {$e->codigo_evento} con {$countConfirmados} participantes por un total de Bs. " . number_format($totalDistribucion, 2)
                );
            }
        });

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
        $personasDisponibles = MusicoPersonal::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();

        return view('livewire.comercial.gestion-eventos', [
            'eventos' => $eventos,
            'clientes' => $clientes,
            'servicios' => $servicios,
            'personasDisponibles' => $personasDisponibles,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Eventos - Mariachi León Guanajuato']);
    }
}
