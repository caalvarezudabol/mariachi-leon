<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Servicio;
use App\Traits\Auditable;

class GestionServicios extends Component
{
    use WithPagination, Auditable;

    public $servicio_id = null;
    public $nombre = '';
    public $descripcion = '';
    public $precio_base = 0.00;
    public $duracion_minutos = 60;
    public $activo = true;
    public $modalOpen = false;
    public $isEdit = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio_base' => 'required|numeric|min:0',
        'duracion_minutos' => 'required|integer|min:15',
    ];

    public function abrirModal()
    {
        $this->reset(['servicio_id', 'nombre', 'descripcion', 'precio_base', 'duracion_minutos', 'isEdit']);
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $s = Servicio::findOrFail($id);
        $this->servicio_id = $s->id;
        $this->nombre = $s->nombre;
        $this->descripcion = $s->descripcion;
        $this->precio_base = $s->precio_base;
        $this->duracion_minutos = $s->duracion_minutos;
        $this->activo = $s->activo;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $s = Servicio::findOrFail($this->servicio_id);
            $s->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio_base' => $this->precio_base,
                'duracion_minutos' => $this->duracion_minutos,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Configuración', 'Editar Servicio', 'Se actualizó el servicio: ' . $s->nombre);
            session()->flash('success', 'Servicio actualizado correctamente.');
        } else {
            $s = Servicio::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio_base' => $this->precio_base,
                'duracion_minutos' => $this->duracion_minutos,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Configuración', 'Crear Servicio', 'Se creó el servicio: ' . $s->nombre);
            session()->flash('success', 'Servicio registrado correctamente.');
        }

        $this->modalOpen = false;
    }

    public function eliminar($id)
    {
        $s = Servicio::findOrFail($id);
        $nombre = $s->nombre;
        $s->delete();

        $this->registrarAuditoria('Configuración', 'Eliminar Servicio', 'Se eliminó (Soft Delete) el servicio: ' . $nombre);
        session()->flash('success', 'Servicio eliminado correctamente.');
    }

    public function render()
    {
        $servicios = Servicio::latest()->paginate(10);

        return view('livewire.configuracion.gestion-servicios', [
            'servicios' => $servicios,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Servicios - Mariachi León']);
    }
}
