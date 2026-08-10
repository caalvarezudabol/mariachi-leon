<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Paquete;
use App\Models\Servicio;
use App\Traits\Auditable;

class GestionPaquetes extends Component
{
    use WithPagination, Auditable;

    public $paquete_id = null;
    public $nombre = '';
    public $descripcion = '';
    public $precio_paquete = 0.00;
    public $destacado = false;
    public $activo = true;
    public $selectedServicios = [];
    public $modalOpen = false;
    public $isEdit = false;

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'descripcion' => 'nullable|string',
        'precio_paquete' => 'required|numeric|min:0',
    ];

    public function abrirModal()
    {
        $this->reset(['paquete_id', 'nombre', 'descripcion', 'precio_paquete', 'destacado', 'selectedServicios', 'isEdit']);
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $p = Paquete::with('servicios')->findOrFail($id);
        $this->paquete_id = $p->id;
        $this->nombre = $p->nombre;
        $this->descripcion = $p->descripcion;
        $this->precio_paquete = $p->precio_paquete;
        $this->destacado = $p->destacado;
        $this->activo = $p->activo;
        $this->selectedServicios = $p->servicios->pluck('id')->toArray();
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $p = Paquete::findOrFail($this->paquete_id);
            $p->update([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio_paquete' => $this->precio_paquete,
                'destacado' => $this->destacado,
                'activo' => $this->activo,
            ]);
            $p->servicios()->sync($this->selectedServicios);
            $this->registrarAuditoria('Configuración', 'Editar Paquete', 'Se actualizó el paquete: ' . $p->nombre);
            session()->flash('success', 'Paquete actualizado correctamente.');
        } else {
            $p = Paquete::create([
                'nombre' => $this->nombre,
                'descripcion' => $this->descripcion,
                'precio_paquete' => $this->precio_paquete,
                'destacado' => $this->destacado,
                'activo' => $this->activo,
            ]);
            $p->servicios()->sync($this->selectedServicios);
            $this->registrarAuditoria('Configuración', 'Crear Paquete', 'Se creó el paquete: ' . $p->nombre);
            session()->flash('success', 'Paquete registrado correctamente.');
        }

        $this->modalOpen = false;
    }

    public function eliminar($id)
    {
        $p = Paquete::findOrFail($id);
        $nombre = $p->nombre;
        $p->delete();

        $this->registrarAuditoria('Configuración', 'Eliminar Paquete', 'Se eliminó (Soft Delete) el paquete: ' . $nombre);
        session()->flash('success', 'Paquete eliminado correctamente.');
    }

    public function render()
    {
        $paquetes = Paquete::with('servicios')->latest()->paginate(10);
        $allServicios = Servicio::where('activo', true)->get();

        return view('livewire.configuracion.gestion-paquetes', [
            'paquetes' => $paquetes,
            'allServicios' => $allServicios,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Paquetes - Mariachi León']);
    }
}
