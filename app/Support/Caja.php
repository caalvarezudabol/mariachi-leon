<?php

namespace App\Support;

use App\Models\FondoComunMovimiento;
use App\Models\MovimientoFinanciero;
use App\Models\MusicoPersonal;
use App\Models\Pago;
use App\Models\PagoParticipante;
use Illuminate\Support\Facades\DB;

/**
 * Cifras de caja compartidas por Cobros, Pagos a personal e Ingresos/Egresos.
 */
class Caja
{
    /** Caja por método: cobros + ingresos extra - gastos - pagos a personal - aportes al fondo + retiros del fondo. */
    public static function saldos(): array
    {
        // Todo lo que no es efectivo (QR, transferencia, tarjeta) cuenta como QR/banco.
        $norm = fn($m) => str_contains(strtoupper((string) $m), 'EFECTIVO') ? 'Efectivo' : 'QR';
        $caja = ['Efectivo' => 0.0, 'QR' => 0.0];

        $sumar = function ($query, $col, $signo, $expr = 'monto') use (&$caja, $norm) {
            foreach ($query->selectRaw("UPPER($col) as m, SUM($expr) as t")->groupByRaw("UPPER($col)")->get() as $r) {
                $caja[$norm($r->m)] += $signo * (float) $r->t;
            }
        };

        $sumar(Pago::where('estado', 'Valido'), 'metodo_pago', 1);
        $sumar(MovimientoFinanciero::where('estado', 'Valido')->where('tipo', 'INGRESO'), 'metodo_pago', 1);
        $sumar(MovimientoFinanciero::where('estado', 'Valido')->where('tipo', 'EGRESO'), 'metodo_pago', -1);
        // Al personal solo sale lo que recibe en mano; lo que deja en el fondo se resta con el aporte.
        $sumar(PagoParticipante::where('estado', 'Valido'), 'metodo_pago', -1, 'monto - COALESCE(aporte_fondo, 0)');
        $sumar(FondoComunMovimiento::where('estado', 'Valido')->where('tipo', 'INGRESO'), 'metodo', -1);
        $sumar(FondoComunMovimiento::where('estado', 'Valido')->where('tipo', 'EGRESO'), 'metodo', 1);

        return $caja;
    }

    /** Saldo del fondo común por método (aportes - retiros). */
    public static function fondoPorMetodo(): array
    {
        $fondo = ['Efectivo' => 0.0, 'QR' => 0.0];
        $rows = FondoComunMovimiento::where('estado', 'Valido')
            ->selectRaw('UPPER(metodo) as m, tipo, SUM(monto) as t')
            ->groupByRaw('UPPER(metodo), tipo')->get();
        foreach ($rows as $r) {
            $k = str_contains((string) $r->m, 'EFECTIVO') ? 'Efectivo' : 'QR';
            $fondo[$k] += ($r->tipo === 'INGRESO' ? 1 : -1) * (float) $r->t;
        }
        return $fondo;
    }

    /** Total que se le debe hoy al personal (shows pendientes + sueldos del mes pendientes). */
    public static function deudaPersonal(): float
    {
        $asignado = DB::table('evento_participantes as ep')
            ->join('eventos as e', 'e.id', '=', 'ep.evento_id')
            ->where('e.estado', '!=', 'Cancelado')
            ->selectRaw('ep.persona_id as pid, SUM(ep.monto_asignado) as total')
            ->groupBy('ep.persona_id')
            ->pluck('total', 'pid');

        $pagado = function ($desde = null) {
            return DB::table('pagos_participantes as pp')
                ->leftJoin('evento_participantes as ep', 'ep.id', '=', 'pp.evento_participante_id')
                ->where('pp.estado', 'Valido')
                ->when($desde, fn($q) => $q->where('pp.fecha_pago', '>=', $desde->toDateTimeString()))
                ->selectRaw('COALESCE(pp.persona_id, ep.persona_id) as pid, SUM(pp.monto) as total')
                ->groupByRaw('COALESCE(pp.persona_id, ep.persona_id)')
                ->pluck('total', 'pid');
        };

        $pagadoTotal = $pagado();
        $pagadoMes = $pagado(now()->startOfMonth());

        $deuda = 0.0;
        foreach (MusicoPersonal::get(['id', 'modalidad_pago', 'sueldo_mensual']) as $p) {
            $saldo = $p->modalidad_pago === 'mensual'
                ? (float) $p->sueldo_mensual - (float) ($pagadoMes[$p->id] ?? 0)
                : (float) ($asignado[$p->id] ?? 0) - (float) ($pagadoTotal[$p->id] ?? 0);
            if ($saldo > 0.001) {
                $deuda += $saldo;
            }
        }

        return round($deuda, 2);
    }
}