<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use App\Models\Evento;
use Carbon\Carbon;

class GestionAgenda extends Component
{
    public $vistaModo = 'mes'; // 'dia', 'semana', 'mes'
    public $fechaSeleccionada = '';
    public $eventoIdDetalle = null;
    public $modalDetalleOpen = false;

    public function mount()
    {
        $this->fechaSeleccionada = date('Y-m-d');
    }

    public function cambiarVista($modo)
    {
        $this->vistaModo = $modo;
    }

    public function verDetalleEvento($id)
    {
        $this->eventoIdDetalle = $id;
        $this->modalDetalleOpen = true;
    }

    public function cerrarModalDetalle()
    {
        $this->modalDetalleOpen = false;
        $this->eventoIdDetalle = null;
    }

    public function irADia($fechaStr)
    {
        $this->fechaSeleccionada = $fechaStr;
        $this->vistaModo = 'dia';
    }

    public function fechaAnterior()
    {
        $dt = Carbon::parse($this->fechaSeleccionada);
        if ($this->vistaModo === 'dia') {
            $this->fechaSeleccionada = $dt->subDay()->format('Y-m-d');
        } elseif ($this->vistaModo === 'semana') {
            $this->fechaSeleccionada = $dt->subWeek()->format('Y-m-d');
        } else {
            $this->fechaSeleccionada = $dt->subMonth()->format('Y-m-d');
        }
    }

    public function fechaSiguiente()
    {
        $dt = Carbon::parse($this->fechaSeleccionada);
        if ($this->vistaModo === 'dia') {
            $this->fechaSeleccionada = $dt->addDay()->format('Y-m-d');
        } elseif ($this->vistaModo === 'semana') {
            $this->fechaSeleccionada = $dt->addWeek()->format('Y-m-d');
        } else {
            $this->fechaSeleccionada = $dt->addMonth()->format('Y-m-d');
        }
    }

    public function fechaHoy()
    {
        $this->fechaSeleccionada = date('Y-m-d');
    }

    public function render()
    {
        Carbon::setLocale('es');
        $dt = Carbon::parse($this->fechaSeleccionada)->locale('es');

        if ($this->vistaModo === 'dia') {
            $inicio = $dt->copy()->startOfDay();
            $fin = $dt->copy()->endOfDay();
        } elseif ($this->vistaModo === 'semana') {
            $inicio = $dt->copy()->startOfWeek(Carbon::MONDAY);
            $fin = $dt->copy()->endOfWeek(Carbon::SUNDAY);
        } else {
            // Mes completo + compensación de días de la semana anterior y posterior
            $inicio = $dt->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $fin = $dt->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        }

        $eventos = Evento::with(['cliente', 'servicio', 'contrato'])
            ->whereBetween('fecha_evento', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->orderBy('fecha_evento', 'asc')
            ->orderBy('hora_evento', 'asc')
            ->get();

        // Detección de Conflictos de Horario
        $conflictos = [];
        $gruposFecha = $eventos->groupBy(function($item) {
            return is_object($item->fecha_evento) ? $item->fecha_evento->format('Y-m-d') : substr((string)$item->fecha_evento, 0, 10);
        });

        foreach ($gruposFecha as $fechaStr => $evsEnFecha) {
            foreach ($evsEnFecha as $ev1) {
                foreach ($evsEnFecha as $ev2) {
                    if ($ev1->id !== $ev2->id && !in_array($ev1->estado, ['Cancelado']) && !in_array($ev2->estado, ['Cancelado'])) {
                        $h1Inicio = Carbon::parse($ev1->hora_evento);
                        $h1Fin = $h1Inicio->copy()->addMinutes((int)($ev1->duracion_horas * 60));

                        $h2Inicio = Carbon::parse($ev2->hora_evento);
                        $h2Fin = $h2Inicio->copy()->addMinutes((int)($ev2->duracion_horas * 60));

                        if ($h1Inicio < $h2Fin && $h1Fin > $h2Inicio) {
                            $conflictos[$ev1->id] = true;
                            $conflictos[$ev2->id] = true;
                        }
                    }
                }
            }
        }

        // Construcción de la matriz de días para el Calendario Mensual y Semanal
        $diasCalendario = [];
        $curr = $inicio->copy();
        while ($curr <= $fin) {
            $dateKey = $curr->format('Y-m-d');
            $evsDia = $gruposFecha->get($dateKey, collect());

            $diasCalendario[] = [
                'fecha' => $curr->copy(),
                'dateKey' => $dateKey,
                'diaNumero' => $curr->day,
                'esMesActual' => $curr->month === $dt->month,
                'esHoy' => $curr->isToday(),
                'eventos' => $evsDia
            ];
            $curr->addDay();
        }

        $eventoDetalle = null;
        if ($this->eventoIdDetalle) {
            $eventoDetalle = Evento::with(['cliente', 'servicio', 'contrato', 'participantes.integrante'])->find($this->eventoIdDetalle);
        }

        return view('livewire.comercial.gestion-agenda', [
            'eventos' => $eventos,
            'conflictos' => $conflictos,
            'diasCalendario' => $diasCalendario,
            'inicioPeriodo' => $inicio,
            'finPeriodo' => $fin,
            'fechaActual' => $dt,
            'eventoDetalle' => $eventoDetalle,
        ])->layout('components.layouts.app', ['title' => 'Gestionar Agenda - Mariachi León Guanajuato']);
    }
}
