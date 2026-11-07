<div class="py-10 px-4 max-w-5xl mx-auto space-y-8">
    <div class="text-center space-y-2 max-w-2xl mx-auto">
        <h1 class="font-serif font-bold text-3xl sm:text-4xl text-slate-900 dark:text-white">Sobre
            {{ $empresa->nombre_comercial ?? 'Mariachi León Guanajuato' }}</h1>
        <p class="text-sm sm:text-base text-slate-600 dark:text-slate-300 leading-relaxed">
            Música mexicana en vivo para tus celebraciones en Santa Cruz y toda Bolivia: calidad, elegancia y
            puntualidad.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
            data-reveal>
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gold-500/10 text-gold-700 dark:text-gold-400 flex items-center justify-center">
                    <i class="fa-solid fa-bullseye"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Misión</h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Regalar momentos inolvidables con interpretaciones apasionadas, puntualidad y trajes de charro de gala
                impecables.
            </p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
            data-reveal>
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gold-500/10 text-gold-700 dark:text-gold-400 flex items-center justify-center">
                    <i class="fa-solid fa-eye"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Visión</h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Ser el mariachi preferido y la referencia de excelencia musical en Santa Cruz y en el país.
            </p>
        </div>

        <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-2 hover:shadow-xl hover:shadow-gold-500/10 transition-shadow"
            data-reveal>
            <div class="flex items-center gap-3">
                <div
                    class="w-10 h-10 rounded-xl bg-gold-500/10 text-gold-700 dark:text-gold-400 flex items-center justify-center">
                    <i class="fa-solid fa-award"></i>
                </div>
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Compromiso</h3>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                Respeto por cada cliente, atención personalizada y cumplimiento en cada presentación.
            </p>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('web.contacto') }}"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 text-sm transition-all">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Cotizar mi evento</span>
        </a>
    </div>
</div>