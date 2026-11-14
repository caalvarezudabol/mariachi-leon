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
    public $por_hora = false;
    public $horas_minimas = '';

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
        'horas_minimas' => 'nullable|numeric|min:0.5|max:24',
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
        $this->reset(['servicio_id', 'nombre', 'descripcion', 'precio_base', 'duracion_minutos', 'isEdit', 'por_hora', 'horas_minimas']);
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
        $this->por_hora = (bool) ($s->por_hora ?? false);
        $this->horas_minimas = $s->horas_minimas ?? '';
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
                'observaciones' => null,
            ]);
            $this->guardarCobroPorHora($s);
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
                'observaciones' => null,
            ]);
            $this->guardarCobroPorHora($s);
            $this->registrarAuditoria('Configuración', 'Crear Servicio', 'Se creó el servicio: ' . $s->nombre);
            session()->flash('success', 'Servicio registrado correctamente.');
        }

        $this->modalOpen = false;
    }

    // Cobro por hora (columnas por_hora y horas_minimas)
    private function guardarCobroPorHora(Servicio $s): void
    {
        $s->forceFill([
            'por_hora' => (bool) $this->por_hora,
            'horas_minimas' => ($this->por_hora && $this->horas_minimas !== '' && $this->horas_minimas !== null)
                ? $this->horas_minimas
                : null,
        ])->save();
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

    private function serviciosQuery()
    {
        return Servicio::query()->when($this->search, function ($q) {
            $q->where(function ($qq) {
                $qq->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('tipo_servicio', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        });
    }

    public function render()
    {
        $servicios = $this->serviciosQuery()
            ->when($this->tipo_filtro, fn($q) => $q->where('tipo_servicio', $this->tipo_filtro))
            ->latest()
            ->paginate(10);

        $tipos = $this->serviciosQuery()
            ->selectRaw("COALESCE(tipo_servicio, 'Musical') as tipo, count(*) as total")
            ->groupBy('tipo')
            ->orderBy('tipo')
            ->pluck('total', 'tipo');

        return view('livewire.configuracion.gestion-servicios', [
            'servicios' => $servicios,
            'totalServicios' => $this->serviciosQuery()->count(),
            'tipos' => $tipos,
        ])->layout('components.layouts.app', [
            'title' => 'Gestión de Servicios - Mariachi León',
            'themed' => true,
        ]);
    }
}
