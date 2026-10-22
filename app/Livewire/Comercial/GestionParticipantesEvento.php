<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Models\MusicoPersonal;
use App\Models\DistribucionEconomica;
use App\Models\PagoParticipante;
use App\Models\Servicio;
use App\Models\ServicioParticipante;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionParticipantesEvento extends Component
{
    use Auditable;

    public $eventoId;
    public $evento;

    // Formulario para agregar participante
    public $persona_id = '';
    public $funcion = 'Músico';
    public $concepto_pago = 'Comisión por Evento';
    public $tipo_calculo = 'monto_fijo';
    public $valor_sugerido = 0.00;
    public $monto_asignado = 0.00;
    public $observaciones = '';

    // Modal Aplicar Plantilla del Servicio
    public $mostrarModalAplicarPlantilla = false;
    public $participantesPlantillaSugeridos = [];

    // Sugerencias inteligentes
    public $personasSugeridas = [];

    // Modal Crear Nueva Persona
    public $mostrarModalPersona = false;
    public $nuevo_nombre = '';
    public $nuevo_apellido = '';
    public $nuevo_ci_nit = '';
    public $nuevo_telefono = '';
    public $nuevo_email = '';
    public $nuevo_cargo = 'Músico';
    public $nueva_direccion = '';

    // Modal Editar Participante
    public $editingParticipanteId = null;
    public $edit_funcion = 'Músico';
    public $edit_concepto_pago = 'Comisión por Evento';
    public $edit_monto_asignado = 0.00;
    public $edit_observaciones = '';

    // Modal Registrar Pago a Participante
    public $mostrarModalPago = false;
    public $pago_participante_id = null;
    public $pago_participante = null;
    public $pago_monto = '';
    public $pago_metodo = 'Efectivo';
    public $pago_glosa = '';
    public $pago_observaciones = '';

    // Modal Historial de Pagos
    public $mostrarModalHistorial = false;
    public $historial_participante_id = null;
    public $historial_participante = null;

    // Modal Confirmar Reversión de Pago
    public $mostrarModalRevertir = false;
    public $pago_a_revertir_id = null;
    public $pago_a_revertir = null;
    public $motivo_reversion = '';

    protected $rules = [
        'persona_id' => 'required|exists:musicos_personal,id',
        'funcion' => 'required|string|max:100',
        'concepto_pago' => 'required|string|max:100',
        'monto_asignado' => 'required|numeric|min:0',
        'observaciones' => 'nullable|string|max:500',
    ];

    public function mount($evento)
    {
        $this->eventoId = is_numeric($evento) ? $evento : $evento->id;
        $this->cargarEvento();
        $this->cargarSugerencias();
    }

    public function cargarEvento()
    {
        $this->evento = Evento::with([
            'cliente',
            'servicio',
            'contrato.pagos',
            'pagos',
            'participantes.persona',
            'participantes.pagos.user',
            'participantes.pagos.revertidoPor',
            'distribuciones.persona',
            'distribuciones.pagos'
        ])->findOrFail($this->eventoId);

        if ($this->pago_participante_id) {
            $this->pago_participante = EventoParticipante::with(['persona', 'pagos'])->find($this->pago_participante_id);
        }

        if ($this->historial_participante_id) {
            $this->historial_participante = EventoParticipante::with(['persona', 'pagos.user', 'pagos.revertidoPor'])
                ->find($this->historial_participante_id);
        }
    }

    public function cargarSugerencias()
    {
        if (!$this->evento || !$this->evento->servicio_id) {
            $this->personasSugeridas = [];
            return;
        }

        $yaAsignadosIds = $this->evento->participantes->pluck('persona_id')->toArray();

        // 1. Prioridad: Participantes configurados en la plantilla activa del servicio
        $plantillaQuery = ServicioParticipante::where('servicio_id', $this->evento->servicio_id)
            ->where('activo', true)
            ->whereNotIn('persona_id', $yaAsignadosIds)
            ->with('persona')
            ->get();

        if ($plantillaQuery->count() > 0) {
            $this->personasSugeridas = $plantillaQuery->pluck('persona')->filter()->values();
            return;
        }

        // 2. Si no hay en plantilla, sugerir por frecuencia en este servicio
        $sugeridosQuery = EventoParticipante::query()
            ->join('eventos', 'evento_participantes.evento_id', '=', 'eventos.id')
            ->where('eventos.servicio_id', $this->evento->servicio_id)
            ->where('eventos.id', '!=', $this->eventoId)
            ->whereNotIn('evento_participantes.persona_id', $yaAsignadosIds)
            ->select('evento_participantes.persona_id', DB::raw('count(*) as frecuencia'))
            ->groupBy('evento_participantes.persona_id')
            ->orderByDesc('frecuencia')
            ->take(6)
            ->get();

        $personaIds = $sugeridosQuery->pluck('persona_id')->toArray();

        if (!empty($personaIds)) {
            $this->personasSugeridas = MusicoPersonal::whereIn('id', $personaIds)
                ->where('estado', 'Activo')
                ->get();
        } else {
            // 3. Fallback: Primeras 5 personas activas no asignadas
            $this->personasSugeridas = MusicoPersonal::where('estado', 'Activo')
                ->whereNotIn('id', $yaAsignadosIds)
                ->take(5)
                ->get();
        }
    }

    public function seleccionarSugerido($personaId)
    {
        $this->persona_id = $personaId;
        $persona = MusicoPersonal::find($personaId);
        if ($persona) {
            $this->funcion = $persona->tipo ?? 'Músico';
        }

        // Si esta persona está configurada en la plantilla del servicio, precargar sus valores
        if ($this->evento && $this->evento->servicio_id) {
            $sp = ServicioParticipante::where('servicio_id', $this->evento->servicio_id)
                ->where('persona_id', $personaId)
                ->first();

            if ($sp) {
                $precioBase = $this->evento->contrato 
                    ? (float)$this->evento->contrato->monto_total 
                    : (float)optional($this->evento->servicio)->precio_base;

                $this->funcion = $sp->funcion;
                $this->concepto_pago = $sp->concepto_pago;
                $this->tipo_calculo = $sp->tipo_calculo;
                $this->valor_sugerido = (float)$sp->valor;
                $this->monto_asignado = $sp->calcularMontoSugerido($precioBase);
            }
        }
    }

    public function abrirModalAplicarPlantilla()
    {
        if (!$this->evento || !$this->evento->servicio_id) {
            session()->flash('error', 'Este evento no tiene un servicio asociado.');
            return;
        }

        $srv = Servicio::with(['plantillaParticipantes.persona'])->find($this->evento->servicio_id);
        if (!$srv || $srv->plantillaParticipantes->where('activo', true)->count() === 0) {
            session()->flash('error', "El servicio '{$this->evento->servicio->nombre}' no tiene participantes configurados en su plantilla económica.");
            return;
        }

        $precioBase = $this->evento->contrato 
            ? (float)$this->evento->contrato->monto_total 
            : (float)$srv->precio_base;

        $existentes = $this->evento->participantes->keyBy('persona_id');
        $this->participantesPlantillaSugeridos = [];

        foreach ($srv->plantillaParticipantes->where('activo', true) as $sp) {
            $montoSugerido = $sp->calcularMontoSugerido($precioBase);
            $ep = $existentes->get($sp->persona_id);
            $yaAsignado = !empty($ep);
            $tienePagos = $yaAsignado ? ($ep->total_pagado > 0) : false;
            $totalPagado = $yaAsignado ? $ep->total_pagado : 0.0;
            $montoFinal = $yaAsignado ? (float)$ep->monto_asignado : $montoSugerido;

            $this->participantesPlantillaSugeridos[] = [
                'persona_id' => $sp->persona_id,
                'nombre_completo' => $sp->persona->nombre_completo ?? 'N/A',
                'ci_nit' => $sp->persona->ci_nit ?? '',
                'funcion' => $yaAsignado ? $ep->funcion : $sp->funcion,
                'concepto_pago' => $yaAsignado ? $ep->concepto_pago : $sp->concepto_pago,
                'tipo_calculo' => $sp->tipo_calculo,
                'valor_sugerido' => (float)$sp->valor,
                'monto_sugerido' => $montoSugerido,
                'monto_asignado' => $montoFinal,
                'incluido' => true,
                'ya_asignado' => $yaAsignado,
                'tiene_pagos' => $tienePagos,
                'total_pagado' => $totalPagado,
            ];
        }

        $this->mostrarModalAplicarPlantilla = true;
    }

    public function cerrarModalAplicarPlantilla()
    {
        $this->mostrarModalAplicarPlantilla = false;
        $this->participantesPlantillaSugeridos = [];
    }

    public function toggleIncluidoPlantillaSugerida($index)
    {
        if (!isset($this->participantesPlantillaSugeridos[$index])) return;
        $item = $this->participantesPlantillaSugeridos[$index];
        if ($item['incluido'] && !empty($item['tiene_pagos'])) {
            session()->flash('error', "No se puede excluir a {$item['nombre_completo']} porque ya cuenta con pagos registrados.");
            return;
        }
        $this->participantesPlantillaSugeridos[$index]['incluido'] = !$this->participantesPlantillaSugeridos[$index]['incluido'];
    }

    public function confirmarAplicarPlantilla()
    {
        // Validar montos en el servidor
        foreach ($this->participantesPlantillaSugeridos as $p) {
            if (!empty($p['incluido'])) {
                if (!is_numeric($p['monto_asignado']) || (float)$p['monto_asignado'] < 0) {
                    session()->flash('error', "El monto asignado a {$p['nombre_completo']} no puede ser negativo.");
                    return;
                }
                if (!empty($p['tiene_pagos']) && (float)$p['monto_asignado'] < (float)$p['total_pagado']) {
                    session()->flash('error', "El monto asignado a {$p['nombre_completo']} no puede ser inferior a lo ya pagado (Mínimo: Bs. " . number_format($p['total_pagado'], 2) . ").");
                    return;
                }
            }
        }

        DB::transaction(function () {
            $aplicados = 0;
            foreach ($this->participantesPlantillaSugeridos as $item) {
                if (!empty($item['incluido'])) {
                    $montoFinal = (float)$item['monto_asignado'];
                    $estadoCalculado = 'Pendiente';
                    if (!empty($item['tiene_pagos'])) {
                        $saldo = max(0.0, $montoFinal - (float)$item['total_pagado']);
                        $estadoCalculado = ($saldo <= 0.001) ? 'Distribuido' : 'Pago Parcial';
                    }

                    $ep = EventoParticipante::updateOrCreate(
                        [
                            'evento_id' => $this->eventoId,
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
                            'evento_id' => $this->eventoId,
                            'persona_id' => $item['persona_id'],
                        ],
                        [
                            'contrato_id' => optional($this->evento->contrato)->id,
                            'evento_participante_id' => $ep->id,
                            'funcion' => $item['funcion'],
                            'concepto' => $item['concepto_pago'],
                            'monto' => $montoFinal,
                            'fecha_distribucion' => now(),
                            'estado' => $estadoCalculado,
                            'user_id' => Auth::id(),
                        ]
                    );
                    $aplicados++;
                }
            }

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Aplicar Plantilla Servicio en Evento',
                "Se aplicaron {$aplicados} participantes de la plantilla del servicio '{$this->evento->servicio->nombre}' al evento {$this->evento->codigo_evento}."
            );
        });

        $this->mostrarModalAplicarPlantilla = false;
        $this->cargarEvento();
        $this->cargarSugerencias();

        session()->flash('message', 'Plantilla económica del servicio aplicada con éxito.');
    }

    public function agregarParticipante()
    {
        $this->validate();

        // Validar que no esté duplicado
        $existe = EventoParticipante::where('evento_id', $this->eventoId)
            ->where('persona_id', $this->persona_id)
            ->exists();

        if ($existe) {
            session()->flash('error', 'La persona seleccionada ya está asignada a este evento.');
            return;
        }

        DB::transaction(function () {
            $participante = EventoParticipante::create([
                'evento_id' => $this->eventoId,
                'persona_id' => $this->persona_id,
                'funcion' => $this->funcion,
                'concepto_pago' => $this->concepto_pago,
                'tipo_calculo' => $this->tipo_calculo ?: 'monto_fijo',
                'valor_sugerido' => $this->valor_sugerido ?: $this->monto_asignado,
                'monto_asignado' => $this->monto_asignado,
                'estado_distribucion' => 'Pendiente',
                'observaciones' => $this->observaciones,
                'user_id' => Auth::id(),
            ]);

            // Sincronizar la entrada en distribuciones_economicas
            DistribucionEconomica::create([
                'evento_id' => $this->eventoId,
                'contrato_id' => optional($this->evento->contrato)->id,
                'persona_id' => $this->persona_id,
                'evento_participante_id' => $participante->id,
                'funcion' => $this->funcion,
                'concepto' => $this->concepto_pago,
                'monto' => $this->monto_asignado,
                'fecha_distribucion' => now(),
                'estado' => 'Pendiente',
                'user_id' => Auth::id(),
            ]);

            $persona = MusicoPersonal::find($this->persona_id);
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Asignar Participante Evento',
                "Se asignó a {$persona->nombre_completo} ({$this->funcion}) con monto de Bs. " . number_format($this->monto_asignado, 2) . " para el evento {$this->evento->codigo_evento}."
            );
        });

        $this->reset(['persona_id', 'monto_asignado', 'observaciones', 'valor_sugerido']);
        $this->funcion = 'Músico';
        $this->concepto_pago = 'Comisión por Evento';
        $this->tipo_calculo = 'monto_fijo';

        $this->cargarEvento();
        $this->cargarSugerencias();

        session()->flash('message', 'Participante asignado correctamente al evento.');
    }

    public function editarParticipante($id)
    {
        $part = EventoParticipante::with('pagos')->findOrFail($id);
        $this->editingParticipanteId = $part->id;
        $this->edit_funcion = $part->funcion;
        $this->edit_concepto_pago = $part->concepto_pago;
        $this->edit_monto_asignado = $part->monto_asignado;
        $this->edit_observaciones = $part->observaciones ?? '';
    }

    public function actualizarParticipante()
    {
        $this->validate([
            'edit_funcion' => 'required|string|max:100',
            'edit_concepto_pago' => 'required|string|max:100',
            'edit_monto_asignado' => 'required|numeric|min:0',
            'edit_observaciones' => 'nullable|string|max:500',
        ]);

        $part = EventoParticipante::with(['persona', 'pagos'])->findOrFail($this->editingParticipanteId);
        $totalPagadoVigente = $part->total_pagado;
        $nuevoMonto = (float)$this->edit_monto_asignado;

        // Regla obligatoria: Impedir que el monto total sea inferior a lo ya pagado
        if ($nuevoMonto < $totalPagadoVigente) {
            session()->flash('error', "No se puede establecer un monto total inferior al importe ya pagado. El monto mínimo permitido para esta asignación es Bs. " . number_format($totalPagadoVigente, 2) . ".");
            return;
        }

        $montoAnterior = (float)$part->monto_asignado;

        DB::transaction(function () use ($part, $nuevoMonto, $totalPagadoVigente, $montoAnterior) {
            // Calcular nuevo estado según el balance
            $nuevoSaldo = $nuevoMonto - $totalPagadoVigente;
            if ($totalPagadoVigente <= 0) {
                $nuevoEstado = 'Pendiente';
            } elseif ($nuevoSaldo <= 0.001) {
                $nuevoEstado = 'Distribuido';
            } else {
                $nuevoEstado = 'Pago Parcial';
            }

            $part->update([
                'funcion' => $this->edit_funcion,
                'concepto_pago' => $this->edit_concepto_pago,
                'monto_asignado' => $nuevoMonto,
                'estado_distribucion' => $nuevoEstado,
                'observaciones' => $this->edit_observaciones,
            ]);

            // Actualizar distribucion asociada
            DistribucionEconomica::where('evento_participante_id', $part->id)->update([
                'funcion' => $this->edit_funcion,
                'concepto' => $this->edit_concepto_pago,
                'monto' => $nuevoMonto,
                'estado' => $nuevoEstado,
            ]);

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Editar Asignación Participante',
                "Se actualizó la asignación de {$part->persona->nombre_completo} en el evento {$this->evento->codigo_evento}. Monto anterior: Bs. " . number_format($montoAnterior, 2) . ", Nuevo monto: Bs. " . number_format($nuevoMonto, 2) . " (Pagado vigente: Bs. " . number_format($totalPagadoVigente, 2) . ", Saldo restante: Bs. " . number_format($nuevoSaldo, 2) . ")."
            );
        });

        $this->editingParticipanteId = null;
        $this->cargarEvento();
        session()->flash('message', 'Datos de la asignación económica actualizados correctamente.');
    }

    public function cancelarEdicion()
    {
        $this->editingParticipanteId = null;
    }

    public function eliminarParticipante($id)
    {
        $part = EventoParticipante::with(['persona', 'pagos'])->findOrFail($id);
        $totalPagado = $part->total_pagado;

        // Regla obligatoria: No permitir eliminar asignaciones con pagos vigentes registrados
        if ($totalPagado > 0) {
            session()->flash('error', "No se puede eliminar una asignación que ya cuenta con pagos registrados (Total pagado: Bs. " . number_format($totalPagado, 2) . "). Revierta los pagos antes de remover al participante.");
            return;
        }

        DB::transaction(function () use ($part) {
            $nombre = $part->persona->nombre_completo ?? 'Participante';
            DistribucionEconomica::where('evento_participante_id', $part->id)->delete();
            $part->delete();

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Remover Participante Evento',
                "Se removió al participante {$nombre} del evento {$this->evento->codigo_evento}."
            );
        });

        $this->cargarEvento();
        $this->cargarSugerencias();

        session()->flash('message', 'Participante removido del evento exitosamente.');
    }

    // =========================================================================
    // REGISTRO DE PAGOS A PARTICIPANTES (Efectivo / QR)
    // =========================================================================

    public function abrirModalPago($participanteId)
    {
        $part = EventoParticipante::with(['persona', 'pagos'])->findOrFail($participanteId);
        $saldoPendiente = $part->saldo_pendiente;

        if ($saldoPendiente <= 0) {
            session()->flash('error', "El participante {$part->persona->nombre_completo} ya tiene cancelado el 100% de su asignación económica.");
            return;
        }

        $this->pago_participante_id = $part->id;
        $this->pago_participante = $part;
        $this->pago_monto = $saldoPendiente;
        $this->pago_metodo = 'Efectivo';
        $this->pago_glosa = "Liquidación de {$part->concepto_pago} a {$part->persona->nombre_completo} - Evento {$this->evento->codigo_evento}";
        $this->pago_observaciones = '';
        $this->mostrarModalPago = true;
    }

    public function cerrarModalPago()
    {
        $this->reset(['pago_participante_id', 'pago_participante', 'pago_monto', 'pago_glosa', 'pago_observaciones']);
        $this->pago_metodo = 'Efectivo';
        $this->mostrarModalPago = false;
    }

    public function guardarPagoParticipante()
    {
        $this->validate([
            'pago_monto' => 'required|numeric|min:0.01',
            'pago_metodo' => 'required|in:Efectivo,QR',
            'pago_glosa' => 'required|string|max:255',
            'pago_observaciones' => 'nullable|string|max:500',
        ]);

        $part = EventoParticipante::with(['persona', 'pagos'])->findOrFail($this->pago_participante_id);
        $saldoDisponible = round($part->saldo_pendiente, 2);
        $montoAPagar = round((float)$this->pago_monto, 2);

        if ($saldoDisponible <= 0) {
            session()->flash('error', 'El participante ya no tiene saldo pendiente por pagar.');
            $this->cerrarModalPago();
            return;
        }

        if ($montoAPagar > $saldoDisponible) {
            session()->flash('error', "El importe a pagar (Bs. " . number_format($montoAPagar, 2) . ") supera el saldo pendiente (Bs. " . number_format($saldoDisponible, 2) . ").");
            return;
        }

        DB::transaction(function () use ($part, $montoAPagar) {
            $distribucionId = DistribucionEconomica::where('evento_participante_id', $part->id)->value('id');

            $pago = PagoParticipante::create([
                'evento_participante_id' => $part->id,
                'distribucion_economica_id' => $distribucionId,
                'evento_id' => $this->eventoId,
                'persona_id' => $part->persona_id,
                'monto' => $montoAPagar,
                'metodo_pago' => $this->pago_metodo,
                'glosa' => trim($this->pago_glosa),
                'observaciones' => trim($this->pago_observaciones) ?: null,
                'fecha_pago' => now(),
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            // Recalcular saldo y estados
            $totalPagadoNuevo = (float)$part->pagos()->where('estado', 'Valido')->sum('monto');
            $nuevoSaldo = (float)$part->monto_asignado - $totalPagadoNuevo;
            $nuevoEstado = ($nuevoSaldo <= 0.001) ? 'Distribuido' : 'Pago Parcial';

            $part->update(['estado_distribucion' => $nuevoEstado]);
            if ($distribucionId) {
                DistribucionEconomica::where('id', $distribucionId)->update(['estado' => $nuevoEstado]);
            }

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Registrar Pago Participante',
                "Se registró un pago de Bs. " . number_format($montoAPagar, 2) . " ({$this->pago_metodo}) a favor de {$part->persona->nombre_completo} para el evento {$this->evento->codigo_evento}. Glosa: {$this->pago_glosa}. Saldo restante: Bs. " . number_format(max(0, $nuevoSaldo), 2) . "."
            );
        });

        $this->cerrarModalPago();
        $this->cargarEvento();

        session()->flash('message', 'Pago registrado exitosamente al participante.');
    }

    // =========================================================================
    // HISTORIAL Y REVERSIÓN DE PAGOS A PARTICIPANTES
    // =========================================================================

    public function abrirHistorialPagos($participanteId)
    {
        $this->historial_participante_id = $participanteId;
        $this->historial_participante = EventoParticipante::with(['persona', 'pagos.user', 'pagos.revertidoPor'])
            ->findOrFail($participanteId);
        $this->mostrarModalHistorial = true;
    }

    public function cerrarHistorialPagos()
    {
        $this->reset(['historial_participante_id', 'historial_participante']);
        $this->mostrarModalHistorial = false;
        $this->cancelarReversion();
    }

    public function abrirModalRevertir($pagoId)
    {
        $this->pago_a_revertir = PagoParticipante::with(['participante.persona'])->findOrFail($pagoId);
        $this->pago_a_revertir_id = $pagoId;
        $this->motivo_reversion = '';
        $this->mostrarModalRevertir = true;
    }

    public function cancelarReversion()
    {
        $this->mostrarModalRevertir = false;
        $this->pago_a_revertir_id = null;
        $this->pago_a_revertir = null;
        $this->motivo_reversion = '';
    }

    public function confirmarReversionPago()
    {
        $this->validate([
            'motivo_reversion' => 'required|string|min:4|max:255',
        ]);

        $pago = PagoParticipante::with(['participante.persona'])->findOrFail($this->pago_a_revertir_id);

        if ($pago->estado === 'Revertido') {
            session()->flash('error', 'Este pago ya ha sido revertido previamente.');
            $this->cancelarReversion();
            return;
        }

        DB::transaction(function () use ($pago) {
            $pago->update([
                'estado' => 'Revertido',
                'motivo_reversion' => trim($this->motivo_reversion),
                'fecha_reversion' => now(),
                'revertido_por' => Auth::id(),
            ]);

            $part = $pago->participante;
            $totalPagadoRestante = (float)$part->pagos()->where('estado', 'Valido')->sum('monto');
            $nuevoSaldo = (float)$part->monto_asignado - $totalPagadoRestante;

            if ($totalPagadoRestante <= 0) {
                $nuevoEstado = 'Pendiente';
            } elseif ($nuevoSaldo <= 0.001) {
                $nuevoEstado = 'Distribuido';
            } else {
                $nuevoEstado = 'Pago Parcial';
            }

            $part->update(['estado_distribucion' => $nuevoEstado]);
            DistribucionEconomica::where('evento_participante_id', $part->id)->update(['estado' => $nuevoEstado]);

            $this->registrarAuditoria(
                'Gestión Comercial',
                'Revertir Pago Participante',
                "Se revirtió el pago ID #{$pago->id} por Bs. " . number_format($pago->monto, 2) . " de {$part->persona->nombre_completo} en el evento {$this->evento->codigo_evento}. Motivo: {$this->motivo_reversion}. Nuevo saldo pendiente: Bs. " . number_format($nuevoSaldo, 2) . "."
            );
        });

        $this->cancelarReversion();
        $this->cargarEvento();

        session()->flash('message', 'El pago ha sido revertido exitosamente y el saldo pendiente fue actualizado.');
    }

    // =========================================================================
    // CREAR NUEVA PERSONA RÁPIDA
    // =========================================================================

    public function abrirModalPersona()
    {
        $this->reset(['nuevo_nombre', 'nuevo_apellido', 'nuevo_ci_nit', 'nuevo_telefono', 'nuevo_email', 'nueva_direccion']);
        $this->nuevo_cargo = 'Músico';
        $this->mostrarModalPersona = true;
    }

    public function cerrarModalPersona()
    {
        $this->mostrarModalPersona = false;
    }

    public function guardarNuevaPersona()
    {
        $this->validate([
            'nuevo_nombre' => 'required|string|max:100',
            'nuevo_apellido' => 'required|string|max:100',
            'nuevo_cargo' => 'required|string|max:100',
            'nuevo_telefono' => 'nullable|string|max:20',
            'nuevo_ci_nit' => 'nullable|string|max:20',
            'nuevo_email' => 'nullable|email|max:100',
        ]);

        $nombreCompleto = trim($this->nuevo_nombre . ' ' . $this->nuevo_apellido);

        $persona = MusicoPersonal::create([
            'nombre' => $this->nuevo_nombre,
            'apellido' => $this->nuevo_apellido,
            'nombre_completo' => $nombreCompleto,
            'tipo' => $this->nuevo_cargo,
            'telefono' => $this->nuevo_telefono,
            'ci_nit' => $this->nuevo_ci_nit,
            'email' => $this->nuevo_email,
            'direccion' => $this->nueva_direccion,
            'estado' => 'Activo',
        ]);

        $this->persona_id = $persona->id;
        $this->funcion = $persona->tipo;
        $this->cerrarModalPersona();

        session()->flash('message', "Persona ({$persona->nombre_completo}) registrada en el catálogo y seleccionada.");
    }

    public function render()
    {
        $todasPersonas = MusicoPersonal::where('estado', 'Activo')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();

        // 1. Datos del Contrato con el Cliente
        $tieneContrato = !empty($this->evento->contrato);
        $contrato = $this->evento->contrato;
        $montoContratado = $tieneContrato ? (float)$contrato->monto_total : null;

        // 2. Cobros del Cliente (Exclusivamente del módulo Pagos & Anticipos con estado Valido)
        if ($tieneContrato) {
            $totalCobradoCliente = (float)$contrato->pagos->where('estado', 'Valido')->sum('monto');
            $saldoPendienteCliente = $montoContratado - $totalCobradoCliente;
        } else {
            $totalCobradoCliente = null;
            $saldoPendienteCliente = null;
        }

        // 3. Distribución a Participantes
        $totalAsignadoParticipantes = (float)$this->evento->participantes->sum('monto_asignado');
        $totalPagadoParticipantes = (float)$this->evento->participantes->sum(function ($p) {
            return $p->pagos->where('estado', 'Valido')->sum('monto');
        });
        $saldoPorPagarParticipantes = max(0.0, $totalAsignadoParticipantes - $totalPagadoParticipantes);

        // 4. Balance Bruto del Evento (Cobrado al Cliente - Asignado a Participantes)
        if ($tieneContrato && $totalCobradoCliente !== null) {
            $balanceBruto = $totalCobradoCliente - $totalAsignadoParticipantes;
        } else {
            $balanceBruto = null;
        }

        return view('livewire.comercial.gestion-participantes-evento', [
            'todasPersonas' => $todasPersonas,
            'tieneContrato' => $tieneContrato,
            'contrato' => $contrato,
            'montoContratado' => $montoContratado,
            'totalCobradoCliente' => $totalCobradoCliente,
            'saldoPendienteCliente' => $saldoPendienteCliente,
            'totalAsignadoParticipantes' => $totalAsignadoParticipantes,
            'totalPagadoParticipantes' => $totalPagadoParticipantes,
            'saldoPorPagarParticipantes' => $saldoPorPagarParticipantes,
            'balanceBruto' => $balanceBruto,
        ]);
    }
}
