<?php

namespace App\Http\Controllers;

use App\Models\Evento;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Disponibilidad pública por día (sin nombres ni horas, solo una señal).
 *   libre     -> no aparece en la lista
 *   consulta  -> ya tiene eventos, se revisa el horario con el cliente
 *   lleno     -> alcanzó el máximo de eventos del día
 */
class DisponibilidadController extends Controller
{
    /** Cuántos shows se pueden tomar el mismo día (ajústalo a tu operación). */
    private const MAX_EVENTOS_DIA = 2;

    public function __invoke(Request $request)
    {
        $mes = $request->query('mes');
        if (!is_string($mes) || !preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
            $mes = now()->format('Y-m');
        }

        $inicio = Carbon::createFromFormat('Y-m-d', $mes . '-01')->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        $conteo = Evento::where('estado', '!=', 'Cancelado')
            ->whereBetween('fecha_evento', [$inicio->toDateString(), $fin->toDateString()])
            ->selectRaw('DATE(fecha_evento) as dia, COUNT(*) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $dias = [];
        foreach ($conteo as $dia => $total) {
            $dias[$dia] = $total >= self::MAX_EVENTOS_DIA ? 'lleno' : 'consulta';
        }

        return response()->json(['dias' => $dias]);
    }
}