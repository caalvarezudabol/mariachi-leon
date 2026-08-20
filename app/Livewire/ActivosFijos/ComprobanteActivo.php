<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use App\Models\AssetAssignment;
use App\Models\AssetReturn;

class ComprobanteActivo extends Component
{
    public $tipo; // 'asignacion' o 'devolucion'
    public $id;
    public $registro;

    public function mount($tipo, $id)
    {
        $this->tipo = strtolower($tipo);
        $this->id = $id;

        if ($this->tipo === 'asignacion') {
            $this->registro = AssetAssignment::with(['asset.category', 'responsable', 'user'])->findOrFail($id);
        } else {
            $this->registro = AssetReturn::with(['asset.category', 'responsable', 'user', 'assignment'])->findOrFail($id);
        }
    }

    public function render()
    {
        return view('livewire.activos-fijos.comprobante-activo')
            ->layout('components.layouts.app', ['title' => 'Comprobante Oficial - Mariachi León Guanajuato']);
    }
}
