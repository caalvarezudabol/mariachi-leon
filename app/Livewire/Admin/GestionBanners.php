<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Banner;
use App\Traits\Auditable;

class GestionBanners extends Component
{
    use WithPagination, Auditable;

    public $banner_id = null;
    public $titulo = '';
    public $subtitulo = '';
    public $imagen_url = '';
    public $boton_texto = 'Cotizar Tu Evento';
    public $boton_link = '#cotizar';
    public $orden = 1;
    public $activo = true;
    public $modalOpen = false;
    public $isEdit = false;

    protected $rules = [
        'titulo' => 'required|string|max:255',
        'subtitulo' => 'nullable|string',
        'imagen_url' => 'required|string',
        'boton_texto' => 'nullable|string',
        'boton_link' => 'nullable|string',
        'orden' => 'required|integer|min:1',
    ];

    protected $messages = [
        'titulo.required' => 'El título del banner es obligatorio.',
        'imagen_url.required' => 'Ingrese la URL de la imagen del banner.',
        'orden.required' => 'Ingrese el número de orden.',
    ];

    public function abrirModal()
    {
        $this->reset(['banner_id', 'titulo', 'subtitulo', 'imagen_url', 'boton_texto', 'boton_link', 'isEdit']);
        $this->boton_texto = 'Cotizar Tu Evento';
        $this->boton_link = '#cotizar';
        $this->orden = Banner::count() + 1;
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $b = Banner::findOrFail($id);
        $this->banner_id = $b->id;
        $this->titulo = $b->titulo;
        $this->subtitulo = $b->subtitulo;
        $this->imagen_url = $b->imagen_url;
        $this->boton_texto = $b->boton_texto;
        $this->boton_link = $b->boton_link;
        $this->orden = $b->orden;
        $this->activo = $b->activo;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

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

    public function eliminar($id)
    {
        $b = Banner::findOrFail($id);
        $titulo = $b->titulo;
        $b->delete();

        $this->registrarAuditoria('Sitio Web', 'Eliminar Banner', 'Se eliminó el banner: ' . $titulo);
        session()->flash('success', 'Banner de inicio eliminado.');
    }

    public function render()
    {
        $banners = Banner::orderBy('orden', 'asc')->paginate(10);

        return view('livewire.admin.gestion-banners', [
            'banners' => $banners,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Banner de Inicio - Mariachi León']);
    }
}
