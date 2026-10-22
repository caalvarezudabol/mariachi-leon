<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php $empresaWeb = \App\Models\Empresa::obtener(); @endphp
    <title>{{ $title ?? (($empresaWeb->nombre_comercial ?? 'Mariachi León Guanajuato') . ' - Música y Mariachi Profesional') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
        [x-cloak] { display: none !important; }
        .hero-bg {
            background: linear-gradient(185deg, rgba(11, 19, 41, 0.88) 0%, rgba(2, 6, 23, 0.96) 100%), 
                        url('https://images.unsplash.com/photo-1514525253161-7a46d19cd819?q=80&w=1920&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
    </style>
    @livewireStyles
</head>
<body class="bg-slate-950 text-slate-100 font-sans antialiased selection:bg-gold-500 selection:text-slate-950" x-data="{ mobileMenuOpen: false }">

    <!-- Header Navigation -->
    @php $empresaWeb = \App\Models\Empresa::obtener(); @endphp
    <header class="fixed top-0 inset-x-0 z-50 bg-slate-950/80 backdrop-blur-lg border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo Oficial Mariachi León Guanajuato -->
            <a href="{{ route('web.home') }}" class="flex items-center gap-3 group">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-gold-500/40 p-1 flex items-center justify-center shadow-lg shadow-gold-500/20 group-hover:scale-105 transition-transform overflow-hidden">
                    @if($empresaWeb->logo_url)
                        <img src="{{ asset($empresaWeb->logo_url) }}" alt="Logo {{ $empresaWeb->nombre_comercial }}" class="w-full h-full object-contain rounded-xl">
                    @else
                        <svg viewBox="0 0 100 100" class="w-full h-full text-gold-400 fill-current">
                            <!-- Emblem Badge with Lions & Trumpets -->
                            <circle cx="50" cy="50" r="45" fill="#0b1329" stroke="#d97706" stroke-width="3"/>
                            <path d="M50 15 L53 25 L63 25 L55 31 L58 41 L50 35 L42 41 L45 31 L37 25 L47 25 Z" fill="#fbbf24"/>
                            <text x="50" y="58" font-family="Cinzel, serif" font-weight="900" font-size="20" fill="#f59e0b" text-anchor="middle">LEÓN</text>
                            <text x="50" y="72" font-family="Cinzel, serif" font-weight="700" font-size="9" fill="#ffffff" text-anchor="middle" letter-spacing="1">GUANAJUATO</text>
                            <text x="50" y="82" font-family="sans-serif" font-weight="800" font-size="7" fill="#fbbf24" text-anchor="middle" letter-spacing="2">MARIACHI</text>
                        </svg>
                    @endif
                </div>
                <div>
                    <span class="font-serif font-black text-xl text-white tracking-wide block leading-none uppercase">{{ $empresaWeb->nombre_comercial ?? 'MARIACHI LEÓN' }}</span>
                    <span class="text-[10px] text-gold-400 font-bold tracking-widest uppercase block mt-1">{{ $empresaWeb->ciudad_pais ?? 'Guanajuato • Bolivia' }}</span>
                </div>
            </a>

            <!-- Desktop Navigation -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="{{ route('web.home') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('web.home') ? 'text-gold-400 font-semibold' : 'text-slate-300 hover:text-white' }}">Inicio</a>
                <a href="{{ route('web.nosotros') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('web.nosotros') ? 'text-gold-400 font-semibold' : 'text-slate-300 hover:text-white' }}">Nosotros</a>
                <a href="{{ route('web.servicios') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('web.servicios') ? 'text-gold-400 font-semibold' : 'text-slate-300 hover:text-white' }}">Servicios & Paquetes</a>
                <a href="{{ route('web.galeria') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('web.galeria') ? 'text-gold-400 font-semibold' : 'text-slate-300 hover:text-white' }}">Galería</a>
                <a href="{{ route('web.contacto') }}" class="text-sm font-medium transition-colors {{ request()->routeIs('web.contacto') ? 'text-gold-400 font-semibold' : 'text-slate-300 hover:text-white' }}">Contacto</a>
            </nav>

            <div class="hidden md:flex items-center gap-3">
                @if($empresaWeb->whatsapp_numero_limpio)
                    <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" title="Contactar por WhatsApp" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-emerald-500/15 text-emerald-400 hover:bg-emerald-500 hover:text-white border border-emerald-500/30 transition-all flex items-center gap-2 shadow-sm">
                        <i class="fa-brands fa-whatsapp text-base"></i>
                        <span class="hidden lg:inline">{{ $empresaWeb->whatsapp_comercial ?: $empresaWeb->telefono_principal }}</span>
                    </a>
                @endif
                <a href="{{ route('login') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-900 border border-slate-800 transition-all">
                    Acceso Personal
                </a>
                <a href="{{ route('web.contacto') }}" class="px-5 py-2 rounded-xl text-xs font-bold bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/20 transition-all hover:scale-[1.02]">
                    Cotizar Evento
                </a>
            </div>

            <!-- Mobile Hamburger -->
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 text-slate-400 hover:text-white">
                <i class="fa-solid fa-bars text-2xl" x-show="!mobileMenuOpen"></i>
                <i class="fa-solid fa-xmark text-2xl" x-show="mobileMenuOpen" x-cloak></i>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden bg-slate-900 border-b border-slate-800 p-6 space-y-4">
            <a href="{{ route('web.home') }}" class="block text-base font-semibold text-slate-200">Inicio</a>
            <a href="{{ route('web.nosotros') }}" class="block text-base font-semibold text-slate-200">Nosotros</a>
            <a href="{{ route('web.servicios') }}" class="block text-base font-semibold text-slate-200">Servicios & Paquetes</a>
            <a href="{{ route('web.galeria') }}" class="block text-base font-semibold text-slate-200">Galería</a>
            <a href="{{ route('web.contacto') }}" class="block text-base font-semibold text-slate-200">Contacto</a>
            <div class="pt-4 border-t border-slate-800 flex flex-col gap-3">
                @if($empresaWeb->whatsapp_numero_limpio)
                    <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" class="w-full text-center py-2.5 rounded-xl font-bold bg-emerald-600 text-white flex items-center justify-center gap-2 text-sm">
                        <i class="fa-brands fa-whatsapp text-lg"></i>
                        <span>WhatsApp Directo: {{ $empresaWeb->whatsapp_comercial ?: $empresaWeb->telefono_principal }}</span>
                    </a>
                @endif
                @if($empresaWeb->redes_linktree)
                    <a href="{{ $empresaWeb->redes_linktree }}" target="_blank" class="w-full text-center py-2.5 rounded-xl font-bold bg-slate-800 text-gold-400 border border-gold-500/30 flex items-center justify-center gap-2 text-sm">
                        <i class="fa-solid fa-tree"></i>
                        <span>Redes Sociales (Linktree)</span>
                    </a>
                @endif
                <a href="{{ route('web.contacto') }}" class="w-full text-center py-3 rounded-xl font-bold bg-gold-500 text-slate-950">Cotizar Evento</a>
                <a href="{{ route('login') }}" class="w-full text-center py-3 rounded-xl font-semibold text-slate-300 border border-slate-700">Acceso Personal</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="pt-20">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-gold-500/40 p-1 flex items-center justify-center shadow-lg shadow-gold-500/20 overflow-hidden">
                            @if($empresaWeb->logo_url)
                                <img src="{{ asset($empresaWeb->logo_url) }}" alt="Logo {{ $empresaWeb->nombre_comercial }}" class="w-full h-full object-contain rounded-xl">
                            @else
                                <svg viewBox="0 0 100 100" class="w-full h-full text-gold-400 fill-current">
                                    <circle cx="50" cy="50" r="45" fill="#0b1329" stroke="#d97706" stroke-width="3"/>
                                    <path d="M50 15 L53 25 L63 25 L55 31 L58 41 L50 35 L42 41 L45 31 L37 25 L47 25 Z" fill="#fbbf24"/>
                                    <text x="50" y="58" font-family="Cinzel, serif" font-weight="900" font-size="20" fill="#f59e0b" text-anchor="middle">LEÓN</text>
                                    <text x="50" y="72" font-family="Cinzel, serif" font-weight="700" font-size="9" fill="#ffffff" text-anchor="middle" letter-spacing="1">GUANAJUATO</text>
                                    <text x="50" y="82" font-family="sans-serif" font-weight="800" font-size="7" fill="#fbbf24" text-anchor="middle" letter-spacing="2">MARIACHI</text>
                                </svg>
                            @endif
                        </div>
                        <div>
                            <span class="font-serif font-bold text-lg text-white block leading-none uppercase">{{ $empresaWeb->nombre_comercial ?? 'MARIACHI LEÓN' }}</span>
                            <span class="text-[10px] text-gold-400 font-bold tracking-widest uppercase block mt-1">{{ $empresaWeb->ciudad_pais ?? 'Guanajuato • Bolivia' }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed">
                        {{ $empresaWeb->slogan ?? 'Música mexicana tradicional y de gala para todo tipo de evento social, bodas, quinceañeras y corporativos.' }}
                    </p>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gold-400 uppercase tracking-widest mb-4">Navegación</h4>
                    <ul class="space-y-2 text-sm text-slate-400">
                        <li><a href="{{ route('web.home') }}" class="hover:text-white">Inicio</a></li>
                        <li><a href="{{ route('web.nosotros') }}" class="hover:text-white">Nosotros</a></li>
                        <li><a href="{{ route('web.servicios') }}" class="hover:text-white">Servicios & Paquetes</a></li>
                        <li><a href="{{ route('web.contacto') }}" class="hover:text-white">Solicitar Cotización</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gold-400 uppercase tracking-widest mb-4">Contacto Directo</h4>
                    <ul class="space-y-3 text-sm text-slate-400">
                        @if($empresaWeb->telefono_principal)
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-gold-500"></i>
                            <a href="{{ $empresaWeb->telefono_url }}" class="hover:text-gold-400 transition-colors">{{ $empresaWeb->telefono_principal }}</a>
                        </li>
                        @endif
                        @if($empresaWeb->whatsapp_numero_limpio)
                        <li class="flex items-center gap-3">
                            <i class="fa-brands fa-whatsapp text-emerald-400"></i>
                            <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" class="hover:text-emerald-400 transition-colors">WhatsApp: {{ $empresaWeb->whatsapp_comercial ?: $empresaWeb->telefono_principal }}</a>
                        </li>
                        @endif
                        @if($empresaWeb->email_contacto)
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-gold-500"></i>
                            <a href="mailto:{{ $empresaWeb->email_contacto }}" class="hover:text-gold-400 transition-colors break-all">{{ $empresaWeb->email_contacto }}</a>
                        </li>
                        @endif
                        @if($empresaWeb->direccion_fisica)
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-gold-500 mt-1"></i>
                            <span>{{ $empresaWeb->direccion_fisica }} @if($empresaWeb->ciudad_pais) ({{ $empresaWeb->ciudad_pais }}) @endif</span>
                        </li>
                        @endif
                        @if($empresaWeb->redes_linktree)
                        <li class="pt-2">
                            <a href="{{ $empresaWeb->redes_linktree }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-950/60 border border-emerald-500/30 text-emerald-300 hover:text-white hover:bg-emerald-900/60 text-xs font-semibold transition-all">
                                <i class="fa-solid fa-tree text-emerald-400"></i>
                                <span>Redes Sociales (Linktree)</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-sm text-gold-400 uppercase tracking-widest mb-4">Atención al Cliente</h4>
                    <p class="text-sm text-slate-400 mb-4">Disponibles los 365 días del año para reservas y cotizaciones personalizadas.</p>
                    <div class="space-y-3">
                        <a href="{{ route('web.contacto') }}" class="inline-flex items-center gap-2 text-sm font-bold text-gold-400 hover:text-gold-300">
                            <span>Solicitar cotización online</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                        @if($empresaWeb->whatsapp_numero_limpio)
                        <div>
                            <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-400 hover:text-emerald-300 bg-emerald-500/10 px-3 py-2 rounded-xl border border-emerald-500/20">
                                <i class="fa-brands fa-whatsapp text-sm"></i>
                                <span>Atención Inmediata por WhatsApp</span>
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="mt-12 pt-8 border-t border-slate-900 text-center text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} {{ $empresaWeb->nombre_comercial ?? 'Mariachi León Guanajuato' }}. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Botón Flotante de WhatsApp para Contacto Inmediato -->
    @if($empresaWeb->whatsapp_numero_limpio)
    <aside class="fixed bottom-6 right-6 z-50">
        <a href="{{ $empresaWeb->whatsapp_url }}" target="_blank" rel="noopener noreferrer" 
           class="group flex items-center gap-3 px-4 py-3 rounded-full bg-emerald-500 hover:bg-emerald-400 text-white shadow-2xl shadow-emerald-500/50 hover:scale-105 active:scale-95 transition-all duration-300 border border-emerald-300/40"
           title="Escríbenos directamente por WhatsApp">
            <i class="fa-brands fa-whatsapp text-2xl animate-pulse"></i>
            <div class="hidden sm:block text-left pr-1">
                <span class="block text-[10px] uppercase font-bold tracking-wider text-emerald-100 leading-none">¿Cotización rápida?</span>
                <span class="block text-xs font-extrabold text-white leading-tight mt-0.5">WhatsApp Directo</span>
            </div>
        </a>
    </aside>
    @endif

    @livewireScripts
</body>
</html>
