{{-- Calendario público: solo muestra días libres / con eventos / llenos. Guarda la fecha en fecha_estimada. --}}
<div x-data="{
    y: new Date().getFullYear(),
    m: new Date().getMonth(),
    dias: {},
    sel: $wire.entangle('fecha_estimada'),
    hoy: (() => { const d = new Date(); return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); })(),
    get titulo() { const t = new Date(this.y, this.m, 1).toLocaleDateString('es', { month: 'long', year: 'numeric' }); return t.charAt(0).toUpperCase() + t.slice(1); },
    get esMesActual() { return this.hoy.slice(0, 7) === this.y + '-' + String(this.m + 1).padStart(2, '0'); },
    get celdas() {
        const off = (new Date(this.y, this.m, 1).getDay() + 6) % 7;
        const n = new Date(this.y, this.m + 1, 0).getDate();
        const a = [];
        for (let i = 0; i < off; i++) a.push(null);
        for (let d = 1; d <= n; d++) {
            const k = this.y + '-' + String(this.m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
            a.push({ d: d, k: k, pasado: k < this.hoy, estado: this.dias[k] || 'libre' });
        }
        return a;
    },
    get estadoSel() { return this.sel ? (this.dias[this.sel] || 'libre') : null; },
    get textoSel() { if (!this.sel) return ''; const p = this.sel.split('-'); return p[2] + '/' + p[1] + '/' + p[0]; },
    async cargar() {
        try {
            const r = await fetch('{{ route('web.disponibilidad') }}?mes=' + this.y + '-' + String(this.m + 1).padStart(2, '0'));
            const j = await r.json();
            this.dias = j.dias || {};
        } catch (e) { this.dias = {}; }
    },
    mover(n) { const d = new Date(this.y, this.m + n, 1);
        this.y = d.getFullYear();
        this.m = d.getMonth();
        this.cargar(); },
    elegir(c) { if (c.pasado || c.estado === 'lleno') return;
        this.sel = c.k; }
}" x-init="cargar()"
    class="rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900 p-2.5 w-full">

    <div class="flex items-center justify-between mb-1">
        <button type="button" @click="mover(-1)" :disabled="esMesActual"
            class="w-7 h-7 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-gold-500/15 disabled:opacity-30 disabled:cursor-not-allowed">
            <i class="fa-solid fa-chevron-left text-xs"></i>
        </button>
        <span class="text-sm sm:text-base font-bold text-slate-900 dark:text-white" x-text="titulo"></span>
        <button type="button" @click="mover(1)"
            class="w-7 h-7 rounded-lg text-slate-500 dark:text-slate-400 hover:bg-gold-500/15">
            <i class="fa-solid fa-chevron-right text-xs"></i>
        </button>
    </div>

    <div class="grid grid-cols-7 gap-0.5 text-center text-[10px] font-bold uppercase text-slate-400 mb-1">
        <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span>
    </div>

    <div class="grid grid-cols-7 gap-0.5">
        <template x-for="(c, i) in celdas" :key="i">
            <div>
                <template x-if="c === null"><span class="block h-8"></span></template>
                <template x-if="c !== null">
                    <button type="button" @click="elegir(c)" x-text="c.d"
                        :disabled="c.pasado || c.estado === 'lleno'"
                        class="w-full h-8 rounded-lg text-xs font-semibold transition-colors"
                        :class="{
                            'bg-gold-500 text-slate-950': sel === c.k,
                            'text-slate-300 dark:text-slate-700 cursor-not-allowed': c.pasado && sel !== c.k,
                            'bg-rose-500/10 text-rose-400 line-through cursor-not-allowed': !c.pasado && c
                                .estado === 'lleno' && sel !== c.k,
                            'bg-amber-500/15 text-amber-700 dark:text-amber-300 hover:bg-amber-500/25': !c.pasado && c
                                .estado === 'consulta' && sel !== c.k,
                            'text-slate-700 dark:text-slate-200 hover:bg-gold-500/15': !c.pasado && c
                                .estado === 'libre' && sel !== c.k
                        }"></button>
                </template>
            </div>
        </template>
    </div>

    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-[11px] text-slate-500 dark:text-slate-400">
        <span class="flex items-center gap-1"><span
                class="w-2.5 h-2.5 rounded bg-slate-300 dark:bg-slate-600"></span>Disponible</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-amber-500/60"></span>Con
            eventos</span>
        <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded bg-rose-500/50"></span>Lleno</span>
    </div>

    <div class="mt-2 text-xs min-h-[1.25rem]" x-show="sel" x-cloak>
        <template x-if="estadoSel === 'libre'">
            <p class="text-emerald-700 dark:text-emerald-400 font-semibold"><i
                    class="fa-solid fa-circle-check mr-1"></i><span x-text="textoSel"></span>: fecha disponible. Te
                confirmamos por WhatsApp.</p>
        </template>
        <template x-if="estadoSel === 'consulta'">
            <p class="text-amber-700 dark:text-amber-300 font-semibold"><i
                    class="fa-solid fa-circle-info mr-1"></i><span x-text="textoSel"></span>: ya hay eventos ese día.
                Revisamos el horario contigo.</p>
        </template>
    </div>
</div>