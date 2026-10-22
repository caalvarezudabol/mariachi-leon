<?php

namespace App\Livewire\Web;

use Livewire\Component;
use App\Models\TipoEvento;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Models\ContactoWeb;
use App\Models\Empresa;

class PaginaContacto extends Component
{
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
    public $captcha_num1;
    public $captcha_num2;
    public $captcha_respuesta = '';

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
        'email' => 'nullable|email',
        'tipo_evento_id' => 'nullable|exists:tipos_evento,id',
        'servicio_id' => 'nullable|exists:servicios,id',
        'paquete_id' => 'nullable|exists:paquetes,id',
        'fecha_estimada' => 'nullable|date',
        'mensaje' => 'required|string',
        'captcha_respuesta' => 'required|numeric',
    ];

    protected $messages = [
        'nombre.required' => 'Por favor ingrese su nombre.',
        'telefono.required' => 'Debe ingresar un número de teléfono de contacto.',
        'mensaje.required' => 'Por favor escriba los detalles de su evento.',
        'captcha_respuesta.required' => 'Por favor resuelva el código Anti-Bot (CAPTCHA).',
        'captcha_respuesta.numeric' => 'El CAPTCHA debe ser un número.',
    ];

    public function enviarMensaje()
    {
        $this->validate();

        // Validar respuesta del CAPTCHA
        if ((int)$this->captcha_respuesta !== ($this->captcha_num1 + $this->captcha_num2)) {
            $this->addError('captcha_respuesta', 'La respuesta del CAPTCHA Anti-Bot es incorrecta. Por favor intente de nuevo.');
            $this->generarCaptcha();
            return;
        }

        ContactoWeb::create([
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'tipo_evento_id' => $this->tipo_evento_id ?: null,
            'servicio_id' => $this->servicio_id ?: null,
            'paquete_id' => $this->paquete_id ?: null,
            'fecha_estimada' => $this->fecha_estimada ?: null,
            'mensaje' => $this->mensaje,
            'estado' => 'nuevo',
        ]);

        $this->reset(['nombre', 'telefono', 'email', 'tipo_evento_id', 'servicio_id', 'paquete_id', 'servicioSeleccionado', 'paqueteSeleccionado', 'item_id', 'fecha_estimada', 'mensaje']);
        $this->generarCaptcha();
        $this->enviadoExito = true;
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
