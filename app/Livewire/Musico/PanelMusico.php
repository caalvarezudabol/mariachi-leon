<?php

namespace App\Livewire\Musico;

use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Models\MusicoPersonal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Panel del músico: agenda por semana y sus pagos.
 * Todo se filtra en el servidor por el músico vinculado al usuario que inició sesión.
 */
class PanelMusico extends Component
{
    public $tab = 'shows';   // shows | pagos
    public $semana = 0;      // 0 = esta semana, -1 = anterior, 1 = siguiente...
    public $dia = null;      // 'Y-m-d' si se eligió un día de la semana

    public function semanaAnterior()
    {
        $this->semana--;
        $this->dia = null;
    }
    public function semanaSiguiente()
    {
        $this->semana++;
        $this->dia = null;
    }
    public function irHoy()
    {
        $this->semana = 0;
        $this->dia = null;
    }
    public function elegirDia($fecha)
    {
        $this->dia = $this->dia === $fecha ? null : $fecha;
    }

    private function persona(): MusicoPersonal
    {
        $p = MusicoPersonal::where('user_id', Auth::id())->first();
        abort_unless($p, 403, 'Tu usuario no está vinculado a ningún músico.');
        return $p;
    }

    /**
     * Shows entre dos fechas. El mensualista ve TODA la agenda; el resto, solo donde está asignado.
     * Devuelve filas tipo "participante" (con ->evento) aunque el mensualista no esté asignado.
     */
    private function showsEntre(MusicoPersonal $persona, bool $esMensual, string $desde, ?string $hasta = null, ?int $limit = null)
    {
        $q = Evento::with(['cliente', 'servicio'])
            ->where('estado', '!=', 'Cancelado')
            ->where('fecha_evento', '>=', $desde);
        if ($hasta) {
            $q->where('fecha_evento', '<=', $hasta);
        }
        if (!$esMensual) {
            $q->whereIn('id', EventoParticipante::where('persona_id', $persona->id)->select('evento_id'));
        }
        $q->orderBy('fecha_evento')->orderBy('hora_evento');
        if ($limit) {
            $q->limit($limit);
        }
        $eventos = $q->get();

        $mios = EventoParticipante::where('persona_id', $persona->id)
            ->whereIn('evento_id', $eventos->pluck('id'))
            ->get()
            ->keyBy('evento_id');

        return $eventos->map(function ($ev) use ($mios, $persona) {
            $ep = $mios[$ev->id] ?? (new EventoParticipante())->forceFill(['evento_id' => $ev->id, 'persona_id' => $persona->id]);
            $ep->setRelation('evento', $ev);
            return $ep;
        })->values();
    }

    public function render()
    {
        $persona = $this->persona();
        $esMensual = $persona->modalidad_pago === 'mensual';
        $hoy = now()->toDateString();

        // ---- Agenda de la semana ----
        $ini = now()->startOfWeek()->addWeeks((int) $this->semana);
        $fin = $ini->copy()->endOfWeek();
        $semanaShows = $this->showsEntre($persona, $esMensual, $ini->toDateString(), $fin->toDateString());

        // Cuántos shows hay cada día (para la tira de días)
        $conteoDia = $semanaShows->groupBy(fn($ep) => $ep->evento->fecha_evento->format('Y-m-d'))->map->count();

        // Lista a mostrar: la semana completa o el día elegido
        $lista = $this->dia
            ? $semanaShows->filter(fn($ep) => $ep->evento->fecha_evento->format('Y-m-d') === $this->dia)->values()
            : $semanaShows;
        $porDia = $lista->groupBy(fn($ep) => $ep->evento->fecha_evento->format('Y-m-d'));

        // Próximos shows (para el aviso grande), sin importar la semana que se mira
        $proximos = $this->showsEntre($persona, $esMensual, $hoy, null, 5);

        // Compañeros de cada show (solo nombres)
        $companeros = [];
        $ids = $semanaShows->pluck('evento_id')->merge($proximos->pluck('evento_id'))->unique()->values();
        if ($ids->isNotEmpty()) {
            $filas = DB::table('evento_participantes as ep')
                ->join('musicos_personal as m', 'm.id', '=', 'ep.persona_id')
                ->whereIn('ep.evento_id', $ids)
                ->where('ep.persona_id', '!=', $persona->id)
                ->select('ep.evento_id', 'm.nombre_completo')
                ->orderBy('m.nombre_completo')
                ->get();
            foreach ($filas as $f) {
                $companeros[$f->evento_id][] = $f->nombre_completo;
            }
        }

        // ---- Mis pagos ----
        // Por evento: solo se ve la SEMANA que se está mirando (shows de esa semana y lo pagado por ellos).
        // Mensualista: su sueldo del mes.
        $pagosBase = DB::table('pagos_participantes as pp')
            ->leftJoin('evento_participantes as ep', 'ep.id', '=', 'pp.evento_participante_id')
            ->where('pp.estado', 'Valido');

        if ($esMensual) {
            $totalShows = DB::table('evento_participantes as ep')
                ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
                ->where('ep.persona_id', $persona->id)->where('e.estado', '!=', 'Cancelado')->count();
            $asignado = (float) $persona->sueldo_mensual;
            $pagosQ = $pagosBase
                ->whereRaw('COALESCE(pp.persona_id, ep.persona_id) = ?', [$persona->id])
                ->where('pp.fecha_pago', '>=', now()->startOfMonth()->toDateTimeString());
        } else {
            $epSemana = DB::table('evento_participantes as ep')
                ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
                ->where('ep.persona_id', $persona->id)
                ->where('e.estado', '!=', 'Cancelado')
                ->whereBetween('e.fecha_evento', [$ini->toDateString(), $fin->toDateString()]);
            $totalShows = (clone $epSemana)->count();
            $asignado = (float) (clone $epSemana)->sum('ep.monto_asignado');
            $idsSemana = (clone $epSemana)->pluck('ep.id');
            $pagosQ = $pagosBase->whereIn('pp.evento_participante_id', $idsSemana);
        }

        $recibido = (float) (clone $pagosQ)->sum('pp.monto');
        $saldo = round($asignado - $recibido, 2);

        $pagos = (clone $pagosQ)
            ->select('pp.fecha_pago', 'pp.monto', 'pp.tipo', 'pp.metodo_pago', 'pp.glosa')
            ->orderByDesc('pp.fecha_pago')
            ->limit(30)
            ->get();

        $showsPago = collect();
        if (!$esMensual && $this->tab === 'pagos') {
            $showsPago = EventoParticipante::with(['evento.cliente', 'evento.servicio'])
                ->where('persona_id', $persona->id)
                ->whereHas('evento', fn($q) => $q->where('estado', '!=', 'Cancelado')
                    ->whereBetween('fecha_evento', [$ini->toDateString(), $fin->toDateString()]))
                ->get()
                ->sortBy(fn($ep) => $ep->evento->fecha_evento)
                ->values();
        }

        return view('livewire.musico.panel-musico', compact(
            'persona',
            'ini',
            'fin',
            'conteoDia',
            'porDia',
            'proximos',
            'companeros',
            'esMensual',
            'totalShows',
            'asignado',
            'recibido',
            'saldo',
            'pagos',
            'showsPago'
        ))->layout('components.layouts.musico', ['title' => 'Mi panel - Mariachi León']);
    }
}