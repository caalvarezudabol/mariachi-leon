<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TipoEvento;
use App\Traits\Auditable;

class GestionTiposEvento extends Component
{
    use WithPagination, Auditable;

    public $tipo_id = null;
    public $nombre = '';
    public $descripcion = '';
    public $precio_sugerido = 0.00;
    public $activo = true;
    public $modalOpen = false;
    public $isEdit = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio_sugerido' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre del tipo de evento es obligatorio.',
        'precio_sugerido.required' => 'Ingrese el precio sugerido.',
        'precio_sugerido.numeric' => 'El precio debe ser numérico.',
    ];

    public function abrirModal()
    {
        $this->reset(['tipo_id', 'nombre', 'descripcion', 'precio_sugerido', 'isEdit']);
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $t = TipoEvento::findOrFail($id);
        $this->tipo_id = $t->id;
        $this->nombre = $t->nombre;
        $this->descripcion = $t->descripcion;
        $this->precio_sugerido = $t->precio_sugerido;
        $this->activo = $t->activo;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $t = TipoEvento::findOrFail($this->tipo_id);
            $t->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio_sugerido' => $this->precio_sugerido,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Configuración', 'Editar Tipo Evento', 'Se actualizó el tipo de evento: ' . $t->nombre);
            session()->flash('success', 'Tipo de evento actualizado.');
        } else {
            $t = TipoEvento::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio_sugerido' => $this->precio_sugerido,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Configuración', 'Crear Tipo Evento', 'Se creó el tipo de evento: ' . $t->nombre);
            session()->flash('success', 'Tipo de evento registrado.');
        }

        $this->modalOpen = false;
    }

    public function eliminar($id)
    {
        $t = TipoEvento::findOrFail($id);
        $nombre = $t->nombre;
        $t->delete();

        $this->registrarAuditoria('Configuración', 'Eliminar Tipo Evento', 'Se eliminó (Soft Delete) el tipo de evento: ' . $nombre);
        session()->flash('success', 'Tipo de evento eliminado.');
    }

    public function render()
    {
        $tipos = TipoEvento::latest()->paginate(10);

        return view('livewire.configuracion.gestion-tipos-evento', [
            'tipos' => $tipos,
        ])->layout('components.layouts.app', ['title' => 'Tipos de Evento - Mariachi León']);
    }
}
