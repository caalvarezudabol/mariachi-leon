<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Traits\Auditable;
use App\Services\Security\DocumentToken;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class EventoPdfController extends Controller
{
    use Auditable;

    public function exportarPdf(Request $request)
    {
        $search = $request->get('search');
        $clienteFiltro = $request->get('cliente_id');
        $estadoFiltro = $request->get('estado');

        // Si se recibe un token cifrado para cliente_id, decodificarlo
        if ($clienteFiltro) {
            $decoded = DocumentToken::decodeId($clienteFiltro, 'cliente');
            if ($decoded) {
                $clienteFiltro = $decoded;
            }
        }

        $query = Evento::with(['cliente', 'servicio', 'user', 'contrato.pagos', 'pagos']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('codigo_evento', 'like', '%' . $search . '%')
                  ->orWhere('contacto_evento', 'like', '%' . $search . '%')
                  ->orWhere('direccion_evento', 'like', '%' . $search . '%')
                  ->orWhereHas('cliente', function($c) use ($search) {
                      $c->where('nombre_completo', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('servicio', function($s) use ($search) {
                      $s->where('nombre', 'like', '%' . $search . '%');
                  });
            });
        }

        $clienteSeleccionado = null;
        if ($clienteFiltro) {
            $query->where('cliente_id', $clienteFiltro);
            $clienteSeleccionado = Cliente::find($clienteFiltro);
        }

        if ($estadoFiltro) {
            $query->where('estado', $estadoFiltro);
        }

        $eventos = $query->orderBy('fecha_evento', 'desc')->orderBy('hora_evento', 'asc')->get();

        // Totales financieros
        $totalEventos = $eventos->count();
        $totalContratado = (float)$eventos->sum(fn($ev) => $ev->monto_contratado);
        $totalAbonado = (float)$eventos->sum(fn($ev) => $ev->total_abonado);
        $totalSaldo = (float)$eventos->sum(fn($ev) => $ev->saldo_pendiente);

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioEmision = Auth::check() ? Auth::user()->name : 'Sistema';

        // Registro en bitácora de auditoría
        $descFiltros = [];
        if ($clienteSeleccionado) $descFiltros[] = "Cliente: {$clienteSeleccionado->nombre_completo}";
        if ($estadoFiltro) $descFiltros[] = "Estado: {$estadoFiltro}";
        if ($search) $descFiltros[] = "Búsqueda: '{$search}'";
        $strFiltros = count($descFiltros) > 0 ? " [" . implode(', ', $descFiltros) . "]" : " [Todos]";

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Generar Reporte Eventos PDF',
            "Se generó en PDF el Reporte de Gestión de Eventos ({$totalEventos} eventos{$strFiltros}). Monto Contratado: Bs " . number_format($totalContratado, 2) . ", Cobrado: Bs " . number_format($totalAbonado, 2) . ", Saldo: Bs " . number_format($totalSaldo, 2)
        );

        $pdf = Pdf::loadView('pdf.reporte-eventos', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'eventos' => $eventos,
            'totalEventos' => $totalEventos,
            'totalContratado' => $totalContratado,
            'totalAbonado' => $totalAbonado,
            'totalSaldo' => $totalSaldo,
            'clienteSeleccionado' => $clienteSeleccionado,
            'estadoFiltro' => $estadoFiltro,
            'search' => $search,
            'fechaEmision' => $fechaEmision,
            'usuarioEmision' => $usuarioEmision,
        ])
        ->setPaper('letter', 'landscape')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'reporte_eventos_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
