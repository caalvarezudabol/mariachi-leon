<?php

namespace App\Http\Controllers;

use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\MovimientoFinanciero;
use App\Models\Pago;
use App\Models\PagoParticipante;
use App\Support\Caja;
use App\Traits\Auditable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

/**
 * "Resumen del mes": una hoja con ingresos, egresos, caja y pendientes.
 * (Mantiene el nombre y la ruta admin.reporte-ejecutivo.pdf.)
 */
class ReporteEjecutivoController extends Controller
{
    use Auditable;

    public function exportarPdf()
    {
        $empresa = Empresa::obtener();
        $inicio = now()->startOfMonth();
        $fin = now()->endOfMonth();

        $cobros = (float) Pago::where('estado', 'Valido')
            ->whereBetween('fecha_pago', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->sum('monto');

        $movimientos = fn($tipo) => MovimientoFinanciero::where('estado', 'Valido')
            ->where('tipo', $tipo)
            ->whereBetween('fecha', [$inicio->toDateString(), $fin->toDateString()]);

        $ingresosExtra = (float) $movimientos('INGRESO')->sum('monto');
        $gastos = (float) $movimientos('EGRESO')->sum('monto');

        $personal = (float) PagoParticipante::where('estado', 'Valido')
            ->whereBetween('fecha_pago', [$inicio->toDateTimeString(), $fin->toDateTimeString()])
            ->sum('monto');

        $gastosPorCategoria = $movimientos('EGRESO')
            ->selectRaw('categoria, SUM(monto) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $vigentes = fn($q) => $q->whereDoesntHave('evento', fn($e) => $e->where('estado', 'Cancelado'));
        $porCobrar = (float) Contrato::where('saldo_pendiente', '>', 0)->where($vigentes)->sum('saldo_pendiente');

        $proximos = Evento::with('cliente')
            ->whereDate('fecha_evento', '>=', now()->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->orderBy('fecha_evento')->orderBy('hora_evento')
            ->take(5)->get();

        $caja = Caja::saldos();

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Generar Resumen del mes PDF',
            'Resumen del mes generado por ' . (Auth::check() ? Auth::user()->name : 'Sistema')
        );

        $pdf = Pdf::loadView('pdf.resumen-mensual', [
            'empresa' => $empresa,
            'logoBase64' => $empresa->obtenerLogoBase64(),
            'mes' => ucfirst($inicio->locale('es')->translatedFormat('F Y')),
            'cobros' => $cobros,
            'ingresosExtra' => $ingresosExtra,
            'gastos' => $gastos,
            'personal' => $personal,
            'gastosPorCategoria' => $gastosPorCategoria,
            'porCobrar' => $porCobrar,
            'deudaPersonal' => Caja::deudaPersonal(),
            'caja' => $caja,
            'fondo' => array_sum(Caja::fondoPorMetodo()),
            'proximos' => $proximos,
            'fechaEmision' => now()->format('d/m/Y H:i'),
            'usuarioGenerador' => Auth::check() ? Auth::user()->name : 'Sistema',
        ])
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Serif');

        return $pdf->download('resumen_' . now()->format('Y_m') . '.pdf');
    }
}