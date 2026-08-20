<?php

namespace App\Livewire\ActivosFijos;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\MusicoPersonal;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;

class GestionArticulosActivos extends Component
{
    use WithPagination, Auditable;

    public $asset_id = null;
    public $codigo = '';
    public $nombre = '';
    public $asset_category_id = '';
    public $descripcion = '';
    public $marca = '';
    public $modelo = '';
    public $numero_serie = '';
    public $fecha_adquisicion = '';
    public $costo_adquisicion = 0;
    public $tipo_control = 'individual';
    public $existencia = 1;
    public $costo_promedio_ppp = 0;
    public $estado = 'disponible';
    public $responsable_id = '';
    public $observaciones = '';

    // Filtros
    public $search = '';
    public $categoria_filtro = '';
    public $estado_filtro = '';
    public $tipo_control_filtro = '';

    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'codigo' => 'required|string|max:50|unique:assets,codigo,' . ($this->asset_id ?? 'NULL') . ',id',
            'nombre' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'descripcion' => 'nullable|string',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100|unique:assets,numero_serie,' . ($this->asset_id ?? 'NULL') . ',id',
            'fecha_adquisicion' => 'nullable|date',
            'costo_adquisicion' => 'required|numeric|min:0',
            'tipo_control' => 'required|in:individual,cantidad',
            'existencia' => 'required|numeric|min:0',
            'estado' => 'required|in:disponible,asignado,en_mantenimiento,deteriorado,perdido,dado_de_baja',
            'responsable_id' => 'nullable|exists:musicos_personal,id',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'codigo.required' => 'El código del artículo es obligatorio.',
        'codigo.unique' => 'Este código AF ya pertenece a otro artículo.',
        'nombre.required' => 'El nombre del artículo es obligatorio.',
        'asset_category_id.required' => 'Seleccione una categoría.',
        'numero_serie.unique' => 'Este número de serie ya está registrado en el sistema.',
        'costo_adquisicion.min' => 'El costo de adquisición debe ser un monto positivo.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoriaFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function updatingTipoControlFiltro()
    {
        $this->resetPage();
    }

    public function generarCodigoAutomatico()
    {
        $last = Asset::orderBy('id', 'desc')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        return 'AF-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    public function abrirModal()
    {
        $this->reset([
            'asset_id', 'codigo', 'nombre', 'asset_category_id', 'descripcion',
            'marca', 'modelo', 'numero_serie', 'fecha_adquisicion', 'costo_adquisicion',
            'tipo_control', 'existencia', 'costo_promedio_ppp', 'estado', 'responsable_id',
            'observaciones', 'isEdit'
        ]);
        $this->codigo = $this->generarCodigoAutomatico();
        $this->fecha_adquisicion = date('Y-m-d');
        $this->tipo_control = 'individual';
        $this->existencia = 1;
        $this->costo_adquisicion = 0;
        $this->estado = 'disponible';
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $asset = Asset::findOrFail($id);
        $this->asset_id = $asset->id;
        $this->codigo = $asset->codigo;
        $this->nombre = $asset->nombre;
        $this->asset_category_id = $asset->asset_category_id;
        $this->descripcion = $asset->descripcion;
        $this->marca = $asset->marca;
        $this->modelo = $asset->modelo;
        $this->numero_serie = $asset->numero_serie;
        $this->fecha_adquisicion = $asset->fecha_adquisicion ? $asset->fecha_adquisicion->format('Y-m-d') : '';
        $this->costo_adquisicion = $asset->costo_adquisicion;
        $this->tipo_control = $asset->tipo_control;
        $this->existencia = $asset->existencia;
        $this->costo_promedio_ppp = $asset->costo_promedio_ppp;
        $this->estado = $asset->estado;
        $this->responsable_id = $asset->responsable_id;
        $this->observaciones = $asset->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        // Para control por cantidad, el PPP inicial es igual al costo de adquisición
        $costoPpp = ($this->tipo_control === 'cantidad') ? $this->costo_adquisicion : $this->costo_promedio_ppp;

        if ($this->isEdit) {
            $asset = Asset::findOrFail($this->asset_id);
            $asset->update([
                'codigo' => strtoupper(trim($this->codigo)),
                'nombre' => trim($this->nombre),
                'asset_category_id' => $this->asset_category_id,
                'descripcion' => $this->descripcion,
                'marca' => $this->marca,
                'modelo' => $this->modelo,
                'numero_serie' => $this->numero_serie ? trim($this->numero_serie) : null,
                'fecha_adquisicion' => $this->fecha_adquisicion ?: null,
                'costo_adquisicion' => $this->costo_adquisicion,
                'tipo_control' => $this->tipo_control,
                'existencia' => $this->existencia,
                'costo_promedio_ppp' => $costoPpp,
                'estado' => $this->estado,
                'responsable_id' => $this->responsable_id ?: null,
                'observaciones' => $this->observaciones,
            ]);
            $this->registrarAuditoria('Activos Fijos', 'Editar Artículo', 'Se actualizó el activo ' . $asset->codigo . ': ' . $asset->nombre);
            session()->flash('success', 'Artículo / Activo actualizado correctamente.');
        } else {
            $asset = Asset::create([
                'codigo' => strtoupper(trim($this->codigo)),
                'nombre' => trim($this->nombre),
                'asset_category_id' => $this->asset_category_id,
                'descripcion' => $this->descripcion,
                'marca' => $this->marca,
                'modelo' => $this->modelo,
                'numero_serie' => $this->numero_serie ? trim($this->numero_serie) : null,
                'fecha_adquisicion' => $this->fecha_adquisicion ?: null,
                'costo_adquisicion' => $this->costo_adquisicion,
                'tipo_control' => $this->tipo_control,
                'existencia' => $this->existencia,
                'costo_promedio_ppp' => $costoPpp,
                'estado' => $this->estado,
                'responsable_id' => $this->responsable_id ?: null,
                'user_id' => Auth::id(),
                'observaciones' => $this->observaciones,
            ]);
            $this->registrarAuditoria('Activos Fijos', 'Crear Artículo', 'Se registró el activo ' . $asset->codigo . ': ' . $asset->nombre);
            session()->flash('success', 'Artículo / Activo registrado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function render()
    {
        $query = Asset::with(['category', 'responsable', 'user']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_serie', 'like', '%' . $this->search . '%')
                  ->orWhere('marca', 'like', '%' . $this->search . '%')
                  ->orWhere('modelo', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoria_filtro) {
            $query->where('asset_category_id', $this->categoria_filtro);
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        if ($this->tipo_control_filtro) {
            $query->where('tipo_control', $this->tipo_control_filtro);
        }

        $articulos = $query->orderBy('codigo', 'asc')->paginate(10);
        $categorias = AssetCategory::where('activo', true)->orderBy('nombre', 'asc')->get();
        $responsables = MusicoPersonal::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();

        return view('livewire.activos-fijos.gestion-articulos-activos', [
            'articulos' => $articulos,
            'categorias' => $categorias,
            'responsables' => $responsables,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Artículos - Activos Fijos']);
    }
}
