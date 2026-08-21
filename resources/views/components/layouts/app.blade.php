<!DOCTYPE html>
<html lang="es" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mariachi León Guanajuato - Sistema de Gestión' }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS (via CDN for fast, clean rendering without Node setup) -->
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
                        },
                        brand: {
                            dark: '#0b1329',
                            card: '#162238',
                            hover: '#1e2d4a',
                            border: '#2a3b5c'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .glass-panel {
            background: rgba(22, 34, 56, 0.85);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
    @livewireStyles
</head>
<body class="h-full bg-slate-950 text-slate-100 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Mobile Overlay -->
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"></div>

        <!-- Sidebar Navigation -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed md:static inset-y-0 left-0 z-50 w-64 bg-brand-dark border-r border-brand-border flex flex-col transition-transform duration-300 ease-in-out md:translate-x-0">
            <!-- Brand Logo -->
            @php $empresaGlobal = \App\Models\Empresa::obtener(); @endphp
            <div class="h-20 flex items-center justify-between px-6 border-b border-brand-border bg-slate-950/40">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-2xl bg-slate-950 border border-gold-500/40 p-1 flex items-center justify-center shadow-lg shadow-gold-500/20 overflow-hidden">
                        @if($empresaGlobal->logo_url)
                            <img src="{{ asset($empresaGlobal->logo_url) }}" alt="Logo" class="w-full h-full object-contain rounded-xl">
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
                        <h1 class="font-extrabold text-sm text-white leading-tight uppercase">{{ $empresaGlobal->nombre_comercial ?? 'MARIACHI LEÓN' }}</h1>
                        <p class="text-[10px] text-gold-400 font-bold tracking-wider uppercase">{{ $empresaGlobal->ciudad_pais ?? 'Guanajuato • Bolivia' }}</p>
                    </div>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Nav Links -->
            <nav class="flex-1 overflow-y-auto p-4 space-y-1.5 scrollbar-thin">
                <div class="px-3 pt-2 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest">General</div>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sprint 1: Base</div>
                <a href="{{ route('admin.usuarios') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.usuarios') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-users-gear w-5 text-center"></i>
                    <span>Usuarios</span>
                </a>
                <a href="{{ route('admin.roles') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.roles') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-user-shield w-5 text-center"></i>
                    <span>Roles & Permisos</span>
                </a>
                <a href="{{ route('admin.auditoria') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.auditoria') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-list-check w-5 text-center"></i>
                    <span>Auditoría de Logs</span>
                </a>
                <a href="{{ route('config.empresa') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('config.empresa') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-building-user w-5 text-center"></i>
                    <span>Datos de la Empresa</span>
                </a>
                <a href="{{ route('config.parametros') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('config.parametros') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-sliders w-5 text-center"></i>
                    <span>Parámetros</span>
                </a>
                <a href="{{ route('config.tipos-evento') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('config.tipos-evento') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-calendar-day w-5 text-center"></i>
                    <span>Tipos de Evento</span>
                </a>
                <a href="{{ route('config.servicios') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('config.servicios') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-music w-5 text-center"></i>
                    <span>Servicios & Paquetes</span>
                </a>
                <div class="px-3 pt-4 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Sitio Web Institucional</div>
                <a href="{{ route('admin.sitio-web.banners') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.sitio-web.banners') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-images w-5 text-center"></i>
                    <span>Gestión de Banner Inicio</span>
                </a>
                <a href="{{ route('admin.sitio-web.galeria') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.sitio-web.galeria') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-photo-film w-5 text-center"></i>
                    <span>Gestión de Galería</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Activos Fijos</div>
                <a href="{{ route('admin.activos-fijos.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.dashboard') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center"></i>
                    <span>Dashboard Activos</span>
                </a>
                <a href="{{ route('admin.activos-fijos.categorias') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.categorias') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-folder-tree w-5 text-center"></i>
                    <span>Categorías</span>
                </a>
                <a href="{{ route('admin.activos-fijos.articulos') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.articulos') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                    <span>Artículos & Productos</span>
                </a>
                <a href="{{ route('admin.activos-fijos.ingresos') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.ingresos') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-arrow-down-to-bracket w-5 text-center"></i>
                    <span>Ingresos</span>
                </a>
                <a href="{{ route('admin.activos-fijos.egresos') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.egresos') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-arrow-up-from-bracket w-5 text-center"></i>
                    <span>Egresos</span>
                </a>
                <a href="{{ route('admin.activos-fijos.asignaciones') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.asignaciones') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-user-check w-5 text-center"></i>
                    <span>Asignaciones</span>
                </a>
                <a href="{{ route('admin.activos-fijos.devoluciones') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.devoluciones') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-arrow-rotate-left w-5 text-center"></i>
                    <span>Devoluciones</span>
                </a>
                <a href="{{ route('admin.activos-fijos.bajas') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.bajas') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-trash-can w-5 text-center"></i>
                    <span>Bajas</span>
                </a>
                <a href="{{ route('admin.activos-fijos.kardex') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-sm transition-all {{ request()->routeIs('admin.activos-fijos.kardex') ? 'bg-gold-500 text-slate-950 font-semibold shadow-lg shadow-gold-500/20' : 'text-slate-300 hover:bg-brand-hover hover:text-white' }}">
                    <i class="fa-solid fa-chart-line w-5 text-center"></i>
                    <span>Kardex (PPP)</span>
                </a>

                <div class="px-3 pt-4 pb-1 text-[11px] font-bold text-slate-400 uppercase tracking-widest">Acceso Público</div>
                <a href="{{ route('web.home') }}" target="_blank" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl font-medium text-sm text-gold-400 hover:bg-brand-hover transition-all">
                    <span class="flex items-center gap-3">
                        <i class="fa-solid fa-globe w-5 text-center"></i>
                        <span>Ver Sitio Web</span>
                    </span>
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                </a>
            </nav>

            <!-- User Footer Profile -->
            <div class="p-4 border-t border-brand-border bg-slate-950/60 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-9 h-9 rounded-full bg-gold-500/20 border border-gold-500/30 flex items-center justify-center font-bold text-gold-400 flex-shrink-0">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div class="truncate">
                        <p class="text-sm font-semibold text-white truncate">{{ Auth::user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ Auth::user()->roles->pluck('name')->first() ?? 'Sin Rol' }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Cerrar Sesión" class="p-2 text-slate-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors">
                        <i class="fa-solid fa-power-off"></i>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content Container -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar Header -->
            <header class="h-20 bg-brand-dark/80 backdrop-blur-md border-b border-brand-border px-6 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="md:hidden text-slate-300 hover:text-white p-2 rounded-lg bg-brand-card border border-brand-border">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <h2 class="font-bold text-lg text-white hidden sm:block">Panel de Administración</h2>
                </div>

                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Sistema En Línea
                    </span>
                </div>
            </header>

            <!-- Notification Messages -->
            @if(session()->has('success'))
                <div class="mx-6 mt-6 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session()->has('error'))
                <div class="mx-6 mt-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-circle-exclamation text-rose-400 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <!-- Main Page Content Slot -->
            <main class="flex-1 p-6 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    <!-- Hidden Form for Auto Logout -->
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">
        @csrf
    </form>

    <!-- Modal de Advertencia de Inactividad (Aparece a los 4 minutos) -->
    <div x-data="{ 
            idleSeconds: 0, 
            warningOpen: false, 
            countdown: 60,
            timer: null,
            resetIdleTimer() {
                this.idleSeconds = 0;
                if (this.warningOpen) {
                    this.warningOpen = false;
                    this.countdown = 60;
                }
            },
            initTimer() {
                const events = ['mousemove', 'keydown', 'scroll', 'click', 'touchstart'];
                events.forEach(evt => window.addEventListener(evt, () => this.resetIdleTimer()));
                
                this.timer = setInterval(() => {
                    this.idleSeconds++;
                    // A los 240s (4 min), mostrar modal de advertencia
                    if (this.idleSeconds >= 240 && this.idleSeconds < 300) {
                        this.warningOpen = true;
                        this.countdown = 300 - this.idleSeconds;
                    } 
                    // A los 300s (5 min), cerrar sesión automáticamente
                    else if (this.idleSeconds >= 300) {
                        clearInterval(this.timer);
                        document.getElementById('logout-form').submit();
                    }
                }, 1000);
            }
         }" 
         x-init="initTimer()">
        
        <div x-show="warningOpen" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md">
            
            <div class="w-full max-w-md bg-slate-900 border border-gold-500/50 rounded-3xl p-6 text-center space-y-6 shadow-2xl">
                <div class="w-16 h-16 rounded-full bg-gold-500/10 border border-gold-500/30 text-gold-400 flex items-center justify-center mx-auto text-3xl animate-bounce">
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
                    <button @click="resetIdleTimer()" class="w-full py-3.5 rounded-xl font-bold text-sm bg-gradient-to-r from-gold-500 to-gold-600 text-slate-950 hover:from-gold-400 hover:to-gold-500 shadow-lg shadow-gold-500/25 transition-all">
                        <i class="fa-solid fa-user-check mr-2"></i> Mantener Sesión Activa
                    </button>
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
