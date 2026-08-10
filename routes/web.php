<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Livewire Components - Web Público
use App\Livewire\Web\PaginaInicio;
use App\Livewire\Web\PaginaNosotros;
use App\Livewire\Web\PaginaServicios;
use App\Livewire\Web\PaginaGaleria;
use App\Livewire\Web\PaginaContacto;

// Livewire Components - Auth & Admin
use App\Livewire\Auth\Login;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\GestionUsuarios;
use App\Livewire\Admin\GestionRoles;
use App\Livewire\Admin\GestionAuditoria;
use App\Livewire\Admin\GestionGaleria;
use App\Livewire\Admin\GestionBanners;
use App\Livewire\Configuracion\GestionParametros;
use App\Livewire\Configuracion\GestionTiposEvento;
use App\Livewire\Configuracion\GestionServicios;
use App\Livewire\Configuracion\GestionPaquetes;

/*
|--------------------------------------------------------------------------
| Rutas Públicas (Sitio Web Institucional)
|--------------------------------------------------------------------------
*/
Route::get('/', PaginaInicio::class)->name('web.home');
Route::get('/nosotros', PaginaNosotros::class)->name('web.nosotros');
Route::get('/servicios', PaginaServicios::class)->name('web.servicios');
Route::get('/galeria', PaginaGaleria::class)->name('web.galeria');
Route::get('/contacto', PaginaContacto::class)->name('web.contacto');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('web.home');
})->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Panel de Administración (Protegido por Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'session.timeout'])->prefix('admin')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');

    // Módulo 1: Administración
    Route::get('/usuarios', GestionUsuarios::class)->name('admin.usuarios');
    Route::get('/roles', GestionRoles::class)->name('admin.roles');
    Route::get('/auditoria', GestionAuditoria::class)->name('admin.auditoria');

    // Módulo 2: Configuración
    Route::get('/configuracion/parametros', GestionParametros::class)->name('config.parametros');
    Route::get('/configuracion/tipos-evento', GestionTiposEvento::class)->name('config.tipos-evento');
    Route::get('/configuracion/servicios', GestionServicios::class)->name('config.servicios');
    Route::get('/configuracion/paquetes', GestionPaquetes::class)->name('config.paquetes');

    // Módulo 3: Sitio Web Institucional (Gestión de Banners & Galería)
    Route::prefix('sitio-web')->group(function () {
        Route::get('/banners', GestionBanners::class)->name('admin.sitio-web.banners');
        Route::get('/galeria', GestionGaleria::class)->name('admin.sitio-web.galeria');
    });
});
