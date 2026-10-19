<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use App\Traits\Auditable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class ReportesPdfController extends Controller
{
    use Auditable;

    public function exportarEventosContratos(Request $request)
    {
        $fechaDesde = $request->get('desde') ?: null;
        $fechaHasta = $request->get('hasta') ?: null;
        $campoFecha = $request->get('campo_fecha', 'fecha_evento');
        $clienteId = $request->get('cliente_id') ?: null;
        $servicioId = $request->get('servicio_id') ?: null;
        $estadoContrato = $request->get('estado_contrato') ?: null;
        $estadoEvento = $request->get('estado_evento') ?: null;
        $searchLugar = $request->get('search_lugar') ?: null;

        $query = Evento::with(['cliente', 'servicio', 'contrato.pagos']);

        if ($clienteId) $query->where('cliente_id', $clienteId);
        if ($servicioId) $query->where('servicio_id', $servicioId);
        if ($estadoEvento) $query->where('estado', $estadoEvento);
        if ($searchLugar) $query->where('direccion_evento', 'like', '%' . $searchLugar . '%');

        if ($estadoContrato) {
            if ($estadoContrato === 'sin_contrato') {
                $query->doesntHave('contrato');
            } else {
                $query->whereHas('contrato', fn($ct) => $ct->where('estado', $estadoContrato));
            }
        }

        if ($campoFecha === 'fecha_evento') {
            if ($fechaDesde) $query->whereDate('fecha_evento', '>=', $fechaDesde);
            if ($fechaHasta) $query->whereDate('fecha_evento', '<=', $fechaHasta);
        } else {
            if ($fechaDesde) $query->whereHas('contrato', fn($ct) => $ct->whereDate('fecha_contrato', '>=', $fechaDesde));
            if ($fechaHasta) $query->whereHas('contrato', fn($ct) => $ct->whereDate('fecha_contrato', '<=', $fechaHasta));
        }

        $eventos = $query->orderBy('fecha_evento', 'desc')->get();

        $resumen = [
            'total_registros' => $eventos->count(),
            'total_contratado' => (float)$eventos->sum('monto_contratado'),
            'total_cobrado' => (float)$eventos->sum('total_abonado'),
            'total_saldo_pendiente' => (float)$eventos->sum('saldo_pendiente'),
        ];

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioGenerador = Auth::check() ? Auth::user()->name : 'Sistema';

        $clienteObj = $clienteId ? Cliente::find($clienteId) : null;
        $servicioObj = $servicioId ? Servicio::find($servicioId) : null;

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Generar PDF Eventos y Contratos',
            "Se exportó el reporte de eventos y contratos en PDF ({$eventos->count()} registros). Total contratado: Bs. " . number_format($resumen['total_contratado'], 2) . "."
        );

        $pdf = Pdf::loadView('pdf.reporte-eventos-contratos', [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'eventos' => $eventos,
            'resumen' => $resumen,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'campoFecha' => $campoFecha,
            'cliente' => $clienteObj,
            'servicio' => $servicioObj,
            'estadoContrato' => $estadoContrato,
            'estadoEvento' => $estadoEvento,
            'fechaEmision' => $fechaEmision,
            'usuarioGenerador' => $usuarioGenerador,
        ])
        ->setPaper('letter', 'landscape')
        ->setOption('isRemoteEnabled', false)
        ->setOption('isPhpEnabled', true)
        ->setOption('isFontSubsettingEnabled', true)
        ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('reporte_eventos_contratos_' . date('Ymd_His') . '.pdf');
    }

    public function exportarDistribucion(Request $request)
    {
        $modalidad = $request->get('modalidad', 'consolidado');
        $eventoId = $request->get('evento_id') ?: null;
        $personaId = $request->get('persona_id') ?: null;
        $fechaDesde = $request->get('desde') ?: null;
        $fechaHasta = $request->get('hasta') ?: null;

        $empresa = Empresa::obtener();
        $logoBase64 = $empresa->obtenerLogoBase64();
        $fechaEmision = now()->format('d/m/Y H:i:s');
        $usuarioGenerador = Auth::check() ? Auth::user()->name : 'Sistema';

        $datos = [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'modalidad' => $modalidad,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'fechaEmision' => $fechaEmision,
            'usuarioGenerador' => $usuarioGenerador,
        ];

        if ($modalidad === 'por_evento') {
            $evento = Evento::with(['cliente', 'servicio', 'participantes.persona', 'participantes.pagos'])->findOrFail($eventoId);
            $participantes = $evento->participantes;
            $totalAsignado = (float)$participantes->sum('monto_asignado');
            $totalPagado = (float)$participantes->sum('total_pagado');
            $saldoPendiente = max(0.0, $totalAsignado - $totalPagado);

            $datos['evento'] = $evento;
            $datos['participantes'] = $participantes;
            $datos['resumen'] = [
                'total_asignado' => $totalAsignado,
                'total_pagado' => $totalPagado,
                'saldo_pendiente' => $saldoPendiente,
            ];

            $this->registrarAuditoria(
                'Informes y Reportes',
                'Generar PDF Distribución por Evento',
                "Se exportó la planilla de distribución económica para el evento {$evento->codigo_evento}."
            );

        } elseif ($modalidad === 'por_participante') {
            $persona = MusicoPersonal::findOrFail($personaId);
            $query = EventoParticipante::with(['evento.cliente', 'pagos'])->where('persona_id', $personaId);
            if ($fechaDesde) $query->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '>=', $fechaDesde));
            if ($fechaHasta) $query->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '<=', $fechaHasta));
            $participaciones = $query->get()->sortByDesc(fn($p) => $p->evento->fecha_evento ?? now())->values();

            $totalAsignado = (float)$participaciones->sum('monto_asignado');
            $totalPagado = (float)$participaciones->sum('total_pagado');
            $saldoPendiente = max(0.0, $totalAsignado - $totalPagado);

            $datos['persona'] = $persona;
            $datos['participaciones'] = $participaciones;
            $datos['resumen'] = [
                'total_eventos' => $participaciones->count(),
                'total_asignado' => $totalAsignado,
                'total_pagado' => $totalPagado,
                'saldo_pendiente' => $saldoPendiente,
            ];

            $this->registrarAuditoria(
                'Informes y Reportes',
                'Generar PDF Distribución por Integrante',
                "Se exportó el historial de liquidaciones de comisiones para el integrante {$persona->nombre_completo}."
            );

        } else {
            // Consolidado
            $musicos = MusicoPersonal::with(['participaciones' => function ($q) use ($fechaDesde, $fechaHasta) {
                if ($fechaDesde) $q->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '>=', $fechaDesde));
                if ($fechaHasta) $q->whereHas('evento', fn($e) => $e->whereDate('fecha_evento', '<=', $fechaHasta));
                $q->with('pagos');
            }])->orderBy('nombre_completo')->get();

            $filas = collect();
            $granAsignado = 0.0;
            $granPagado = 0.0;
            $granSaldo = 0.0;

            foreach ($musicos as $m) {
                $cant = $m->participaciones->count();
                $asig = (float)$m->participaciones->sum('monto_asignado');
                $pag = (float)$m->participaciones->sum('total_pagado');
                $saldo = max(0.0, $asig - $pag);

                $granAsignado += $asig;
                $granPagado += $pag;
                $granSaldo += $saldo;

                $filas->push([
                    'nombre_completo' => $m->nombre_completo,
                    'tipo' => $m->tipo,
                    'total_eventos' => $cant,
                    'total_asignado' => $asig,
                    'total_pagado' => $pag,
                    'saldo_pendiente' => $saldo,
                ]);
            }

            $datos['filas'] = $filas;
            $datos['resumen'] = [
                'total_musicos' => $musicos->count(),
                'total_asignado' => $granAsignado,
                'total_pagado' => $granPagado,
                'saldo_pendiente' => $granSaldo,
            ];

            $this->registrarAuditoria(
                'Informes y Reportes',
                'Generar PDF Distribución Consolidada',
                "Se exportó el consolidado de distribución económica en PDF ({$musicos->count()} integrantes)."
            );
        }

        $pdf = Pdf::loadView('pdf.reporte-distribucion-economica', $datos)
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        return $pdf->stream('distribucion_economica_' . date('Ymd_His') . '.pdf');
    }
}
