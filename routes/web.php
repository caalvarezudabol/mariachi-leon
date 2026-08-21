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
use App\Livewire\Configuracion\GestionEmpresa;
use App\Livewire\Configuracion\GestionParametros;
use App\Livewire\Configuracion\GestionTiposEvento;
use App\Livewire\Configuracion\GestionServicios;
use App\Livewire\Configuracion\GestionPaquetes;

// Livewire Components - Módulo de Activos Fijos
use App\Livewire\ActivosFijos\DashboardActivos;
use App\Livewire\ActivosFijos\GestionCategoriasActivos;
use App\Livewire\ActivosFijos\GestionArticulosActivos;
use App\Livewire\ActivosFijos\GestionIngresosActivos;
use App\Livewire\ActivosFijos\GestionEgresosActivos;
use App\Livewire\ActivosFijos\GestionAsignacionesActivos;
use App\Livewire\ActivosFijos\GestionDevolucionesActivos;
use App\Livewire\ActivosFijos\GestionBajasActivos;
use App\Livewire\ActivosFijos\GestionKardexActivos;
use App\Livewire\ActivosFijos\ComprobanteActivo;
use App\Http\Controllers\ActivosFijos\KardexPdfController;
use App\Http\Controllers\ActivosFijos\InventarioPdfController;
use App\Http\Controllers\ReporteEjecutivoController;

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
Route::get('/login', Login::class)->name('login')->middleware('guest');
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
    Route::get('/reporte-ejecutivo/pdf', [ReporteEjecutivoController::class, 'exportarPdf'])->name('admin.reporte-ejecutivo.pdf');

    // Módulo 1: Administración
    Route::get('/usuarios', GestionUsuarios::class)->name('admin.usuarios');
    Route::get('/roles', GestionRoles::class)->name('admin.roles');
    Route::get('/auditoria', GestionAuditoria::class)->name('admin.auditoria');

    // Módulo 2: Configuración
    Route::get('/configuracion/empresa', GestionEmpresa::class)->name('config.empresa');
    Route::get('/configuracion/parametros', GestionParametros::class)->name('config.parametros');
    Route::get('/configuracion/tipos-evento', GestionTiposEvento::class)->name('config.tipos-evento');
    Route::get('/configuracion/servicios', GestionServicios::class)->name('config.servicios');
    Route::get('/configuracion/paquetes', GestionPaquetes::class)->name('config.paquetes');

    // Módulo 3: Sitio Web Institucional (Gestión de Banners & Galería)
    Route::prefix('sitio-web')->group(function () {
        Route::get('/banners', GestionBanners::class)->name('admin.sitio-web.banners');
        Route::get('/galeria', GestionGaleria::class)->name('admin.sitio-web.galeria');
    });

    // Módulo 4: ACTIVOS FIJOS (Gestión de Bienes, Inventario & Kardex)
    Route::prefix('activos-fijos')->group(function () {
        Route::get('/dashboard', DashboardActivos::class)->name('admin.activos-fijos.dashboard');
        Route::get('/categorias', GestionCategoriasActivos::class)->name('admin.activos-fijos.categorias');
        Route::get('/articulos', GestionArticulosActivos::class)->name('admin.activos-fijos.articulos');
        Route::get('/ingresos', GestionIngresosActivos::class)->name('admin.activos-fijos.ingresos');
        Route::get('/egresos', GestionEgresosActivos::class)->name('admin.activos-fijos.egresos');
        Route::get('/asignaciones', GestionAsignacionesActivos::class)->name('admin.activos-fijos.asignaciones');
        Route::get('/devoluciones', GestionDevolucionesActivos::class)->name('admin.activos-fijos.devoluciones');
        Route::get('/bajas', GestionBajasActivos::class)->name('admin.activos-fijos.bajas');
        Route::get('/kardex', GestionKardexActivos::class)->name('admin.activos-fijos.kardex');
        Route::get('/kardex/pdf/{asset_id}', [KardexPdfController::class, 'exportarPdf'])->name('admin.activos-fijos.kardex.pdf');
        Route::get('/inventario/pdf', [InventarioPdfController::class, 'exportarPdf'])->name('admin.activos-fijos.inventario.pdf');
        Route::get('/comprobante/{tipo}/{id}', ComprobanteActivo::class)->name('admin.activos-fijos.comprobante');
    });
});
