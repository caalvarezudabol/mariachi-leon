<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\Empresa;
use App\Models\Configuracion;
use App\Traits\Auditable;

class GestionEmpresa extends Component
{
    use Auditable;

    public $empresa_id;
    public $nombre_comercial;
    public $razon_social;
    public $nit_ruc;
    public $slogan;
    public $representante_legal;
    public $telefono_principal;
    public $whatsapp_comercial;
    public $email_contacto;
    public $direccion_fisica;
    public $ciudad_pais;
    public $logo_url;
    public $moneda_nombre;
    public $moneda_simbolo;
    public $redes_linktree;
    public $banco_nombre;
    public $banco_numero_cuenta;
    public $banco_titular;
    public $banco_qr_url;
    public $terminos_contrato;
    public $observaciones;

    public $activeTab = 'general';

    protected function rules()
    {
        return [
            'nombre_comercial' => 'required|string|max:255',
            'razon_social' => 'required|string|max:255',
            'nit_ruc' => 'nullable|string|max:50',
            'slogan' => 'nullable|string|max:255',
            'representante_legal' => 'required|string|max:255',
            'telefono_principal' => 'required|string|max:50',
            'whatsapp_comercial' => 'nullable|string|max:50',
            'email_contacto' => 'required|email|max:100',
            'direccion_fisica' => 'required|string|max:255',
            'ciudad_pais' => 'nullable|string|max:100',
            'logo_url' => 'nullable|string|max:255',
            'moneda_nombre' => 'required|string|max:50',
            'moneda_simbolo' => 'required|string|max:10',
            'redes_linktree' => 'nullable|url|max:255',
            'banco_nombre' => 'nullable|string|max:100',
            'banco_numero_cuenta' => 'nullable|string|max:100',
            'banco_titular' => 'nullable|string|max:100',
            'banco_qr_url' => 'nullable|string|max:255',
            'terminos_contrato' => 'nullable|string',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'nombre_comercial.required' => 'El nombre comercial de la agrupación es obligatorio.',
        'razon_social.required' => 'La razón social es obligatoria.',
        'representante_legal.required' => 'El nombre del representante legal es obligatorio.',
        'email_contacto.required' => 'El correo de contacto es obligatorio.',
        'email_contacto.email' => 'Ingrese una dirección de correo válida.',
        'redes_linktree.url' => 'El enlace a Linktree/Redes debe ser una URL válida (http:// o https://).',
    ];

    public function mount()
    {
        $empresa = Empresa::obtener();
        $this->empresa_id = $empresa->id;
        $this->nombre_comercial = $empresa->nombre_comercial;
        $this->razon_social = $empresa->razon_social;
        $this->nit_ruc = $empresa->nit_ruc;
        $this->slogan = $empresa->slogan;
        $this->representante_legal = $empresa->representante_legal;
        $this->telefono_principal = $empresa->telefono_principal;
        $this->whatsapp_comercial = $empresa->whatsapp_comercial;
        $this->email_contacto = $empresa->email_contacto;
        $this->direccion_fisica = $empresa->direccion_fisica;
        $this->ciudad_pais = $empresa->ciudad_pais;
        $this->logo_url = $empresa->logo_url;
        $this->moneda_nombre = $empresa->moneda_nombre;
        $this->moneda_simbolo = $empresa->moneda_simbolo;
        $this->redes_linktree = $empresa->redes_linktree;
        $this->banco_nombre = $empresa->banco_nombre;
        $this->banco_numero_cuenta = $empresa->banco_numero_cuenta;
        $this->banco_titular = $empresa->banco_titular;
        $this->banco_qr_url = $empresa->banco_qr_url;
        $this->terminos_contrato = $empresa->terminos_contrato;
        $this->observaciones = $empresa->observaciones;
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function guardar()
    {
        $this->validate();

        $empresa = Empresa::findOrFail($this->empresa_id);
        $empresa->update([
            'nombre_comercial' => trim($this->nombre_comercial),
            'razon_social' => trim($this->razon_social),
            'nit_ruc' => trim($this->nit_ruc),
            'slogan' => trim($this->slogan),
            'representante_legal' => trim($this->representante_legal),
            'telefono_principal' => trim($this->telefono_principal),
            'whatsapp_comercial' => trim($this->whatsapp_comercial),
            'email_contacto' => trim($this->email_contacto),
            'direccion_fisica' => trim($this->direccion_fisica),
            'ciudad_pais' => trim($this->ciudad_pais),
            'logo_url' => trim($this->logo_url),
            'moneda_nombre' => trim($this->moneda_nombre),
            'moneda_simbolo' => trim($this->moneda_simbolo),
            'redes_linktree' => trim($this->redes_linktree),
            'banco_nombre' => trim($this->banco_nombre),
            'banco_numero_cuenta' => trim($this->banco_numero_cuenta),
            'banco_titular' => trim($this->banco_titular),
            'banco_qr_url' => trim($this->banco_qr_url),
            'terminos_contrato' => $this->terminos_contrato,
            'observaciones' => $this->observaciones,
        ]);

        // Sincronización transparente con el almacén key-value de Configuraciones
        $syncKeys = [
            'nombre_agrupacion' => $this->nombre_comercial,
            'moneda_simbolo' => $this->moneda_simbolo,
            'moneda_nombre' => $this->moneda_nombre,
            'redes_linktree' => $this->redes_linktree,
            'telefono_contacto' => $this->telefono_principal,
            'email_contacto' => $this->email_contacto,
            'direccion_oficina' => $this->direccion_fisica,
            'terminos_contrato' => $this->terminos_contrato,
        ];

        foreach ($syncKeys as $clave => $valor) {
            Configuracion::where('clave', $clave)->update(['valor' => $valor]);
        }

        $this->registrarAuditoria('Configuración', 'Actualizar Empresa', 'Se actualizaron los datos institucionales de la empresa: ' . $empresa->nombre_comercial);
        session()->flash('success', 'Datos de la empresa y parámetros institucionales guardados correctamente.');
    }

    public function render()
    {
        return view('livewire.configuracion.gestion-empresa')
            ->layout('components.layouts.app', ['title' => 'Gestionar Empresa - Mariachi León Guanajuato']);
    }
}
