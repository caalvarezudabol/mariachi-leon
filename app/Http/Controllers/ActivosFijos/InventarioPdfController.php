<?php

namespace App\Http\Controllers\ActivosFijos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Empresa;
use App\Models\Asset;
use App\Models\AssetCategory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class InventarioPdfController extends Controller
{
    public function exportarPdf()
    {
        $empresa = Empresa::obtener();

        // Conversión del logo a Base64
        $logoBase64 = null;
        if ($empresa->logo_url && Storage::disk('public')->exists(str_replace('storage/', '', $empresa->logo_url))) {
            $path = storage_path('app/public/' . str_replace('storage/', '', $empresa->logo_url));
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Carga de Artículos con su Categoría
        $articulos = Asset::with('category')->orderBy('nombre', 'asc')->get();
        $categorias = AssetCategory::orderBy('nombre', 'asc')->get();

        $stockTotalUnidades = 0;
        $valorTotalGeneral = 0;

        foreach ($articulos as $art) {
            $stockTotalUnidades += $art->stock_actual;
            $valorTotalGeneral += $art->valor_total_inventario;
        }

        // Resumen por Categoría usando el campo correcto `asset_category_id`
        $resumenCategorias = [];
        foreach ($categorias as $cat) {
            $articulosCat = $articulos->where('asset_category_id', $cat->id);
            if ($articulosCat->count() > 0) {
                $stockCat = $articulosCat->sum(function($a) { return $a->stock_actual; });
                $valorCat = $articulosCat->sum(function($a) { return $a->valor_total_inventario; });

                $resumenCategorias[] = [
                    'nombre' => $cat->nombre,
                    'cant_articulos' => $articulosCat->count(),
                    'stock_total' => $stockCat,
                    'valor_total' => $valorCat,
                ];
            }
        }

        // Artículos sin categoría asociada
        $articulosSinCat = $articulos->whereNull('asset_category_id');
        if ($articulosSinCat->count() > 0) {
            $resumenCategorias[] = [
                'nombre' => 'Sin Categoría / Varios',
                'cant_articulos' => $articulosSinCat->count(),
                'stock_total' => $articulosSinCat->sum(function($a) { return $a->stock_actual; }),
                'valor_total' => $articulosSinCat->sum(function($a) { return $a->valor_total_inventario; }),
            ];
        }

        $datos = [
            'empresa' => $empresa,
            'logoBase64' => $logoBase64,
            'articulos' => $articulos,
            'resumenCategorias' => $resumenCategorias,
            'stockTotalUnidades' => $stockTotalUnidades,
            'valorTotalGeneral' => $valorTotalGeneral,
            'usuarioEmision' => Auth::user()->name ?? 'Enrrique Escalera',
            'fechaEmision' => now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('pdf.reporte-inventario-valorado', $datos)
            ->setPaper('letter', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('isPhpEnabled', true)
            ->setOption('isFontSubsettingEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans');

        $filename = 'informe_inventario_valorado_mariachi_leon.pdf';

        return $pdf->download($filename);
    }
}
