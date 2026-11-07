<?php

namespace App\Services\Comercial;

use App\Models\DistribucionEconomica;
use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Models\MusicoPersonal;
use Illuminate\Support\Facades\DB;

/**
 * Punto único para resolver el monto sugerido de una persona y para crear/sincronizar
 * EventoParticipante + DistribucionEconomica de un evento. Reemplaza la lógica duplicada
 * que hoy existe en GestionEventos, GestionContratos, GestionAgenda y GestionParticipantesEvento.
 *
 * El monto ya NO se resuelve contra plantillas de servicio: sale directo del catálogo
 * de personas (musicos_personal.tarifa_evento / modalidad_pago).
 */
class AsignacionParticipantesService
{
    public function esMensual(MusicoPersonal $persona): bool
    {
        return ($persona->modalidad_pago ?? 'por_evento') === 'mensual';
    }

    public function conceptoPago(MusicoPersonal $persona): string
    {
        return $this->esMensual($persona) ? 'Sueldo mensual' : 'Comisión por Evento';
    }

    /**
     * Monto sugerido para un evento: la tarifa_evento de la persona si es por_evento,
     * 0 si es mensual (el sueldo fijo se paga aparte, no por evento), o el monto forzado
     * si se pasa uno explícito (p.ej. al copiar el equipo de otro evento).
     */
    public function resolverMontoEvento(MusicoPersonal $persona, ?float $montoForzado = null): float
    {
        if ($montoForzado !== null) {
            return $montoForzado;
        }

        return $this->esMensual($persona) ? 0.0 : (float) ($persona->tarifa_evento ?? 0);
    }

    /**
     * Arma el item con la misma forma que usan los formularios de evento
     * (participantesSugeridos) para una persona del catálogo.
     */
    public function itemDesdePersona(MusicoPersonal $persona, ?float $montoForzado = null): array
    {
        $monto = $this->resolverMontoEvento($persona, $montoForzado);

        return [
            'persona_id' => $persona->id,
            'nombre_completo' => $persona->nombre_completo,
            'ci_nit' => $persona->ci_nit ?? '',
            'funcion' => $persona->tipo ?: 'Músico',
            'concepto_pago' => $this->conceptoPago($persona),
            'tipo_calculo' => 'monto_fijo',
            'valor_sugerido' => $monto,
            'monto_sugerido' => $monto,
            'monto_asignado' => $monto,
            'modalidad' => $this->esMensual($persona) ? 'mensual' : 'por_evento',
            'incluido' => true,
            'origen' => 'adicional',
            'tiene_pagos' => false,
            'total_pagado' => 0.0,
            'evento_participante_id' => null,
        ];
    }

    /**
     * Estado automático de una asignación: Pendiente / Pago Parcial / Distribuido.
     */
    public function calcularEstado(float $montoAsignado, float $totalPagado): string
    {
        if ($totalPagado <= 0) {
            return 'Pendiente';
        }

        $saldo = max(0.0, $montoAsignado - $totalPagado);

        return $saldo <= 0.001 ? 'Distribuido' : 'Pago Parcial';
    }

    /**
     * Crea o actualiza el EventoParticipante + DistribucionEconomica de una persona en un evento.
     */
    public function asignar(Evento $evento, array $item, ?int $userId = null): EventoParticipante
    {
        $montoFinal = (float) ($item['monto_asignado'] ?? 0);
        $estado = $this->calcularEstado($montoFinal, (float) ($item['total_pagado'] ?? 0));

        $ep = EventoParticipante::updateOrCreate(
            [
                'evento_id' => $evento->id,
                'persona_id' => $item['persona_id'],
            ],
            [
                'funcion' => $item['funcion'] ?? 'Músico',
                'concepto_pago' => $item['concepto_pago'] ?? 'Comisión por Evento',
                'tipo_calculo' => $item['tipo_calculo'] ?? 'monto_fijo',
                'valor_sugerido' => $item['valor_sugerido'] ?? $montoFinal,
                'monto_asignado' => $montoFinal,
                'estado_distribucion' => $estado,
                'user_id' => $userId,
            ]
        );

        DistribucionEconomica::updateOrCreate(
            [
                'evento_id' => $evento->id,
                'persona_id' => $item['persona_id'],
            ],
            [
                'contrato_id' => optional($evento->contrato)->id,
                'evento_participante_id' => $ep->id,
                'funcion' => $item['funcion'] ?? 'Músico',
                'concepto' => $item['concepto_pago'] ?? 'Comisión por Evento',
                'monto' => $montoFinal,
                'fecha_distribucion' => now(),
                'estado' => $estado,
                'user_id' => $userId,
            ]
        );

        return $ep;
    }

    /**
     * Quita a una persona de un evento (participante + distribución). Lanza excepción
     * si ya tiene pagos válidos registrados, igual que la validación actual en los modales.
     */
    public function desasignar(EventoParticipante $ep): void
    {
        if ($ep->total_pagado > 0) {
            $nombre = $ep->persona->nombre_completo ?? 'esta persona';
            throw new \RuntimeException("No se puede excluir a {$nombre} porque ya registra pagos confirmados en este evento.");
        }

        DistribucionEconomica::where('evento_participante_id', $ep->id)->delete();
        $ep->delete();
    }

    /**
     * Sincroniza la lista completa de participantes de un evento (equivalente al bloque
     * que hoy corre dentro de GestionEventos::guardar()): asigna a los incluidos, retira
     * a los desmarcados sin pagos, y desvincula a quien haya desaparecido de la lista
     * (edición) sin pagos registrados. Devuelve el total asignado y el conteo confirmado.
     *
     * @param  array<int, array<string, mixed>>  $items  Items con la forma de itemDesdePersona().
     */
    public function sincronizar(Evento $evento, array $items, ?int $userId = null): array
    {
        return DB::transaction(function () use ($evento, $items, $userId) {
            $totalAsignado = 0.0;
            $countConfirmados = 0;
            $incluidosPersonaIds = [];

            foreach ($items as $item) {
                if (!empty($item['incluido'])) {
                    $this->asignar($evento, $item, $userId);
                    $totalAsignado += (float) ($item['monto_asignado'] ?? 0);
                    $countConfirmados++;
                    $incluidosPersonaIds[] = $item['persona_id'];
                } elseif (!empty($item['evento_participante_id'])) {
                    $ep = EventoParticipante::find($item['evento_participante_id']);
                    if ($ep) {
                        $this->desasignar($ep);
                    }
                }
            }

            $existentes = EventoParticipante::where('evento_id', $evento->id)->get();
            foreach ($existentes as $epBd) {
                if (!in_array($epBd->persona_id, $incluidosPersonaIds)) {
                    if ($epBd->pagosValidos()->count() > 0) {
                        continue;
                    }
                    DistribucionEconomica::where('evento_participante_id', $epBd->id)->delete();
                    $epBd->delete();
                }
            }

            return [
                'total_asignado' => $totalAsignado,
                'count_confirmados' => $countConfirmados,
            ];
        });
    }
}
