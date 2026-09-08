@php $empresaDash = \App\Models\Empresa::obtener(); @endphp
<div class="space-y-5">
    <!-- Header Banner -->
    <div
        class="p-5 rounded-2xl bg-gradient-to-r from-brand-card via-slate-900 to-slate-950 border border-brand-border flex flex-col md:flex-row items-start md:items-center justify-between gap-4 shadow-lg">
        <div class="flex items-center gap-3">
            @if ($empresaDash->logo_url)
                <div
                    class="w-11 h-11 rounded-xl bg-slate-950 p-1.5 border border-gold-500/30 flex items-center justify-center shadow-md flex-shrink-0">
                    <img src="{{ asset($empresaDash->logo_url) }}" alt="Logo"
                        class="max-w-full max-h-full object-contain">
                </div>
            @endif
            <div class="space-y-0.5">
                <div
                    class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-gold-500/10 text-gold-400 border border-gold-500/20">
                    <i class="fa-solid fa-crown text-gold-400 text-[10px]"></i> Sprint 1 Inicializado
                </div>
                <h1 class="text-lg md:text-lg font-extrabold text-white">¡Bienvenido, {{ Auth::user()->name }}!</h1>
                <p class="text-xs text-slate-400">Sistema de Administración y Gestión Operativa de
                    {{ $empresaDash->nombre_comercial }}.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('web.home') }}" target="_blank"
                class="px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold border border-slate-700 transition-all flex items-center gap-1.5">
                <i class="fa-solid fa-globe text-gold-400 text-xs"></i>
                <span>Ver Sitio Web</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
        <div
            class="p-2.5 rounded-xl bg-brand-card border border-brand-border flex items-center gap-2.5 hover:border-gold-500/30 transition-all">
            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-users text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">Usuarios Registrados
                </p>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-extrabold text-white">{{ $totalUsuarios }}</span>
                    <span class="text-[10px] text-slate-400">en sistema</span>
                </div>
            </div>
        </div>

        <div
            class="p-2.5 rounded-xl bg-brand-card border border-brand-border flex items-center gap-2.5 hover:border-gold-500/30 transition-all">
            <div
                class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-calendar-days text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">Tipos de Eventos</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-extrabold text-white">{{ $totalTiposEvento }}</span>
                    <span class="text-[10px] text-slate-400">configurados</span>
                </div>
            </div>
        </div>

        <div
            class="p-2.5 rounded-xl bg-brand-card border border-brand-border flex items-center gap-2.5 hover:border-gold-500/30 transition-all">
            <div
                class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-music text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">Servicios Activos</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-extrabold text-white">{{ $totalServicios }}</span>
                    <span class="text-[10px] text-slate-400">servicios base</span>
                </div>
            </div>
        </div>

        <div
            class="p-2.5 rounded-xl bg-brand-card border border-brand-border flex items-center gap-2.5 hover:border-gold-500/30 transition-all">
            <div class="w-8 h-8 rounded-lg bg-gold-500/10 text-gold-400 flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-envelope-open-text text-sm"></i>
            </div>
            <div class="min-w-0">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">Consultas Web</p>
                <div class="flex items-baseline gap-1">
                    <span class="text-lg font-extrabold text-white">{{ $contactosNuevos }}</span>
                    <span class="text-[10px] text-amber-400 font-semibold truncate">pendientes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Activity Log Preview -->
    <div class="p-5 rounded-2xl bg-brand-card border border-brand-border space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-sm text-white">Últimos Registros de Auditoría</h3>
                <p class="text-[11px] text-slate-400">Trazabilidad en tiempo real de operaciones en el sistema.</p>
            </div>
            <a href="{{ route('admin.auditoria') }}"
                class="text-[11px] font-bold text-gold-400 hover:text-gold-300 flex items-center gap-1">
                <span>Ver todo</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="space-y-2">
            @forelse($ultimosLogs as $log)
                <div
                    class="p-3 rounded-lg bg-slate-950/60 border border-brand-border/60 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="w-7 h-7 rounded-md bg-gold-500/10 text-gold-400 flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                            <i class="fa-solid fa-shield"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-white">{{ $log->descripcion }}</p>
                            <p class="text-[11px] text-slate-400">Módulo: <span
                                    class="text-slate-300">{{ $log->modulo }}</span> &bull; Usuario: <span
                                    class="text-gold-400">{{ $log->user->name ?? 'Sistema' }}</span></p>
                        </div>
                    </div>
                    <span
                        class="text-[11px] text-slate-500 font-mono flex-shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="text-center py-6 text-slate-500 text-xs">
                    No hay registros de auditoría aún.
                </div>
            @endforelse
        </div>
    </div>
</div>