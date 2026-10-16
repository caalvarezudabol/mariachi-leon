<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Comercial\ClienteExcelService;
use App\Traits\Auditable;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $descFiltro = $estadoFiltro ? "estado: {$estadoFiltro}" : "todos los estados";
        if ($search) {
            $descFiltro .= " con búsqueda: '{$search}'";
        }
        $this->registrarAuditoria('Gestión Comercial', 'Exportar Clientes Excel', "Se exportó el listado de clientes en formato Excel ({$descFiltro}).");

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
