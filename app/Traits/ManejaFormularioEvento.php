<?php

namespace App\Traits;

use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Pago;
use App\Models\Contrato;
use Illuminate\Support\Str;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use App\Models\DistribucionEconomica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

/**
 * Formulario de Nuevo / Editar evento (modal con contrato, adelanto y equipo).
 * Lo usan Eventos y Agenda. El componente debe usar también el trait Auditable
 * y mostrar el modal con @include('livewire.comercial.partials.modal-evento').
 */
trait ManejaFormularioEvento
{
    public $evento_id = null;
    public $codigo_evento = '';
    public $cliente_id = '';
    public $servicio_id = '';
    public $contacto_evento = '';
    public $telefono_contacto = '';
    public $fecha_evento = '';
    public $hora_evento = '';
    public $duracion_horas = 1.00;
    public $duracion_min = 60; // lo que se escribe en pantalla; se guarda como horas
    public $direccion_evento = '';
    public $latitud = null;
    public $longitud = null;
    public $estado = 'Confirmado';
    public $observaciones = '';

    // Contrato y adelanto (solo al crear un evento nuevo)
    public $contrato_opcion = 'si'; // 'si' | 'no'
    public $monto_contrato = '';
    public $adelanto = '';
    public $metodo_adelanto = 'Efectivo'; // Efectivo | QR

    public $modalOpen = false;
    public $isEdit = false;

    // =========================================================================
    // EQUIPO DEL EVENTO (personas del catálogo "Músicos y personal")
    // Cada item: persona_id, nombre_completo, funcion, monto_asignado, modalidad, ...
    // =========================================================================
    public $participantesSugeridos = [];


    protected function rules()
    {
        return [
            'codigo_evento' => [
                'required',
                'string',
                'max:50',
                Rule::unique('eventos', 'codigo_evento')
                    ->ignore($this->evento_id)
                    ->whereNull('deleted_at'),
            ],
            'cliente_id' => 'required|exists:clientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'contacto_evento' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:50',
            'fecha_evento' => 'required|date',
            'hora_evento' => 'required',
            'duracion_horas' => 'required|numeric|min:0.25',
            'direccion_evento' => 'required|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'estado' => 'required|in:Pendiente,Cotizado,Reservado,Confirmado,Realizado,Cancelado,Finalizado',
            'observaciones' => 'nullable|string',
            'contrato_opcion' => 'required|in:si,no',
            'monto_contrato' => [
                Rule::requiredIf(!$this->isEdit && $this->contrato_opcion === 'si'),
                'nullable',
                'numeric',
                'min:0.01',
                'max:99999999',
            ],
            'adelanto' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'metodo_adelanto' => 'required|in:Efectivo,QR',
        ];
    }

    protected $messages = [
        'monto_contrato.required' => 'Ingrese el monto total del contrato.',
        'monto_contrato.numeric' => 'El monto del contrato debe ser un número.',
        'monto_contrato.min' => 'El monto del contrato debe ser mayor a cero.',
        'adelanto.numeric' => 'El adelanto debe ser un número.',
        'adelanto.min' => 'El adelanto no puede ser negativo.',
        'codigo_evento.required' => 'El código del evento es obligatorio.',
        'codigo_evento.unique' => 'El código de evento generado ya está en uso. Genere o guarde nuevamente.',
        'cliente_id.required' => 'Debe seleccionar un cliente para el evento.',
        'cliente_id.exists' => 'El cliente seleccionado no es válido.',
        'servicio_id.required' => 'Debe seleccionar el servicio a contratar.',
        'servicio_id.exists' => 'El servicio seleccionado no es válido.',
        'fecha_evento.required' => 'La fecha del evento es obligatoria.',
        'fecha_evento.date' => 'La fecha del evento debe tener un formato válido.',
        'hora_evento.required' => 'La hora del evento es obligatoria.',
        'duracion_horas.required' => 'La duración en horas es obligatoria.',
        'duracion_horas.min' => 'La duración mínima es de 15 minutos.',
        'duracion_horas.numeric' => 'La duración debe ser un valor numérico.',
        'direccion_evento.required' => 'Debe ingresar la dirección completa o lugar exacto del evento.',
        'estado.required' => 'El estado del evento es obligatorio.',
    ];

    public function generarCodigoAutomatico()
    {
        $maxNum = 0;
        $codigos = Evento::withTrashed()->pluck('codigo_evento');
        foreach ($codigos as $codigo) {
            if (preg_match('/EVT-(\d+)/', (string)$codigo, $matches)) {
                $num = (int)$matches[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }
        $nextNum = $maxNum + 1;
        do {
            $candidate = 'EVT-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
            $nextNum++;
        } while (Evento::withTrashed()->where('codigo_evento', $candidate)->exists());

        return $candidate;
    }

    public function abrirModal($fecha = null)
    {
        $this->reset([
            'evento_id',
            'codigo_evento',
            'cliente_id',
            'servicio_id',
            'contacto_evento',
            'telefono_contacto',
            'fecha_evento',
            'hora_evento',
            'duracion_horas',
            'direccion_evento',
            'latitud',
            'longitud',
            'observaciones',
            'isEdit',
            'participantesSugeridos',
            'contrato_opcion',
            'monto_contrato',
            'adelanto',
            'metodo_adelanto',
        ]);
        $this->resetValidation();
        $this->codigo_evento = $this->generarCodigoAutomatico();
        $this->fecha_evento = ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) ? $fecha : date('Y-m-d');
        $this->hora_evento = $fecha ? '20:00' : '';
        $this->duracion_horas = 1.00;
        $this->duracion_min = 60;
        $this->estado = 'Confirmado';
        $this->cargarEquipoPorDefecto();
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
                // La dirección del evento NO se copia sola: puede ser otro lugar.
                // El usuario puede usar la del cliente con el botón "Usar la del cliente".
            }
        }
    }

    public function usarDireccionCliente()
    {
        $cliente = $this->cliente_id ? Cliente::find($this->cliente_id) : null;
        if ($cliente && $cliente->direccion) {
            $this->direccion_evento = $cliente->direccion;
            $this->resetValidation('direccion_evento');
        }
    }

    // Llamado por el buscador de servicio del modal
    public function seleccionarServicio($id)
    {
        if ((string) $this->servicio_id === (string) $id) {
            return;
        }
        $this->servicio_id = $id;
        $this->updatedServicioId($id);
    }

    public function updatedServicioId($value)
    {
        // Evento nuevo: la duración se toma del servicio (sigue siendo editable).
        // El equipo NO depende del servicio: se elige desde el catálogo de personas.
        if (empty($value) || $this->isEdit) {
            return;
        }
        $srv = Servicio::find($value);
        if ($srv && $srv->duracion_minutos) {
            $this->duracion_horas = round($srv->duracion_minutos / 60, 2);
        }
        // Servicio por hora: la duración arranca en las horas mínimas
        if ($srv && $srv->por_hora && (float) $srv->horas_minimas > 0) {
            $this->duracion_horas = (float) $srv->horas_minimas;
        }
        $this->duracion_min = (int) round(((float) $this->duracion_horas) * 60);
        // El monto del contrato sale del precio del servicio (editable)
        if ($srv) {
            $this->monto_contrato = $this->montoSegunHoras($srv, $this->duracion_horas);
        }
    }

    // La duración se escribe en minutos (25, 60, 300...) y se guarda en horas.
    // Servicio por hora: al cambiarla se recalcula el monto (sigue editable).
    public function updatedDuracionMin($value)
    {
        if (!is_numeric($value) || (float) $value <= 0) {
            return;
        }
        $horas = ((float) $value) / 60;
        $this->duracion_horas = round($horas, 2);

        if ($this->isEdit || empty($this->servicio_id)) {
            return;
        }
        $srv = Servicio::find($this->servicio_id);
        if ($srv && $srv->por_hora) {
            $this->monto_contrato = $this->montoSegunHoras($srv, $horas);
        }
    }

    // Precio fijo: el precio base. Por hora: precio x horas (nunca menos que las horas mínimas).
    private function montoSegunHoras(Servicio $srv, $horas): string
    {
        $monto = (float) $srv->precio_base;
        if ($srv->por_hora) {
            $horasCobradas = max((float) $horas, (float) $srv->horas_minimas);
            $monto = $monto * $horasCobradas;
        }
        return number_format($monto, 2, '.', '');
    }

    // ---------------------------------------------------------------------
    // EQUIPO DEL EVENTO
    // ---------------------------------------------------------------------

    private function itemDesdePersona(MusicoPersonal $persona, ?float $montoForzado = null): array
    {
        $mensual = ($persona->modalidad_pago ?? 'por_evento') === 'mensual';
        $monto = $montoForzado ?? ($mensual ? 0.0 : (float) ($persona->tarifa_evento ?? 0));

        return [
            'persona_id' => $persona->id,
            'nombre_completo' => $persona->nombre_completo,
            'ci_nit' => $persona->ci_nit ?? '',
            'funcion' => $persona->tipo ?: 'Músico',
            'concepto_pago' => $mensual ? 'Sueldo mensual' : 'Comisión por Evento',
            'tipo_calculo' => 'monto_fijo',
            'valor_sugerido' => $monto,
            'monto_sugerido' => $monto,
            'monto_asignado' => $monto,
            'modalidad' => $mensual ? 'mensual' : 'por_evento',
            'incluido' => true,
            'origen' => 'adicional',
            'tiene_pagos' => false,
            'total_pagado' => 0.0,
            'evento_participante_id' => null,
        ];
    }

    private function personaYaEnLista($personaId): ?int
    {
        foreach ($this->participantesSugeridos as $idx => $item) {
            if ($item['persona_id'] == $personaId) {
                return $idx;
            }
        }
        return null;
    }

    // Personas marcadas "Va en todos los eventos" (si la columna ya existe)
    private function cargarEquipoPorDefecto(): void
    {
        if (!Schema::hasColumn('musicos_personal', 'va_en_todos_eventos')) {
            return;
        }
        $personas = MusicoPersonal::where('estado', 'Activo')
            ->where('va_en_todos_eventos', true)
            ->orderBy('nombre_completo')
            ->get();
        foreach ($personas as $persona) {
            $this->participantesSugeridos[] = $this->itemDesdePersona($persona);
        }
    }

    public function agregarPersona($personaId)
    {
        $persona = MusicoPersonal::where('estado', 'Activo')->find($personaId);
        if (!$persona) {
            return;
        }

        $idx = $this->personaYaEnLista($persona->id);
        if ($idx !== null) {
            if (empty($this->participantesSugeridos[$idx]['incluido'])) {
                $this->participantesSugeridos[$idx]['incluido'] = true;
            } else {
                session()->flash('participante_error', "{$persona->nombre_completo} ya está en el equipo de este evento.");
            }
            return;
        }

        $this->participantesSugeridos[] = $this->itemDesdePersona($persona);
    }

    // Misma tarifa para todos los músicos del equipo (no toca transporte, sonido ni mensualistas)
    public function aplicarTarifaMusicos($monto)
    {
        if (!is_numeric($monto) || (float) $monto < 0) {
            session()->flash('participante_error', 'Escriba un monto válido para los músicos.');
            return;
        }
        $monto = round((float) $monto, 2);

        foreach ($this->participantesSugeridos as $i => $p) {
            if (empty($p['incluido'])) {
                continue;
            }
            if (($p['modalidad'] ?? 'por_evento') === 'mensual') {
                continue;
            }
            if (!$this->esFuncionMusico($p['funcion'] ?? '')) {
                continue;
            }
            // Si ya tiene pagos, no se puede bajar de lo ya pagado
            if (!empty($p['tiene_pagos']) && $monto < (float) ($p['total_pagado'] ?? 0)) {
                continue;
            }
            $this->participantesSugeridos[$i]['monto_asignado'] = $monto;
        }
    }

    private function esFuncionMusico($funcion): bool
    {
        $t = mb_strtolower((string) $funcion);
        if (str_contains($t, 'transp') || str_contains($t, 'bus') || str_contains($t, 'chofer') || str_contains($t, 'sonid')) {
            return false;
        }
        return $t === '' || str_contains($t, 'músic') || str_contains($t, 'music') || str_contains($t, 'cantan');
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

        if (empty($item['evento_participante_id'])) {
            array_splice($this->participantesSugeridos, $index, 1);
        } else {
            $this->participantesSugeridos[$index]['incluido'] = false;
        }
    }

    public function editar($id)
    {
        $e = Evento::findOrFail($id);
        $this->resetValidation();
        $this->contrato_opcion = 'no';
        $this->monto_contrato = '';
        $this->adelanto = '';
        $this->evento_id = $e->id;
        $this->codigo_evento = $e->codigo_evento;
        $this->cliente_id = $e->cliente_id;
        $this->servicio_id = $e->servicio_id;
        $this->contacto_evento = $e->contacto_evento;
        $this->telefono_contacto = $e->telefono_contacto;
        $this->fecha_evento = $e->fecha_evento ? $e->fecha_evento->format('Y-m-d') : '';
        $this->hora_evento = $e->hora_evento;
        $this->duracion_horas = $e->duracion_horas;
        $this->duracion_min = (int) round(((float) $e->duracion_horas) * 60);
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
                    'modalidad' => $ep->persona->modalidad_pago ?? 'por_evento',
                    'origen' => 'existente',
                    'tiene_pagos' => $tienePagos,
                    'total_pagado' => $pagado,
                    'evento_participante_id' => $ep->id,
                ];
            }
        }

        $this->modalOpen = true;
    }

    public function guardar()
    {
        if (!$this->isEdit) {
            if (empty($this->codigo_evento) || Evento::where('codigo_evento', $this->codigo_evento)->exists()) {
                $this->codigo_evento = $this->generarCodigoAutomatico();
            }
        }

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errores = $e->validator->errors()->all();
            session()->flash('modal_error', 'Por favor complete todos los campos obligatorios del evento: ' . implode(', ', $errores));
            $this->dispatch('evento-error-validacion');
            throw $e;
        }

        // El horario no puede cruzarse con otro evento (los cancelados no cuentan)
        if ($this->estado !== 'Cancelado') {
            $cruces = $this->eventosQueSeCruzan($this->fecha_evento, $this->hora_evento, $this->duracion_horas, $this->evento_id);
            if (!empty($cruces)) {
                $msg = 'Ese horario se cruza con: ' . implode('; ', $cruces) . '. Cambie la hora o cancele ese evento.';
                $this->addError('hora_evento', $msg);
                $this->dispatch('evento-error-validacion');
                return;
            }
        }

        // Validaciones financieras de participantes en el servidor
        foreach ($this->participantesSugeridos as $p) {
            if (!empty($p['incluido'])) {
                $monto = $p['monto_asignado'] ?? '';
                if ($monto === '' || !is_numeric($monto) || (float)$monto < 0) {
                    $msg = "El monto asignado a {$p['nombre_completo']} debe ser un número válido mayor o igual a 0.";
                    $this->addError('participantes', $msg);
                    session()->flash('participante_error', $msg);
                    $this->dispatch('evento-error-validacion');
                    return;
                }
                if (!empty($p['tiene_pagos']) && (float)$monto < (float)$p['total_pagado']) {
                    $msg = "El monto asignado a {$p['nombre_completo']} no puede ser inferior al total ya pagado (Mínimo: Bs. " . number_format($p['total_pagado'], 2) . ").";
                    $this->addError('participantes', $msg);
                    session()->flash('participante_error', $msg);
                    $this->dispatch('evento-error-validacion');
                    return;
                }
            }
        }

        $conContrato = !$this->isEdit && $this->contrato_opcion === 'si';
        $montoContrato = $conContrato ? round((float) $this->monto_contrato, 2) : 0.0;
        $adelanto = $conContrato ? round((float) $this->adelanto, 2) : 0.0;
        if ($conContrato && $adelanto > $montoContrato + 0.001) {
            $this->addError('adelanto', 'El adelanto no puede ser mayor al monto total del contrato.');
            session()->flash('modal_error', 'El adelanto (Bs ' . number_format($adelanto, 2) . ') es mayor al monto del contrato (Bs ' . number_format($montoContrato, 2) . ').');
            $this->dispatch('evento-error-validacion');
            return;
        }

        try {
            DB::transaction(function () use ($conContrato, $montoContrato, $adelanto) {
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
                        // Reservado hasta que haya adelanto; Confirmado si el cliente ya dejó algo.
                        'estado' => $this->estado === 'Pendiente'
                            ? ($adelanto > 0 ? 'Confirmado' : 'Reservado')
                            : $this->estado,
                        'observaciones' => trim($this->observaciones),
                        'user_id' => Auth::id(),
                    ]);
                    $this->registrarAuditoria('Gestión Comercial', 'Crear Evento', 'Se registró el evento ' . $e->codigo_evento);
                    session()->flash('success', 'Evento registrado exitosamente.');

                    // Contrato y adelanto en el mismo guardado
                    if ($conContrato) {
                        $ultimoContrato = Contrato::orderBy('id', 'desc')->first();
                        $numeroContrato = 'CTR-' . str_pad($ultimoContrato ? $ultimoContrato->id + 1 : 1, 5, '0', STR_PAD_LEFT);

                        $contrato = Contrato::create([
                            'numero_contrato' => $numeroContrato,
                            'evento_id' => $e->id,
                            'cliente_id' => $e->cliente_id,
                            'servicio_id' => $e->servicio_id,
                            'cotizacion_id' => null,
                            'fecha_contrato' => date('Y-m-d'),
                            'hora_contrato' => date('H:i'),
                            'monto_total' => $montoContrato,
                            'pago_inicial' => $adelanto,
                            'total_pagado' => $adelanto,
                            'saldo_pendiente' => max(0, $montoContrato - $adelanto),
                            'estado' => $adelanto > 0 ? 'Confirmado' : 'Pendiente de Confirmacion',
                            'observaciones' => '',
                            'user_id' => Auth::id(),
                        ]);
                        $e->setRelation('contrato', $contrato);

                        $mensaje = "Evento registrado con contrato {$numeroContrato}";

                        if ($adelanto > 0) {
                            $ultimoPago = Pago::orderBy('id', 'desc')->first();
                            $numeroRecibo = 'REC-' . str_pad($ultimoPago ? $ultimoPago->id + 1 : 1, 5, '0', STR_PAD_LEFT);

                            Pago::create([
                                'numero_recibo' => $numeroRecibo,
                                'contrato_id' => $contrato->id,
                                'cliente_id' => $contrato->cliente_id,
                                'evento_id' => $e->id,
                                'fecha_pago' => date('Y-m-d H:i:s'),
                                'monto' => $adelanto,
                                'metodo_pago' => $this->metodo_adelanto === 'QR' ? 'QR' : 'EFECTIVO',
                                'observaciones' => 'Adelanto al reservar - contrato ' . $numeroContrato,
                                'codigo_verificacion' => Str::random(40),
                                'estado' => 'Valido',
                                'user_id' => Auth::id(),
                            ]);
                            $mensaje .= " y adelanto {$numeroRecibo} de Bs " . number_format($adelanto, 2) . " ({$this->metodo_adelanto})";
                        }

                        $this->registrarAuditoria('Gestión Comercial', 'Crear Contrato', "Se creó el contrato {$numeroContrato} junto con el evento {$e->codigo_evento}" . ($adelanto > 0 ? " con adelanto de Bs " . number_format($adelanto, 2) : ''));
                        session()->flash('success', $mensaje . '.');
                    }
                }

                // Sincronizar participantes definitivos y distribución económica
                $totalDistribucion = 0;
                $countConfirmados = 0;
                $incluidosPersonaIds = [];

                foreach ($this->participantesSugeridos as $item) {
                    if (!empty($item['incluido'])) {
                        $montoFinal = (float)($item['monto_asignado'] ?? 0);
                        $totalDistribucion += $montoFinal;
                        $countConfirmados++;
                        $incluidosPersonaIds[] = $item['persona_id'];

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
                                'funcion' => $item['funcion'] ?? 'Músico',
                                'concepto_pago' => $item['concepto_pago'] ?? 'Comisión por Evento',
                                'tipo_calculo' => $item['tipo_calculo'] ?? 'monto_fijo',
                                'valor_sugerido' => $item['valor_sugerido'] ?? $montoFinal,
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
                                'funcion' => $item['funcion'] ?? 'Músico',
                                'concepto' => $item['concepto_pago'] ?? 'Comisión por Evento',
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

                // Si se estaba editando, desvincular participantes retirados que no tengan pagos registrados
                if ($this->isEdit) {
                    $existentesEnBd = EventoParticipante::where('evento_id', $e->id)->get();
                    foreach ($existentesEnBd as $epBd) {
                        if (!in_array($epBd->persona_id, $incluidosPersonaIds)) {
                            if ($epBd->pagosValidos()->count() > 0) {
                                continue;
                            }
                            DistribucionEconomica::where('evento_participante_id', $epBd->id)->delete();
                            $epBd->delete();
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
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::error('Error al guardar evento: ' . $ex->getMessage(), ['trace' => $ex->getTraceAsString()]);
            session()->flash('modal_error', 'Error al guardar evento: ' . $ex->getMessage());
            $this->addError('general', $ex->getMessage());
            $this->dispatch('evento-error-validacion');
            return;
        }

        $this->modalOpen = false;
    }

    // Propiedades
    public bool $modalClienteOpen = false;
    public string $nc_nombre_completo = '';
    public ?string $nc_ci_nit = null;
    public ?string $nc_telefono = null;
    public ?string $nc_whatsapp = null;
    public ?string $nc_email = null;
    public ?string $nc_direccion = null;

    // Métodos
    public function abrirModalNuevoCliente(): void
    {
        $this->reset(['nc_nombre_completo', 'nc_ci_nit', 'nc_telefono', 'nc_whatsapp', 'nc_email', 'nc_direccion']);
        $this->resetValidation();
        $this->modalClienteOpen = true;
    }

    public function cerrarModalNuevoCliente(): void
    {
        $this->resetValidation();
        $this->modalClienteOpen = false;
    }

    public function guardarNuevoCliente(): void
    {
        $this->validate([
            'nc_nombre_completo' => 'required|string|max:255',
            'nc_ci_nit'          => 'nullable|string|max:50',
            'nc_telefono'        => 'nullable|string|max:50',
            'nc_whatsapp'        => 'nullable|string|max:50',
            'nc_email'           => 'nullable|email|max:255',
        ], [
            'nc_nombre_completo.required' => 'El nombre del cliente es obligatorio.',
        ]);

        $cliente = \App\Models\Cliente::create([
            'nombre_completo' => $this->nc_nombre_completo,
            'ci_nit'          => $this->nc_ci_nit,
            'telefono'        => $this->nc_telefono,
            'whatsapp'        => $this->nc_whatsapp,
            'email'           => $this->nc_email,
            'direccion'       => $this->nc_direccion,
            'estado'          => 'Activo',
        ]);

        $this->modalClienteOpen = false;
        $this->seleccionarCliente($cliente->id);

        $this->dispatch(
            'cliente-creado',
            id: $cliente->id,
            label: $cliente->nombre_completo,
            meta: trim(($cliente->ci_nit ? 'CI: ' . $cliente->ci_nit : '') . ($cliente->telefono ? ' - Tel: ' . $cliente->telefono : '')),
            search: mb_strtolower($cliente->nombre_completo . ' ' . $cliente->ci_nit . ' ' . $cliente->telefono),
        );
    }

    /**
     * Datos que necesita el modal en la vista (solo se cargan con el modal abierto).
     */
    /**
     * Eventos (no cancelados) del mismo día cuyo horario se cruza con el indicado.
     * Devuelve textos como "EVT-00001 - Cliente (20:00 a 21:00)".
     */
    protected function eventosQueSeCruzan($fecha, $hora, $duracionHoras, $exceptoId = null): array
    {
        if (!$fecha || !$hora) {
            return [];
        }

        $aMin = fn($h) => ((int) substr((string) $h, 0, 2)) * 60 + (int) substr((string) $h, 3, 2);
        $s1 = $aMin($hora);
        $f1 = $s1 + (int) round(((float) $duracionHoras) * 60);

        return Evento::with('cliente')
            ->whereDate('fecha_evento', $fecha)
            ->where('estado', '!=', 'Cancelado')
            ->when($exceptoId, fn($q) => $q->where('id', '!=', $exceptoId))
            ->get()
            ->filter(function ($ev) use ($aMin, $s1, $f1) {
                $s2 = $aMin($ev->hora_evento);
                $f2 = $s2 + (int) round(((float) $ev->duracion_horas) * 60);
                return $s1 < $f2 && $f1 > $s2;
            })
            ->map(function ($ev) use ($aMin) {
                $s2 = $aMin($ev->hora_evento);
                $f2 = $s2 + (int) round(((float) $ev->duracion_horas) * 60);
                return $ev->codigo_evento . ' - ' . ($ev->cliente->nombre_completo ?? 'N/A')
                    . sprintf(' (%02d:%02d a %02d:%02d)', intdiv($s2, 60) % 24, $s2 % 60, intdiv($f2, 60) % 24, $f2 % 60);
            })
            ->values()
            ->all();
    }

    protected function datosFormularioEvento(): array
    {
        $clientes = Cliente::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre', 'asc')->get();
        $personasDisponibles = collect();
        $precioReferencia = 0.0;

        if ($this->modalOpen) {
            $personasDisponibles = MusicoPersonal::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();

            // Precio de referencia: monto del contrato si existe, si no el precio del servicio
            $contratoRef = $this->evento_id ? optional(Evento::with('contrato')->find($this->evento_id))->contrato : null;
            if ($contratoRef) {
                $precioReferencia = (float) $contratoRef->monto_total;
            } elseif (!$this->isEdit && $this->contrato_opcion === 'si' && is_numeric($this->monto_contrato) && (float) $this->monto_contrato > 0) {
                $precioReferencia = (float) $this->monto_contrato;
            } elseif ($this->servicio_id) {
                $precioReferencia = (float) optional($servicios->firstWhere('id', $this->servicio_id))->precio_base;
            }
        }

        return [
            'clientes' => $clientes,
            'servicios' => $servicios,
            'personasDisponibles' => $personasDisponibles,
            'precioReferencia' => $precioReferencia,
        ];
    }
}