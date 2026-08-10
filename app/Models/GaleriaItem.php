<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GaleriaItem extends Model
{
    protected $table = 'galeria_items';

    protected $fillable = [
        'titulo',
        'descripcion',
        'tipo',
        'imagen_url',
        'video_url',
        'facebook_url',
        'categoria',
        'fecha_evento',
        'destacado',
    ];

    protected $casts = [
        'fecha_evento' => 'date',
        'destacado' => 'boolean',
    ];

    public function getFacebookEmbedUrlAttribute(): string
    {
        $url = $this->facebook_url ?: $this->video_url;
        if (!$url) return '';

        return 'https://www.facebook.com/plugins/video.php?height=314&href=' . urlencode($url) . '&show_text=false';
    }
}
