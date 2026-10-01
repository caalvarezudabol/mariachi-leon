<?php

namespace App\Http\Controllers\ActivosFijos;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Empresa;
use App\Models\InventoryMovement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class KardexPdfController extends Controller
{
    public function exportarPdf($asset_id)
    {
        // 1. Obtener la Empresa Activa
        $empresa = Empresa::obtener();

        // 2. Obtener el Producto Seleccionado con su Categoría
        $asset = Asset::with('category')->findOrFail($asset_id);

        // 3. Obtener el Historial COMPLETO de Movimientos (Sin paginación)
        $movimientos = InventoryMovement::with(['user', 'responsable'])
            ->where('asset_id', $asset_id)
            ->orderBy('fecha_movimiento', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // 4. Calcular Resumen del Kardex
        $totalEntradas = (float)$movimientos->where('tipo_movimiento', 'entrada')->sum('cantidad');
        $totalSalidas = (float)$movimientos->where('tipo_movimiento', 'salida')->sum('cantidad');

        $ultimoMovimiento = $movimientos->last();
        
        if ($ultimoMovimiento) {
            $saldoActual = (float)$ultimoMovimiento->cantidad_saldo;
            $pppActual = (float)$ultimoMovimiento->costo_ppp_saldo;
            $valorInventario = (float)$ultimoMovimiento->valor_total_saldo;
        } else {
            $saldoActual = (float)$asset->existencia;
            $pppActual = (float)$asset->costo_promedio_ppp;
            $valorInventario = $asset->tipo_control === 'cantidad' 
                ? ($saldoActual * $pppActual) 
                : (float)$asset->costo_adquisicion;
        }

        // 5. Cargar y optimizar Logo de Empresa para DomPDF (Base64 ultraligero < 20KB)
        $logoBase64 = $empresa->obtenerLogoBase64();

        // 6. Preparar Datos para la Vista PDF
        $datos = [
            'empresa' => $empresa,
            'asset' => $asset,
            'movimientos' => $movimientos,
            'totalEntradas' => $totalEntradas,
            'totalSalidas' => $totalSalidas,
            'saldoActual' => $saldoActual,
            'pppActual' => $pppActual,
            'valorInventario' => $valorInventario,
            'logoBase64' => $logoBase64,
            'usuarioEmision' => Auth::user()->name ?? 'Enrrique Escalera',
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ];

        // 7. Generar PDF Ultrarrápido en Tamaño Carta y Márgenes de 2 cm
        $pdf = Pdf::loadView('pdf.kardex-producto', $datos)
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'kardex_' . Str::slug($asset->codigo) . '_' . Str::slug($asset->nombre) . '.pdf';

        return $pdf->download($filename);
    }
}
