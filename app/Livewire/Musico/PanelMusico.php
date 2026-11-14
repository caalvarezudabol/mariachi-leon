<?php

namespace App\Livewire\Musico;

use App\Models\EventoParticipante;
use App\Models\MusicoPersonal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

/**
 * Panel del músico: solo ve SUS shows y SUS pagos.
 * Todo se filtra en el servidor por el músico vinculado al usuario que inició sesión.
 */
class PanelMusico extends Component
{
    public $tab = 'shows';      // shows | pagos
    public $verPasados = false; // en "Mis shows": incluir los ya realizados

    private function persona(): MusicoPersonal
    {
        $p = MusicoPersonal::where('user_id', Auth::id())->first();
        abort_unless($p, 403, 'Tu usuario no está vinculado a ningún músico.');
        return $p;
    }

    public function render()
    {
        $persona = $this->persona();
        $hoy = now()->toDateString();

        // ---- Mis shows ----
        $shows = EventoParticipante::with(['evento.cliente', 'evento.servicio'])
            ->where('persona_id', $persona->id)
            ->whereHas('evento', function ($q) use ($hoy) {
                $q->where('estado', '!=', 'Cancelado');
                $this->verPasados
                    ? $q->where('fecha_evento', '<', $hoy)
                    : $q->where('fecha_evento', '>=', $hoy);
            })
            ->get()
            ->sortBy(fn ($ep) => ($ep->evento->fecha_evento?->format('Y-m-d') ?? '') . ' ' . $ep->evento->hora_evento, SORT_REGULAR, $this->verPasados)
            ->take(40)
            ->values();

        // Compañeros de cada show (solo nombres)
        $companeros = [];
        if ($shows->isNotEmpty()) {
            $filas = DB::table('evento_participantes as ep')
                ->join('musicos_personal as m', 'm.id', '=', 'ep.persona_id')
                ->whereIn('ep.evento_id', $shows->pluck('evento_id'))
                ->where('ep.persona_id', '!=', $persona->id)
                ->select('ep.evento_id', 'm.nombre_completo')
                ->orderBy('m.nombre_completo')
                ->get();
            foreach ($filas as $f) {
                $companeros[$f->evento_id][] = $f->nombre_completo;
            }
        }

        // ---- Mis pagos ----
        $esMensual = $persona->modalidad_pago === 'mensual';
        $asignadoQ = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->where('ep.persona_id', $persona->id)
            ->where('e.estado', '!=', 'Cancelado');

        $totalShows = (clone $asignadoQ)->count();
        $asignado = $esMensual ? (float) $persona->sueldo_mensual : (float) (clone $asignadoQ)->sum('ep.monto_asignado');

        $pagosQ = DB::table('pagos_participantes as pp')
            ->leftJoin('evento_participantes as ep', 'ep.id', '=', 'pp.evento_participante_id')
            ->where('pp.estado', 'Valido')
            ->whereRaw('COALESCE(pp.persona_id, ep.persona_id) = ?', [$persona->id])
            ->when($esMensual, fn ($q) => $q->where('pp.fecha_pago', '>=', now()->startOfMonth()->toDateTimeString()));

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
                ->whereHas('evento', fn ($q) => $q->where('estado', '!=', 'Cancelado'))
                ->get()
                ->sortByDesc(fn ($ep) => $ep->evento->fecha_evento)
                ->take(20)
                ->values();
        }

        return view('livewire.musico.panel-musico', compact(
            'persona', 'shows', 'companeros', 'esMensual', 'totalShows', 'asignado', 'recibido', 'saldo', 'pagos', 'showsPago'
        ))->layout('components.layouts.musico', ['title' => 'Mi panel - Mariachi León']);
    }
}
