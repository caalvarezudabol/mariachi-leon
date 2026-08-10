<?php

namespace App\Livewire\Web;

use Livewire\Component;
use App\Models\Servicio;
use App\Models\Paquete;
use App\Models\TipoEvento;

class PaginaServicios extends Component
{
    public function render()
    {
        $servicios = Servicio::where('activo', true)->get();
        $paquetes = Paquete::with('servicios')->where('activo', true)->get();
        $tiposEvento = TipoEvento::where('activo', true)->get();

        return view('livewire.web.pagina-servicios', [
            'servicios' => $servicios,
            'paquetes' => $paquetes,
            'tiposEvento' => $tiposEvento,
        ])->layout('components.layouts.web', ['title' => 'Servicios y Paquetes - Mariachi León']);
    }
}
