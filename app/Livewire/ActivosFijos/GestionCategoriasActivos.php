<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AssetCategory;
use App\Traits\Auditable;

class GestionCategoriasActivos extends Component
{
    use WithPagination, Auditable;

    public $categoria_id = null;
    public $codigo = '';
    public $nombre = '';
    public $descripcion = '';
    public $activo = true;
    public $search = '';
    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'codigo' => 'required|string|max:50|unique:asset_categories,codigo,' . ($this->categoria_id ?? 'NULL') . ',id',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'codigo.required' => 'El código de la categoría es obligatorio.',
        'codigo.unique' => 'Este código de categoría ya está en uso.',
        'nombre.required' => 'El nombre de la categoría es obligatorio.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->reset(['categoria_id', 'codigo', 'nombre', 'descripcion', 'isEdit']);
        $this->activo = true;
        // Auto-generar sugerencia de código
        $count = AssetCategory::count() + 1;
        $this->codigo = 'CAT-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $cat = AssetCategory::findOrFail($id);
        $this->categoria_id = $cat->id;
        $this->codigo = $cat->codigo;
        $this->nombre = $cat->nombre;
        $this->descripcion = $cat->descripcion;
        $this->activo = $cat->activo;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $cat = AssetCategory::findOrFail($this->categoria_id);
            $cat->update([
                'codigo' => strtoupper(trim($this->codigo)),
                'nombre' => trim($this->nombre),
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Activos Fijos', 'Editar Categoría', 'Se actualizó la categoría: ' . $cat->nombre);
            session()->flash('success', 'Categoría de activo actualizada correctamente.');
        } else {
            $cat = AssetCategory::create([
                'codigo' => strtoupper(trim($this->codigo)),
                'nombre' => trim($this->nombre),
                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Activos Fijos', 'Crear Categoría', 'Se creó la categoría: ' . $cat->nombre);
            session()->flash('success', 'Categoría de activo registrada exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function toggleEstado($id)
    {
        $cat = AssetCategory::findOrFail($id);
        $cat->activo = !$cat->activo;
        $cat->save();

        $estadoStr = $cat->activo ? 'activó' : 'desactivó';
        $this->registrarAuditoria('Activos Fijos', 'Cambiar Estado Categoría', 'Se ' . $estadoStr . ' la categoría: ' . $cat->nombre);
        session()->flash('success', 'Estado de categoría actualizado.');
    }

    public function render()
    {
        $query = AssetCategory::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%');
            });
        }

        $categorias = $query->orderBy('nombre', 'asc')->paginate(10);

        return view('livewire.activos-fijos.gestion-categorias-activos', [
            'categorias' => $categorias,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Categorías - Activos Fijos']);
    }
}
