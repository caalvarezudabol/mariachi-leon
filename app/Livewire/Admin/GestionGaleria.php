<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\GaleriaItem;
use App\Traits\Auditable;

class GestionGaleria extends Component
{
    use WithPagination, Auditable;

    public $item_id = null;
    public $titulo = '';
    public $descripcion = '';
    public $tipo = 'foto';
    public $imagen_url = '';
    public $video_url = '';
    public $facebook_url = '';
    public $categoria = 'General';
    public $fecha_evento = '';
    public $destacado = false;
    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:foto,video,facebook',
            'imagen_url' => 'required_if:tipo,foto|nullable|string',
            'video_url' => 'required_if:tipo,video|nullable|string',
            'facebook_url' => 'required_if:tipo,facebook|nullable|string',
            'categoria' => 'required|string',
            'fecha_evento' => 'required|date',
        ];
    }

    protected $messages = [
        'titulo.required' => 'El título de la publicación es obligatorio.',
        'imagen_url.required_if' => 'Ingrese la URL de la imagen.',
        'video_url.required_if' => 'Ingrese la URL del video (YouTube/Embed).',
        'facebook_url.required_if' => 'Ingrese el enlace oficial del video o publicación de Facebook.',
        'fecha_evento.required' => 'Seleccione la fecha del evento.',
    ];

    public function abrirModal()
    {
        $this->reset(['item_id', 'titulo', 'descripcion', 'tipo', 'imagen_url', 'video_url', 'facebook_url', 'categoria', 'fecha_evento', 'destacado', 'isEdit']);
        $this->tipo = 'foto';
        $this->categoria = 'Bodas';
        $this->fecha_evento = date('Y-m-d');
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $g = GaleriaItem::findOrFail($id);
        $this->item_id = $g->id;
        $this->titulo = $g->titulo;
        $this->descripcion = $g->descripcion;
        $this->tipo = $g->tipo;
        $this->imagen_url = $g->imagen_url;
        $this->video_url = $g->video_url;
        $this->facebook_url = $g->facebook_url;
        $this->categoria = $g->categoria;
        $this->fecha_evento = $g->fecha_evento ? $g->fecha_evento->format('Y-m-d') : '';
        $this->destacado = $g->destacado;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $g = GaleriaItem::findOrFail($this->item_id);
            $g->update([
                'titulo' => $this->titulo,
                'descripcion' => $this->descripcion,
                'tipo' => $this->tipo,
                'imagen_url' => $this->imagen_url,
                'video_url' => $this->video_url,
                'facebook_url' => $this->facebook_url,
                'categoria' => $this->categoria,
                'fecha_evento' => $this->fecha_evento,
                'destacado' => $this->destacado,
            ]);
            $this->registrarAuditoria('Galería', 'Editar Publicación', 'Se actualizó la galería: ' . $g->titulo);
            session()->flash('success', 'Galería actualizada correctamente.');
        } else {
            $g = GaleriaItem::create([
                'titulo' => $this->titulo,
                'descripcion' => $this->descripcion,
                'tipo' => $this->tipo,
                'imagen_url' => $this->imagen_url,
                'video_url' => $this->video_url,
                'facebook_url' => $this->facebook_url,
                'categoria' => $this->categoria,
                'fecha_evento' => $this->fecha_evento,
                'destacado' => $this->destacado,
            ]);
            $this->registrarAuditoria('Galería', 'Crear Publicación', 'Se publicó en la galería: ' . $g->titulo);
            session()->flash('success', 'Galería publicada exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function eliminar($id)
    {
        $g = GaleriaItem::findOrFail($id);
        $titulo = $g->titulo;
        $g->delete();

        $this->registrarAuditoria('Galería', 'Eliminar Publicación', 'Se eliminó la galería: ' . $titulo);
        session()->flash('success', 'Publicación de galería eliminada.');
    }

    public function render()
    {
        $items = GaleriaItem::latest('fecha_evento')->paginate(10);

        return view('livewire.admin.gestion-galeria', [
            'items' => $items,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Galería - Mariachi León']);
    }
}
