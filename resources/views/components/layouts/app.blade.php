<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Tema claro/oscuro: se aplica antes de pintar para evitar parpadeo -->
    <script>
        (function() {
            var stored = localStorage.getItem('theme');
            var theme = stored ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.classList.toggle('dark', theme === 'dark');
        })();
    </script>
    <title>{{ $title ?? 'Mariachi León Guanajuato - Sistema de Gestión' }}</title>
    <!-- Favicon -->
    @php $empresaFavicon = \App\Models\Empresa::obtener(); @endphp
    @if ($empresaFavicon->logo_url)
        <link rel="icon" href="{{ asset($empresaFavicon->logo_url) }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset($empresaFavicon->logo_url) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    @endif
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Leaflet Maps API & CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <!-- Flatpickr Date Picker CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <!-- Tailwind CSS (via CDN for fast, clean rendering without Node setup) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gold: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309',
                            800: '#92400e',
                            900: '#78350f',
                        },
                        brand: {
                            dark: '#0b1329',
                            card: '#162238',
                            hover: '#1e2d4a',
                            border: '#2a3b5c'
                        },
                        // Tokens semánticos: cambian de valor entre modo claro y oscuro (ver <style> abajo).
                        surface: {
                            page: 'rgb(var(--color-surface-page) / <alpha-value>)',
                            card: 'rgb(var(--color-surface-card) / <alpha-value>)',
                            sunken: 'rgb(var(--color-surface-sunken) / <alpha-value>)',
                            hover: 'rgb(var(--color-surface-hover) / <alpha-value>)',
                        },
                        outline: 'rgb(var(--color-border) / <alpha-value>)',
                        ink: {
                            primary: 'rgb(var(--color-ink-primary) / <alpha-value>)',
                            secondary: 'rgb(var(--color-ink-secondary) / <alpha-value>)',
                            muted: 'rgb(var(--color-ink-muted) / <alpha-value>)',
                        },
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        :root {
            color-scheme: light;
            --color-surface-page: 248 250 252;
            --color-surface-card: 255 255 255;
            --color-surface-sunken: 241 245 249;
            --color-surface-hover: 226 232 240;
            --color-border: 203 213 225;
            --color-ink-primary: 15 23 42;
            --color-ink-secondary: 71 85 105;
            --color-ink-muted: 100 116 139;
        }

        .dark {
            color-scheme: dark;
            --color-surface-page: 2 6 23;
            --color-surface-card: 22 34 56;
            --color-surface-sunken: 11 19 41;
            --color-surface-hover: 30 45 74;
            --color-border: 42 59 92;
            --color-ink-primary: 255 255 255;
            --color-ink-secondary: 203 213 225;
            --color-ink-muted: 148 163 184;
        }

        .dark input[type="date"]::-webkit-calendar-picker-indicator,
        .dark input[type="time"]::-webkit-calendar-picker-indicator {
            filter: invert(0.9);
        }

        input[type="date"]::-webkit-calendar-picker-indicator,
        input[type="time"]::-webkit-calendar-picker-indicator {
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }

        input[type="date"]::-webkit-calendar-picker-indicator:hover,
        input[type="time"]::-webkit-calendar-picker-indicator:hover {
            background-color: rgba(251, 191, 36, 0.2);
        }

        .nav-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgb(var(--color-border)) transparent;
        }

        .nav-scroll::-webkit-scrollbar {
            width: 6px;
        }

        .nav-scroll::-webkit-scrollbar-thumb {
            background: rgb(var(--color-border));
            border-radius: 9999px;
        }

        .glass-panel {
            background: rgb(var(--color-surface-card) / 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgb(var(--color-border) / 0.5);
        }
    </style>
    <script>
        // Menú lateral tipo acordeón: solo un grupo abierto a la vez
        document.addEventListener('alpine:init', () => {
            Alpine.store('nav', {
                open: null
            });
        });
    </script>
    @livewireStyles
</head>

<body class="h-full bg-surface-page text-ink-primary font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Mobile Overlay -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"></div>

        <!-- Sidebar Navigation (persistida: no se vuelve a renderizar entre navegaciones wire:navigate) -->
        @persist('sidebar')
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed md:sticky md:top-0 md:h-screen shrink-0 inset-y-0 left-0 z-50 w-64 bg-surface-card border-r border-outline flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0">
                <!-- Brand Logo -->
                @php $empresaGlobal = $empresaFavicon; @endphp
                <div class="h-16 flex items-center justify-between px-5 border-b border-outline">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-xl bg-surface-sunken border border-gold-500/30 p-1 flex items-center justify-center overflow-hidden">
                            @if ($empresaGlobal->logo_url)
                                <img src="{{ asset($empresaGlobal->logo_url) }}" alt="Logo"
                                    class="w-full h-full object-contain rounded-lg">
                            @else
                                <svg viewBox="0 0 100 100" class="w-full h-full text-gold-400 fill-current">
                                    <circle cx="50" cy="50" r="45" fill="#0b1329" stroke="#d97706"
                                        stroke-width="3" />
                                    <path d="M50 15 L53 25 L63 25 L55 31 L58 41 L50 35 L42 41 L45 31 L37 25 L47 25 Z"
                                        fill="#fbbf24" />
                                    <text x="50" y="58" font-family="Cinzel, serif" font-weight="900" font-size="20"
                                        fill="#f59e0b" text-anchor="middle">LEÓN</text>
                                    <text x="50" y="72" font-family="Cinzel, serif" font-weight="700" font-size="9"
                                        fill="#ffffff" text-anchor="middle" letter-spacing="1">GUANAJUATO</text>
                                    <text x="50" y="82" font-family="sans-serif" font-weight="800" font-size="7"
                                        fill="#fbbf24" text-anchor="middle" letter-spacing="2">MARIACHI</text>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <h1 class="font-bold text-sm text-ink-primary leading-tight">
                                {{ $empresaGlobal->nombre_comercial ?? 'MARIACHI LEÓN' }}</h1>
                            <p class="text-[11px] text-ink-muted">
                                {{ $empresaGlobal->ciudad_pais ?? 'Guanajuato • Bolivia' }}</p>
                        </div>
                    </div>
                    <button @click="sidebarOpen = false" class="md:hidden text-ink-muted hover:text-ink-primary">
                        <i class="fa-solid fa-xmark text-xl"></i>
                    </button>
                </div>

                <!-- Nav Links -->
                <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5 nav-scroll">

                    <!-- General -->
                    <a href="{{ route('admin.dashboard') }}" wire:navigate
                        wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg font-medium text-sm transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                        <i class="fa-solid fa-house w-5 text-center"></i>
                        <span>Inicio</span>
                    </a>

                    <!-- Grupo: Catálogos (servicios, músicos y clientes) -->
                    @php
                        $pathsCatalogos = [
                            route('config.servicios', [], false),
                            route('admin.musicos', [], false),
                            route('admin.clientes', [], false),
                        ];
                    @endphp
                    <div x-data="{
                        key: 'catalogos',
                        get open() { return Alpine.store('nav').open === this.key },
                        toggle() { Alpine.store('nav').open = this.open ? null : this.key },
                        paths: @js($pathsCatalogos),
                        checkOpen() {
                            const p = window.location.pathname;
                            if (this.paths.some(path => p === path || p.startsWith(path + '/'))) Alpine.store('nav').open = this.key;
                        }
                    }" x-init="checkOpen()" x-on:livewire:navigated.window="checkOpen()"
                        class="pt-0.5">
                        <button @click="toggle()" type="button"
                            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-left text-sm font-bold text-ink-primary hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                            <span class="flex items-center gap-3 min-w-0"><i
                                    class="fa-solid fa-book w-5 text-center text-base text-gold-600 dark:text-gold-400 shrink-0"></i><span>Catálogos</span></span>
                            <i class="fa-solid fa-chevron-down text-xs text-ink-secondary transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-collapse class="space-y-0.5 mt-0.5 ml-4 pl-2 border-l border-outline">
                            <a href="{{ route('config.servicios') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-music w-5 text-center"></i>
                                <span class="truncate">Servicios</span>
                            </a>
                            <a href="{{ route('admin.musicos') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-people-group w-5 text-center"></i>
                                <span class="truncate">Músicos y personal</span>
                            </a>
                            <a href="{{ route('admin.clientes') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-users w-5 text-center"></i>
                                <span>Clientes</span>
                            </a>
                        </div>
                    </div>

                    <!-- Grupo: Gestión Comercial -->
                    @php
                        $pathsComercial = [
                            route('admin.solicitudes-web', [], false),
                            route('admin.eventos', [], false),
                            route('admin.agenda', [], false),
                            route('admin.contratos', [], false),
                        ];
                        $solicitudesNuevasBadge = \App\Models\ContactoWeb::where('estado', 'nuevo')->count();
                    @endphp
                    <div x-data="{
                        key: 'comercial',
                        get open() { return Alpine.store('nav').open === this.key },
                        toggle() { Alpine.store('nav').open = this.open ? null : this.key },
                        paths: @js($pathsComercial),
                        checkOpen() {
                            const p = window.location.pathname;
                            if (this.paths.some(path => p === path || p.startsWith(path + '/'))) Alpine.store('nav').open = this.key;
                        }
                    }" x-init="checkOpen()" x-on:livewire:navigated.window="checkOpen()"
                        class="pt-0.5">
                        <button @click="toggle()" type="button"
                            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-left text-sm font-bold text-ink-primary hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                            <span class="flex items-center gap-3 min-w-0"><i
                                    class="fa-solid fa-briefcase w-5 text-center text-base text-gold-600 dark:text-gold-400 shrink-0"></i><span>Gestión
                                    Comercial</span></span>
                            <i class="fa-solid fa-chevron-down text-xs text-ink-secondary transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-collapse class="space-y-0.5 mt-0.5 ml-4 pl-2 border-l border-outline">
                            <a href="{{ route('admin.eventos') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-calendar-check w-5 text-center"></i>
                                <span>Eventos</span>
                            </a>
                            <a href="{{ route('admin.agenda') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-calendar-days w-5 text-center"></i>
                                <span>Agenda</span>
                            </a>
                            <a href="{{ route('admin.contratos') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-file-contract w-5 text-center"></i>
                                <span>Contratos</span>
                            </a>
                            <a href="{{ route('admin.solicitudes-web') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center justify-between px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <span class="flex items-center gap-3">
                                    <i class="fa-solid fa-inbox w-5 text-center"></i>
                                    <span>Solicitudes Web</span>
                                </span>
                                @if ($solicitudesNuevasBadge > 0)
                                    <span
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-slate-950">
                                        {{ $solicitudesNuevasBadge }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>

                    <!-- Grupo: Gestión Financiera (Sprint 3) -->
                    @php
                        $pathsFinanciera = [
                            route('admin.pagos', [], false),
                            route('admin.finanzas.dashboard', [], false),
                            route('admin.finanzas.movimientos', [], false),
                            route('admin.finanzas.fondo-comun', [], false),
                            route('admin.pagos-personal', [], false),
                        ];
                    @endphp
                    <div x-data="{
                        key: 'financiera',
                        get open() { return Alpine.store('nav').open === this.key },
                        toggle() { Alpine.store('nav').open = this.open ? null : this.key },
                        paths: @js($pathsFinanciera),
                        checkOpen() {
                            const p = window.location.pathname;
                            if (this.paths.some(path => p === path || p.startsWith(path + '/'))) Alpine.store('nav').open = this.key;
                        }
                    }" x-init="checkOpen()" x-on:livewire:navigated.window="checkOpen()"
                        class="pt-0.5">
                        <button @click="toggle()" type="button"
                            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-left text-sm font-bold text-ink-primary hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                            <span class="flex items-center gap-3 min-w-0"><i
                                    class="fa-solid fa-sack-dollar w-5 text-center text-base text-gold-600 dark:text-gold-400 shrink-0"></i><span>Finanzas</span></span>
                            <i class="fa-solid fa-chevron-down text-xs text-ink-secondary transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-collapse class="space-y-0.5 mt-0.5 ml-4 pl-2 border-l border-outline">
                            <a href="{{ route('admin.pagos') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-receipt w-5 text-center text-emerald-600 dark:text-emerald-400"></i>
                                <span>Cobros</span>
                            </a>
                            <a href="{{ route('admin.pagos-personal') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i
                                    class="fa-solid fa-hand-holding-dollar w-5 text-center text-rose-600 dark:text-rose-400"></i>
                                <span>Pagos a personal</span>
                            </a>
                            <a href="{{ route('admin.finanzas.movimientos') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-money-bill-transfer w-5 text-center"></i>
                                <span>Ingresos / Egresos</span>
                            </a>
                            <a href="{{ route('admin.finanzas.fondo-comun') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-vault w-5 text-center"></i>
                                <span>Fondo Común</span>
                            </a>
                            {{-- Dashboard Financiero (oculto por ahora)
                        <a href="{{ route('admin.finanzas.dashboard') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-chart-line w-5 text-center"></i>
                            <span>Dashboard Financiero</span>
                        </a>
                        --}}
                        </div>
                    </div>

                    <!-- Grupo: Informes y Reportes (Sprint 3) -->
                    @php
                        $pathsReportes = [
                            route('admin.reportes.eventos-contratos', [], false),
                            route('admin.reportes.estados-cuenta', [], false),
                            route('admin.reportes.distribucion', [], false),
                            route('admin.reportes.financieros', [], false),
                        ];
                    @endphp
                    <div x-data="{
                        key: 'reportes',
                        get open() { return Alpine.store('nav').open === this.key },
                        toggle() { Alpine.store('nav').open = this.open ? null : this.key },
                        paths: @js($pathsReportes),
                        checkOpen() {
                            const p = window.location.pathname;
                            if (this.paths.some(path => p === path || p.startsWith(path + '/'))) Alpine.store('nav').open = this.key;
                        }
                    }" x-init="checkOpen()" x-on:livewire:navigated.window="checkOpen()"
                        class="pt-0.5">
                        <button @click="toggle()" type="button"
                            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-left text-sm font-bold text-ink-primary hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                            <span class="flex items-center gap-3 min-w-0"><i
                                    class="fa-solid fa-chart-column w-5 text-center text-base text-gold-600 dark:text-gold-400 shrink-0"></i><span>Informes
                                    & Reportes</span></span>
                            <i class="fa-solid fa-chevron-down text-xs text-ink-secondary transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-collapse class="space-y-0.5 mt-0.5 ml-4 pl-2 border-l border-outline">
                            <a href="{{ route('admin.reportes.eventos-contratos') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-file-contract w-5 text-center"></i>
                                <span>Eventos & Contratos</span>
                            </a>
                            <a href="{{ route('admin.reportes.estados-cuenta') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i>
                                <span>Estados de Cuenta</span>
                            </a>
                            <a href="{{ route('admin.reportes.distribucion') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-users-viewfinder w-5 text-center"></i>
                                <span>Distribución a Músicos</span>
                            </a>
                            <a href="{{ route('admin.reportes.financieros') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-scale-balanced w-5 text-center"></i>
                                <span>Balances & Financiero</span>
                            </a>
                        </div>
                    </div>



                    {{-- Grupo: Activos Fijos - OCULTO POR AHORA (quitar este comentario para volver a mostrarlo)
                @php
                    $pathsActivos = [
                        route('admin.activos-fijos.dashboard', [], false),
                        route('admin.activos-fijos.categorias', [], false),
                        route('admin.activos-fijos.articulos', [], false),
                        route('admin.activos-fijos.ingresos', [], false),
                        route('admin.activos-fijos.egresos', [], false),
                        route('admin.activos-fijos.asignaciones', [], false),
                        route('admin.activos-fijos.devoluciones', [], false),
                        route('admin.activos-fijos.bajas', [], false),
                        route('admin.activos-fijos.kardex', [], false),
                    ];
                @endphp
                <div x-data="{
                        key: 'activos',
                        get open() { return Alpine.store('nav').open === this.key },
                        toggle() { Alpine.store('nav').open = this.open ? null : this.key },
                        paths: @js($pathsActivos),
                        checkOpen() {
                            const p = window.location.pathname;
                            if (this.paths.some(path => p === path || p.startsWith(path + '/'))) Alpine.store('nav').open = this.key;
                        }
                    }"
                    x-init="checkOpen()" x-on:livewire:navigated.window="checkOpen()" class="pt-0.5">
                    <button @click="toggle()" type="button"
                        class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-left text-sm font-bold text-ink-primary hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                        <span class="flex items-center gap-3 min-w-0"><i class="fa-solid fa-warehouse w-5 text-center text-base text-gold-600 dark:text-gold-400 shrink-0"></i><span>Activos Fijos</span></span>
                        <i class="fa-solid fa-chevron-down text-xs text-ink-secondary transition-transform"
                            :class="open ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="open" x-collapse class="space-y-0.5 mt-0.5 ml-4 pl-2 border-l border-outline">
                        <a href="{{ route('admin.activos-fijos.dashboard') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                            <span>Dashboard Activos</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.categorias') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-folder-tree w-5 text-center"></i>
                            <span>Categorías</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.articulos') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                            <span>Artículos & Productos</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.ingresos') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-arrow-down-to-bracket w-5 text-center"></i>
                            <span>Ingresos</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.egresos') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-arrow-up-from-bracket w-5 text-center"></i>
                            <span>Egresos</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.asignaciones') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-user-check w-5 text-center"></i>
                            <span>Asignaciones</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.devoluciones') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-arrow-rotate-left w-5 text-center"></i>
                            <span>Devoluciones</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.bajas') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-trash-can w-5 text-center"></i>
                            <span>Bajas</span>
                        </a>
                        <a href="{{ route('admin.activos-fijos.kardex') }}" wire:navigate
                            wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                            class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                            <i class="fa-solid fa-chart-line w-5 text-center"></i>
                            <span>Kardex (PPP)</span>
                        </a>
                    </div>
                </div>
                --}}

                    <!-- Grupo: Administración -->
                    @php
                        $pathsAdministracion = [
                            route('config.empresa', [], false),
                            route('admin.sitio-web.banners', [], false),
                            route('admin.sitio-web.galeria', [], false),
                            route('admin.usuarios', [], false),
                            route('admin.roles', [], false),
                            route('admin.auditoria', [], false),
                        ];
                    @endphp
                    <div x-data="{
                        key: 'administracion',
                        get open() { return Alpine.store('nav').open === this.key },
                        toggle() { Alpine.store('nav').open = this.open ? null : this.key },
                        paths: @js($pathsAdministracion),
                        checkOpen() {
                            const p = window.location.pathname;
                            if (this.paths.some(path => p === path || p.startsWith(path + '/'))) Alpine.store('nav').open = this.key;
                        }
                    }" x-init="checkOpen()" x-on:livewire:navigated.window="checkOpen()"
                        class="pt-0.5">
                        <button @click="toggle()" type="button"
                            class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-left text-sm font-bold text-ink-primary hover:text-gold-600 dark:hover:text-gold-400 transition-colors">
                            <span class="flex items-center gap-3 min-w-0"><i
                                    class="fa-solid fa-user-gear w-5 text-center text-base text-gold-600 dark:text-gold-400 shrink-0"></i><span>Administración</span></span>
                            <i class="fa-solid fa-chevron-down text-xs text-ink-secondary transition-transform"
                                :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div x-show="open" x-collapse class="space-y-0.5 mt-0.5 ml-4 pl-2 border-l border-outline">
                            <a href="{{ route('config.empresa') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-building w-5 text-center"></i>
                                <span>Empresa</span>
                            </a>
                            <a href="{{ route('admin.sitio-web.banners') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-images w-5 text-center"></i>
                                <span>Gestión de Banner Inicio</span>
                            </a>
                            <a href="{{ route('admin.sitio-web.galeria') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-photo-film w-5 text-center"></i>
                                <span>Gestión de Galería</span>
                            </a>
                            <a href="{{ route('admin.usuarios') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-users-gear w-5 text-center"></i>
                                <span>Usuarios</span>
                            </a>
                            <a href="{{ route('admin.roles') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-user-shield w-5 text-center"></i>
                                <span>Roles & Permisos</span>
                            </a>
                            <a href="{{ route('admin.auditoria') }}" wire:navigate
                                wire:current="!bg-gold-500 !text-slate-950 !font-semibold"
                                class="flex items-center gap-3 px-3 py-1.5 rounded-lg font-medium text-[13px] transition-colors text-ink-secondary hover:bg-surface-hover hover:text-ink-primary">
                                <i class="fa-solid fa-list-check w-5 text-center"></i>
                                <span>Auditoría de Logs</span>
                            </a>
                        </div>
                    </div>
                </nav>
            </aside>
        @endpersist

        <!-- Main Content Container -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar Header -->
            <header
                class="h-16 bg-surface-card border-b border-outline px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true"
                        class="md:hidden text-ink-secondary hover:text-ink-primary p-2 rounded-lg bg-surface-sunken border border-outline">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <h2 class="font-bold text-base text-ink-primary hidden sm:block">
                        {{ $heading ?? 'Panel de Administración' }}</h2>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Ver sitio web público -->
                    <a href="{{ route('web.home') }}" target="_blank" rel="noopener" title="Ver sitio web"
                        class="w-9 h-9 rounded-lg flex items-center justify-center bg-surface-sunken border border-outline text-ink-secondary hover:text-gold-600 dark:hover:text-gold-400 hover:border-gold-500/40 transition-colors">
                        <i class="fa-solid fa-arrow-up-right-from-square"></i>
                    </a>

                    <!-- Selector de Tema Claro/Oscuro -->
                    <button type="button" x-data="{
                        theme: localStorage.getItem('theme') ?? (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                    }"
                        @click="
                            theme = theme === 'dark' ? 'light' : 'dark';
                            localStorage.setItem('theme', theme);
                            document.documentElement.classList.toggle('dark', theme === 'dark');
                        "
                        class="w-9 h-9 rounded-lg flex items-center justify-center bg-surface-sunken border border-outline text-ink-secondary hover:text-gold-400 hover:border-gold-500/40 transition-colors"
                        title="Cambiar tema claro/oscuro">
                        <i class="fa-solid" :class="theme === 'dark' ? 'fa-sun' : 'fa-moon'"></i>
                    </button>

                    <!-- Usuario: perfil y cierre de sesión -->
                    <div class="relative" x-data="{ userMenu: false }" @click.outside="userMenu = false"
                        @keydown.escape.window="userMenu = false">
                        <button type="button" @click="userMenu = !userMenu"
                            class="flex items-center gap-2.5 h-9 pl-1 pr-2.5 rounded-lg bg-surface-sunken border border-outline hover:border-gold-500/40 transition-colors">
                            <span
                                class="w-7 h-7 rounded-full bg-gold-500/20 border border-gold-500/30 flex items-center justify-center text-xs font-bold text-gold-700 dark:text-gold-400 shrink-0">
                                {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                            </span>
                            <span class="hidden sm:block text-left leading-tight">
                                <span
                                    class="block text-xs font-semibold text-ink-primary max-w-[140px] truncate">{{ Auth::user()->name ?? 'Usuario' }}</span>
                                <span
                                    class="block text-[11px] text-ink-muted max-w-[140px] truncate">{{ Auth::user()->roles->pluck('name')->first() ?? 'Sin Rol' }}</span>
                            </span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-ink-muted"></i>
                        </button>
                        <div x-show="userMenu" x-cloak x-transition.opacity
                            class="absolute right-0 mt-2 w-56 bg-surface-card border border-outline rounded-lg shadow-lg py-1 z-40">
                            <div class="px-3 py-2 border-b border-outline">
                                <p class="text-sm font-semibold text-ink-primary truncate">
                                    {{ Auth::user()->name ?? 'Usuario' }}</p>
                                <p class="text-xs text-ink-muted truncate">
                                    {{ Auth::user()->roles->pluck('name')->first() ?? 'Sin Rol' }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-ink-primary hover:bg-surface-hover transition-colors">
                                    <i class="fa-solid fa-right-from-bracket w-4 text-center text-ink-muted"></i>
                                    <span>Cerrar sesión</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Notification Messages -->
            @if (session()->has('success'))
                <div
                    class="mx-6 mt-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-500 dark:text-emerald-400 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if (session()->has('error'))
                <div
                    class="mx-6 mt-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-rose-500 dark:text-rose-400 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Page Content Slot -->
            <main class="flex-1 overflow-y-auto">
                @if ($themed ?? false)
                    <div class="min-h-full bg-surface-page p-6">
                        {{ $slot }}
                    </div>
                @else
                    {{-- Pantalla aún no migrada al sistema de temas: se fuerza el modo oscuro
                         para que se vea igual que antes, sin importar el tema elegido. --}}
                    <div class="dark min-h-full bg-surface-page p-6">
                        {{ $slot }}
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Hidden Form for Auto Logout -->
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>

    <!-- Modal de Advertencia de Inactividad (sincronizado con SESSION_LIFETIME del .env) -->
    @php
        // Tiempo total de sesión en segundos (config/session.php -> SESSION_LIFETIME en minutos)
        $sessionLifetimeSeconds = (int) config('session.lifetime') * 60;
        // El aviso aparece 60s antes de que la sesión expire (mínimo 10s si la sesión es muy corta)
        $warningLeadSeconds = min(60, max(10, (int) ($sessionLifetimeSeconds * 0.2)));
        $warningAtSeconds = max(0, $sessionLifetimeSeconds - $warningLeadSeconds);
    @endphp
    <div x-data="{
        idleSeconds: 0,
        warningOpen: false,
        countdown: {{ $warningLeadSeconds }},
        timer: null,
        sessionLifetime: {{ $sessionLifetimeSeconds }},
        warningAt: {{ $warningAtSeconds }},
        resetIdleTimer() {
            this.idleSeconds = 0;
            if (this.warningOpen) {
                this.warningOpen = false;
                this.countdown = {{ $warningLeadSeconds }};
            }
        },
        initTimer() {
            const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'];
            events.forEach(evt => window.addEventListener(evt, () => this.resetIdleTimer()));
    
            this.timer = setInterval(() => {
                this.idleSeconds++;
                // Muestra el modal de advertencia poco antes de que expire la sesión
                if (this.idleSeconds >= this.warningAt && this.idleSeconds < this.sessionLifetime) {
                    this.warningOpen = true;
                    this.countdown = this.sessionLifetime - this.idleSeconds;
                }
                // Al llegar al tiempo de vida de la sesión, cierra sesión automáticamente
                else if (this.idleSeconds >= this.sessionLifetime) {
                    clearInterval(this.timer);
                    window.location.href = @js(route('logout'));
                }
            }, 1000);
        }
    }" x-init="initTimer();
    window.addEventListener('livewire:navigating', () => clearInterval(timer), { once: true })">

        <div x-show="warningOpen" x-cloak x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">

            <div
                class="w-full max-w-md bg-slate-900 border border-gold-500/50 rounded-3xl p-6 text-center space-y-6 shadow-2xl">
                <div
                    class="w-16 h-16 rounded-full bg-gold-500/10 border border-gold-500/30 text-gold-400 flex items-center justify-center mx-auto text-3xl animate-bounce">
                    <i class="fa-solid fa-clock"></i>
                </div>

                <div class="space-y-2">
                    <h3 class="font-serif font-bold text-2xl text-white">¿Sigues ahí?</h3>
                    <p class="text-xs text-slate-300">
                        Por motivos de seguridad, tu sesión se cerrará automáticamente por inactividad en:
                    </p>
                </div>

                <div class="py-4">
                    <span class="font-mono font-black text-5xl text-gold-400" x-text="countdown + 's'">60s</span>
                </div>

                <p class="text-[11px] text-slate-400">
                    Mueve el cursor o presiona cualquier tecla para mantener tu sesión activa.
                </p>

                <div class="pt-2">
                    <button @click="resetIdleTimer()"
                        class="w-full py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/25 transition-all">
                        <i class="fa-solid fa-user-check mr-2"></i> Mantener Sesión Activa
                    </button>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>