<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Livewire\Web\PaginaInicio;
use App\Livewire\Web\PaginaNosotros;
use App\Livewire\Web\PaginaServicios;
use App\Livewire\Web\PaginaGaleria;
use App\Livewire\Web\PaginaContacto;

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

use App\Livewire\Comercial\GestionClientes;
use App\Livewire\Comercial\GestionEventos;
use App\Livewire\Comercial\GestionAgenda;
use App\Livewire\Comercial\GestionCotizaciones;
use App\Livewire\Comercial\GestionContratos;
use App\Livewire\Comercial\GestionPagos;
use App\Livewire\Comercial\GestionParticipantesEvento;
use App\Http\Controllers\Comercial\ContratoPdfController;
use App\Http\Controllers\Comercial\ReciboPdfController;
use App\Http\Controllers\Comercial\VerificacionQrController;
use App\Livewire\Comercial\EstadoCuentaCliente;
use App\Http\Controllers\Comercial\EstadoCuentaPdfController;
use App\Http\Controllers\Comercial\EventoPdfController;
use App\Http\Controllers\Comercial\ClienteContratosPdfController;
use App\Http\Controllers\Comercial\EventoComisionesPdfController;
use App\Http\Controllers\Comercial\ClienteExcelController;

// SPRINT 3: Gestión Financiera y Reportes
use App\Livewire\Finanzas\DashboardFinanciero;
use App\Livewire\Finanzas\GestionMovimientos;
use App\Livewire\Finanzas\GestionFondoComun;
use App\Livewire\Reportes\ReporteEventosContratos;
use App\Livewire\Reportes\ReporteEstadosCuenta;
use App\Livewire\Reportes\ReporteDistribucionEconomica;
use App\Livewire\Reportes\ReporteFinanciero;
use App\Http\Controllers\Finanzas\FinanzasPdfController;
use App\Http\Controllers\Finanzas\FinanzasExcelController;
use App\Http\Controllers\Reportes\ReportesPdfController;

Route::get('/', PaginaInicio::class)->name('web.home');
Route::get('/nosotros', PaginaNosotros::class)->name('web.nosotros');
Route::get('/servicios', PaginaServicios::class)->name('web.servicios');
Route::get('/galeria', PaginaGaleria::class)->name('web.galeria');
Route::get('/contacto', PaginaContacto::class)->name('web.contacto');

// Portal Público de Verificación QR Anti-Fraude
Route::get('/verificar/recibo/{token}', [VerificacionQrController::class, 'verificarRecibo'])->name('public.verificar.recibo');
Route::get('/verificar/contrato/{token}', [VerificacionQrController::class, 'verificarContrato'])->name('public.verificar.contrato');
Route::get('/verificar/comisiones/{token}', [VerificacionQrController::class, 'verificarComisiones'])->name('public.verificar.comisiones');

// Descarga / Visualización de Comprobantes Oficiales PDF e Impresión
Route::get('/comercial/pagos/recibo/{token}', [ReciboPdfController::class, 'verRecibo'])->name('comercial.pagos.recibo');
Route::get('/comercial/pagos/pdf/{token}', [ReciboPdfController::class, 'exportarPdf'])->name('comercial.pagos.pdf');
Route::get('/comercial/contratos/pdf/{token}', [ContratoPdfController::class, 'exportarPdf'])->name('comercial.contratos.pdf');

// Fallback para servir storage cuando el hosting no sigue el symlink
Route::get('/storage/{path}', function (string $path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!is_file($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath);
})->where('path', '.*')->name('storage.local');

Route::get('/login', Login::class)->name('login')->middleware('guest');
Route::match(['get', 'post'], '/logout', function () {
    if (Auth::check()) {
        Auth::logout();
    }
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('web.home');
})->name('logout');

Route::middleware(['auth', 'session.timeout'])->prefix('admin')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('admin.dashboard');
    Route::get('/reporte-ejecutivo/pdf', [ReporteEjecutivoController::class, 'exportarPdf'])->name('admin.reporte-ejecutivo.pdf');

    Route::get('/usuarios', GestionUsuarios::class)->name('admin.usuarios');
    Route::get('/roles', GestionRoles::class)->name('admin.roles');
    Route::get('/auditoria', GestionAuditoria::class)->name('admin.auditoria');

    Route::get('/configuracion/empresa', GestionEmpresa::class)->name('config.empresa');
    Route::get('/configuracion/parametros', GestionParametros::class)->name('config.parametros');
    Route::get('/configuracion/tipos-evento', GestionTiposEvento::class)->name('config.tipos-evento');
    Route::get('/configuracion/servicios', GestionServicios::class)->name('config.servicios');
    Route::get('/configuracion/paquetes', GestionPaquetes::class)->name('config.paquetes');

    Route::prefix('sitio-web')->group(function () {
        Route::get('/banners', GestionBanners::class)->name('admin.sitio-web.banners');
        Route::get('/galeria', GestionGaleria::class)->name('admin.sitio-web.galeria');
    });

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
        Route::get('/kardex/pdf/{token}', [KardexPdfController::class, 'exportarPdf'])->name('admin.activos-fijos.kardex.pdf');
        Route::get('/inventario/pdf', [InventarioPdfController::class, 'exportarPdf'])->name('admin.activos-fijos.inventario.pdf');
        Route::get('/comprobante/{tipo}/{token}', ComprobanteActivo::class)->name('admin.activos-fijos.comprobante');
    });

    // SPRINT 2: Módulo Comercial
    Route::prefix('comercial')->group(function () {
        Route::get('/clientes', GestionClientes::class)->name('admin.clientes');
        Route::get('/clientes/exportar-excel', [ClienteExcelController::class, 'exportarExcel'])->name('admin.clientes.excel');
        Route::get('/clientes/{token}/estado-cuenta', EstadoCuentaCliente::class)->name('admin.clientes.estado-cuenta');
        Route::get('/clientes/{token}/estado-cuenta/pdf', [EstadoCuentaPdfController::class, 'exportarPdf'])->name('admin.clientes.estado-cuenta.pdf');
        Route::get('/clientes/{token}/historial-contratos/pdf', [ClienteContratosPdfController::class, 'exportarPdf'])->name('admin.clientes.historial-contratos.pdf');
        Route::get('/eventos', GestionEventos::class)->name('admin.eventos');
        Route::get('/eventos/pdf', [EventoPdfController::class, 'exportarPdf'])->name('admin.eventos.pdf');
        Route::get('/eventos/{evento}/participantes', GestionParticipantesEvento::class)->name('admin.eventos.participantes');
        Route::get('/eventos/{token}/comisiones/pdf', [EventoComisionesPdfController::class, 'exportarPdf'])->name('admin.eventos.comisiones.pdf');
        Route::get('/agenda', GestionAgenda::class)->name('admin.agenda');
        Route::get('/cotizaciones', GestionCotizaciones::class)->name('admin.cotizaciones');
        Route::get('/contratos', GestionContratos::class)->name('admin.contratos');
        Route::get('/contratos/pdf/{token}', [ContratoPdfController::class, 'exportarPdf'])->name('admin.contratos.pdf');
        Route::get('/pagos', GestionPagos::class)->name('admin.pagos');
    });

    // SPRINT 3: Módulo de Gestión Financiera
    Route::prefix('finanzas')->group(function () {
        Route::get('/dashboard', DashboardFinanciero::class)->name('admin.finanzas.dashboard');
        Route::get('/movimientos', GestionMovimientos::class)->name('admin.finanzas.movimientos');
        Route::get('/movimientos/exportar-excel', [FinanzasExcelController::class, 'exportarMovimientos'])->name('admin.finanzas.movimientos.excel');
        Route::get('/fondo-comun', GestionFondoComun::class)->name('admin.finanzas.fondo-comun');
    });

    // SPRINT 3: Módulo de Informes y Reportes
    Route::prefix('reportes')->group(function () {
        Route::get('/eventos-contratos', ReporteEventosContratos::class)->name('admin.reportes.eventos-contratos');
        Route::get('/eventos-contratos/pdf', [ReportesPdfController::class, 'exportarEventosContratos'])->name('admin.reportes.eventos-contratos.pdf');
        Route::get('/eventos-contratos/excel', [FinanzasExcelController::class, 'exportarEventosContratos'])->name('admin.reportes.eventos-contratos.excel');

        Route::get('/estados-cuenta', ReporteEstadosCuenta::class)->name('admin.reportes.estados-cuenta');

        Route::get('/distribucion-economica', ReporteDistribucionEconomica::class)->name('admin.reportes.distribucion');
        Route::get('/distribucion-economica/pdf', [ReportesPdfController::class, 'exportarDistribucion'])->name('admin.reportes.distribucion.pdf');
        Route::get('/distribucion-economica/excel', [FinanzasExcelController::class, 'exportarDistribucion'])->name('admin.reportes.distribucion.excel');

        Route::get('/financieros', ReporteFinanciero::class)->name('admin.reportes.financieros');
        Route::get('/financieros/pdf', [FinanzasPdfController::class, 'exportarReporteFinanciero'])->name('admin.reportes.financieros.pdf');
        Route::get('/financieros/excel', [FinanzasExcelController::class, 'exportarFinanciero'])->name('admin.reportes.financieros.excel');

        Route::get('/gastos/pdf', [FinanzasPdfController::class, 'exportarGastos'])->name('admin.reportes.gastos.pdf');
        Route::get('/fondo-comun/pdf', [FinanzasPdfController::class, 'exportarFondoComun'])->name('admin.reportes.fondo-comun.pdf');
    });
});

// Redirección directa para accesos incompletos a rutas de PDF sin token
Route::get('/comercial/pagos/recibo', fn() => redirect()->route('web.home'));
Route::get('/comercial/pagos/pdf', fn() => redirect()->route('web.home'));
Route::get('/comercial/contratos/pdf', fn() => redirect()->route('web.home'));
Route::get('/admin/comercial/contratos/pdf', fn() => redirect()->route('web.home'));
Route::get('/admin/comercial/eventos/comisiones/pdf', fn() => redirect()->route('web.home'));
Route::get('/verificar/comisiones', fn() => redirect()->route('web.home'));
Route::get('/admin/comercial/clientes/estado-cuenta', fn() => redirect()->route('web.home'));
Route::get('/admin/comercial/clientes/estado-cuenta/pdf', fn() => redirect()->route('web.home'));
Route::get('/admin/comercial/clientes/historial-contratos/pdf', fn() => redirect()->route('web.home'));
Route::get('/admin/activos-fijos/kardex/pdf', fn() => redirect()->route('web.home'));
Route::get('/admin/activos-fijos/comprobante', fn() => redirect()->route('web.home'));

// Redirección global de respaldo: cualquier ruta inválida o no registrada va al index principal
Route::fallback(function () {
    return redirect()->route('web.home');
});
