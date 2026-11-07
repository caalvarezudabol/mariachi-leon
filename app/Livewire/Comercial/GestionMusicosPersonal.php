<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MusicoPersonal;
use App\Support\Texto;
use App\Traits\Auditable;
use Illuminate\Validation\Rule;

class GestionMusicosPersonal extends Component
{
    use WithPagination, Auditable;

    // Tipos habituales (el campo "tipo" es texto libre en la base de datos)
    public const TIPOS = ['Músico', 'Transporte', 'Sonido', 'Técnico', 'Personal'];

    public $persona_id = null;
    public $nombre_completo = '';
    public $tipo = 'Músico';
    public $telefono = '';
    public $ci_nit = '';
    public $modalidad_pago = 'por_evento';
    public $tarifa_evento = '';
    public $sueldo_mensual = '';
    public $va_en_todos_eventos = false;
    public $estado = 'Activo';
    public $observaciones = '';

    public $modalOpen = false;
    public $isEdit = false;

    // Filtros del listado
    public $search = '';
    public $tipo_filtro = '';
    public $estado_filtro = ''; // '', 'Activo', 'Inactivo'

    protected function rules()
    {
        return [
            'nombre_completo' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:50',
            'ci_nit' => 'nullable|string|max:50',
            'modalidad_pago' => ['required', Rule::in(['por_evento', 'mensual'])],
            'tarifa_evento' => [
                Rule::requiredIf($this->modalidad_pago === 'por_evento'),
                'nullable',
                'numeric',
                'min:0',
                'max:99999999',
            ],
            'sueldo_mensual' => [
                Rule::requiredIf($this->modalidad_pago === 'mensual'),
                'nullable',
                'numeric',
                'min:0',
                'max:99999999',
            ],
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'nombre_completo.required' => 'Ingrese el nombre de la persona.',
        'tipo.required' => 'Seleccione el tipo.',
        'tarifa_evento.required' => 'Ingrese cuánto cobra por evento (puede ser un monto aproximado).',
        'sueldo_mensual.required' => 'Ingrese el sueldo mensual.',
        'tarifa_evento.numeric' => 'La tarifa debe ser un número.',
        'sueldo_mensual.numeric' => 'El sueldo debe ser un número.',
        'tarifa_evento.min' => 'La tarifa no puede ser negativa.',
        'sueldo_mensual.min' => 'El sueldo no puede ser negativo.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTipoFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function updatedModalidadPago()
    {
        $this->resetValidation(['tarifa_evento', 'sueldo_mensual']);
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'tipo_filtro', 'estado_filtro']);
        $this->resetPage();
    }

    private function personasQuery(bool $conEstado = true)
    {
        $query = MusicoPersonal::query();

        if ($this->search) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre_completo', 'like', $term)
                    ->orWhere('ci_nit', 'like', $term)
                    ->orWhere('telefono', 'like', $term)
                    ->orWhere('tipo', 'like', $term);
            });
        }

        if ($this->tipo_filtro !== '') {
            $query->where('tipo', $this->tipo_filtro);
        }

        if ($conEstado && $this->estado_filtro !== '') {
            $query->where('estado', $this->estado_filtro);
        }

        return $query;
    }

    public function abrirModal()
    {
        $this->reset([
            'persona_id',
            'nombre_completo',
            'telefono',
            'ci_nit',
            'tarifa_evento',
            'sueldo_mensual',
            'va_en_todos_eventos',
            'observaciones',
            'isEdit',
        ]);
        $this->resetValidation();
        $this->tipo = 'Músico';
        $this->modalidad_pago = 'por_evento';
        $this->estado = 'Activo';
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $p = MusicoPersonal::findOrFail($id);
        $this->resetValidation();
        $this->persona_id = $p->id;
        $this->nombre_completo = $p->nombre_completo;
        $this->tipo = $p->tipo ?: 'Músico';
        $this->telefono = $p->telefono;
        $this->ci_nit = $p->ci_nit;
        $this->modalidad_pago = $p->modalidad_pago ?: 'por_evento';
        $this->tarifa_evento = $p->tarifa_evento !== null ? (float) $p->tarifa_evento : '';
        $this->sueldo_mensual = $p->sueldo_mensual !== null ? (float) $p->sueldo_mensual : '';
        $this->va_en_todos_eventos = (bool) $p->va_en_todos_eventos;
        $this->estado = $p->estado ?: 'Activo';
        $this->observaciones = $p->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardar()
    {
        $this->validate();

        $nombreCompleto = Texto::nombrePropio($this->nombre_completo);
        $partes = explode(' ', $nombreCompleto, 2);
        $esMensual = $this->modalidad_pago === 'mensual';

        $datos = [
            'nombre' => $partes[0],
            'apellido' => $partes[1] ?? '',
            'nombre_completo' => $nombreCompleto,
            'ci_nit' => trim((string) $this->ci_nit) ?: null,
            'tipo' => trim($this->tipo),
            'telefono' => trim((string) $this->telefono) ?: null,
            'estado' => $this->estado,
            'observaciones' => trim((string) $this->observaciones) ?: null,
            'modalidad_pago' => $this->modalidad_pago,
            'tarifa_evento' => $esMensual ? null : (float) $this->tarifa_evento,
            'sueldo_mensual' => $esMensual ? (float) $this->sueldo_mensual : null,
            'va_en_todos_eventos' => (bool) $this->va_en_todos_eventos,
        ];

        if ($this->isEdit) {
            $p = MusicoPersonal::findOrFail($this->persona_id);
            $p->update($datos);
            $this->registrarAuditoria('Gestión Comercial', 'Editar Persona', 'Se actualizó a ' . $p->nombre_completo . ' (' . $p->tipo . ')');
            session()->flash('success', 'Datos actualizados correctamente.');
        } else {
            $p = MusicoPersonal::create($datos);
            $this->registrarAuditoria('Gestión Comercial', 'Crear Persona', 'Se registró a ' . $p->nombre_completo . ' (' . $p->tipo . ')');
            session()->flash('success', 'Persona registrada correctamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id)
    {
        $p = MusicoPersonal::findOrFail($id);
        $nuevo = $p->estado === 'Activo' ? 'Inactivo' : 'Activo';
        $p->update(['estado' => $nuevo]);

        $this->registrarAuditoria(
            'Gestión Comercial',
            $nuevo === 'Activo' ? 'Activar Persona' : 'Desactivar Persona',
            ($nuevo === 'Activo' ? 'Se activó a ' : 'Se desactivó a ') . $p->nombre_completo
        );
        session()->flash('success', $nuevo === 'Activo'
            ? $p->nombre_completo . ' está activo y vuelve a aparecer en los eventos.'
            : $p->nombre_completo . ' fue desactivado: ya no aparece al armar eventos, su historial se conserva.');
    }

    public function alternarEnTodos($id)
    {
        $p = MusicoPersonal::findOrFail($id);
        $p->update(['va_en_todos_eventos' => !$p->va_en_todos_eventos]);
        session()->flash('success', $p->va_en_todos_eventos
            ? $p->nombre_completo . ' se agregará solo a cada evento nuevo.'
            : $p->nombre_completo . ' ya no se agrega solo a los eventos.');
    }

    public function render()
    {
        $personas = $this->personasQuery()
            ->orderBy('nombre_completo')
            ->paginate(12);

        // Contadores de los chips (respetan búsqueda y tipo)
        $porEstado = $this->personasQuery(false)
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        // Tipos para filtro y formulario: los habituales + cualquiera que ya exista en la base
        $tipos = collect(self::TIPOS)
            ->merge(MusicoPersonal::query()->whereNotNull('tipo')->where('tipo', '!=', '')->distinct()->pluck('tipo'))
            ->unique()
            ->values();

        // Aviso de nombre repetido (no bloquea, solo avisa)
        $posibleDuplicado = null;
        if ($this->modalOpen && mb_strlen(trim($this->nombre_completo)) >= 3) {
            $posibleDuplicado = MusicoPersonal::query()
                ->whereRaw('LOWER(nombre_completo) = ?', [mb_strtolower(trim($this->nombre_completo))])
                ->when($this->persona_id, fn($q) => $q->where('id', '!=', $this->persona_id))
                ->first();
        }

        return view('livewire.comercial.gestion-musicos-personal', [
            'personas' => $personas,
            'totalActivos' => (int) ($porEstado['Activo'] ?? 0),
            'totalInactivos' => (int) ($porEstado['Inactivo'] ?? 0),
            'tipos' => $tipos,
            'posibleDuplicado' => $posibleDuplicado,
        ])->layout('components.layouts.app', [
            'title' => 'Músicos y personal - Mariachi León',
            'themed' => true,
        ]);
    }
}