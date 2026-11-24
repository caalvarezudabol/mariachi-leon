<?php

namespace App\Services;

use App\Models\QrTransaccion;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BanecoQrService
{
    protected string $baseUrl;
    protected string $user;
    protected string $password;
    protected string $aesKey;
    protected string $account;
    protected int $timeoutMinutes;
    protected string $currency;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('baneco.base_url', 'https://apimktdesa.baneco.com.bo/ApiGateway'), '/');
        $this->user = (string) config('baneco.user', '26551010');
        $this->password = (string) config('baneco.password', '1234');
        $this->aesKey = (string) config('baneco.aes_key', '40A318B299F245C2B697176723088629');
        $this->account = (string) config('baneco.account', '1061602532');
        $this->timeoutMinutes = (int) config('baneco.timeout_minutes', 4);
        $this->currency = (string) config('baneco.currency', 'BOB');
    }

    /**
     * Cliente HTTP ligero y nativo mediante cURL (sin dependencias externas)
     */
    protected function curlRequest(string $method, string $url, array $params = [], ?string $bearerToken = null): array
    {
        $ch = curl_init();
        $headers = [
            'Accept: application/json',
        ];

        if ($bearerToken) {
            $headers[] = 'Authorization: Bearer ' . $bearerToken;
        }

        if (strtoupper($method) === 'GET' && !empty($params)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($params);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 6);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            $headers[] = 'Content-Type: application/json';
        } elseif (strtoupper($method) === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            if (!empty($params)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
                $headers[] = 'Content-Type: application/json';
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $responseBody = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $json = json_decode((string) $responseBody, true);

        return [
            'status' => $httpCode,
            'body' => $responseBody,
            'json' => is_array($json) ? $json : null,
            'error' => $curlError,
        ];
    }

    /**
     * Cifrar un texto utilizando el endpoint oficial del Banco Económico
     */
    public function cifrar(string $texto): string
    {
        $cacheKey = 'baneco_enc_' . md5($texto . '_' . $this->aesKey);
        return Cache::remember($cacheKey, 86400, function () use ($texto) {
            $res = $this->curlRequest('GET', "{$this->baseUrl}/api/authentication/encrypt", [
                'text' => $texto,
                'aesKey' => $this->aesKey,
            ]);

            if ($res['status'] === 200 && !empty($res['body'])) {
                return trim($res['body'], "\"\r\n ");
            }

            throw new \Exception('Error al cifrar datos con la API del Banco Económico: ' . ($res['body'] ?? $res['error']));
        });
    }

    /**
     * Obtener el Bearer Token JWT para consumir los servicios del Banco
     */
    public function obtenerToken(bool $forzarRenovacion = false): string
    {
        $cacheKey = 'baneco_jwt_bearer_token_' . $this->user;

        if ($forzarRenovacion) {
            Cache::forget($cacheKey);
        }

        return Cache::remember($cacheKey, 1800, function () {
            $encPassword = $this->cifrar($this->password);

            $res = $this->curlRequest('POST', "{$this->baseUrl}/api/authentication/authenticate", [
                'userName' => $this->user,
                'password' => $encPassword,
            ]);

            if ($res['status'] === 200 && isset($res['json']['token']) && !empty($res['json']['token'])) {
                return $res['json']['token'];
            }

            Log::error('Fallo en autenticación con API Banco Económico', [
                'status' => $res['status'],
                'body' => $res['body'],
                'error' => $res['error'],
            ]);

            throw new \Exception('No se pudo autenticar con la API del Banco Económico: ' . ($res['json']['message'] ?? $res['body']));
        });
    }

    /**
     * Generar un código QR de Pago Simple con temporizador de 4 minutos
     *
     * @param array $params [
     *    'monto' => float,
     *    'descripcion' => string,
     *    'transaction_id' => ?string (opcional, max 30 chars),
     *    'origen' => string ('contacto_web', 'pago_contrato', etc.),
     *    'contacto_web_id' => ?int,
     *    'contrato_id' => ?int,
     *    'cliente_id' => ?int,
     *    'pago_id' => ?int,
     *    'created_by' => ?int,
     * ]
     * @return array
     */
    public function generarQr(array $params): array
    {
        $monto = round((float) ($params['monto'] ?? 100.0), 2);
        $descripcion = Str::limit($params['descripcion'] ?? 'Anticipo reserva Mariachi León', 95, '');
        $origen = $params['origen'] ?? 'contacto_web';

        // Generar transactionId único de hasta 30 caracteres
        $prefix = strtoupper(substr($origen, 0, 3));
        $transactionId = $params['transaction_id'] ?? ($prefix . '-' . date('ymdHis') . '-' . Str::random(5));
        $transactionId = substr($transactionId, 0, 30);

        $dueDate = $params['due_date'] ?? date('Y-m-d', strtotime('+1 day'));

        // Cifrar cuenta destino
        $accountCredit = $this->cifrar($this->account);

        // Obtener Token
        $token = $this->obtenerToken();

        $payload = [
            'transactionId' => $transactionId,
            'accountCredit' => $accountCredit,
            'currency' => $this->currency,
            'amount' => $monto,
            'description' => $descripcion,
            'dueDate' => $dueDate,
            'singleUse' => true,
            'modifyAmount' => false,
        ];

        $res = $this->curlRequest('POST', "{$this->baseUrl}/api/qrsimple/generateQR", $payload, $token);

        // Si el token expiró en el banco, reintentar una vez forzando renovación
        if ($res['status'] === 401) {
            $token = $this->obtenerToken(true);
            $res = $this->curlRequest('POST', "{$this->baseUrl}/api/qrsimple/generateQR", $payload, $token);
        }

        if ($res['status'] !== 200 || ($res['json']['responseCode'] ?? -1) !== 0) {
            $msg = $res['json']['message'] ?? 'Error en la respuesta del banco al generar QR';
            Log::error('Error al generar QR Banco Económico', [
                'payload' => $payload,
                'response' => $res['body'],
                'error' => $res['error'],
            ]);
            return [
                'success' => false,
                'message' => $msg,
            ];
        }

        $qrId = (string) $res['json']['qrId'];
        $qrImage = (string) $res['json']['qrImage'];

        // Vigencia exacta de 4 minutos en pantalla
        $expiraEn = now()->addMinutes($this->timeoutMinutes);

        // Registrar en BD
        $transaccion = QrTransaccion::create([
            'transaction_id' => $transactionId,
            'qr_id' => $qrId,
            'origen' => $origen,
            'contacto_web_id' => $params['contacto_web_id'] ?? null,
            'contrato_id' => $params['contrato_id'] ?? null,
            'pago_id' => $params['pago_id'] ?? null,
            'cliente_id' => $params['cliente_id'] ?? null,
            'monto' => $monto,
            'moneda' => $this->currency,
            'descripcion' => $descripcion,
            'due_date' => $dueDate,
            'qr_image_base64' => $qrImage,
            'estado' => 'PENDIENTE',
            'expira_en' => $expiraEn,
            'created_by' => $params['created_by'] ?? null,
        ]);

        return [
            'success' => true,
            'transaccion' => $transaccion,
            'transaction_id' => $transactionId,
            'qr_id' => $qrId,
            'qr_image_base64' => $qrImage,
            'monto' => $monto,
            'moneda' => $this->currency,
            'expira_en' => $expiraEn->toIso8601String(),
            'segundos_restantes' => $this->timeoutMinutes * 60, // 240 segundos
        ];
    }

    /**
     * Verificar estado de un código QR en tiempo real
     *
     * @param string $qrId
     * @param QrTransaccion|null $transaccion
     * @return array
     */
    public function consultarEstado(string $qrId, ?QrTransaccion $transaccion = null): array
    {
        if (!$transaccion) {
            $transaccion = QrTransaccion::where('qr_id', $qrId)->first();
        }

        // Si ya está pagado en BD, retornar directamente
        if ($transaccion && $transaccion->estado === 'PAGADO') {
            return [
                'success' => true,
                'status' => 1,
                'es_pagado' => true,
                'es_anulado' => false,
                'es_expirado' => false,
                'segundos_restantes' => 0,
                'transaccion' => $transaccion,
                'payment' => $transaccion->datos_pago,
                'message' => 'Pago confirmado.',
            ];
        }

        // Si ya expiró el temporizador de 4 minutos
        if ($transaccion && $transaccion->estaExpirado()) {
            if ($transaccion->estado === 'PENDIENTE') {
                $transaccion->update(['estado' => 'EXPIRADO']);
            }
            return [
                'success' => true,
                'status' => 9,
                'es_pagado' => false,
                'es_anulado' => false,
                'es_expirado' => true,
                'segundos_restantes' => 0,
                'transaccion' => $transaccion,
                'message' => 'El código QR ha expirado (límite 4 minutos alcanzado).',
            ];
        }

        $token = $this->obtenerToken();

        $res = $this->curlRequest('GET', "{$this->baseUrl}/api/qrsimple/v2/statusQR/{$qrId}", [], $token);

        if ($res['status'] === 401) {
            $token = $this->obtenerToken(true);
            $res = $this->curlRequest('GET', "{$this->baseUrl}/api/qrsimple/v2/statusQR/{$qrId}", [], $token);
        }

        if ($res['status'] !== 200 || !is_array($res['json'])) {
            return [
                'success' => false,
                'status' => 0,
                'es_pagado' => false,
                'es_anulado' => false,
                'es_expirado' => false,
                'segundos_restantes' => $transaccion ? $transaccion->segundosRestantes() : 0,
                'message' => 'No se pudo consultar el estado con el banco',
            ];
        }

        $statusQrCode = (int) ($res['json']['statusQrCode'] ?? 0);
        $paymentData = $res['json']['payment'] ?? [];
        $primerPago = is_array($paymentData) && count($paymentData) > 0 ? $paymentData[0] : null;

        $esPagado = ($statusQrCode === 1);
        $esAnulado = ($statusQrCode === 9);

        if ($transaccion) {
            if ($esPagado && $transaccion->estado !== 'PAGADO') {
                $transaccion->update([
                    'estado' => 'PAGADO',
                    'pagado_en' => now(),
                    'datos_pago' => $primerPago ?: $paymentData,
                ]);
            } elseif ($esAnulado && $transaccion->estado !== 'ANULADO') {
                $transaccion->update([
                    'estado' => 'ANULADO',
                ]);
            }
        }

        return [
            'success' => true,
            'status' => $statusQrCode,
            'es_pagado' => $esPagado,
            'es_anulado' => $esAnulado,
            'es_expirado' => false,
            'segundos_restantes' => $transaccion ? $transaccion->segundosRestantes() : 0,
            'payment' => $primerPago ?: $paymentData,
            'transaccion' => $transaccion,
            'message' => $res['json']['message'] ?? '',
        ];
    }

    /**
     * Anular un QR en el Banco y en BD
     */
    public function anularQr(string $qrId): bool
    {
        try {
            $token = $this->obtenerToken();

            $res = $this->curlRequest('DELETE', "{$this->baseUrl}/api/qrsimple/cancelQR", [
                'qrId' => $qrId,
            ], $token);

            $transaccion = QrTransaccion::where('qr_id', $qrId)->first();
            if ($transaccion && $transaccion->estado === 'PENDIENTE') {
                $transaccion->update(['estado' => 'ANULADO']);
            }

            return $res['status'] === 200 && ($res['json']['responseCode'] ?? -1) === 0;
        } catch (\Throwable $e) {
            Log::warning('No se pudo anular QR en banco: ' . $e->getMessage());
            return false;
        }
    }
}
