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
            document.documentElement.classList.add('dark'); // el panel del músico siempre va en oscuro (negro y dorado)
        })();
    </script>
    <title>{{ $title ?? 'Mi panel - Mariachi León' }}</title>
    <!-- Favicon -->
    @php $empresaFavicon = \App\Models\Empresa::obtener(); @endphp
    @if ($empresaFavicon->logo_url)
        <link rel="icon" href="{{ asset($empresaFavicon->logo_url) }}" type="image/png">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
        <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    @endif
    <!-- PWA -->
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="León Guanajuato">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="apple-touch-icon" href="{{ route('pwa.icono', ['size' => 192]) }}">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('{{ route('pwa.sw', [], false) }}')
                .catch(() => {}));
        }
    </script>
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

<body class="min-h-full bg-surface-page text-ink-primary font-sans antialiased"
    style="background-image: radial-gradient(70% 45% at 50% 0%, rgba(245, 158, 11, .20), transparent 70%); background-attachment: fixed;">
    @php $empresaMusico = $empresaFavicon; @endphp
    <div class="min-h-full flex flex-col">
        <header class="sticky top-0 z-30 bg-surface-card/95 backdrop-blur border-b-2 border-gold-500">
            <div class="max-w-6xl mx-auto px-3 lg:px-6 h-14 lg:h-16 flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    @if ($empresaMusico->logo_url)
                        <img src="{{ asset($empresaMusico->logo_url) }}" alt="Logo"
                            class="w-9 h-9 lg:w-11 lg:h-11 rounded-lg object-contain bg-white p-0.5">
                    @endif
                    <div class="min-w-0 leading-tight">
                        <div class="text-sm lg:text-base font-bold text-ink-primary truncate">
                            {{ $empresaMusico->nombre_comercial ?? 'Mariachi León' }}</div>
                        <div class="text-[11px] lg:text-xs text-gold-400 truncate">{{ Auth::user()->name ?? '' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="h-9 px-3 rounded-lg bg-surface-sunken border border-outline text-sm font-semibold text-ink-primary hover:border-gold-500/60 inline-flex items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket text-ink-muted"></i>
                        <span>Salir</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1">
            <div class="max-w-6xl mx-auto p-3 lg:p-6">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>

</html>