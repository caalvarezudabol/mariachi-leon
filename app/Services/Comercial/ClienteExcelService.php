<?php

namespace App\Services\Comercial;

use App\Models\Cliente;
use App\Models\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ClienteExcelService
{
    /**
     * Genera el archivo Excel profesional para los clientes filtrados.
     */
    public function generar(?string $estadoFiltro = null, ?string $search = null): Spreadsheet
    {
        $empresa = Empresa::obtener();
        $user = Auth::user();

        // 1. Consulta con relaciones y conteos
        $query = Cliente::withCount(['eventos', 'contratos']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'like', '%' . $search . '%')
                  ->orWhere('ci_nit', 'like', '%' . $search . '%')
                  ->orWhere('telefono', 'like', '%' . $search . '%')
                  ->orWhere('whatsapp', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if ($estadoFiltro && in_array($estadoFiltro, ['Activo', 'Inactivo'])) {
            $query->where('estado', $estadoFiltro);
        }

        $clientes = $query->orderBy('nombre_completo', 'asc')->get();

        // 2. Métricas y KPIs para las tarjetas de resumen
        $totalClientes = $clientes->count();
        $totalActivos = $clientes->where('estado', 'Activo')->count();
        $totalInactivos = $clientes->where('estado', 'Inactivo')->count();
        $totalConEventos = $clientes->where('eventos_count', '>', 0)->count();
        $totalSumaEventos = $clientes->sum('eventos_count');
        $totalSumaContratos = $clientes->sum('contratos_count');

        // Texto del filtro aplicado
        $textoFiltro = 'Todos los Estados (Activos e Inactivos)';
        if ($estadoFiltro === 'Activo') {
            $textoFiltro = 'Solo Clientes ACTIVOS';
        } elseif ($estadoFiltro === 'Inactivo') {
            $textoFiltro = 'Solo Clientes INACTIVOS';
        }

        // 3. Crear hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator($empresa->nombre_comercial ?? 'Mariachi León Guanajuato')
            ->setLastModifiedBy($user->name ?? 'Administración')
            ->setTitle('Reporte Oficial de Clientes')
            ->setSubject('Directorio de Clientes de Mariachi León Guanajuato')
            ->setDescription('Reporte exportado desde el Sistema de Gestión Comercial de Mariachi León Guanajuato')
            ->setKeywords('clientes mariachi leon guanajuato reporte excel comercial')
            ->setCategory('Reportes Comerciales');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Directorio de Clientes');

        // Configuración de visualización e impresión
        $sheet->setShowGridLines(true);
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LETTER);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        // Paleta de colores corporativos
        $colorNavyDark = '0F172A';
        $colorSlateHeader = '1E293B';
        $colorGold = 'D97706';
        $colorGoldLight = 'F59E0B';
        $colorZebra = 'F8FAFC';
        $colorBorder = 'CBD5E1';
        $colorBorderLight = 'E2E8F0';

        // ----------------------------------------------------
        // ENCABEZADO CORPORATIVO (Filas 1 a 4)
        // ----------------------------------------------------
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', mb_strtoupper($empresa->nombre_comercial ?? 'MARIACHI LEÓN GUANAJUATO', 'UTF-8'));
        $sheet->getStyle('A1')->getFont()->setName('Segoe UI')->setSize(16)->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorNavyDark);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(36);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'REPORTE OFICIAL DEL DIRECTORIO CENTRALIZADO DE CLIENTES');
        $sheet->getStyle('A2')->getFont()->setName('Segoe UI')->setSize(11)->setBold(true)->getColor()->setRGB($colorGoldLight);
        $sheet->getStyle('A2')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorSlateHeader);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', ($empresa->slogan ?: 'Puntualidad, elegancia y virtuosismo musical en cada presentación') . '  |  ' . ($empresa->ciudad_pais ?: 'Santa Cruz - Bolivia'));
        $sheet->getStyle('A3')->getFont()->setName('Segoe UI')->setSize(9.5)->setItalic(true)->getColor()->setRGB('94A3B8');
        $sheet->getStyle('A3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($colorSlateHeader);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(3)->setRowHeight(18);

        // Fila 4: Espacio separador
        $sheet->getRowDimension(4)->setRowHeight(8);

        // ----------------------------------------------------
        // BLOQUE DE METADATOS (Filas 5 y 6)
        // ----------------------------------------------------
        $sheet->setCellValue('A5', 'Fecha de Emisión:');
        $sheet->setCellValue('B5', Carbon::now()->format('d/m/Y H:i:s'));
        $sheet->setCellValue('D5', 'Generado Por:');
        $sheet->setCellValue('E5', $user->name ?? 'Usuario del Sistema');
        $sheet->setCellValue('G5', 'Filtro de Estado:');
        $sheet->setCellValue('H5', $textoFiltro);
        $sheet->setCellValue('J5', 'Criterio Búsqueda:');
        $sheet->setCellValue('K5', $search ? ('"' . $search . '"') : 'Todos los registros');

        $estiloMetaLabels = [
            'font' => ['name' => 'Segoe UI', 'size' => 9, 'bold' => true, 'color' => ['rgb' => '475569']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $estiloMetaValues = [
            'font' => ['name' => 'Segoe UI', 'size' => 9, 'bold' => true, 'color' => ['rgb' => '0F172A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        foreach (['A5', 'D5', 'G5', 'J5'] as $celda) {
            $sheet->getStyle($celda)->applyFromArray($estiloMetaLabels);
        }
        foreach (['B5', 'E5', 'H5', 'K5'] as $celda) {
            $sheet->getStyle($celda)->applyFromArray($estiloMetaValues);
        }
        // Resaltar el filtro de estado con color corporativo
        $sheet->getStyle('H5')->getFont()->getColor()->setRGB($colorGold);
        $sheet->getRowDimension(5)->setRowHeight(20);

        // Fila 6: Separador
        $sheet->getRowDimension(6)->setRowHeight(8);

        // ----------------------------------------------------
        // TARJETAS KPI / RESUMEN EJECUTIVO (Filas 7 y 8)
        // ----------------------------------------------------
        // Card 1: Total Clientes (A7:B8)
        $sheet->mergeCells('A7:B7');
        $sheet->setCellValue('A7', 'TOTAL CLIENTES');
        $sheet->mergeCells('A8:B8');
        $sheet->setCellValue('A8', $totalClientes);

        // Card 2: Clientes Activos (C7:D7, C8:D8)
        $sheet->mergeCells('C7:D7');
        $sheet->setCellValue('C7', 'CLIENTES ACTIVOS');
        $sheet->mergeCells('C8:D8');
        $sheet->setCellValue('C8', $totalActivos);

        // Card 3: Clientes Inactivos (E7:F7, E8:F8)
        $sheet->mergeCells('E7:F7');
        $sheet->setCellValue('E7', 'CLIENTES INACTIVOS');
        $sheet->mergeCells('E8:F8');
        $sheet->setCellValue('E8', $totalInactivos);

        // Card 4: Clientes con Eventos (G7:H7, G8:H8)
        $sheet->mergeCells('G7:H7');
        $sheet->setCellValue('G7', 'CON EVENTOS');
        $sheet->mergeCells('G8:H8');
        $sheet->setCellValue('G8', $totalConEventos);

        // Card 5: Total Eventos Contratados (I7:J7, I8:J8)
        $sheet->mergeCells('I7:J7');
        $sheet->setCellValue('I7', 'TOTAL EVENTOS HISTÓRICOS');
        $sheet->mergeCells('I8:J8');
        $sheet->setCellValue('I8', $totalSumaEventos);

        // Card 6: Total Contratos Formalizados (K7:L7, K8:L8)
        $sheet->mergeCells('K7:L7');
        $sheet->setCellValue('K7', 'TOTAL CONTRATOS');
        $sheet->mergeCells('K8:L8');
        $sheet->setCellValue('K8', $totalSumaContratos);

        $sheet->getRowDimension(7)->setRowHeight(16);
        $sheet->getRowDimension(8)->setRowHeight(24);

        // Estilos de las tarjetas KPI
        $kpiConfigs = [
            ['rangeTitle' => 'A7:B7', 'rangeVal' => 'A8:B8', 'boxRange' => 'A7:B8', 'bg' => 'F1F5F9', 'border' => 'CBD5E1', 'color' => '0F172A'],
            ['rangeTitle' => 'C7:D7', 'rangeVal' => 'C8:D8', 'boxRange' => 'C7:D8', 'bg' => 'ECFDF5', 'border' => 'A7F3D0', 'color' => '047857'],
            ['rangeTitle' => 'E7:F7', 'rangeVal' => 'E8:F8', 'boxRange' => 'E7:F8', 'bg' => 'FEF2F2', 'border' => 'FECACA', 'color' => 'B91C1C'],
            ['rangeTitle' => 'G7:H7', 'rangeVal' => 'G8:H8', 'boxRange' => 'G7:H8', 'bg' => 'FFFBEB', 'border' => 'FDE68A', 'color' => 'B45309'],
            ['rangeTitle' => 'I7:J7', 'rangeVal' => 'I8:J8', 'boxRange' => 'I7:J8', 'bg' => 'EFF6FF', 'border' => 'BFDBFE', 'color' => '1D4ED8'],
            ['rangeTitle' => 'K7:L7', 'rangeVal' => 'K8:L8', 'boxRange' => 'K7:L8', 'bg' => 'F5F3FF', 'border' => 'DDD6FE', 'color' => '6D28D9'],
        ];

        foreach ($kpiConfigs as $kpi) {
            $sheet->getStyle($kpi['rangeTitle'])->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 8, 'bold' => true, 'color' => ['rgb' => '64748B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $kpi['bg']]],
            ]);
            $sheet->getStyle($kpi['rangeVal'])->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 14, 'bold' => true, 'color' => ['rgb' => $kpi['color']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $kpi['bg']]],
            ]);
            $sheet->getStyle($kpi['boxRange'])->applyFromArray([
                'borders' => [
                    'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $kpi['border']]],
                ],
            ]);
        }

        // Fila 9: Separador
        $sheet->getRowDimension(9)->setRowHeight(10);

        // ----------------------------------------------------
        // CABECERA DE LA TABLA (Fila 10)
        // ----------------------------------------------------
        $columnas = [
            'A' => ['titulo' => 'N°', 'ancho' => 6, 'align' => Alignment::HORIZONTAL_CENTER],
            'B' => ['titulo' => 'CLIENTE / RAZÓN SOCIAL', 'ancho' => 32, 'align' => Alignment::HORIZONTAL_LEFT],
            'C' => ['titulo' => 'C.I. / NIT', 'ancho' => 15, 'align' => Alignment::HORIZONTAL_CENTER],
            'D' => ['titulo' => 'TELÉFONO', 'ancho' => 16, 'align' => Alignment::HORIZONTAL_CENTER],
            'E' => ['titulo' => 'WHATSAPP', 'ancho' => 16, 'align' => Alignment::HORIZONTAL_CENTER],
            'F' => ['titulo' => 'CORREO ELECTRÓNICO', 'ancho' => 28, 'align' => Alignment::HORIZONTAL_LEFT],
            'G' => ['titulo' => 'DIRECCIÓN', 'ancho' => 36, 'align' => Alignment::HORIZONTAL_LEFT],
            'H' => ['titulo' => 'EVENTOS', 'ancho' => 12, 'align' => Alignment::HORIZONTAL_CENTER],
            'I' => ['titulo' => 'CONTRATOS', 'ancho' => 13, 'align' => Alignment::HORIZONTAL_CENTER],
            'J' => ['titulo' => 'ESTADO', 'ancho' => 14, 'align' => Alignment::HORIZONTAL_CENTER],
            'K' => ['titulo' => 'FECHA REGISTRO', 'ancho' => 17, 'align' => Alignment::HORIZONTAL_CENTER],
            'L' => ['titulo' => 'OBSERVACIONES', 'ancho' => 30, 'align' => Alignment::HORIZONTAL_LEFT],
        ];

        foreach ($columnas as $col => $config) {
            $sheet->setCellValue($col . '10', $config['titulo']);
            $sheet->getColumnDimension($col)->setWidth($config['ancho']);
        }

        $sheet->getRowDimension(10)->setRowHeight(28);
        $sheet->getStyle('A10:L10')->applyFromArray([
            'font' => ['name' => 'Segoe UI', 'size' => 10, 'bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $colorSlateHeader]],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $colorGoldLight]],
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '334155']],
            ],
        ]);

        // ----------------------------------------------------
        // FILAS DE DATOS (Fila 11 en adelante)
        // ----------------------------------------------------
        $fila = 11;
        $num = 1;

        foreach ($clientes as $cliente) {
            $sheet->setCellValue('A' . $fila, $num);
            $sheet->setCellValue('B' . $fila, $cliente->nombre_completo);
            $sheet->setCellValue('C' . $fila, $cliente->ci_nit ?: '-');
            $sheet->setCellValue('D' . $fila, $cliente->telefono ?: '-');
            $sheet->setCellValue('E' . $fila, $cliente->whatsapp ?: '-');
            $sheet->setCellValue('F' . $fila, $cliente->email ?: '-');
            $sheet->setCellValue('G' . $fila, $cliente->direccion ?: '-');
            $sheet->setCellValue('H' . $fila, $cliente->eventos_count);
            $sheet->setCellValue('I' . $fila, $cliente->contratos_count);
            $sheet->setCellValue('J' . $fila, mb_strtoupper($cliente->estado, 'UTF-8'));
            $sheet->setCellValue('K' . $fila, $cliente->created_at ? $cliente->created_at->format('d/m/Y H:i') : '-');
            $sheet->setCellValue('L' . $fila, $cliente->observaciones ?: '-');

            $sheet->getRowDimension($fila)->setRowHeight(22);

            // Fondo alternado (Zebra)
            $bgFila = ($num % 2 === 0) ? $colorZebra : 'FFFFFF';
            $sheet->getStyle('A' . $fila . ':L' . $fila)->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 9.5],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgFila]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorderLight]],
                ],
            ]);

            // Alineaciones específicas
            $sheet->getStyle('A' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $fila)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

            $sheet->getStyle('B' . $fila)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($colorNavyDark));

            $sheet->getStyle('C' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // WhatsApp con toque verde
            $sheet->getStyle('E' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            if ($cliente->whatsapp) {
                $sheet->getStyle('E' . $fila)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('059669'));
            }

            // Correo
            if ($cliente->email) {
                $sheet->getStyle('F' . $fila)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('2563EB'));
            }

            $sheet->getStyle('H' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $fila)->getFont()->setBold(true);

            $sheet->getStyle('I' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $fila)->getFont()->setBold(true);

            // Badge de Estado
            $sheet->getStyle('J' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $fila)->getFont()->setBold(true);
            if ($cliente->estado === 'Activo') {
                $sheet->getStyle('J' . $fila)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCFCE7']],
                    'font' => ['color' => ['rgb' => '15803D']],
                ]);
            } else {
                $sheet->getStyle('J' . $fila)->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FEE2E2']],
                    'font' => ['color' => ['rgb' => 'B91C1C']],
                ]);
            }

            $sheet->getStyle('K' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $fila++;
            $num++;
        }

        // Si no hay datos, mostrar fila de mensaje
        if ($totalClientes === 0) {
            $sheet->mergeCells('A11:L11');
            $sheet->setCellValue('A11', 'No se encontraron clientes registrados con los filtros aplicados.');
            $sheet->getStyle('A11')->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 10, 'italic' => true, 'color' => ['rgb' => '64748B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getRowDimension(11)->setRowHeight(30);
            $fila = 12;
        } else {
            // ----------------------------------------------------
            // FILA DE TOTALES / RESUMEN FINAL
            // ----------------------------------------------------
            $sheet->setCellValue('A' . $fila, '');
            $sheet->setCellValue('B' . $fila, 'TOTAL REGISTROS EXPORTADOS: ' . $totalClientes);
            $sheet->setCellValue('H' . $fila, '=SUM(H11:H' . ($fila - 1) . ')');
            $sheet->setCellValue('I' . $fila, '=SUM(I11:I' . ($fila - 1) . ')');
            $sheet->setCellValue('J' . $fila, $totalActivos . ' Act. / ' . $totalInactivos . ' Inac.');

            $sheet->getRowDimension($fila)->setRowHeight(26);
            $sheet->getStyle('A' . $fila . ':L' . $fila)->applyFromArray([
                'font' => ['name' => 'Segoe UI', 'size' => 10, 'bold' => true, 'color' => ['rgb' => $colorNavyDark]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => $colorSlateHeader]],
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => $colorSlateHeader]],
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => $colorBorder]],
                ],
            ]);

            $sheet->getStyle('B' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('H' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $fila)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Activar AutoFiltro en la tabla de datos
        $sheet->setAutoFilter('A10:L10');

        // Congelar paneles para mantener cabeceras fijas al hacer scroll
        $sheet->freezePane('A11');

        return $spreadsheet;
    }

    /**
     * Retorna el nombre de archivo sugerido con timestamp.
     */
    public function getNombreArchivo(?string $estadoFiltro = null): string
    {
        $sufijo = 'Todos';
        if ($estadoFiltro === 'Activo') {
            $sufijo = 'Activos';
        } elseif ($estadoFiltro === 'Inactivo') {
            $sufijo = 'Inactivos';
        }

        $fecha = Carbon::now()->format('Ymd_His');
        return "Reporte_Clientes_Mariachi_Leon_{$sufijo}_{$fecha}.xlsx";
    }
}
