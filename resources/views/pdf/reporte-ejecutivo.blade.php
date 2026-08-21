<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Ejecutivo de Proyecto & Bitácora de Prompts</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.2cm 1.4cm 1.4cm 1.4cm;
        }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            color: #0f172a;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* Fixed Footer */
        .page-footer {
            position: fixed;
            bottom: -1.0cm;
            left: 0;
            right: 0;
            height: 0.8cm;
            font-size: 7pt;
            color: #475569;
            border-top: 1.5px solid #d97706;
            padding-top: 5px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-table td {
            vertical-align: middle;
            font-size: 7pt;
        }

        /* Top Header */
        .top-header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .logo-col {
            width: 20%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-col img {
            max-width: 100px;
            max-height: 75px;
            object-fit: contain;
        }

        .title-col {
            width: 80%;
            vertical-align: middle;
            text-align: center;
            padding-right: 5%;
        }

        .main-brand-name {
            font-size: 15pt;
            font-weight: 800;
            color: #0b1329;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .main-sub-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-top: 2px;
        }

        .doc-type-title {
            font-size: 11pt;
            font-weight: 800;
            color: #d97706;
            text-transform: uppercase;
            margin-top: 4px;
            letter-spacing: 0.5px;
        }

        /* Decorative Gold Line */
        .gold-line-container {
            text-align: center;
            margin: 4px auto 10px auto;
            width: 90%;
        }

        .gold-line-table {
            width: 100%;
            border-collapse: collapse;
        }

        .gold-line-table td {
            vertical-align: middle;
        }

        .gold-line-hr {
            border-top: 1px solid #d97706;
            width: 100%;
        }

        .gold-line-diamond {
            color: #d97706;
            font-size: 8pt;
            padding: 0 6px;
        }

        /* Banners */
        .section-banner {
            background-color: #0b1a30;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 8px;
            border-radius: 3px;
            margin-top: 12px;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            border-left: 4px solid #d97706;
            page-break-inside: avoid;
        }

        /* Info Grid */
        .grid-info {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            border: 1px solid #cbd5e1;
            page-break-inside: avoid;
        }

        .grid-info td {
            padding: 4px 6px;
            font-size: 7.5pt;
            border: 1px solid #cbd5e1;
        }

        .g-lbl {
            font-weight: bold;
            color: #0f172a;
            background-color: #f1f5f9;
            width: 20%;
            text-transform: uppercase;
            font-size: 7pt;
        }

        .g-val {
            color: #1e293b;
            width: 30%;
        }

        /* Prompts Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 7pt;
        }

        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            padding: 5px 4px;
            font-size: 6.8pt;
            text-align: center;
            border: 1px solid #0f172a;
        }

        .data-table td {
            padding: 4px 4px;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }

        .data-table tr {
            page-break-inside: avoid;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .badge-num {
            font-weight: bold;
            color: #d97706;
            text-align: center;
        }

        .prompt-text {
            font-weight: bold;
            color: #0f172a;
            font-family: 'Courier', monospace;
        }

        .solution-text {
            color: #334155;
        }

        /* Signatures Grid */
        .signatures-grid {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }

        .signatures-grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 15px;
        }

        .sig-row-table {
            width: 100%;
            border-collapse: collapse;
        }

        .sig-label-td {
            width: 35%;
            font-weight: bold;
            font-size: 7.5pt;
            color: #0f172a;
            vertical-align: bottom;
            padding-bottom: 2px;
        }

        .sig-line-td {
            width: 65%;
            border-bottom: 1px solid #0f172a;
            vertical-align: bottom;
            height: 25px;
        }

        .sig-subtext {
            text-align: center;
            font-size: 7pt;
            color: #64748b;
            margin-top: 3px;
        }

        .tag-status {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 6.5pt;
            text-transform: uppercase;
        }
        .tag-ok { background-color: #dcfce7; color: #15803d; }
        .tag-php { background-color: #fef3c7; color: #b45309; }
    </style>
</head>
<body>

    <!-- Fixed Footer -->
    <div class="page-footer">
        <table class="footer-table">
            <tr>
                <td style="width: 45%; text-align: left;">
                    <div style="font-weight: bold; color: #0f172a;">{{ $empresa->nombre_comercial }}</div>
                    <div>Informe Ejecutivo de Proyecto & Bitácora de Prompts</div>
                    <div style="font-size: 6.5pt; color: #94a3b8;">Documento oficial del sistema</div>
                </td>
                <td style="width: 30%; text-align: center;">
                    <div style="font-weight: bold; color: #334155;">Fecha de Emisión:</div>
                    <div>{{ $fechaEmision }}</div>
                </td>
                <td style="width: 25%; text-align: right; font-weight: bold; color: #0f172a;">
                    <!-- Inyectado por DomPDF -->
                </td>
            </tr>
        </table>
    </div>

    <!-- Script PHP DomPDF -->
    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("DejaVu Sans", "bold");
            $size = 7;
            $color = array(0.06, 0.09, 0.16);
            $pdf->page_text(495, 756, $text, $font, $size, $color);
        }
    </script>

    <!-- Header Section -->
    <table class="top-header-table">
        <tr>
            <td class="logo-col">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td class="title-col">
                <div class="main-brand-name">{{ $empresa->nombre_comercial }}</div>
                <div class="main-sub-title">SISTEMA DE GESTIÓN INTEGRAL Y CONTROL DE INVENTARIOS</div>
                <div class="doc-type-title">INFORME EJECUTIVO DE PROYECTO & BITÁCORA DE PROMPTS</div>
            </td>
        </tr>
    </table>

    <!-- Gold Line Divider -->
    <div class="gold-line-container">
        <table class="gold-line-table">
            <tr>
                <td><div class="gold-line-hr"></div></td>
                <td class="gold-line-diamond">◆</td>
                <td><div class="gold-line-hr"></div></td>
            </tr>
        </table>
    </div>

    <!-- 1. Ficha del Proyecto -->
    <div class="section-banner">1. FICHA TÉCNICA DEL PROYECTO Y ENTORNO</div>
    <table class="grid-info">
        <tr>
            <td class="g-lbl">Nombre Proyecto:</td>
            <td class="g-val"><strong>Mariachi León Guanajuato</strong></td>
            <td class="g-lbl">Versión PHP Obligatoria:</td>
            <td class="g-val"><span class="tag-status tag-php">PHP 8.1.10 (Inflexible)</span></td>
        </tr>
        <tr>
            <td class="g-lbl">Representante Legal:</td>
            <td class="g-val"><strong>{{ $empresa->representante_legal }}</strong></td>
            <td class="g-lbl">Framework Base:</td>
            <td class="g-val">Laravel 10.x / Livewire 3.x</td>
        </tr>
        <tr>
            <td class="g-lbl">Procesamiento de Imagen:</td>
            <td class="g-val">Extensión Nactiva GD (100% PHP 8.1.10)</td>
            <td class="g-lbl">Motor de PDFs:</td>
            <td class="g-val">DomPDF (Base64 Thumbnail Engine)</td>
        </tr>
        <tr>
            <td class="g-lbl">Repositorio GitHub:</td>
            <td class="g-val" colspan="3"><code>https://github.com/caalvarezudabol/mariachi-leon.git</code></td>
        </tr>
    </table>

    <!-- 2. Módulos Implementados -->
    <div class="section-banner">2. RESUMEN DE MÓDULOS Y ALCANCE DESARROLLADO</div>
    <table class="grid-info">
        <tr>
            <td class="g-lbl" style="width: 25%;">Sprint 1: Base & Seguridad</td>
            <td class="g-val" style="width: 75%;" colspan="3">
                Gestión de Usuarios, Roles & Permisos (Spatie), Registro de Auditoría de Logs de actividad, Parámetros del Sistema, Tipos de Evento y Servicios/Paquetes.
            </td>
        </tr>
        <tr>
            <td class="g-lbl">Módulo Empresa & Marca:</td>
            <td class="g-val" colspan="3">
                Servicio <code>ImageOptimizerService</code> para carga interactiva, optimización de imágenes mediante GD, eliminación de archivos obsoletos y propagación del logotipo a Sidebar Admin, Comprobantes, Kardex PDF y Portada del Sitio Web.
            </td>
        </tr>
        <tr>
            <td class="g-lbl">Activos Fijos & Inventario:</td>
            <td class="g-val" colspan="3">
                Gestión de Categorías, Artículos, Ingresos, Egresos, Asignaciones, Devoluciones y Bajas con generación de comprobantes oficiales impresos.
            </td>
        </tr>
        <tr>
            <td class="g-lbl">Motor Kardex PDF 1:1:</td>
            <td class="g-val" colspan="3">
                Algoritmo PPP (Precio Promedio Ponderado), Buscador interactivamente filtrable con autocompletado en tiempo real y selector con botón <code>[ ✕ ]</code>. Plantilla PDF idéntica a la maqueta 1:1 sin hojas en blanco ni emojis defectuosos, con numeración dinámica <code>Página X de Y</code>.
            </td>
        </tr>
    </table>

    <!-- 3. Bitácora de Prompts -->
    <div class="section-banner">3. BITÁCORA HISTÓRICA DE PROMPTS Y REQUERIMIENTOS DEL USUARIO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 32%;">Prompt / Solicitud del Usuario</th>
                <th style="width: 53%;">Acción Desarrollada y Solución Implementada</th>
                <th style="width: 10%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($prompts as $index => $item)
                <tr>
                    <td class="badge-num">#{{ $index + 1 }}</td>
                    <td class="prompt-text">"{{ $item['prompt'] }}"</td>
                    <td class="solution-text">{{ $item['solucion'] }}</td>
                    <td style="text-align: center;">
                        <span class="tag-status tag-ok">Completado</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Declaración Final -->
    <div style="font-size: 7.5pt; color: #334155; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 4px; padding: 6px 10px; margin-top: 10px; page-break-inside: avoid;">
        <strong>Declaración de Conformidad:</strong> El presente informe de proyecto certifica la totalidad de los desarrollos, correcciones, optimizaciones de diseño y bitácora de interacción ejecutados sobre el proyecto <strong>{{ $empresa->nombre_comercial }}</strong>, garantizando estricta compatibilidad con <strong>PHP 8.1.10</strong>.
    </div>

    <!-- Signatures Area -->
    <table class="signatures-grid">
        <tr>
            <td>
                <table class="sig-row-table">
                    <tr>
                        <td class="sig-label-td">Responsable de emisión:</td>
                        <td class="sig-line-td"></td>
                    </tr>
                </table>
                <div class="sig-subtext">Sistema de Gestión / Antigravity AI</div>
            </td>
            <td>
                <table class="sig-row-table">
                    <tr>
                        <td class="sig-label-td">Director / Rep. Legal:</td>
                        <td class="sig-line-td"></td>
                    </tr>
                </table>
                <div class="sig-subtext">{{ $empresa->representante_legal }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
