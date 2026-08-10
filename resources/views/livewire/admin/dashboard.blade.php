<div class="space-y-8">
    <!-- Header Banner -->
    <div class="p-8 rounded-3xl bg-gradient-to-r from-brand-card via-slate-900 to-slate-950 border border-brand-border flex flex-col md:flex-row items-start md:items-center justify-between gap-6 shadow-xl">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-gold-500/10 text-gold-400 border border-gold-500/20">
                <i class="fa-solid fa-crown text-gold-400"></i> Sprint 1 Inicializado
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white">¡Bienvenido, {{ Auth::user()->name }}!</h1>
            <p class="text-sm text-slate-400">Sistema de Administración y Gestión Operativa del Mariachi León Guanajuato.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('web.home') }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-sm font-semibold border border-slate-700 transition-all flex items-center gap-2">
                <i class="fa-solid fa-globe text-gold-400"></i>
                <span>Ver Sitio Web</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 hover:border-gold-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Usuarios Registrados</span>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center">
                    <i class="fa-solid fa-users text-lg"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ $totalUsuarios }}</span>
                <span class="text-xs text-slate-400">en sistema</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 hover:border-gold-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Tipos de Eventos</span>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ $totalTiposEvento }}</span>
                <span class="text-xs text-slate-400">configurados</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 hover:border-gold-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Servicios Activos</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                    <i class="fa-solid fa-music text-lg"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ $totalServicios }}</span>
                <span class="text-xs text-slate-400">servicios base</span>
            </div>
        </div>

        <div class="p-6 rounded-2xl bg-brand-card border border-brand-border space-y-4 hover:border-gold-500/30 transition-all">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Consultas Web</span>
                <div class="w-10 h-10 rounded-xl bg-gold-500/10 text-gold-400 flex items-center justify-center">
                    <i class="fa-solid fa-envelope-open-text text-lg"></i>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <span class="text-3xl font-extrabold text-white">{{ $contactosNuevos }}</span>
                <span class="text-xs text-amber-400 font-semibold">pendientes de cotización</span>
            </div>
        </div>
    </div>

    <!-- Activity Log Preview -->
    <div class="p-6 rounded-3xl bg-brand-card border border-brand-border space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="font-bold text-lg text-white">Últimos Registros de Auditoría</h3>
                <p class="text-xs text-slate-400">Trazabilidad en tiempo real de operaciones en el sistema.</p>
            </div>
            <a href="{{ route('admin.auditoria') }}" class="text-xs font-bold text-gold-400 hover:text-gold-300 flex items-center gap-1">
                <span>Ver todo</span>
                <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($ultimosLogs as $log)
                <div class="p-4 rounded-xl bg-slate-950/60 border border-brand-border/60 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gold-500/10 text-gold-400 flex items-center justify-center text-xs font-bold">
                            <i class="fa-solid fa-shield"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-white">{{ $log->descripcion }}</p>
                            <p class="text-xs text-slate-400">Módulo: <span class="text-slate-300">{{ $log->modulo }}</span> &bull; Usuario: <span class="text-gold-400">{{ $log->user->name ?? 'Sistema' }}</span></p>
                        </div>
                    </div>
                    <span class="text-xs text-slate-500 font-mono flex-shrink-0">{{ $log->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="text-center py-8 text-slate-500 text-sm">
                    No hay registros de auditoría aún.
                </div>
            @endforelse
        </div>
    </div>
</div>
