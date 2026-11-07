<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $empresaWeb = \App\Models\Empresa::obtener(); @endphp
    <title>
        {{ $title ?? ($empresaWeb->nombre_comercial ?? 'Mariachi León Guanajuato') . ' - Música y Mariachi Profesional' }}
    </title>

    {{-- Ícono de la pestaña: el logo de la empresa --}}
    @if ($empresaWeb->logo_url)
        <link rel="icon" href="{{ asset($empresaWeb->logo_url) }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ asset($empresaWeb->logo_url) }}">
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @endif

    {{-- Tema: oscuro por defecto; recuerda la elección del visitante --}}
    <script>
        try {
            if (localStorage.getItem('tema') !== 'light') document.documentElement.classList.add('dark');
        } catch (e) {
            document.documentElement.classList.add('dark');
        }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        serif: ['Cinzel', 'serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        /* Aparece al hacer scroll */
        [data-reveal] {
            opacity: 0;
            transform: translateY(18px);
            transition: opacity .6s ease, transform .6s ease;
        }

        [data-reveal].in {
            opacity: 1;
            transform: none;
        }

        /* Hero: zoom lento de la imagen y entrada del texto */
        @keyframes kenburns {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.08);
            }
        }

        .kenburns {
            animation: kenburns 14s ease-out forwards;
        }

        @keyframes heroin {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: none;
            }
        }

        .hero-in {
            animation: heroin .8s ease-out both;
        }

        .hero-in-2 {
            animation: heroin .8s .15s ease-out both;
        }

        .hero-in-3 {
            animation: heroin .8s .3s ease-out both;
        }

        /* WhatsApp flotante: pulso constante + sacudida cada tanto */
        @keyframes waring {
            0% {
                transform: scale(1);
                opacity: .55;
            }

            70%,
            100% {
                transform: scale(1.5);
                opacity: 0;
            }
        }

        .wa-ring {
            animation: waring 2s ease-out infinite;
        }

        @keyframes wawiggle {

            0%,
            82%,
            100% {
                transform: rotate(0);
            }

            86% {
                transform: rotate(-7deg);
            }

            90% {
                transform: rotate(7deg);
            }

            94% {
                transform: rotate(-5deg);
            }

            98% {
                transform: rotate(4deg);
            }
        }

        .wa-wiggle {
            animation: wawiggle 5s ease-in-out infinite;
        }

        @media (prefers-reduced-motion: reduce) {
            [data-reveal] {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .wa-ring,
            .wa-wiggle {
                animation: none;
            }

            .kenburns,
            .hero-in,
            .hero-in-2,
            .hero-in-3 {
                animation: none;
            }
        }
    </style>
    <noscript>
        <style>
            [data-reveal] {
                opacity: 1;
                transform: none;
            }
        </style>
    </noscript>
    @livewireStyles
</head>

<body
    class="bg-white text-slate-700 dark:bg-slate-950 dark:text-slate-100 font-sans antialiased selection:bg-gold-500 selection:text-slate-950 pb-14 md:pb-0"
    x-data="{ mobileMenuOpen: false, scrolled: false }" @scroll.window="scrolled = window.scrollY > 10">

    @php
        $links = [
            ['web.home', 'Inicio'],
            ['web.nosotros', 'Nosotros'],
            ['web.servicios', 'Servicios'],
            ['web.galeria', 'Galería'],
            ['web.contacto', 'Contacto'],
        ];
    @endphp

    <!-- Cabecera -->
    <header class="fixed top-0 inset-x-0 z-50 border-b transition-all duration-300 backdrop-blur-lg"
        :class="scrolled ? 'bg-white/95 dark:bg-slate-950/95 border-slate-200 dark:border-slate-800 shadow-sm' :
            'bg-white/80 dark:bg-slate-950/80 border-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <a href="{{ route('web.home') }}" class="flex items-center gap-3 group shrink-0">
                <div
                    class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-gold-500/40 p-1 flex items-center justify-center group-hover:scale-105 transition-transform overflow-hidden">
                    @if ($empresaWeb->logo_url)
                        <img src="{{ asset($empresaWeb->logo_url) }}" alt="Logo {{ $empresaWeb->nombre_comercial }}"
                            class="w-full h-full object-contain rounded-lg">
                    @else
                        <i class="fa-solid fa-guitar text-gold-600 dark:text-gold-400 text-lg"></i>
                    @endif
                </div>
                <div class="leading-none">
                    <span
                        class="font-serif font-black text-base sm:text-lg text-slate-900 dark:text-white tracking-wide block uppercase whitespace-nowrap">{{ $empresaWeb->nombre_comercial ?? 'MARIACHI LEÓN' }}</span>
                    <span
                        class="text-[10px] text-gold-700 dark:text-gold-400 font-bold tracking-widest uppercase block mt-1">{{ $empresaWeb->ciudad_pais ?? 'Santa Cruz - Bolivia' }}</span>
                </div>
            </a>

            <!-- Menú escritorio -->
            <nav class="hidden md:flex items-center gap-7">
                @foreach ($links as [$ruta, $texto])
                    @php $activo = request()->routeIs($ruta); @endphp
                    <a href="{{ route($ruta) }}"
                        class="relative text-sm font-medium whitespace-nowrap transition-colors py-1 {{ $activo ? 'text-gold-700 dark:text-gold-400 font-semibold' : 'text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white' }}">
                        {{ $texto }}
                        @if ($activo)
                            <span class="absolute -bottom-0.5 left-0 right-0 h-0.5 rounded-full bg-gold-500"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                <!-- Tema claro / oscuro -->
                <button type="button" x-data="{ dark: document.documentElement.classList.contains('dark') }"
                    @click="dark = !dark; document.documentElement.classList.toggle('dark', dark); try { localStorage.setItem('tema', dark ? 'dark' : 'light') } catch (e) {}"
                    title="Cambiar tema"
                    class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-gold-500/50 hover:text-gold-600 flex items-center justify-center transition-all">
                    <i class="fa-solid" :class="dark ? 'fa-sun' : 'fa-moon'"></i>
                </button>

                <!-- Iniciar sesión (personal) -->
                <a href="{{ route('login') }}" title="Iniciar sesión"
                    class="h-9 px-3 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:border-gold-500/50 hover:text-gold-700 dark:hover:text-gold-400 flex items-center gap-2 text-sm font-medium whitespace-nowrap transition-all">
                    <i class="fa-regular fa-user"></i><span class="hidden lg:inline">Ingresar</span>
                </a>

                <a href="{{ route('web.contacto') }}"
                    class="hidden sm:inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold whitespace-nowrap bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-md shadow-gold-500/20 transition-all hover:scale-[1.03]">
                    <i class="fa-solid fa-calendar-check text-xs"></i>Cotizar
                </a>

                <button @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden w-9 h-9 flex items-center justify-center text-slate-600 dark:text-slate-300">
                    <i class="fa-solid fa-bars text-xl" x-show="!mobileMenuOpen"></i>
                    <i class="fa-solid fa-xmark text-xl" x-show="mobileMenuOpen" x-cloak></i>
                </button>
            </div>
        </div>

        <!-- Menú móvil -->
        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="md:hidden bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 px-4 py-3 space-y-1">
            @foreach ($links as [$ruta, $texto])
                <a href="{{ route($ruta) }}"
                    class="block px-3 py-2.5 rounded-lg text-base font-semibold {{ request()->routeIs($ruta) ? 'bg-gold-500/10 text-gold-700 dark:text-gold-400' : 'text-slate-700 dark:text-slate-200' }}">{{ $texto }}</a>
            @endforeach
            <a href="{{ route('web.contacto') }}"
                class="block text-center mt-2 py-3 rounded-xl font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950">Cotizar
                evento</a>
            <a href="{{ route('login') }}"
                class="block text-center py-2.5 rounded-xl text-sm font-semibold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-800"><i
                    class="fa-regular fa-user mr-1.5"></i>Iniciar sesión</a>
        </div>
    </header>

    <!-- Mensaje de éxito -->
    @if (session()->has('mensaje_exito') || session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 8000)" x-show="show"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-6 scale-95"
            class="fixed top-20 left-1/2 -translate-x-1/2 z-[100] w-full max-w-lg px-4 pointer-events-none">
            <div
                class="p-4 rounded-2xl bg-white dark:bg-slate-900 border-2 border-emerald-500/70 text-slate-800 dark:text-white shadow-2xl flex items-start gap-3 pointer-events-auto">
                <i class="fa-solid fa-circle-check text-2xl text-emerald-500"></i>
                <div class="flex-1 min-w-0">
                    <h4 class="font-bold text-sm text-emerald-600 dark:text-emerald-400">¡Solicitud enviada!</h4>
                    <p class="text-xs text-slate-600 dark:text-slate-200 mt-0.5">
                        {{ session('mensaje_exito') ?? session('success') }}</p>
                </div>
                <button type="button" @click="show = false"
                    class="text-slate-400 hover:text-slate-700 dark:hover:text-white p-1 text-sm">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    <main class="pt-16">
        {{ $slot }}
    </main>

    <!-- Pie -->
    <footer class="bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800/80 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-xl bg-white dark:bg-slate-900 border border-gold-500/40 p-1 flex items-center justify-center overflow-hidden shrink-0">
                        @if ($empresaWeb->logo_url)
                            <img src="{{ asset($empresaWeb->logo_url) }}" alt="Logo"
                                class="w-full h-full object-contain rounded-lg">
                        @else
                            <i class="fa-solid fa-guitar text-gold-600 dark:text-gold-400 text-lg"></i>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <span
                            class="font-serif font-bold text-base text-slate-900 dark:text-white block leading-none uppercase">{{ $empresaWeb->nombre_comercial ?? 'MARIACHI LEÓN' }}</span>
                        <span
                            class="text-xs text-slate-500 dark:text-slate-400 block mt-1">{{ $empresaWeb->slogan ?? 'Música mexicana en vivo para tus celebraciones' }}</span>
                    </div>
                </div>

                <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600 dark:text-slate-400">
                    @foreach ($links as [$ruta, $texto])
                        <a href="{{ route($ruta) }}"
                            class="hover:text-gold-700 dark:hover:text-white transition-colors">{{ $texto }}</a>
                    @endforeach
                </nav>
            </div>

            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pt-5 border-t border-slate-200 dark:border-slate-800/80">
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-slate-600 dark:text-slate-400">
                    @if ($empresaWeb->telefono_principal)
                        <a href="{{ $empresaWeb->telefono_url }}"
                            class="flex items-center gap-2 hover:text-gold-700 dark:hover:text-gold-400 transition-colors">
                            <i
                                class="fa-solid fa-phone text-gold-600 dark:text-gold-500 text-xs"></i>{{ $empresaWeb->telefono_principal }}
                        </a>
                    @endif
                    @if ($empresaWeb->email_contacto)
                        <a href="mailto:{{ $empresaWeb->email_contacto }}"
                            class="flex items-center gap-2 hover:text-gold-700 dark:hover:text-gold-400 transition-colors break-all">
                            <i
                                class="fa-solid fa-envelope text-gold-600 dark:text-gold-500 text-xs"></i>{{ $empresaWeb->email_contacto }}
                        </a>
                    @endif
                    @if ($empresaWeb->direccion_fisica)
                        <span class="flex items-center gap-2">
                            <i
                                class="fa-solid fa-location-dot text-gold-600 dark:text-gold-500 text-xs"></i>{{ $empresaWeb->direccion_fisica }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @php
                        $redes = [
                            ['redes_instagram', 'fa-instagram', 'Instagram'],
                            ['redes_tiktok', 'fa-tiktok', 'TikTok'],
                            ['redes_youtube', 'fa-youtube', 'YouTube'],
                            ['redes_facebook', 'fa-facebook-f', 'Facebook'],
                        ];
                    @endphp
                    @if ($empresaWeb->whatsapp_numero_limpio)
                        <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                            title="WhatsApp"
                            class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-emerald-500 hover:border-emerald-500 hover:text-white flex items-center justify-center transition-all hover:scale-110">
                            <i class="fa-brands fa-whatsapp text-lg"></i>
                        </a>
                    @endif
                    @foreach ($redes as [$campo, $icono, $nombre])
                        @if ($empresaWeb->{$campo})
                            <a href="{{ $empresaWeb->{$campo} }}" target="_blank" rel="noopener noreferrer"
                                title="{{ $nombre }}"
                                class="w-9 h-9 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-gold-500 hover:border-gold-500 hover:text-slate-950 flex items-center justify-center transition-all hover:scale-110">
                                <i class="fa-brands {{ $icono }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ $empresaWeb->nombre_comercial ?? 'Mariachi León Guanajuato' }}. Todos
                    los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Barra fija en celular: Llamar / WhatsApp -->
    <div
        class="md:hidden fixed bottom-0 inset-x-0 z-50 grid grid-cols-2 bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800">
        @if ($empresaWeb->telefono_principal)
            <a href="{{ $empresaWeb->telefono_url }}"
                class="py-3 flex items-center justify-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100">
                <i class="fa-solid fa-phone text-gold-600 dark:text-gold-400"></i>Llamar
            </a>
        @else
            <a href="{{ route('web.contacto') }}"
                class="py-3 flex items-center justify-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-100">
                <i class="fa-solid fa-calendar-check text-gold-600 dark:text-gold-400"></i>Cotizar
            </a>
        @endif
        @if ($empresaWeb->whatsapp_numero_limpio)
            <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                class="py-3 flex items-center justify-center gap-2 text-sm font-bold bg-emerald-500 text-white">
                <i class="fa-brands fa-whatsapp text-lg"></i>WhatsApp
            </a>
        @endif
    </div>

    <!-- WhatsApp flotante (solo escritorio) -->
    @if ($empresaWeb->whatsapp_numero_limpio)
        <aside class="hidden md:flex fixed bottom-6 right-6 z-50 flex-col items-end gap-2" x-data="{ globo: false }"
            x-init="setTimeout(() => globo = true, 3000);
            setTimeout(() => globo = false, 12000)">
            <div x-show="globo" x-cloak x-transition
                class="relative px-3 py-2 rounded-xl rounded-br-sm bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-semibold shadow-xl border border-slate-200 dark:border-slate-700">
                ¿Cotizamos tu evento? 🎺
                <button type="button" @click="globo = false" class="ml-2 text-slate-400 hover:text-slate-600"
                    title="Cerrar"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="relative">
                <span class="wa-ring absolute inset-0 rounded-full bg-emerald-500"></span>
                <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" rel="noopener noreferrer"
                    class="wa-wiggle relative flex items-center gap-3 px-4 py-3 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white shadow-xl shadow-emerald-500/40 hover:scale-105 active:scale-95 transition-all duration-300"
                    title="Escríbenos por WhatsApp">
                    <i class="fa-brands fa-whatsapp text-2xl"></i>
                    <span class="text-xs font-extrabold pr-1">WhatsApp</span>
                </a>
            </div>
        </aside>
    @endif

    <script>
        // Animación de aparición al hacer scroll
        (function() {
            var io = null;

            function init() {
                var els = document.querySelectorAll('[data-reveal]:not(.in):not([data-seen])');
                if (!('IntersectionObserver' in window)) {
                    els.forEach(function(e) {
                        e.classList.add('in');
                    });
                    return;
                }
                if (!io) {
                    io = new IntersectionObserver(function(entries) {
                        entries.forEach(function(x) {
                            if (x.isIntersecting) {
                                x.target.classList.add('in');
                                io.unobserve(x.target);
                            }
                        });
                    }, {
                        threshold: 0.1
                    });
                }
                els.forEach(function(e, i) {
                    e.setAttribute('data-seen', '1');
                    e.style.transitionDelay = ((i % 3) * 80) + 'ms';
                    io.observe(e);
                });
            }
            document.addEventListener('DOMContentLoaded', function() {
                init();
                var t = null;
                new MutationObserver(function() {
                        clearTimeout(t);
                        t = setTimeout(init, 40);
                    })
                    .observe(document.body, {
                        childList: true,
                        subtree: true
                    });
            });
        })();
    </script>

    @livewireScripts
</body>

</html>