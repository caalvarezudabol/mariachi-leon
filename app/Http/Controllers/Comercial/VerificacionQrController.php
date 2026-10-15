<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Contrato;
use App\Models\Empresa;
use App\Services\NumeroALetras;

class VerificacionQrController extends Controller
{
    public function verificarRecibo($token)
    {
        $pago = Pago::with(['cliente', 'contrato', 'evento', 'user', 'anuladoPor'])
            ->where('codigo_verificacion', $token)
            ->first();

        $empresa = Empresa::obtener();
        $fechaConsulta = now()->format('d/m/Y H:i:s');
        $montoLiteral = $pago ? NumeroALetras::convertir((float)$pago->monto) : '';

        return view('public.verificar-recibo', [
            'pago' => $pago,
            'empresa' => $empresa,
            'token' => $token,
            'fechaConsulta' => $fechaConsulta,
            'montoLiteral' => $montoLiteral,
        ]);
    }

    public function verificarContrato($token)
    {
        $contrato = Contrato::with(['cliente', 'evento', 'servicio', 'user', 'pagos'])
            ->where('codigo_verificacion', $token)
            ->first();

        $empresa = Empresa::obtener();
        $fechaConsulta = now()->format('d/m/Y H:i:s');

        return view('public.verificar-contrato', [
            'contrato' => $contrato,
            'empresa' => $empresa,
            'token' => $token,
            'fechaConsulta' => $fechaConsulta,
        ]);
    }

    public function verificarComisiones($token)
    {
        $eventoId = \App\Services\Security\DocumentToken::decodeId($token, 'evento');
        $evento = null;
        if ($eventoId) {
            $evento = \App\Models\Evento::with(['cliente', 'servicio', 'contrato', 'participantes.persona'])
                ->find($eventoId);
        }

        $empresa = Empresa::obtener();
        $fechaConsulta = now()->format('d/m/Y H:i:s');

        return view('public.verificar-comisiones', [
            'evento' => $evento,
            'empresa' => $empresa,
            'token' => $token,
            'fechaConsulta' => $fechaConsulta,
        ]);
    }
}

