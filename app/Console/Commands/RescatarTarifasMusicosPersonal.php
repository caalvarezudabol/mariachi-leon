<?php

namespace App\Console\Commands;

use App\Models\ServicioParticipante;
use Illuminate\Console\Command;

/**
 * Antes de apagar las plantillas de servicio, rescata el monto que cada persona
 * tenía configurado ahí como su tarifa_evento inicial en musicos_personal.
 *
 * Por defecto solo MUESTRA la lista persona -> monto (no escribe nada).
 * Pasar --aplicar para guardar tarifa_evento en los casos sin conflicto.
 */
class RescatarTarifasMusicosPersonal extends Command
{
    protected $signature = 'musicos:rescatar-tarifas {--aplicar : Guarda tarifa_evento en musicos_personal en vez de solo mostrar la lista}';

    protected $description = 'Vista previa (u opcionalmente aplicación) del rescate de tarifas desde servicio_participantes hacia musicos_personal.tarifa_evento';

    public function handle(): int
    {
        $aplicar = (bool) $this->option('aplicar');

        $porPersona = ServicioParticipante::with('persona')->get()->groupBy('persona_id');

        if ($porPersona->isEmpty()) {
            $this->info('No hay filas en servicio_participantes.');
            return self::SUCCESS;
        }

        $rows = [];
        foreach ($porPersona as $items) {
            $persona = $items->first()->persona;
            if (!$persona) {
                continue;
            }

            $valores = $items->pluck('valor')->unique();
            $monto = (float) $valores->first();
            $conflicto = $valores->count() > 1;

            $rows[] = [
                $persona->id,
                $persona->nombre_completo,
                $persona->tarifa_evento !== null ? number_format((float) $persona->tarifa_evento, 2) : '-',
                number_format($monto, 2),
                $conflicto ? 'SÍ - revisar a mano' : 'No',
            ];

            if ($aplicar && !$conflicto) {
                $persona->forceFill([
                    'tarifa_evento' => $monto,
                    'modalidad_pago' => $persona->modalidad_pago ?: 'por_evento',
                ])->save();
            }
        }

        $this->table(['ID', 'Persona', 'tarifa_evento actual', 'Monto en plantilla', 'Montos distintos'], $rows);

        if ($aplicar) {
            $this->info('tarifa_evento actualizada para los casos sin conflicto.');
        } else {
            $this->warn('Vista previa solamente. Ejecuta con --aplicar para guardar tarifa_evento.');
        }

        return self::SUCCESS;
    }
}
