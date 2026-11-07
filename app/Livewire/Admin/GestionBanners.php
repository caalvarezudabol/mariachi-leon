<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use App\Models\Banner;
use App\Traits\Auditable;
use App\Services\ImageOptimizerService;
use Illuminate\Support\Str;

class GestionBanners extends Component
{
    use WithPagination, WithFileUploads, Auditable;

    public $banner_id = null;
    public $titulo = '';
    public $subtitulo = '';
    public $imagen_url = '';
    public $imagen_file = null;
    public $boton_texto = 'Cotizar Tu Evento';
    public $boton_link = '#cotizar';
    public $orden = 1;
    public $activo = true;
    public $modalOpen = false;
    public $isEdit = false;

    // Filtros del listado
    public $search = '';
    public $estado_filtro = ''; // '', '1' (activos), '0' (inactivos)

    protected $rules = [
        'titulo' => 'required|string|max:255',
        'subtitulo' => 'nullable|string',
        'imagen_url' => 'nullable|string',
        'imagen_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        'boton_texto' => 'nullable|string',
        'boton_link' => 'nullable|string',
        'orden' => 'required|integer|min:1',
    ];

    protected $messages = [
        'titulo.required' => 'El título del banner es obligatorio.',
        'imagen_file.image' => 'El archivo debe ser una imagen.',
        'imagen_file.mimes' => 'La imagen debe ser JPG, PNG o WEBP.',
        'imagen_file.max' => 'La imagen supera el tamaño máximo de 5 MB.',
        'orden.required' => 'Ingrese el número de orden.',
        'orden.min' => 'El orden debe ser 1 o mayor.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    private function bannersQuery(bool $conEstado = true)
    {
        $query = Banner::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('titulo', 'like', '%' . $this->search . '%')
                  ->orWhere('subtitulo', 'like', '%' . $this->search . '%');
            });
        }

        if ($conEstado && $this->estado_filtro !== '') {
            $query->where('activo', (bool) $this->estado_filtro);
        }

        return $query;
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
        if ($ruta && !Str::startsWith($ruta, ['http://', 'https://']) && str_contains($ruta, '/banners/')) {
            $optimizer->eliminarArchivo(ltrim($ruta, '/'));
        }
    }

    public function abrirModal()
    {
        $this->reset(['banner_id', 'titulo', 'subtitulo', 'imagen_url', 'imagen_file', 'isEdit']);
        $this->resetValidation();
        $this->boton_texto = 'Cotizar Tu Evento';
        $this->boton_link = '#cotizar';
        $this->orden = Banner::count() + 1;
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $b = Banner::findOrFail($id);
        $this->resetValidation();
        $this->banner_id = $b->id;
        $this->titulo = $b->titulo;
        $this->subtitulo = $b->subtitulo;
        $this->imagen_url = $b->imagen_url;
        $this->boton_texto = $b->boton_texto;
        $this->boton_link = $b->boton_link;
        $this->orden = $b->orden;
        $this->activo = (bool) $b->activo;
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

        if (!$this->imagen_file && !$this->imagen_url) {
            $this->addError('imagen_file', 'Seleccione la imagen del banner.');
            return;
        }

        $imagenAnterior = $this->imagen_url;
        if ($this->imagen_file) {
            $ruta = $optimizer->optimizarYGuardar($this->imagen_file, 'banners');
            $this->imagen_url = '/' . ltrim($ruta, '/');
            $this->imagen_file = null;
            if ($this->isEdit && $imagenAnterior !== $this->imagen_url) {
                $this->eliminarImagenSubida($imagenAnterior, $optimizer);
            }
        }

        if ($this->isEdit) {
            $b = Banner::findOrFail($this->banner_id);
            $b->update([
                'titulo' => $this->titulo,
                'subtitulo' => $this->subtitulo,
                'imagen_url' => $this->imagen_url,
                'boton_texto' => $this->boton_texto,
                'boton_link' => $this->boton_link,
                'orden' => $this->orden,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Sitio Web', 'Editar Banner', 'Se actualizó el banner: ' . $b->titulo);
            session()->flash('success', 'Banner de inicio actualizado correctamente.');
        } else {
            $b = Banner::create([
                'titulo' => $this->titulo,
                'subtitulo' => $this->subtitulo,
                'imagen_url' => $this->imagen_url,
                'boton_texto' => $this->boton_texto,
                'boton_link' => $this->boton_link,
                'orden' => $this->orden,
                'activo' => $this->activo,
            ]);
            $this->registrarAuditoria('Sitio Web', 'Crear Banner', 'Se creó el banner: ' . $b->titulo);
            session()->flash('success', 'Banner de inicio registrado correctamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id)
    {
        $b = Banner::findOrFail($id);
        $b->update(['activo' => !$b->activo]);

        $this->registrarAuditoria(
            'Sitio Web',
            $b->activo ? 'Activar Banner' : 'Desactivar Banner',
            ($b->activo ? 'Se activó' : 'Se desactivó') . ' el banner: ' . $b->titulo
        );
        session()->flash('success', $b->activo ? 'Banner activado: ya se muestra en el sitio.' : 'Banner desactivado: se oculta del sitio.');
    }

    public function eliminar($id)
    {
        $b = Banner::findOrFail($id);
        $titulo = $b->titulo;
        $imagen = $b->imagen_url;
        $b->delete();
        $this->eliminarImagenSubida($imagen, app(ImageOptimizerService::class));

        $this->registrarAuditoria('Sitio Web', 'Eliminar Banner', 'Se eliminó el banner: ' . $titulo);
        session()->flash('success', 'Banner de inicio eliminado.');
    }

    public function render()
    {
        $banners = $this->bannersQuery()->orderBy('orden', 'asc')->paginate(10);

        // Contadores para los chips (respetan la búsqueda)
        $estados = $this->bannersQuery(false)
            ->selectRaw('activo, COUNT(*) as total')
            ->groupBy('activo')
            ->pluck('total', 'activo');

        return view('livewire.admin.gestion-banners', [
            'banners' => $banners,
            'totalActivos' => (int) ($estados[1] ?? 0),
            'totalInactivos' => (int) ($estados[0] ?? 0),
        ])->layout('components.layouts.app', [
            'title' => 'Gestión de Banner de Inicio - Mariachi León',
            'themed' => true,
        ]);
    }
}