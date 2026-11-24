<?php

namespace App\Livewire\Web;

use Livewire\Component;
use Livewire\Attributes\Locked;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\TipoEvento;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Models\ContactoWeb;
use App\Models\Empresa;
use App\Models\QrTransaccion;
use App\Services\BanecoQrService;
use App\Traits\Auditable;

class PaginaContacto extends Component
{
    use Auditable;
    public $nombre = '';
    public $telefono = '';
    public $email = '';
    public $tipo_evento_id = '';
    public $fecha_estimada = '';
    public $mensaje = '';
    public $enviadoExito = false;

    // Selección de Servicio / Paquete para Cotización
    public $servicio_id = null;
    public $paquete_id = null;
    public $servicioSeleccionado = null;
    public $paqueteSeleccionado = null;
    public $item_id = ''; // "servicio_X" o "paquete_Y"

    // CAPTCHA anti-bot
    #[Locked]
    public $captcha_num1;
    #[Locked]
    public $captcha_num2;
    public $captcha_respuesta = '';

    // Integración de Cobro QR Banco Económico (Anticipo 100 Bs)
    public $modalAnticipoOpen = false;
    public $modalQrOpen = false;
    public $qrGenerando = false;
    public $qrError = '';
    public $qrId = null;
    public $qrTransactionId = null;
    public $qrImageBase64 = null;
    public $qrSegundosRestantes = 240; // 4 minutos exactos
    public $qrExpirado = false;
    public $pagoConfirmadoExito = false;
    public $datosPagoConfirmado = null;
    public $qrTransaccionId = null;

    public function mount()
    {
        $this->generarCaptcha();

        $servicioQuery = request()->query('servicio_id') ?? request()->query('servicio');
        $paqueteQuery = request()->query('paquete_id') ?? request()->query('paquete');

        if ($servicioQuery) {
            $this->seleccionarServicio($servicioQuery);
        } elseif ($paqueteQuery) {
            $this->seleccionarPaquete($paqueteQuery);
        }
    }

    public function seleccionarServicio($id)
    {
        $servicio = Servicio::find($id);
        if ($servicio) {
            $this->servicio_id = $servicio->id;
            $this->servicioSeleccionado = $servicio;
            $this->paquete_id = null;
            $this->paqueteSeleccionado = null;
            $this->item_id = 'servicio_' . $servicio->id;

            $empresa = Empresa::obtener();
            $moneda = $empresa->moneda_simbolo ?? 'Bs.';

            // Sugerir mensaje pre-llenado si está vacío o si era otro mensaje por defecto
            if (empty($this->mensaje) || str_starts_with($this->mensaje, 'Hola Mariachi León')) {
                $this->mensaje = "Hola Mariachi León Guanajuato, deseo solicitar una cotización formal para el servicio: {$servicio->nombre} ({$moneda} " . number_format($servicio->precio_base, 2) . ", Duración: {$servicio->duracion_minutos} min). Por favor confirmen disponibilidad de fecha y detalles.";
            }
        }
    }

    public function seleccionarPaquete($id)
    {
        $paquete = Paquete::with('servicios')->find($id);
        if ($paquete) {
            $this->paquete_id = $paquete->id;
            $this->paqueteSeleccionado = $paquete;
            $this->servicio_id = null;
            $this->servicioSeleccionado = null;
            $this->item_id = 'paquete_' . $paquete->id;

            $empresa = Empresa::obtener();
            $moneda = $empresa->moneda_simbolo ?? 'Bs.';

            if (empty($this->mensaje) || str_starts_with($this->mensaje, 'Hola Mariachi León')) {
                $this->mensaje = "Hola Mariachi León Guanajuato, deseo solicitar una cotización/reserva para el paquete: {$paquete->nombre} ({$moneda} " . number_format($paquete->precio_paquete, 2) . "). Por favor confirmen disponibilidad de fecha y detalles.";
            }
        }
    }

    public function updatedItemId($val)
    {
        if (str_starts_with($val, 'servicio_')) {
            $sId = (int)str_replace('servicio_', '', $val);
            $this->seleccionarServicio($sId);
        } elseif (str_starts_with($val, 'paquete_')) {
            $pId = (int)str_replace('paquete_', '', $val);
            $this->seleccionarPaquete($pId);
        } else {
            $this->quitarSeleccion();
        }
    }

    public function quitarSeleccion()
    {
        $this->servicio_id = null;
        $this->servicioSeleccionado = null;
        $this->paquete_id = null;
        $this->paqueteSeleccionado = null;
        $this->item_id = '';
        if (str_starts_with($this->mensaje, 'Hola Mariachi León')) {
            $this->mensaje = '';
        }
    }

    public function generarCaptcha()
    {
        $this->captcha_num1 = rand(1, 9);
        $this->captcha_num2 = rand(1, 9);
        $this->captcha_respuesta = '';
    }

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'telefono' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'tipo_evento_id' => 'nullable|exists:tipos_evento,id',
        'servicio_id' => 'nullable|exists:servicios,id',
        'paquete_id' => 'nullable|exists:paquetes,id',
        'fecha_estimada' => 'nullable|date|after_or_equal:today',
        'mensaje' => 'required|string|max:2000',
        'captcha_respuesta' => 'required|numeric',
    ];

    protected $messages = [
        'nombre.required' => 'Por favor ingrese su nombre.',
        'telefono.required' => 'Debe ingresar un número de teléfono de contacto.',
        'fecha_estimada.after_or_equal' => 'Elige una fecha de hoy en adelante.',
        'fecha_estimada.date' => 'La fecha no es válida.',
        'mensaje.max' => 'El mensaje es demasiado largo (máximo 2000 caracteres).',
        'mensaje.required' => 'Por favor escriba los detalles de su evento.',
        'captcha_respuesta.required' => 'Por favor resuelva el código Anti-Bot (CAPTCHA).',
        'captcha_respuesta.numeric' => 'El CAPTCHA debe ser un número.',
    ];

    /**
     * Paso 1: Al enviar el formulario, validar y preguntar si desea dejar anticipo de 100 Bs
     */
    public function enviarMensaje()
    {
        $this->validate();

        // Límite por IP: 5 solicitudes por hora
        $clave = 'contacto-web:' . request()->ip();
        if (RateLimiter::tooManyAttempts($clave, 5)) {
            $this->addError('mensaje', 'Enviaste varias solicitudes seguidas. Intenta más tarde o escríbenos por WhatsApp.');
            return;
        }

        // Validar respuesta del CAPTCHA
        if ((int)$this->captcha_respuesta !== ($this->captcha_num1 + $this->captcha_num2)) {
            $this->addError('captcha_respuesta', 'La respuesta del CAPTCHA Anti-Bot es incorrecta. Por favor intente de nuevo.');
            $this->generarCaptcha();
            return;
        }

        // Abrir modal interactivo preguntando si desea dejar anticipo de Bs. 100 mediante QR
        $this->modalAnticipoOpen = true;
    }

    /**
     * Opción: Enviar solo cotización formal sin anticipo
     */
    public function enviarSinAnticipo()
    {
        $this->guardarSolicitud(false, null);
    }

    /**
     * Opción: Pagar anticipo de Bs. 100 con código QR del Banco Económico
     */
    public function iniciarPagoQr(BanecoQrService $qrService)
    {
        $this->modalAnticipoOpen = false;
        $this->modalQrOpen = true;
        $this->generarNuevoQr($qrService);
    }

    /**
     * Generar o renovar el código QR con 4 minutos de vigencia
     */
    public function generarNuevoQr(BanecoQrService $qrService)
    {
        $this->qrGenerando = true;
        $this->qrError = '';
        $this->qrExpirado = false;
        $this->pagoConfirmadoExito = false;

        $detalle = 'Anticipo reserva: ' . ($this->servicioSeleccionado ? $this->servicioSeleccionado->nombre : ($this->paqueteSeleccionado ? $this->paqueteSeleccionado->nombre : 'Mariachi Leon'));

        $res = $qrService->generarQr([
            'monto' => 100.00,
            'descripcion' => $detalle,
            'origen' => 'contacto_web',
        ]);

        $this->qrGenerando = false;

        if ($res['success']) {
            $this->qrId = $res['qr_id'];
            $this->qrTransactionId = $res['transaction_id'];
            $this->qrImageBase64 = $res['qr_image_base64'];
            $this->qrSegundosRestantes = 240; // 4 minutos exactos
            $this->qrTransaccionId = $res['transaccion']->id;
        } else {
            $this->qrError = $res['message'] ?? 'No se pudo generar el código QR. Intente de nuevo.';
        }
    }

    /**
     * Sondeo automático en segundo plano para verificar si el cliente pagó
     */
    public function verificarEstadoQr(BanecoQrService $qrService)
    {
        if (!$this->modalQrOpen || !$this->qrId || $this->pagoConfirmadoExito || $this->qrExpirado) {
            return;
        }

        $res = $qrService->consultarEstado($this->qrId);

        if ($res['es_pagado']) {
            $this->pagoConfirmadoExito = true;
            $this->datosPagoConfirmado = $res['payment'];
            // Guardar la solicitud web confirmada con anticipo
            $this->guardarSolicitud(true, $this->qrTransaccionId);
        } elseif ($res['es_expirado']) {
            $this->qrExpirado = true;
            $this->qrSegundosRestantes = 0;
        } else {
            $this->qrSegundosRestantes = $res['segundos_restantes'];
            if ($this->qrSegundosRestantes <= 0) {
                $this->qrExpirado = true;
            }
        }
    }

    /**
     * Notificación cuando el temporizador de 4 minutos expira en la pantalla
     */
    public function tiempoExpirado()
    {
        $this->qrExpirado = true;
        $this->qrSegundosRestantes = 0;
        if ($this->qrTransaccionId) {
            QrTransaccion::where('id', $this->qrTransaccionId)
                ->where('estado', 'PENDIENTE')
                ->update(['estado' => 'EXPIRADO']);
        }
    }

    /**
     * Simulación de pago para pruebas locales / ambiente sandbox de certificación
     */
    public function simularPagoPrueba()
    {
        if (!config('app.debug') && config('app.env') === 'production') {
            return;
        }

        if ($this->qrTransaccionId) {
            QrTransaccion::where('id', $this->qrTransaccionId)->update([
                'estado' => 'PAGADO',
                'pagado_en' => now(),
                'datos_pago' => [
                    'qrId' => $this->qrId,
                    'transactionId' => 'SIM-' . time(),
                    'paymentDate' => now()->format('Y-m-d\TH:i:s'),
                    'amount' => 100.0,
                    'currency' => 'BOB',
                    'senderName' => 'SIMULADOR DE PRUEBAS BANCO',
                    'senderBankCode' => '1016',
                ],
            ]);
        }

        $this->pagoConfirmadoExito = true;
        $this->guardarSolicitud(true, $this->qrTransaccionId);
    }

    /**
     * Cancelar el QR y cerrar ventana
     */
    public function cancelarQr(BanecoQrService $qrService)
    {
        if ($this->qrId) {
            $qrService->anularQr($this->qrId);
        }
        $this->modalQrOpen = false;
        $this->qrId = null;
        $this->qrImageBase64 = null;
    }

    /**
     * Guardar solicitud en base de datos
     */
    protected function guardarSolicitud(bool $anticipoPagado = false, ?int $qrTransaccionId = null)
    {
        $clave = 'contacto-web:' . request()->ip();
        RateLimiter::hit($clave, 3600);

        $contacto = ContactoWeb::create([
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'email' => $this->email ?: null,
            'tipo_evento_id' => $this->tipo_evento_id ?: null,
            'servicio_id' => $this->servicio_id ?: null,
            'paquete_id' => $this->paquete_id ?: null,
            'fecha_estimada' => $this->fecha_estimada ?: null,
            'mensaje' => $this->mensaje,
            'estado' => $anticipoPagado ? 'cotizado' : 'nuevo',
            'anticipo_pagado' => $anticipoPagado,
            'monto_anticipo' => $anticipoPagado ? 100.00 : 0.00,
            'qr_transaccion_id' => $qrTransaccionId,
        ]);

        if ($qrTransaccionId) {
            QrTransaccion::where('id', $qrTransaccionId)->update([
                'contacto_web_id' => $contacto->id,
            ]);
        }

        if ($anticipoPagado) {
            $qrTx = $qrTransaccionId ? QrTransaccion::find($qrTransaccionId) : null;
            $txCode = $qrTx ? $qrTx->transaction_id : ('ID #' . $qrTransaccionId);
            $this->registrarAuditoria(
                'Sitio Web',
                'Cobro QR Anticipo Web',
                "Cobro exitoso de anticipo Bs. 100.00 mediante QR Banco Económico del cliente web {$contacto->nombre} (Teléfono: {$contacto->telefono}). Transacción QR: {$txCode}."
            );
        } else {
            $this->registrarAuditoria(
                'Sitio Web',
                'Contacto Web Recibido',
                "Nueva solicitud de contacto web de {$contacto->nombre} (Teléfono: {$contacto->telefono}) sin anticipo."
            );
        }

        $this->enviadoExito = true;
        $this->modalAnticipoOpen = false;

        $this->reset([
            'nombre',
            'telefono',
            'email',
            'tipo_evento_id',
            'servicio_id',
            'paquete_id',
            'servicioSeleccionado',
            'paqueteSeleccionado',
            'item_id',
            'fecha_estimada',
            'mensaje',
            'captcha_respuesta',
        ]);
        $this->generarCaptcha();
    }

    public function render()
    {
        $tiposEvento = TipoEvento::where('activo', true)->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $paquetes = Paquete::where('activo', true)->orderBy('nombre')->get();
        $empresa = Empresa::obtener();

        return view('livewire.web.pagina-contacto', [
            'tiposEvento' => $tiposEvento,
            'servicios' => $servicios,
            'paquetes' => $paquetes,
            'empresa' => $empresa,
        ])->layout('components.layouts.web', ['title' => 'Cotización y Contacto - ' . ($empresa->nombre_comercial ?? 'Mariachi León')]);
    }
}
