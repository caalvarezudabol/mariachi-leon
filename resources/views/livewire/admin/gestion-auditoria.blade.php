<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Auditoría de Operaciones</h1>
            <p class="text-xs text-slate-400">Historial completo y trazabilidad de acciones realizadas en la plataforma.</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="p-4 rounded-2xl bg-brand-card border border-brand-border flex flex-col sm:flex-row gap-4">
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-500">
                <i class="fa-solid fa-magnifying-glass"></i>
            </span>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar en la descripción o acción..." class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white placeholder-slate-500 focus:outline-none focus:border-gold-500 text-sm">
        </div>
        <select wire:model.live="modulo" class="px-4 py-2.5 bg-slate-950 border border-slate-800 rounded-xl text-white text-sm focus:border-gold-500">
            <option value="">Todos los Módulos</option>
            @foreach($modulos as $m)
                <option value="{{ $m }}">{{ $m }}</option>
            @endforeach
        </select>
    </div>

    <!-- Table -->
    <div class="rounded-2xl bg-brand-card border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 border-b border-brand-border text-xs uppercase font-bold text-slate-400">
                    <tr>
                        <th class="px-6 py-4">Fecha y Hora</th>
                        <th class="px-6 py-4">Usuario</th>
                        <th class="px-6 py-4">Módulo</th>
                        <th class="px-6 py-4">Acción</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4">Dirección IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/60">
                    @forelse($logs as $log)
                        <tr class="hover:bg-brand-hover/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-400 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-white">
                                {{ $log->user->name ?? 'Sistema' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    {{ $log->modulo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gold-400">
                                {{ $log->accion }}
                            </td>
                            <td class="px-6 py-4 text-slate-300">
                                {{ $log->descripcion }}
                            </td>
                            <td class="px-6 py-4 font-mono text-xs text-slate-500">
                                {{ $log->ip_address ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">No se registraron entradas de auditoría.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $logs->links() }}
        </div>
    </div>
</div>
