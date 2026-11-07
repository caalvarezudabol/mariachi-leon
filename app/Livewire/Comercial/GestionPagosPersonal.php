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
    public $filtro = 'pendientes'; // pendientes | hoy | semana | mes | todos

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

    public function abrir($personaId, $modo = 'pago')
    {
        $fila = $this->resumen()->firstWhere('id', (int) $personaId);
        abort_unless($fila, 404);

        $this->resetValidation();
        $this->personaId = $fila->id;
        $this->modo = $modo === 'anticipo' || $fila->saldo <= 0 ? 'anticipo' : 'pago';
        $this->metodo = 'Efectivo';
        $this->fondo = '';
        $this->monto = $this->modo === 'pago' ? number_format($fila->saldo, 2, '.', '') : '';
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
        $hoy = Carbon::today();
        $idsHoy = $this->idsConShow($hoy, $hoy);
        $idsSemana = $this->idsConShow($hoy->copy()->startOfWeek(), $hoy->copy()->endOfWeek());
        $idsMes = $this->idsConShow($hoy->copy()->startOfMonth(), $hoy->copy()->endOfMonth());

        $conActividad = fn($p) => $p->estado === 'Activo' || abs($p->saldo) > 0.001;

        $conteos = [
            'pendientes' => $todas->filter(fn($p) => abs($p->saldo) > 0.001)->count(),
            'hoy' => $todas->filter(fn($p) => in_array($p->id, $idsHoy))->count(),
            'semana' => $todas->filter(fn($p) => in_array($p->id, $idsSemana))->count(),
            'mes' => $todas->filter(fn($p) => in_array($p->id, $idsMes))->count(),
            'todos' => $todas->filter($conActividad)->count(),
        ];

        $personas = $todas->filter(function ($p) use ($idsHoy, $idsSemana, $idsMes, $conActividad) {
            return match ($this->filtro) {
                'pendientes' => abs($p->saldo) > 0.001,
                'hoy' => in_array($p->id, $idsHoy),
                'semana' => in_array($p->id, $idsSemana),
                'mes' => in_array($p->id, $idsMes),
                default => $conActividad($p),
            };
        });

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