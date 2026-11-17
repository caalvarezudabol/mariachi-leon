<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use App\Models\EventoParticipante;
use App\Models\FondoComunMovimiento;
use App\Models\MovimientoFinanciero;
use App\Models\MusicoPersonal;
use App\Models\Pago;
use App\Models\PagoParticipante;
use App\Traits\Auditable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionPagosPersonal extends Component
{
    use Auditable;

    // Filtros
    public $search = '';
    public $filtro = 'fechas'; // pendientes | fechas | todos
    public $desde = '';
    public $hasta = '';

    // Filas desplegadas (ids de persona)
    public $abiertos = [];

    // Modal pagar / anticipo
    public $modalOpen = false;
    public $personaId = null;
    public $modo = 'pago'; // pago | anticipo
    public $monto = '';
    public $metodo = 'Efectivo';
    public $fondo = ''; // lo que la persona deja en el fondo común (opcional)

    // Modal anular
    public $anularId = null;
    public $motivo = '';

    public function mount()
    {
        $this->rangoSemana();
        abort_unless($this->puedeGestionar(), 403);
    }

    private function puedeGestionar(): bool
    {
        $u = Auth::user();
        return $u && ($u->can('gestionar-pagos') || $u->hasRole('Administrador'));
    }

    public function toggleAbierto($id)
    {
        $id = (int) $id;
        $this->abiertos = in_array($id, $this->abiertos)
            ? array_values(array_diff($this->abiertos, [$id]))
            : array_merge($this->abiertos, [$id]);
    }

    // ------------------------------------------------------------------
    //  Cálculo de saldos
    // ------------------------------------------------------------------

    /** Total pagado vigente por persona (incluye pagos viejos ligados solo al participante). */
    private function pagadoPorPersona(?Carbon $desde = null)
    {
        return DB::table('pagos_participantes as pp')
            ->leftJoin('evento_participantes as ep', 'ep.id', '=', 'pp.evento_participante_id')
            ->where('pp.estado', 'Valido')
            ->when($desde, fn($q) => $q->where('pp.fecha_pago', '>=', $desde->toDateTimeString()))
            ->selectRaw('COALESCE(pp.persona_id, ep.persona_id) as pid, SUM(pp.monto) as total')
            ->groupByRaw('COALESCE(pp.persona_id, ep.persona_id)')
            ->pluck('total', 'pid');
    }

    // Si cambia una fecha a mano, se vuelve a la vista por fechas
    public function updatedDesde()
    {
        $this->filtro = 'fechas';
    }

    public function updatedHasta()
    {
        $this->filtro = 'fechas';
    }

    // ---- Rango de fechas (por defecto: la semana actual, lunes a domingo) ----
    public function rangoSemana()
    {
        $this->filtro = 'fechas';
        $this->desde = now()->startOfWeek()->toDateString();
        $this->hasta = now()->endOfWeek()->toDateString();
    }

    public function rangoHoy()
    {
        $this->desde = $this->hasta = now()->toDateString();
    }

    public function rangoMes()
    {
        $this->desde = now()->startOfMonth()->toDateString();
        $this->hasta = now()->endOfMonth()->toDateString();
    }

    // Mueve el rango hacia atrás (-1) o adelante (+1) tanto como dure (una semana si es una semana)
    public function moverRango($dir)
    {
        $this->filtro = 'fechas';
        [$d, $h] = $this->rangoActual();
        $dias = $d->diffInDays($h) + 1;
        if ($d->isSameDay($d->copy()->startOfMonth()) && $h->isSameDay($d->copy()->endOfMonth())) {
            $nd = $d->copy()->addMonthsNoOverflow((int) $dir)->startOfMonth();
            $this->desde = $nd->toDateString();
            $this->hasta = $nd->copy()->endOfMonth()->toDateString();
            return;
        }
        $this->desde = $d->copy()->addDays($dias * (int) $dir)->toDateString();
        $this->hasta = $h->copy()->addDays($dias * (int) $dir)->toDateString();
    }

    private function rangoActual(): array
    {
        try {
            $d = Carbon::parse($this->desde)->startOfDay();
            $h = Carbon::parse($this->hasta)->startOfDay();
        } catch (\Throwable $e) {
            $d = now()->startOfWeek()->startOfDay();
            $h = now()->endOfWeek()->startOfDay();
        }
        if ($h->lt($d)) {
            [$d, $h] = [$h, $d];
        }
        return [$d, $h];
    }

    /** Por persona (no mensualistas): lo asignado, lo pagado por esos shows y la cantidad, dentro de un periodo. */
    private function periodoPorPersona(Carbon $desde, Carbon $hasta): array
    {
        $filas = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->leftJoin('pagos_participantes as pp', function ($j) {
                $j->on('pp.evento_participante_id', '=', 'ep.id')->where('pp.estado', 'Valido');
            })
            ->where('e.estado', '!=', 'Cancelado')
            ->whereBetween('e.fecha_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('ep.persona_id as pid, COUNT(DISTINCT ep.id) as shows, COALESCE(SUM(pp.monto),0) as pagado')
            ->groupBy('ep.persona_id')
            ->get();

        // El asignado se suma aparte para no duplicar filas por varios pagos del mismo show
        $asignado = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->where('e.estado', '!=', 'Cancelado')
            ->whereBetween('e.fecha_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->selectRaw('ep.persona_id as pid, SUM(ep.monto_asignado) as total')
            ->groupBy('ep.persona_id')
            ->pluck('total', 'pid');

        $out = [];
        foreach ($filas as $f) {
            $out[$f->pid] = [
                'shows' => (int) $f->shows,
                'deuda' => (float) ($asignado[$f->pid] ?? 0),
                'pagado' => (float) $f->pagado,
            ];
        }
        return $out;
    }

    /** Resumen por persona: asignado, pagado, saldo (positivo = se le debe, negativo = anticipo por descontar). */
    private function resumen()
    {
        $asignado = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->where('e.estado', '!=', 'Cancelado')
            ->selectRaw('ep.persona_id as pid, SUM(ep.monto_asignado) as total, COUNT(*) as shows')
            ->groupBy('ep.persona_id')
            ->get()
            ->keyBy('pid');

        $pagado = $this->pagadoPorPersona();
        $pagadoMes = $this->pagadoPorPersona(now()->startOfMonth());

        return MusicoPersonal::query()->orderBy('nombre_completo')->get()->map(function ($p) use ($asignado, $pagado, $pagadoMes) {
            $esMensual = $p->modalidad_pago === 'mensual';
            if ($esMensual) {
                $deuda = (float) $p->sueldo_mensual;
                $entregado = (float) ($pagadoMes[$p->id] ?? 0);
                $shows = 0;
            } else {
                $deuda = (float) ($asignado[$p->id]->total ?? 0);
                $entregado = (float) ($pagado[$p->id] ?? 0);
                $shows = (int) ($asignado[$p->id]->shows ?? 0);
            }
            $p->deuda = $deuda;
            $p->entregado = $entregado;
            $p->saldo = round($deuda - $entregado, 2);
            $p->num_shows = $shows;
            $p->es_mensual = $esMensual;
            return $p;
        });
    }

    /** Caja por método (efectivo / QR). */
    private function caja(): array
    {
        return \App\Support\Caja::saldos();
    }

    // ------------------------------------------------------------------
    //  Pagar / anticipo
    // ------------------------------------------------------------------

    public function abrir($personaId, $modo = 'pago', $sugerido = null)
    {
        $fila = $this->resumen()->firstWhere('id', (int) $personaId);
        abort_unless($fila, 404);

        $this->resetValidation();
        $this->personaId = $fila->id;
        $this->modo = $modo === 'anticipo' || $fila->saldo <= 0 ? 'anticipo' : 'pago';
        $this->metodo = 'Efectivo';
        $this->fondo = '';
        $this->monto = $this->modo === 'pago'
            ? number_format(is_numeric($sugerido) && (float) $sugerido > 0 ? min((float) $sugerido, $fila->saldo) : $fila->saldo, 2, '.', '')
            : '';
        $this->modalOpen = true;
    }

    /** Porcentaje sugerido para el fondo común (se configura en Empresa; es solo una sugerencia). */
    private function porcentajeFondo(): float
    {
        return (float) (\App\Models\Configuracion::where('clave', 'porcentaje_fondo_comun')->value('valor') ?? 0);
    }

    public function usarFondoSugerido()
    {
        $m = is_numeric($this->monto) ? (float) $this->monto : 0;
        $this->fondo = $m > 0 ? number_format(round($m * $this->porcentajeFondo() / 100, 2), 2, '.', '') : '';
    }

    public function cambiarModo($modo)
    {
        $fila = $this->resumen()->firstWhere('id', (int) $this->personaId);
        if ($modo === 'pago' && $fila && $fila->saldo > 0) {
            $this->modo = 'pago';
            $this->monto = number_format($fila->saldo, 2, '.', '');
        } else {
            $this->modo = 'anticipo';
            $this->monto = '';
        }
        $this->resetValidation();
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardar()
    {
        abort_unless($this->puedeGestionar(), 403);

        $this->validate([
            'monto' => 'required|numeric|min:0.01|max:99999999',
            'metodo' => 'required|in:Efectivo,QR',
            'fondo' => 'nullable|numeric|min:0',
        ], [
            'monto.required' => 'Ingrese el monto.',
            'monto.numeric' => 'El monto debe ser un número.',
            'monto.min' => 'El monto debe ser mayor a cero.',
        ]);

        $fila = $this->resumen()->firstWhere('id', (int) $this->personaId);
        abort_unless($fila, 404);

        $monto = round((float) $this->monto, 2);

        if ($this->modo === 'pago' && $monto > $fila->saldo + 0.001) {
            $this->addError('monto', 'No puede pagar más de lo que se le debe (Bs ' . number_format($fila->saldo, 2) . '). Use "Anticipo" si es un adelanto.');
            return;
        }

        $aporte = round((float) ($this->fondo ?: 0), 2);
        if ($aporte > $monto + 0.001) {
            $this->addError('fondo', 'Lo que deja en el fondo no puede ser más que el monto.');
            return;
        }

        $disponible = $this->caja()[$this->metodo] ?? 0;
        if ($monto > $disponible + 0.001) {
            $this->addError('monto', 'No alcanza la caja ' . ($this->metodo === 'QR' ? 'QR' : 'de efectivo') . ' (hay Bs ' . number_format($disponible, 2) . ').');
            return;
        }

        DB::transaction(function () use ($fila, $monto, $aporte) {
            $glosa = $fila->es_mensual
                ? ($this->modo === 'anticipo' ? 'Anticipo de sueldo' : 'Sueldo mensual')
                : ($this->modo === 'anticipo' ? 'Anticipo' : 'Pago de saldo');

            $base = [
                'persona_id' => $fila->id,
                'metodo_pago' => $this->metodo,
                'tipo' => $this->modo,
                'glosa' => $glosa,
                'fecha_pago' => now(),
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ];

            $restante = $monto;
            $aporteRestante = $aporte;

            // Crea la fila de pago y, si deja algo en el fondo, su aporte (cada fila lleva su parte).
            $crear = function (array $extra, float $montoFila) use ($base, $fila, &$aporteRestante) {
                $parte = round(min($aporteRestante, $montoFila), 2);
                $fondoId = null;
                if ($parte > 0) {
                    $saldo = \App\Models\FondoComunMovimiento::obtenerSaldoActual();
                    $mov = \App\Models\FondoComunMovimiento::create([
                        'tipo' => 'INGRESO',
                        'concepto' => 'Aporte de ' . $fila->nombre_completo,
                        'monto' => $parte,
                        'saldo_anterior' => $saldo,
                        'saldo_resultante' => $saldo + $parte,
                        'fecha' => now()->toDateString(),
                        'metodo' => $this->metodo,
                        'glosa' => 'Aporte desde pagos a personal',
                        'estado' => 'Valido',
                        'user_id' => Auth::id(),
                    ]);
                    $fondoId = $mov->id;
                    $aporteRestante = round($aporteRestante - $parte, 2);
                }
                return PagoParticipante::create($base + $extra + [
                    'monto' => $montoFila,
                    'aporte_fondo' => $parte,
                    'fondo_movimiento_id' => $fondoId,
                ]);
            };

            // Los shows antiguos se van cubriendo primero (así cada show queda Pendiente / Parcial / Pagado).
            if (!$fila->es_mensual) {
                $pendientes = EventoParticipante::with('pagos')
                    ->where('persona_id', $fila->id)
                    ->whereHas('evento', fn($q) => $q->where('estado', '!=', 'Cancelado'))
                    ->get()
                    ->sortBy(fn($ep) => optional($ep->evento)->fecha_evento)
                    ->filter(fn($ep) => $ep->saldo_pendiente > 0);

                foreach ($pendientes as $ep) {
                    if ($restante <= 0) break;
                    $aplicar = min($restante, $ep->saldo_pendiente);
                    $crear([
                        'evento_participante_id' => $ep->id,
                        'evento_id' => $ep->evento_id,
                    ], (float) $aplicar);
                    $restante = round($restante - $aplicar, 2);
                }
            }

            // Lo que no corresponde a ningún show queda "a cuenta" (anticipo o sueldo).
            if ($restante > 0) {
                $crear([], (float) $restante);
            }

            $this->registrarAuditoria(
                'Pagos a personal',
                $this->modo === 'anticipo' ? 'Dar anticipo' : 'Pagar saldo',
                "{$glosa} a {$fila->nombre_completo}: Bs " . number_format($monto, 2) . " ({$this->metodo})" . ($aporte > 0 ? ", dejó Bs " . number_format($aporte, 2) . " en el fondo común." : ".")
            );
        });

        session()->flash('success', ($this->modo === 'anticipo' ? 'Anticipo' : 'Pago') . ' de Bs ' . number_format($monto, 2) . " registrado para {$fila->nombre_completo}.");
        $this->modalOpen = false;
        if (!in_array($fila->id, $this->abiertos)) {
            $this->abiertos[] = $fila->id;
        }
    }

    // ------------------------------------------------------------------
    //  Anular un pago entregado
    // ------------------------------------------------------------------

    public function pedirAnular($pagoId)
    {
        $this->resetValidation();
        $this->anularId = (int) $pagoId;
        $this->motivo = '';
    }

    public function cancelarAnular()
    {
        $this->anularId = null;
        $this->resetValidation();
    }

    public function anular()
    {
        abort_unless($this->puedeGestionar(), 403);
        $this->validate(['motivo' => 'required|string|min:5|max:255'], [
            'motivo.required' => 'Escriba el motivo.',
            'motivo.min' => 'Escriba al menos 5 letras.',
        ]);

        $pago = PagoParticipante::with('persona')->where('estado', 'Valido')->findOrFail($this->anularId);

        // Si dejó algo en el fondo, ese aporte se anula junto con el pago (solo si el fondo lo cubre).
        $aporteMov = $pago->fondo_movimiento_id ? FondoComunMovimiento::find($pago->fondo_movimiento_id) : null;
        if ($aporteMov && $aporteMov->estado === 'Valido') {
            $enMetodo = \App\Support\Caja::fondoPorMetodo()[str_contains(strtoupper((string) $aporteMov->metodo), 'EFECTIVO') ? 'Efectivo' : 'QR'] ?? 0;
            if ($enMetodo - (float) $aporteMov->monto < -0.001) {
                $this->addError('motivo', 'No se puede anular: el aporte de Bs ' . number_format($aporteMov->monto, 2) . ' ya no está en el fondo.');
                return;
            }
        }

        DB::transaction(function () use ($pago, $aporteMov) {
            $pago->update([
                'estado' => 'Revertido',
                'motivo_reversion' => $this->motivo,
                'fecha_reversion' => now(),
                'revertido_por' => Auth::id(),
            ]);

            if ($aporteMov && $aporteMov->estado === 'Valido') {
                $aporteMov->update([
                    'estado' => 'Anulado',
                    'motivo_anulacion' => 'Se anuló el pago a personal. ' . $this->motivo,
                    'fecha_anulacion' => now(),
                    'anulado_por' => Auth::id(),
                ]);
            }
        });

        $this->registrarAuditoria(
            'Pagos a personal',
            'Anular pago',
            "Se anuló {$pago->glosa} #{$pago->id} de Bs " . number_format($pago->monto, 2) . ' a ' . ($pago->persona->nombre_completo ?? 'persona') . ". Motivo: {$this->motivo}."
        );

        session()->flash('success', 'Pago anulado. El saldo de la persona volvió a su valor anterior.');
        $this->anularId = null;
    }

    // ------------------------------------------------------------------
    //  Render
    // ------------------------------------------------------------------

    public function render()
    {
        $todas = $this->resumen();

        // Cuántas hay en cada chip (sobre todo el listado)
        [$rd, $rh] = $this->rangoActual();
        $idsRango = $this->idsConShow($rd, $rh);

        $conActividad = fn($p) => $p->estado === 'Activo' || abs($p->saldo) > 0.001;

        $conteos = [
            'pendientes' => $todas->filter(fn($p) => abs($p->saldo) > 0.001)->count(),
            'fechas' => $todas->filter(fn($p) => in_array($p->id, $idsRango))->count(),
            'todos' => $todas->filter($conActividad)->count(),
        ];

        $personas = $todas->filter(function ($p) use ($idsRango, $conActividad) {
            return match ($this->filtro) {
                'pendientes' => abs($p->saldo) > 0.001,
                'fechas' => in_array($p->id, $idsRango),
                default => $conActividad($p),
            };
        });

        // Con Hoy / Semana / Mes la cifra de cada persona es la de ese periodo (no el acumulado de siempre)
        $rango = $this->filtro === 'fechas' ? [$rd, $rh] : null;
        $semanaActual = $rd->isSameDay(now()->startOfWeek()) && $rh->isSameDay(now()->endOfWeek());
        $etiquetaPeriodo = $rango
            ? ($semanaActual ? 'Esta semana' : ($rd->isSameDay($rh) ? $rd->format('d/m/Y') : $rd->format('d/m') . ' al ' . $rh->format('d/m')))
            : null;
        $totalPeriodo = null;
        if ($rango) {
            $per = $this->periodoPorPersona($rango[0], $rango[1]);
            $personas = $personas->map(function ($p) use ($per) {
                if (!$p->es_mensual) {
                    $x = $per[$p->id] ?? ['shows' => 0, 'deuda' => 0.0, 'pagado' => 0.0];
                    $p->shows_ver = $x['shows'];
                    $p->entregado_ver = $x['pagado'];
                    $p->saldo_ver = round($x['deuda'] - $x['pagado'], 2);
                }
                return $p;
            });
            $totalPeriodo = (float) $personas->filter(fn($p) => isset($p->saldo_ver) && $p->saldo_ver > 0)->sum('saldo_ver')
                + (float) $personas->filter(fn($p) => $p->es_mensual && $p->saldo > 0)->sum('saldo');
        }

        if (trim($this->search) !== '') {
            $term = mb_strtolower(trim($this->search));
            $personas = $personas->filter(fn($p) => str_contains(mb_strtolower($p->nombre_completo . ' ' . $p->tipo), $term));
        }

        // Detalle de las filas desplegadas
        $detalle = [];
        foreach ($this->abiertos as $id) {
            $detalle[$id] = [
                'shows' => EventoParticipante::with('evento.cliente', 'pagos')
                    ->where('persona_id', $id)
                    ->whereHas('evento', fn($q) => $q->where('estado', '!=', 'Cancelado'))
                    ->get()
                    ->sortByDesc(fn($ep) => optional($ep->evento)->fecha_evento)
                    ->take(12),
                'pagos' => PagoParticipante::with('participante.evento.cliente')
                    ->where('estado', 'Valido')
                    ->where(function ($q) use ($id) {
                        $q->where('persona_id', $id)
                            ->orWhereHas('participante', fn($p) => $p->where('persona_id', $id));
                    })
                    ->orderByDesc('fecha_pago')
                    ->take(15)
                    ->get(),
            ];
        }

        $modalFila = $this->modalOpen ? $todas->firstWhere('id', (int) $this->personaId) : null;
        $caja = $this->caja();

        return view('livewire.comercial.gestion-pagos-personal', [
            'personas' => $personas->values(),
            'conteos' => $conteos,
            'etiquetaPeriodo' => $etiquetaPeriodo,
            'totalPeriodo' => $totalPeriodo,
            'detalle' => $detalle,
            'modalFila' => $modalFila,
            'caja' => $caja,
            'pctFondo' => $this->modalOpen ? $this->porcentajeFondo() : 0,
        ])->layout('components.layouts.app', [
            'title' => 'Pagos a personal - Mariachi León',
            'themed' => true,
        ]);
    }

    private function idsConShow(Carbon $desde, Carbon $hasta): array
    {
        return DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->where('e.estado', '!=', 'Cancelado')
            ->whereBetween('e.fecha_evento', [$desde->toDateString(), $hasta->toDateString()])
            ->distinct()
            ->pluck('ep.persona_id')
            ->all();
    }
}