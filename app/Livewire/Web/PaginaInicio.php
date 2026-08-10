<?php

namespace App\Livewire\Web;

use Livewire\Component;
use App\Models\TipoEvento;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Models\ContactoWeb;
use App\Models\Banner;

class PaginaInicio extends Component
{
    public $nombre = '';
    public $telefono = '';
    public $email = '';
    public $tipo_evento_id = '';
    public $fecha_estimada = '';
    public $mensaje = '';
    public $enviadoExito = false;

    // CAPTCHA anti-bot
    public $captcha_num1;
    public $captcha_num2;
    public $captcha_respuesta = '';

    public function mount()
    {
        $this->generarCaptcha();
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

    public function enviarSolicitud()
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
            'tipo_evento_id' => $this->tipo_evento_id ? $this->tipo_evento_id : null,
            'fecha_estimada' => $this->fecha_estimada ? $this->fecha_estimada : null,
            'mensaje' => $this->mensaje,
            'estado' => 'nuevo',
        ]);

        $this->reset(['nombre', 'telefono', 'email', 'tipo_evento_id', 'fecha_estimada', 'mensaje']);
        $this->generarCaptcha();
        $this->enviadoExito = true;
    }

    public function render()
    {
        $banners = Banner::where('activo', true)->orderBy('orden', 'asc')->get();
        $tiposEvento = TipoEvento::where('activo', true)->get();
        $serviciosDestacados = Servicio::where('activo', true)->take(3)->get();
        $paquetesDestacados = Paquete::where('activo', true)->where('destacado', true)->take(3)->get();

        return view('livewire.web.pagina-inicio', [
            'banners' => $banners,
            'tiposEvento' => $tiposEvento,
            'serviciosDestacados' => $serviciosDestacados,
            'paquetesDestacados' => $paquetesDestacados,
        ])->layout('components.layouts.web', ['title' => 'Mariachi León Guanajuato - Sitio Oficial']);
    }
}
