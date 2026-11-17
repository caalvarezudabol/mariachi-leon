{{-- Situación de hoy: no depende de los filtros del reporte. Uso: <x-ui.situacion-actual /> --}}
@php
    $caja = \App\Support\Caja::saldos();
    $efe = (float) ($caja['Efectivo'] ?? 0);
    $qr = (float) ($caja['QR'] ?? 0);
    $deuda = (float) \App\Support\Caja::deudaPersonal();
    $fondo = (float) array_sum(\App\Support\Caja::fondoPorMetodo());

    $porCobrar = (float) \App\Models\Contrato::where('saldo_pendiente', '>', 0)->sum('saldo_pendiente');
    $nPorCobrar = \App\Models\Contrato::where('saldo_pendiente', '>', 0)->count();

    $ini = now()->startOfMonth();
    $fin = now()->endOfMonth();
    $cobradoMes = (float) \App\Models\Pago::where('estado', 'Valido')
        ->whereBetween('fecha_pago', [$ini->toDateString() . ' 00:00:00', $fin->toDateString() . ' 23:59:59'])
        ->sum('monto');
    $personalMes = (float) \App\Models\PagoParticipante::where('estado', 'Valido')
        ->whereBetween('fecha_pago', [$ini->toDateString() . ' 00:00:00', $fin->toDateString() . ' 23:59:59'])
        ->sum('monto');
    $gastosMes = (float) \App\Models\MovimientoFinanciero::valido()
        ->egresos()
        ->whereBetween('fecha', [$ini->toDateString(), $fin->toDateString()])
        ->sum('monto');
    $egresoMes = $personalMes + $gastosMes;
    $mes = ucfirst($ini->translatedFormat('F'));
    $bs = fn($n) => 'Bs ' . number_format((float) $n, 2);

    $celdas = [
        [
            'Caja total',
            $bs($efe + $qr),
            'Efectivo ' . number_format($efe, 0) . ' · QR ' . number_format($qr, 0),
            'fa-vault',
            $efe + $qr >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-amber-700 dark:text-amber-400',
        ],
        [
            'Debes al personal',
            $bs($deuda),
            'de todos los shows',
            'fa-hand-holding-dollar',
            $deuda > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-emerald-700 dark:text-emerald-400',
        ],
        [
            'Por cobrar',
            $bs($porCobrar),
            $nPorCobrar . ' contratos con saldo',
            'fa-hourglass-half',
            'text-sky-700 dark:text-sky-400',
        ],
        [
            'Cobrado en ' . $mes,
            $bs($cobradoMes),
            'pagos de clientes',
            'fa-circle-check',
            'text-emerald-700 dark:text-emerald-400',
        ],
        [
            'Egresos de ' . $mes,
            $bs($egresoMes),
            'personal ' . number_format($personalMes, 0) . ' · gastos ' . number_format($gastosMes, 0),
            'fa-arrow-trend-down',
            'text-rose-700 dark:text-rose-400',
        ],
        ['Fondo común', $bs($fondo), 'guardado hasta fin de año', 'fa-piggy-bank', 'text-ink-primary'],
    ];
@endphp

<div
    class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 rounded-lg border border-outline bg-surface-card overflow-hidden divide-x divide-y divide-outline">
    @foreach ($celdas as [$etq, $val, $sub, $ico, $tono])
        <div class="px-3 py-1.5 -mb-px -mr-px min-w-0">
            <span class="block text-[11px] text-ink-secondary truncate"><i
                    class="fa-solid {{ $ico }} mr-1 {{ $tono }}"></i>{{ $etq }}</span>
            <span
                class="block text-base font-bold leading-tight tabular-nums truncate {{ $tono }}">{{ $val }}</span>
            <span class="block text-[11px] text-ink-secondary truncate">{{ $sub }}</span>
        </div>
    @endforeach
</div>