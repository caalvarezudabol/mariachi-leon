<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Comercial\ClienteExcelService;
use App\Traits\Auditable;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Auth;

class ClienteExcelController extends Controller
{
    use Auditable;

    public function exportarExcel(Request $request, ClienteExcelService $excelService): StreamedResponse
    {
        $estadoFiltro = $request->get('estado');
        $search = $request->get('search');

        // Mapear si viene 'todos' o vacío
        if ($estadoFiltro === 'todos' || $estadoFiltro === '' || $estadoFiltro === 'all') {
            $estadoFiltro = null;
        }

        $spreadsheet = $excelService->generar($estadoFiltro, $search);
        $nombreArchivo = $excelService->getNombreArchivo($estadoFiltro);
        $totalClientes = $excelService->getUltimoConteo();

        $descFiltro = $estadoFiltro ? "estado: {$estadoFiltro}" : "todos los estados (activos e inactivos)";
        if ($search) {
            $descFiltro .= ", búsqueda: '{$search}'";
        }

        $nombreUsuario = Auth::user()?->name ?? 'Usuario del Sistema';

        // Registro detallado en la Bitácora de Operaciones
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Descargar Reporte Excel Clientes',
            "El usuario {$nombreUsuario} descargó el reporte oficial en formato Excel del directorio de clientes ({$totalClientes} registros, {$descFiltro}). Archivo: {$nombreArchivo}"
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
