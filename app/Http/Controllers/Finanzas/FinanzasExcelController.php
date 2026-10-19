<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Finanzas\FinanzasExcelService;
use App\Traits\Auditable;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class FinanzasExcelController extends Controller
{
    use Auditable;

    public function exportarMovimientos(Request $request, FinanzasExcelService $service): StreamedResponse
    {
        $filtros = $request->only(['desde', 'hasta', 'tipo', 'clasificacion', 'estado']);
        $spreadsheet = $service->generarMovimientos($filtros);
        $nombreArchivo = 'movimientos_gastos_' . date('Ymd_His') . '.xlsx';

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Descargar Excel Movimientos y Gastos',
            "Descarga del reporte Excel de movimientos financieros y gastos ({$nombreArchivo})."
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportarEventosContratos(Request $request, FinanzasExcelService $service): StreamedResponse
    {
        $filtros = $request->only(['desde', 'hasta', 'campo_fecha', 'cliente_id', 'servicio_id', 'estado_contrato', 'estado_evento', 'search_lugar']);
        $spreadsheet = $service->generarEventosContratos($filtros);
        $nombreArchivo = 'eventos_contratos_' . date('Ymd_His') . '.xlsx';

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Descargar Excel Eventos y Contratos',
            "Descarga del reporte Excel de eventos y contratos ({$nombreArchivo})."
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportarDistribucion(Request $request, FinanzasExcelService $service): StreamedResponse
    {
        $filtros = $request->only(['modalidad', 'evento_id', 'persona_id', 'desde', 'hasta']);
        $spreadsheet = $service->generarDistribucion($filtros);
        $nombreArchivo = 'distribucion_economica_' . date('Ymd_His') . '.xlsx';

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Descargar Excel Distribución Económica',
            "Descarga del reporte Excel de distribución económica a integrantes ({$nombreArchivo})."
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function exportarFinanciero(Request $request, FinanzasExcelService $service): StreamedResponse
    {
        $filtros = [
            'fecha_inicio' => $request->get('inicio'),
            'fecha_fin' => $request->get('fin'),
            'cliente_id' => $request->get('cliente_id'),
            'evento_id' => $request->get('evento_id'),
            'servicio_id' => $request->get('servicio_id'),
        ];

        $spreadsheet = $service->generarFinanciero($filtros);
        $nombreArchivo = 'informe_financiero_ejecutivo_' . date('Ymd_His') . '.xlsx';

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Descargar Excel Reporte Financiero',
            "Descarga del informe financiero consolidado en Excel ({$nombreArchivo})."
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
