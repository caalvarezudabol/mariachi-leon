<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\Configuracion;
use App\Traits\Auditable;

class GestionParametros extends Component
{
    use Auditable;

    public $config = [];

    public function mount()
    {
        $all = Configuracion::all();
        foreach ($all as $c) {
            $this->config[$c->clave] = $c->valor;
        }
    }

    public function guardar()
    {
        foreach ($this->config as $clave => $valor) {
            Configuracion::where('clave', $clave)->update(['valor' => $valor]);
        }

        $this->registrarAuditoria('Configuración', 'Actualizar Parámetros', 'Se actualizaron los parámetros generales del sistema.');
        session()->flash('success', 'Parámetros del sistema guardados correctamente.');
    }

    public function render()
    {
        $parametros = Configuracion::all()->groupBy('grupo');

        return view('livewire.configuracion.gestion-parametros', [
            'parametros' => $parametros,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Parámetros - Mariachi León']);
    }
}
