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

    public function mount($tipo, $token)
    {
        $this->tipo = strtolower($tipo);

        // Rechazar accesos que utilicen IDs numéricos simples directos (anti-enumeración)
        if (\App\Services\Security\DocumentToken::isPlainNumeric($token)) {
            abort(404);
        }

        $id = \App\Services\Security\DocumentToken::decodeId($token, 'comprobante');
        if (!$id) {
            abort(404);
        }

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
