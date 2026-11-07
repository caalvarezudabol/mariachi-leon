@php
    $cli = $contrato->cliente;
    $ev = $contrato->evento;
    $moneda = fn($n) => 'Bs ' . number_format((float) $n, 2, ',', '.');

    // Monto en letras (si el servidor tiene la extensión intl)
    $formateador = class_exists(\NumberFormatter::class)
        ? new \NumberFormatter('es', \NumberFormatter::SPELLOUT)
        : null;
    $literal = function ($n) use ($formateador) {
        if (!$formateador) {
            return '';
        }
        $n = round((float) $n, 2);
        $ent = (int) floor($n);
        $cent = (int) round(($n - $ent) * 100);
        return ucfirst($formateador->format($ent)) . ' ' . sprintf('%02d/100 Bolivianos', $cent);
    };
    $b = fn($t) => '<strong>' . e($t) . '</strong>';

    // Solo se muestran los datos que existen
    $telCliente = collect([$cli->telefono ?? null, $cli->whatsapp ?? null])
        ->map(fn($t) => trim((string) $t))
        ->filter()
        ->unique()
        ->implode(' / ');
    $telEmpresa = collect([$empresa->telefono_principal ?? null, $empresa->whatsapp_comercial ?? null])
        ->map(fn($t) => trim((string) $t))
        ->filter()
        ->unique()
        ->implode(' / ');
    $docCliente = trim((string) ($cli->ci_nit ?? ''));
    $nitEmpresa = trim((string) ($empresa->nit_ruc ?? ''));
    $domicilioEmpresa = collect([$empresa->direccion_fisica ?? null, $empresa->ciudad_pais ?? null])
        ->map(fn($t) => trim((string) $t))
        ->filter()
        ->implode(', ');

    $fechaEvento = $ev->fecha_evento->copy()->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY');
    $horaEvento = substr((string) $ev->hora_evento, 0, 5);
    $horas = (float) $ev->duracion_horas;
    $duracion =
        $horas > 0
            ? ($horas < 1
                ? (int) round($horas * 60) . ' minutos'
                : rtrim(rtrim(number_format($horas, 2, '.', ''), '0'), '.') . ($horas == 1 ? ' hora' : ' horas'))
            : null;
    $fechaFirma = now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    $ciudadFirma = trim((string) ($empresa->ciudad_pais ?? '')) ?: 'Santa Cruz de la Sierra';
    $ciudadFirma = trim(explode('-', $ciudadFirma)[0]);

    $total = (float) $contrato->monto_total;
    $inicial = (float) $contrato->pago_inicial;
    $abonado = (float) $contrato->total_pagado;
    $saldo = (float) $contrato->saldo_pendiente;

    $bancoNombre = trim((string) ($empresa->banco_nombre ?? ''));
    $bancoCuenta = trim((string) ($empresa->banco_numero_cuenta ?? ''));
    $bancoTitular = trim((string) ($empresa->banco_titular ?? ''));

    // Frases armadas aquí para que ningún formateador de código las parta
    $conTerminos = trim((string) ($empresa->terminos_contrato ?? '')) !== '';
    $ultimaClausula = $conTerminos ? 'Octava' : 'Séptima';
    $subEncabezado = e(
        trim(
            $domicilioEmpresa .
                ($domicilioEmpresa && $telEmpresa ? ' - ' : '') .
                ($telEmpresa ? 'Cel.: ' . $telEmpresa : ''),
        ),
    );
    $txtEmpresa =
        $b($empresa->razon_social ?: $empresa->nombre_comercial) .
        e($nitEmpresa ? ', con NIT ' . $nitEmpresa : '') .
        ', representada legalmente por ' .
        $b($empresa->representante_legal) .
        e($domicilioEmpresa ? ', con domicilio en ' . $domicilioEmpresa : '') .
        '.';
    $txtCliente =
        $b($cli->nombre_completo) .
        e(
            ($docCliente ? ', con C.I./NIT ' . $docCliente : '') .
                ($telCliente ? ', teléfono ' . $telCliente : '') .
                (!empty($cli->email) ? ', correo electrónico ' . $cli->email : ''),
        ) .
        '.';
    $txtObjeto =
        'EL PRESTADOR se obliga a brindar a EL CLIENTE el servicio ' .
        $b($contrato->servicio->nombre) .
        e($duracion ? ', con una duración de ' . $duracion : '') .
        ', el día ' .
        $b($fechaEvento) .
        ' a horas ' .
        $b($horaEvento) .
        ', en ' .
        $b($ev->direccion_evento ?: 'el lugar que las partes acuerden') .
        '.' .
        (!empty($ev->contacto_evento)
            ? ' ' .
                e(
                    'La persona de contacto en el lugar es ' .
                        $ev->contacto_evento .
                        (!empty($ev->telefono_contacto) ? ', teléfono ' . $ev->telefono_contacto : '') .
                        '.',
                )
            : '');
    $enLetras = $literal($total);
    $txtPrecio =
        'El precio total del servicio es de ' .
        $b($moneda($total)) .
        ($enLetras ? ' (' . e($enLetras) . ')' : '') .
        ', que EL CLIENTE se obliga a cancelar de la siguiente manera:';
    $txtBanco =
        $bancoNombre && $bancoCuenta
            ? e(
                ' Los pagos por transferencia se realizan a la cuenta ' .
                    $bancoNombre .
                    ' N° ' .
                    $bancoCuenta .
                    ($bancoTitular ? ', a nombre de ' . $bancoTitular : '') .
                    '.',
            )
            : '';
    $txtFirmaCliente = e($cli->nombre_completo) . ($docCliente ? '<br>' . e('C.I./NIT: ' . $docCliente) : '');
    $txtFirmaPrestador =
        e($empresa->representante_legal) .
        '<br>' .
        e('Representante legal - ' . ($empresa->razon_social ?: $empresa->nombre_comercial));
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Contrato de Prestación de Servicios Musicales - {{ $contrato->numero_contrato }}</title>
    <style>
        @page {
            size: letter portrait;
            margin: 1.8cm 2cm 2cm 2cm;
        }

        body {
            font-family: 'DejaVu Serif', 'Times New Roman', serif;
            font-size: 9.5pt;
            color: #000;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        .page-footer {
            position: fixed;
            bottom: -1.2cm;
            left: 0;
            right: 0;
            font-size: 7pt;
            color: #444;
            border-top: 0.5pt solid #777;
            padding-top: 4px;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        }

        .head {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.2pt solid #000;
            margin-bottom: 14px;
        }

        .head td {
            vertical-align: middle;
            padding: 0 0 8px 0;
        }

        .head img {
            max-width: 70px;
            max-height: 60px;
        }

        .brand {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .brand-sub {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 7.5pt;
            color: #444;
            margin-top: 2px;
        }

        .num {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8pt;
            text-align: right;
        }

        h1 {
            font-size: 12.5pt;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin: 4px 0 2px 0;
        }

        .subtitulo {
            text-align: center;
            font-size: 8.5pt;
            color: #333;
            margin-bottom: 14px;
        }

        p {
            margin: 0 0 8px 0;
            text-align: justify;
        }

        .clausula {
            margin-top: 10px;
            page-break-inside: avoid;
        }

        .clausula-titulo {
            font-weight: bold;
            text-transform: uppercase;
        }

        table.pagos {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 8px 0;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
        }

        table.pagos td {
            border: 0.6pt solid #555;
            padding: 4px 8px;
        }

        table.pagos td.monto {
            text-align: right;
            white-space: nowrap;
        }

        table.pagos tr.total td {
            font-weight: bold;
            background: #eee;
        }

        ul.lista {
            margin: 2px 0 8px 0;
            padding-left: 18px;
        }

        ul.lista li {
            margin-bottom: 3px;
            text-align: justify;
        }

        table.firmas {
            width: 100%;
            border-collapse: collapse;
            margin-top: 55px;
            page-break-inside: avoid;
        }

        table.firmas td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 18px;
        }

        .firma-linea {
            border-top: 0.8pt solid #000;
            padding-top: 4px;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        }

        .firma-dato {
            font-size: 8pt;
            color: #222;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        }

        table.verif {
            width: 100%;
            margin-top: 26px;
            border-top: 0.5pt solid #999;
            padding-top: 6px;
            page-break-inside: avoid;
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
        }

        .verif-txt {
            font-size: 6.8pt;
            color: #444;
            line-height: 1.4;
        }
    </style>
</head>

<body>

    <div class="page-footer">
        <table style="width:100%; border-collapse:collapse;">
            <tr>
                <td style="text-align:left;">{{ $empresa->nombre_comercial }} - Contrato N°
                    {{ $contrato->numero_contrato }}</td>
                <td style="text-align:right;"></td>
            </tr>
        </table>
    </div>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $size = 7;
            $width = $fontMetrics->get_text_width($text, $font, $size);
            $pdf->page_text($pdf->get_width() - $width - 57, $pdf->get_height() - 40, $text, $font, $size, array(0.27, 0.27, 0.27));
        }
    </script>

    {{-- Encabezado --}}
    <table class="head">
        <tr>
            <td style="width: 80px;">
                @if (!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" alt="Logo">
                @endif
            </td>
            <td>
                <div class="brand">{{ $empresa->nombre_comercial }}</div>
                @if ($subEncabezado !== '')
                    <div class="brand-sub">{!! $subEncabezado !!}</div>
                @endif
            </td>
            <td class="num" style="width: 150px;">
                <strong>Contrato N° {{ $contrato->numero_contrato }}</strong><br>
                Evento {{ $ev->codigo_evento }}
            </td>
        </tr>
    </table>

    <h1>Contrato de Prestación de Servicios Musicales</h1>
    <div class="subtitulo">{{ $ciudadFirma }}, {{ $fechaFirma }}</div>

    <p>
        Conste por el presente documento privado de prestación de servicios musicales, que las partes
        abajo identificadas celebran de manera libre y voluntaria, bajo las siguientes cláusulas:
    </p>

    {{-- PRIMERA --}}
    <div class="clausula">
        <p><span class="clausula-titulo">Primera.- De las partes.</span></p>
        <p><strong>1.1. EL PRESTADOR:</strong> {!! $txtEmpresa !!}</p>
        <p><strong>1.2. EL CLIENTE:</strong> {!! $txtCliente !!}</p>
    </div>

    {{-- SEGUNDA --}}
    <div class="clausula">
        <p><span class="clausula-titulo">Segunda.- Del objeto.</span></p>
        <p>{!! $txtObjeto !!}</p>
    </div>

    {{-- TERCERA --}}
    <div class="clausula">
        <p><span class="clausula-titulo">Tercera.- Del precio y la forma de pago.</span></p>
        <p>{!! $txtPrecio !!}</p>
        <table class="pagos">
            <tr>
                <td>Monto total del servicio</td>
                <td class="monto">{{ $moneda($total) }}</td>
            </tr>
            @if ($inicial > 0)
                <tr>
                    <td>Anticipo / reserva de fecha</td>
                    <td class="monto">{{ $moneda($inicial) }}</td>
                </tr>
            @endif
            <tr>
                <td>Total abonado a la fecha de emisión</td>
                <td class="monto">{{ $moneda($abonado) }}</td>
            </tr>
            <tr class="total">
                <td>Saldo a cancelar</td>
                <td class="monto">{{ $moneda($saldo) }}</td>
            </tr>
        </table>
        <p>El saldo debe ser cancelado, como máximo, al inicio de la presentación.{!! $txtBanco !!}</p>
    </div>

    {{-- CUARTA --}}
    <div class="clausula">
        <p><span class="clausula-titulo">Cuarta.- Obligaciones de El Prestador.</span></p>
        <ul class="lista">
            <li>Presentarse puntualmente en la fecha, hora y lugar acordados.</li>
            <li>Asistir con vestimenta oficial e instrumentos profesionales en buen estado.</li>
            <li>Brindar el servicio contratado con la calidad y el repertorio propios de la agrupación.</li>
        </ul>
    </div>

    {{-- QUINTA --}}
    <div class="clausula">
        <p><span class="clausula-titulo">Quinta.- Obligaciones de El Cliente.</span></p>
        <ul class="lista">
            <li>Cancelar el precio en los plazos señalados en la cláusula tercera.</li>
            <li>Facilitar el acceso al lugar del evento y un espacio adecuado y seguro para la presentación.</li>
            <li>Proveer, cuando corresponda, suministro de energía eléctrica para el equipo de sonido.</li>
        </ul>
    </div>

    {{-- SEXTA --}}
    <div class="clausula">
        <p><span class="clausula-titulo">Sexta.- Modificaciones y cancelación.</span></p>
        <p>
            Cualquier cambio de fecha, hora o lugar debe ser coordinado por las partes con al menos
            cuarenta y ocho (48) horas de anticipación y queda sujeto a la disponibilidad de la agrupación.
            En caso de cancelación, las partes acordarán el tratamiento del anticipo entregado.
        </p>
    </div>

    {{-- SÉPTIMA (condiciones propias de la empresa) --}}
    @if ($conTerminos)
        <div class="clausula">
            <p><span class="clausula-titulo">Séptima.- Condiciones adicionales.</span></p>
            <p>{!! nl2br(e($empresa->terminos_contrato)) !!}</p>
        </div>
    @endif

    {{-- Conformidad --}}
    <div class="clausula">
        <p><span class="clausula-titulo">{{ $ultimaClausula }}.- Conformidad.</span></p>
        <p>
            En señal de conformidad, ambas partes firman el presente contrato en dos ejemplares de un mismo tenor,
            en la ciudad de {{ $ciudadFirma }}, a {{ $fechaFirma }}.
        </p>
    </div>

    {{-- Firmas --}}
    <table class="firmas">
        <tr>
            <td>
                <div class="firma-linea">El Cliente</div>
                <div class="firma-dato">{!! $txtFirmaCliente !!}</div>
            </td>
            <td>
                <div class="firma-linea">El Prestador</div>
                <div class="firma-dato">{!! $txtFirmaPrestador !!}</div>
            </td>
        </tr>
    </table>

    {{-- Verificación --}}
    <table class="verif">
        <tr>
            <td style="vertical-align: middle; padding-right: 12px;">
                <div class="verif-txt">
                    <strong>Verificación del documento.</strong> Escanee el código QR para comprobar en línea que este
                    contrato es auténtico y ver su estado actual.<br>
                    Emitido el {{ $fechaEmision }} por {{ $usuarioImpresion }}. Registrado el {{ $fechaCreacion }}
                    por {{ $usuarioCreacion }}.<br>
                    <span style="font-family: monospace; color: #666;">Código:
                        {{ $contrato->codigo_verificacion }}</span>
                </div>
            </td>
            <td style="width: 80px; text-align: right; vertical-align: middle;">
                @if (isset($qrBase64))
                    <img src="{{ $qrBase64 }}" style="width: 70px; height: 70px;" alt="QR de verificación">
                @endif
            </td>
        </tr>
    </table>

</body>

</html>