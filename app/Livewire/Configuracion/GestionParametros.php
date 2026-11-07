<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use App\Models\Configuracion;
use App\Traits\Auditable;

class GestionParametros extends Component
{
    use Auditable;

    /**
     * Claves que se editan en Configuración > Empresa y que Empresa copia a la tabla
     * `configuraciones` al guardar. Aquí NO se muestran ni se guardan, para que el mismo dato
     * no se pueda editar en dos pantallas distintas.
     */
    public const CLAVES_DE_EMPRESA = [
        'nombre_agrupacion',
        'moneda_simbolo',
        'moneda_nombre',
        'redes_linktree',
        'redes_facebook',
        'redes_instagram',
        'redes_tiktok',
        'redes_youtube',
        'telefono_contacto',
        'email_contacto',
        'direccion_oficina',
        'terminos_contrato',
        'logo_url',
        'banco_qr_url',
    ];

    public $config = [];

    private function parametrosEditables()
    {
        return Configuracion::whereNotIn('clave', self::CLAVES_DE_EMPRESA)
            ->orderBy('grupo')
            ->orderBy('id')
            ->get();
    }

    public function mount()
    {
        foreach ($this->parametrosEditables() as $c) {
            $this->config[$c->clave] = $c->valor;
        }
    }

    public function guardar()
    {
        $editables = $this->parametrosEditables()->keyBy('clave');

        // Validaciones simples: los porcentajes deben ser números entre 0 y 100
        $reglas = [];
        $mensajes = [];
        foreach ($editables as $clave => $param) {
            if (str_contains($clave, 'porcentaje')) {
                $reglas["config.$clave"] = 'required|numeric|min:0|max:100';
                $mensajes["config.$clave.required"] = 'El porcentaje es obligatorio.';
                $mensajes["config.$clave.numeric"] = 'El porcentaje debe ser un número.';
                $mensajes["config.$clave.min"] = 'El porcentaje no puede ser negativo.';
                $mensajes["config.$clave.max"] = 'El porcentaje no puede ser mayor a 100.';
            }
        }
        if ($reglas) {
            $this->validate($reglas, $mensajes);
        }

        // Solo se guardan los parámetros que se muestran aquí (nunca los de Empresa)
        $cambios = [];
        foreach ($editables as $clave => $param) {
            if (!array_key_exists($clave, $this->config)) {
                continue;
            }
            $nuevo = $this->config[$clave];
            if ((string) $param->valor !== (string) $nuevo) {
                $param->update(['valor' => $nuevo]);
                $cambios[] = $clave;
            }
        }

        $this->registrarAuditoria(
            'Configuración',
            'Actualizar Parámetros',
            $cambios
                ? 'Se actualizaron los parámetros: ' . implode(', ', $cambios) . '.'
                : 'Se guardaron los parámetros generales del sistema sin cambios.'
        );
        session()->flash('success', 'Parámetros del sistema guardados correctamente.');
    }

    public function render()
    {
        $parametros = $this->parametrosEditables()->groupBy('grupo');

        return view('livewire.configuracion.gestion-parametros', [
            'parametros' => $parametros,
            'totalParametros' => $parametros->flatten()->count(),
        ])->layout('components.layouts.app', [
            'title' => 'Gestión de Parámetros - Mariachi León',
            'themed' => true,
        ]);
    }
}