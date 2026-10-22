<?php

namespace App\Livewire\Web;

use Livewire\Component;

class PaginaNosotros extends Component
{
    public function render()
    {
        $empresa = \App\Models\Empresa::obtener();
        return view('livewire.web.pagina-nosotros', [
            'empresa' => $empresa,
        ])->layout('components.layouts.web', ['title' => 'Sobre Nosotros - ' . ($empresa->nombre_comercial ?? 'Mariachi León')]);
    }
}
