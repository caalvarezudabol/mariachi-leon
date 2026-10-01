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
    public $tipo_servicio = 'Musical';
    public $descripcion = '';
    public $precio_base = 0.00;
    public $duracion_minutos = 60;
    public $activo = true;
    public $observaciones = '';

    public $search = '';
    public $tipo_filtro = '';
    public $modalOpen = false;
    public $isEdit = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'tipo_servicio' => 'required|string|max:100',
        'descripcion' => 'nullable|string',
        'precio_base' => 'required|numeric|min:0',
        'duracion_minutos' => 'required|integer|min:15',
        'observaciones' => 'nullable|string',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro()
    {
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->reset(['servicio_id', 'nombre', 'descripcion', 'precio_base', 'duracion_minutos', 'observaciones', 'isEdit']);
        $this->tipo_servicio = 'Musical';
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $s = Servicio::findOrFail($id);
        $this->servicio_id = $s->id;
        $this->nombre = $s->nombre;
        $this->tipo_servicio = $s->tipo_servicio ?: 'Musical';
        $this->descripcion = $s->descripcion;
        $this->precio_base = $s->precio_base;
        $this->duracion_minutos = $s->duracion_minutos;
        $this->activo = $s->activo;
        $this->observaciones = $s->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $s = Servicio::findOrFail($this->servicio_id);
            $s->update([
                'nombre' => trim($this->nombre),
                'tipo_servicio' => trim($this->tipo_servicio),
                'descripcion' => trim($this->descripcion),
                'precio_base' => $this->precio_base,
                'duracion_minutos' => $this->duracion_minutos,
                'activo' => $this->activo,
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Configuración', 'Editar Servicio', 'Se actualizó el servicio: ' . $s->nombre);
            session()->flash('success', 'Servicio actualizado correctamente.');
        } else {
            $s = Servicio::create([
                'nombre' => trim($this->nombre),
                'tipo_servicio' => trim($this->tipo_servicio),
                'descripcion' => trim($this->descripcion),
                'precio_base' => $this->precio_base,
                'duracion_minutos' => $this->duracion_minutos,
                'activo' => $this->activo,
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Configuración', 'Crear Servicio', 'Se creó el servicio: ' . $s->nombre);
            session()->flash('success', 'Servicio registrado correctamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id)
    {
        $s = Servicio::findOrFail($id);
        $s->update(['activo' => !$s->activo]);
        $this->registrarAuditoria('Configuración', 'Cambiar Estado Servicio', 'Se modificó estado del servicio: ' . $s->nombre);
        session()->flash('success', 'Estado del servicio actualizado correctamente.');
    }

    public function eliminar($id)
    {
        $s = Servicio::findOrFail($id);
        $nombre = $s->nombre;
        $s->delete();

        $this->registrarAuditoria('Configuración', 'Eliminar Servicio', 'Se eliminó el servicio: ' . $nombre);
        session()->flash('success', 'Servicio eliminado correctamente.');
    }

    public function render()
    {
        $query = Servicio::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo_servicio', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo_filtro) {
            $query->where('tipo_servicio', $this->tipo_filtro);
        }

        $servicios = $query->latest()->paginate(10);

        return view('livewire.configuracion.gestion-servicios', [
            'servicios' => $servicios,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Servicios - Mariachi León']);
    }
}
