<?php

namespace App\Livewire\Web;

use Livewire\Component;

class PaginaNosotros extends Component
{
    public function render()
    {
        return view('livewire.web.pagina-nosotros')
            ->layout('components.layouts.web', ['title' => 'Sobre Nosotros - Mariachi León']);
    }
}
