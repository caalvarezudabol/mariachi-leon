<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\GaleriaItem;
use App\Traits\Auditable;
use App\Services\ImageOptimizerService;
use Illuminate\Support\Str;

class GestionGaleria extends Component
{
    use WithPagination, WithFileUploads, Auditable;

    public const CATEGORIAS = ['Bodas', 'XV Años', 'Serenatas', 'Corporativos', 'Cumpleaños', 'General'];

    public $item_id = null;
    public $titulo = '';
    public $descripcion = '';
    public $tipo = 'foto';
    public $imagen_url = '';
    public $imagen_file = null;
    public $video_url = '';
    public $facebook_url = '';
    public $categoria = 'Bodas';
    public $fecha_evento = '';
    public $destacado = false;
    public $modalOpen = false;
    public $isEdit = false;

    // Filtros del listado
    public $search = '';
    public $tipo_filtro = '';       // '', foto, video, facebook
    public $categoria_filtro = '';

    protected function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:foto,video,facebook',
            'imagen_url' => 'nullable|string',
            'imagen_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'video_url' => 'required_if:tipo,video|nullable|string',
            'facebook_url' => 'required_if:tipo,facebook|nullable|string',
            'categoria' => 'required|string',
            'fecha_evento' => 'required|date',
        ];
    }

    protected $messages = [
        'titulo.required' => 'El título de la publicación es obligatorio.',
        'imagen_file.image' => 'El archivo debe ser una imagen.',
        'imagen_file.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
        'imagen_file.max' => 'La imagen supera el tamaño máximo de 5 MB.',
        'video_url.required_if' => 'Ingrese la URL del video (YouTube/Embed).',
        'facebook_url.required_if' => 'Ingrese el enlace oficial del video o publicación de Facebook.',
        'fecha_evento.required' => 'Seleccione la fecha del evento.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro()
    {
        $this->resetPage();
    }

    public function updatingCategoriaFiltro()
    {
        $this->resetPage();
    }

    public function updatedImagenFile()
    {
        $this->validateOnly('imagen_file');
    }

    public function quitarImagenNueva()
    {
        $this->imagen_file = null;
        $this->resetValidation('imagen_file');
    }

    // Solo se borran del disco las imágenes subidas desde este módulo (no las del sitio)
    private function eliminarImagenSubida(?string $ruta, ImageOptimizerService $optimizer): void
    {
        if ($ruta && !Str::startsWith($ruta, ['http://', 'https://']) && str_contains($ruta, '/galeria/')) {
            $optimizer->eliminarArchivo(ltrim($ruta, '/'));
        }
    }

    public function updatedTipo()
    {
        // Al cambiar el tipo, los errores de URL del tipo anterior ya no aplican
        $this->resetValidation(['imagen_url', 'video_url', 'facebook_url']);
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'tipo_filtro', 'categoria_filtro']);
        $this->resetPage();
    }

    private function galeriaQuery(bool $conTipo = true)
    {
        $query = GaleriaItem::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->categoria_filtro !== '') {
            $query->where('categoria', $this->categoria_filtro);
        }

        if ($conTipo && $this->tipo_filtro !== '') {
            $query->where('tipo', $this->tipo_filtro);
        }

        return $query;
    }

    public function abrirModal()
    {
        $this->reset(['item_id', 'titulo', 'descripcion', 'imagen_url', 'imagen_file', 'video_url', 'facebook_url', 'destacado', 'isEdit']);
        $this->resetValidation();
        $this->tipo = 'foto';
        $this->categoria = 'Bodas';
        $this->fecha_evento = date('Y-m-d');
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $g = GaleriaItem::findOrFail($id);
        $this->resetValidation();
        $this->item_id = $g->id;
        $this->titulo = $g->titulo;
        $this->descripcion = $g->descripcion;
        $this->tipo = $g->tipo;
        $this->imagen_url = $g->imagen_url;
        $this->video_url = $g->video_url;
        $this->facebook_url = $g->facebook_url;
        $this->categoria = $g->categoria;
        $this->fecha_evento = $g->fecha_evento ? $g->fecha_evento->format('Y-m-d') : '';
        $this->destacado = (bool) $g->destacado;
        $this->imagen_file = null;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->imagen_file = null;
        $this->resetValidation();
    }

    public function guardar(?ImageOptimizerService $optimizer = null)
    {
        $optimizer = $optimizer ?: app(ImageOptimizerService::class);

        $this->validate();

        if ($this->tipo !== 'foto') {
            $this->imagen_file = null;
        } elseif (!$this->imagen_file && !$this->imagen_url) {
            $this->addError('imagen_file', 'Seleccione la fotografía.');
            return;
        }

        $imagenAnterior = $this->imagen_url;
        if ($this->imagen_file) {
            $ruta = $optimizer->optimizarYGuardar($this->imagen_file, 'galeria');
            $this->imagen_url = '/' . ltrim($ruta, '/');
            $this->imagen_file = null;
            if ($this->isEdit && $imagenAnterior !== $this->imagen_url) {
                $this->eliminarImagenSubida($imagenAnterior, $optimizer);
            }
        }

        $datos = [
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'tipo' => $this->tipo,
            'imagen_url' => $this->imagen_url,
            'video_url' => $this->video_url,
            'facebook_url' => $this->facebook_url,
            'categoria' => $this->categoria,
            'fecha_evento' => $this->fecha_evento,
            'destacado' => $this->destacado,
        ];

        if ($this->isEdit) {
            $g = GaleriaItem::findOrFail($this->item_id);
            $g->update($datos);
            $this->registrarAuditoria('Galería', 'Editar Publicación', 'Se actualizó la galería: ' . $g->titulo);
            session()->flash('success', 'Galería actualizada correctamente.');
        } else {
            $g = GaleriaItem::create($datos);
            $this->registrarAuditoria('Galería', 'Crear Publicación', 'Se publicó en la galería: ' . $g->titulo);
            session()->flash('success', 'Galería publicada exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function alternarDestacado($id)
    {
        $g = GaleriaItem::findOrFail($id);
        $g->update(['destacado' => !$g->destacado]);

        $this->registrarAuditoria(
            'Galería',
            $g->destacado ? 'Destacar Publicación' : 'Quitar Destacado',
            ($g->destacado ? 'Se destacó' : 'Se quitó el destacado de') . ' la publicación: ' . $g->titulo
        );
        session()->flash('success', $g->destacado ? 'Publicación destacada en la web.' : 'Publicación quitada de destacados.');
    }

    public function eliminar($id)
    {
        $g = GaleriaItem::findOrFail($id);
        $titulo = $g->titulo;
        $imagen = $g->imagen_url;
        $g->delete();
        $this->eliminarImagenSubida($imagen, app(ImageOptimizerService::class));

        $this->registrarAuditoria('Galería', 'Eliminar Publicación', 'Se eliminó la galería: ' . $titulo);
        session()->flash('success', 'Publicación de galería eliminada.');
    }

    public function render()
    {
        $items = $this->galeriaQuery()->latest('fecha_evento')->latest('id')->paginate(10);

        // Contadores de los chips (respetan búsqueda y categoría)
        $porTipo = $this->galeriaQuery(false)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        $categorias = self::CATEGORIAS;
        if ($this->categoria && !in_array($this->categoria, $categorias, true)) {
            $categorias[] = $this->categoria; // categoría antigua que ya no está en la lista
        }

        return view('livewire.admin.gestion-galeria', [
            'items' => $items,
            'categorias' => $categorias,
            'totalFotos' => (int) ($porTipo['foto'] ?? 0),
            'totalVideos' => (int) ($porTipo['video'] ?? 0),
            'totalFacebook' => (int) ($porTipo['facebook'] ?? 0),
        ])->layout('components.layouts.app', [
            'title' => 'Gestión de Galería - Mariachi León',
            'themed' => true,
        ]);
    }
}