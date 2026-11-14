<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use App\Models\Evento;
use App\Traits\Auditable;
use App\Traits\ManejaFormularioEvento;
use Carbon\Carbon;

class GestionAgenda extends Component
{
    use Auditable, ManejaFormularioEvento;

    public $vistaModo = 'semana'; // 'dia', 'semana', 'mes'
    public $fechaSeleccionada = '';
    public $eventoIdDetalle = null;
    public $modalDetalleOpen = false;

    public function mount()
    {
        $this->fechaSeleccionada = date('Y-m-d');
    }

    public function cambiarVista($modo)
    {
        if (in_array($modo, ['dia', 'semana', 'mes'], true)) {
            $this->vistaModo = $modo;
        }
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

    // Ver el día completo (cambia a la vista Día)
    public function irADia($fechaStr)
    {
        $this->fechaSeleccionada = $fechaStr;
        $this->vistaModo = 'dia';
    }

    public function fechaAnterior()
    {
        $dt = $this->fechaBase();
        if ($this->vistaModo === 'dia') {
            $this->fechaSeleccionada = $dt->subDay()->format('Y-m-d');
        } elseif ($this->vistaModo === 'semana') {
            $this->fechaSeleccionada = $dt->subWeek()->format('Y-m-d');
        } else {
            $this->fechaSeleccionada = $dt->subMonthNoOverflow()->format('Y-m-d');
        }
    }

    public function fechaSiguiente()
    {
        $dt = $this->fechaBase();
        if ($this->vistaModo === 'dia') {
            $this->fechaSeleccionada = $dt->addDay()->format('Y-m-d');
        } elseif ($this->vistaModo === 'semana') {
            $this->fechaSeleccionada = $dt->addWeek()->format('Y-m-d');
        } else {
            $this->fechaSeleccionada = $dt->addMonthNoOverflow()->format('Y-m-d');
        }
    }

    public function fechaHoy()
    {
        $this->fechaSeleccionada = date('Y-m-d');
    }

    /**
     * Nuevo evento: abre el mismo formulario de Eventos (con contrato, adelanto y equipo)
     * aquí mismo, con la fecha ya puesta.
     */
    public function abrirNuevoEvento($fecha = null)
    {
        if (!$fecha) {
            $fecha = $this->vistaModo === 'dia' && $this->fechaSeleccionada
                ? $this->fechaSeleccionada
                : date('Y-m-d');
        }

        $this->abrirModal($fecha);
    }

    // Desde el detalle de un evento: abre el mismo formulario para editarlo
    public function editarDesdeDetalle()
    {
        $id = $this->eventoIdDetalle;
        $this->cerrarModalDetalle();
        if ($id) {
            $this->editar($id);
        }
    }

    private function fechaBase(): Carbon
    {
        return $this->fechaSeleccionada
            ? Carbon::parse($this->fechaSeleccionada)
            : Carbon::today();
    }

    private function aMinutos($hora): int
    {
        $hora = (string) $hora;

        return ((int) substr($hora, 0, 2)) * 60 + (int) substr($hora, 3, 2);
    }

    // Agrega hora_txt y hora_fin_txt (HH:MM) al evento
    private function conHoras($ev)
    {
        $ini = $this->aMinutos($ev->hora_evento);
        $finMin = $ini + (int) round(((float) $ev->duracion_horas) * 60);
        $ev->hora_txt = sprintf('%02d:%02d', intdiv($ini, 60) % 24, $ini % 60);
        $ev->hora_fin_txt = sprintf('%02d:%02d', intdiv($finMin, 60) % 24, $finMin % 60);

        return $ev;
    }

    public function render()
    {
        Carbon::setLocale('es');
        $dt = $this->fechaBase()->locale('es');

        if ($this->vistaModo === 'dia') {
            $inicio = $dt->copy()->startOfDay();
            $fin = $dt->copy()->endOfDay();
        } elseif ($this->vistaModo === 'semana') {
            $inicio = $dt->copy()->startOfWeek(Carbon::MONDAY);
            $fin = $dt->copy()->endOfWeek(Carbon::SUNDAY);
        } else {
            // Mes completo + días de la semana anterior y posterior
            $inicio = $dt->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
            $fin = $dt->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        }

        $eventos = Evento::with(['cliente', 'servicio'])
            ->whereBetween('fecha_evento', [$inicio->format('Y-m-d'), $fin->format('Y-m-d')])
            ->orderBy('fecha_evento', 'asc')
            ->orderBy('hora_evento', 'asc')
            ->get();

        foreach ($eventos as $ev) {
            $this->conHoras($ev);
        }

        $gruposFecha = $eventos->groupBy(function ($item) {
            return is_object($item->fecha_evento) ? $item->fecha_evento->format('Y-m-d') : substr((string) $item->fecha_evento, 0, 10);
        });

        // Conflictos de horario (mismo día, horarios que se cruzan)
        $conflictos = [];
        $conflictosPares = [];

        foreach ($gruposFecha as $fechaStr => $evsEnFecha) {
            $lista = $evsEnFecha->reject(fn($e) => $e->estado === 'Cancelado')->values();
            $n = $lista->count();

            for ($i = 0; $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $e1 = $lista[$i];
                    $e2 = $lista[$j];

                    $s1 = $this->aMinutos($e1->hora_evento);
                    $f1 = $s1 + (int) round(((float) $e1->duracion_horas) * 60);
                    $s2 = $this->aMinutos($e2->hora_evento);
                    $f2 = $s2 + (int) round(((float) $e2->duracion_horas) * 60);

                    if ($s1 < $f2 && $f1 > $s2) {
                        $conflictos[$e1->id] = true;
                        $conflictos[$e2->id] = true;
                        $conflictosPares[] = [
                            'fecha' => $fechaStr,
                            'a' => $e1->codigo_evento,
                            'b' => $e2->codigo_evento,
                        ];
                    }
                }
            }
        }

        // Días para las vistas de mes y semana
        $diasCalendario = [];
        $curr = $inicio->copy();
        while ($curr <= $fin) {
            $dateKey = $curr->format('Y-m-d');

            $diasCalendario[] = [
                'fecha' => $curr->copy(),
                'dateKey' => $dateKey,
                'diaNumero' => $curr->day,
                'esMesActual' => $curr->month === $dt->month,
                'esHoy' => $curr->isToday(),
                'eventos' => $gruposFecha->get($dateKey, collect()),
            ];
            $curr->addDay();
        }

        // Resumen del período visible (sin contar cancelados)
        $activos = $eventos->reject(fn($e) => $e->estado === 'Cancelado');
        $resumen = [
            'total' => $activos->count(),
            'confirmados' => $activos->where('estado', 'Confirmado')->count(),
            'porConfirmar' => $activos->whereIn('estado', ['Pendiente', 'Cotizado', 'Reservado'])->count(),
            'realizados' => $activos->whereIn('estado', ['Realizado', 'Finalizado'])->count(),
        ];
        // En vista mes, el resumen cuenta solo el mes que se está viendo
        if ($this->vistaModo === 'mes') {
            $delMes = $activos->filter(fn($e) => $e->fecha_evento->month === $dt->month && $e->fecha_evento->year === $dt->year);
            $resumen = [
                'total' => $delMes->count(),
                'confirmados' => $delMes->where('estado', 'Confirmado')->count(),
                'porConfirmar' => $delMes->whereIn('estado', ['Pendiente', 'Cotizado', 'Reservado'])->count(),
                'realizados' => $delMes->whereIn('estado', ['Realizado', 'Finalizado'])->count(),
            ];
        }

        $eventoDetalle = null;
        if ($this->eventoIdDetalle) {
            $eventoDetalle = Evento::with(['cliente', 'servicio'])->find($this->eventoIdDetalle);
            if ($eventoDetalle) {
                $this->conHoras($eventoDetalle);
            }
        }

        return view('livewire.comercial.gestion-agenda', [
            'eventos' => $eventos,
            'conflictos' => $conflictos,
            'conflictosPares' => $conflictosPares,
            'diasCalendario' => $diasCalendario,
            'inicioPeriodo' => $inicio,
            'finPeriodo' => $fin,
            'fechaActual' => $dt,
            'eventoDetalle' => $eventoDetalle,
            'resumen' => $resumen,
        ] + $this->datosFormularioEvento())->layout('components.layouts.app', [
            'title' => 'Gestionar Agenda - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}