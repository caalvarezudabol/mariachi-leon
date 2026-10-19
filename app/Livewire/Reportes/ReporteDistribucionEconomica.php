<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use App\Models\Evento;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use App\Traits\Auditable;

class ReporteDistribucionEconomica extends Component
{
    use Auditable;

    public $modalidad = 'consolidado'; // 'consolidado', 'por_evento', 'por_participante'
    public $evento_id = '';
    public $persona_id = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $search = '';

    public function mount()
    {
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');

        // Seleccionar primer evento si existe
        $primerEvento = Evento::has('participantes')->orderBy('fecha_evento', 'desc')->first();
        if ($primerEvento) {
            $this->evento_id = $primerEvento->id;
        }

        // Seleccionar primer participante si existe
        $primerMusico = MusicoPersonal::has('participaciones')->orderBy('nombre_completo')->first();
        if ($primerMusico) {
            $this->persona_id = $primerMusico->id;
        }

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Consultar Distribución Económica',
            'El usuario accedió al reporte de distribución económica y comisiones de participantes.'
        );
    }

    public function updatedModalidad()
    {
        // Al cambiar de modalidad, registrar auditoría
        $this->registrarAuditoria(
            'Informes y Reportes',
            'Cambiar Vista Distribución Económica',
            "Cambio de vista a modalidad: {$this->modalidad}."
        );
    }

    public function render()
    {
        $eventosLista = Evento::with('cliente')->orderBy('fecha_evento', 'desc')->limit(60)->get();
        $musicosLista = MusicoPersonal::where('estado', 'Activo')->orderBy('nombre_completo')->get();

        $datosVista = [];

        if ($this->modalidad === 'por_evento') {
            $evento = null;
            $participantes = collect();
            $resumenEvento = [
                'total_asignado' => 0.0,
                'total_pagado' => 0.0,
                'saldo_pendiente' => 0.0,
                'cantidad_participantes' => 0,
            ];

            if ($this->evento_id) {
                $evento = Evento::with(['cliente', 'servicio', 'contrato.pagos', 'participantes.persona', 'participantes.pagos'])->find($this->evento_id);
                if ($evento) {
                    $participantes = $evento->participantes;
                    $totalAsignado = (float)$participantes->sum('monto_asignado');
                    $totalPagado = (float)$participantes->sum('total_pagado');
                    $saldoPendiente = max(0.0, $totalAsignado - $totalPagado);

                    $resumenEvento = [
                        'total_asignado' => $totalAsignado,
                        'total_pagado' => $totalPagado,
                        'saldo_pendiente' => $saldoPendiente,
                        'cantidad_participantes' => $participantes->count(),
                    ];
                }
            }

            $datosVista = [
                'evento' => $evento,
                'participantes' => $participantes,
                'resumenEvento' => $resumenEvento,
            ];

        } elseif ($this->modalidad === 'por_participante') {
            $persona = null;
            $participaciones = collect();
            $resumenParticipante = [
                'total_asignado' => 0.0,
                'total_pagado' => 0.0,
                'saldo_pendiente' => 0.0,
                'total_eventos' => 0,
            ];

            if ($this->persona_id) {
                $persona = MusicoPersonal::find($this->persona_id);
                if ($persona) {
                    $query = EventoParticipante::with(['evento.cliente', 'pagos'])
                        ->where('persona_id', $this->persona_id);

                    if ($this->fecha_desde) {
                        $query->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '>=', $this->fecha_desde));
                    }
                    if ($this->fecha_hasta) {
                        $query->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '<=', $this->fecha_hasta));
                    }

                    $participaciones = $query->get()->sortByDesc(fn($p) => $p->evento->fecha_evento ?? now())->values();

                    $totalAsignado = (float)$participaciones->sum('monto_asignado');
                    $totalPagado = (float)$participaciones->sum('total_pagado');
                    $saldoPendiente = max(0.0, $totalAsignado - $totalPagado);

                    $resumenParticipante = [
                        'total_asignado' => $totalAsignado,
                        'total_pagado' => $totalPagado,
                        'saldo_pendiente' => $saldoPendiente,
                        'total_eventos' => $participaciones->count(),
                    ];
                }
            }

            $datosVista = [
                'persona' => $persona,
                'participaciones' => $participaciones,
                'resumenParticipante' => $resumenParticipante,
            ];

        } else {
            // Modalidad Consolidado por Período
            $musicosQuery = MusicoPersonal::with(['participaciones' => function ($q) {
                if ($this->fecha_desde) {
                    $q->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '>=', $this->fecha_desde));
                }
                if ($this->fecha_hasta) {
                    $q->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '<=', $this->fecha_hasta));
                }
                $q->with('pagos');
            }]);

            if ($this->search) {
                $musicosQuery->where(function ($q) {
                    $q->where('nombre_completo', 'like', '%' . $this->search . '%')
                      ->orWhere('ci_nit', 'like', '%' . $this->search . '%')
                      ->orWhere('telefono', 'like', '%' . $this->search . '%');
                });
            }

            $musicos = $musicosQuery->orderBy('nombre_completo')->get();

            // Calcular totales agregados
            $filasConsolidadas = collect();
            $granTotalAsignado = 0.0;
            $granTotalPagado = 0.0;
            $granTotalPendiente = 0.0;

            foreach ($musicos as $m) {
                $parts = $m->participaciones;
                $cantEventos = $parts->count();
                $asig = (float)$parts->sum('monto_asignado');
                $pag = (float)$parts->sum('total_pagado');
                $saldo = max(0.0, $asig - $pag);

                $granTotalAsignado += $asig;
                $granTotalPagado += $pag;
                $granTotalPendiente += $saldo;

                $filasConsolidadas->push([
                    'id' => $m->id,
                    'nombre_completo' => $m->nombre_completo,
                    'tipo' => $m->tipo,
                    'telefono' => $m->telefono,
                    'total_eventos' => $cantEventos,
                    'total_asignado' => $asig,
                    'total_pagado' => $pag,
                    'saldo_pendiente' => $saldo,
                    'estado_global' => $saldo <= 0.01 ? 'Al Día' : 'Saldo Pendiente',
                ]);
            }

            $datosVista = [
                'filasConsolidadas' => $filasConsolidadas,
                'resumenGlobal' => [
                    'total_musicos' => $musicos->count(),
                    'total_asignado' => $granTotalAsignado,
                    'total_pagado' => $granTotalPagado,
                    'saldo_pendiente' => $granTotalPendiente,
                ],
            ];
        }

        return view('livewire.reportes.reporte-distribucion-economica', array_merge($datosVista, [
            'eventosLista' => $eventosLista,
            'musicosLista' => $musicosLista,
            'modalidad' => $this->modalidad,
            'evento_id' => $this->evento_id,
            'persona_id' => $this->persona_id,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'search' => $this->search,
        ]))->layout('components.layouts.app', ['title' => 'Distribución Económica a Músicos - Mariachi León Guanajuato']);
    }
}
