@php
    $caja = \App\Support\Caja::saldos();
    $deuda = \App\Support\Caja::deudaPersonal();
    $bs = fn($n) => ($n < 0 ? '- ' : '') . 'Bs ' . number_format(abs((float) $n), 2);
    $roja = 'text-rose-700 dark:text-rose-400';
@endphp
<div class="grid grid-cols-3 gap-2">
    <div class="px-3 py-2 rounded-lg border border-outline bg-surface-card min-w-0">
        <div class="text-xs text-ink-secondary truncate"><i class="fa-solid fa-money-bill-wave mr-1"></i>Efectivo</div>
        <div
            class="text-base sm:text-lg font-bold tabular-nums truncate {{ $caja['Efectivo'] < 0 ? $roja : 'text-ink-primary' }}">
            {{ $bs($caja['Efectivo']) }}</div>
    </div>
    <div class="px-3 py-2 rounded-lg border border-outline bg-surface-card min-w-0">
        <div class="text-xs text-ink-secondary truncate"><i class="fa-solid fa-qrcode mr-1"></i>QR</div>
        <div
            class="text-base sm:text-lg font-bold tabular-nums truncate {{ $caja['QR'] < 0 ? $roja : 'text-ink-primary' }}">
            {{ $bs($caja['QR']) }}</div>
    </div>
    <a href="{{ route('admin.pagos-personal') }}" wire:navigate
        class="px-3 py-2 rounded-lg border border-outline bg-surface-card min-w-0 hover:border-rose-500/50 transition-colors">
        <div class="text-xs text-ink-secondary truncate"><i class="fa-solid fa-hand-holding-dollar mr-1"></i>Debes al
            personal</div>
        <div class="text-base sm:text-lg font-bold tabular-nums truncate {{ $deuda > 0 ? $roja : 'text-ink-primary' }}">
            {{ $bs($deuda) }}</div>
    </a>
</div>