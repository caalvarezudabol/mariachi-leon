<?php

namespace App\Livewire\Configuracion;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Servicio;
use App\Models\ServicioParticipante;
use App\Models\MusicoPersonal;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;

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
    public $observaciones = '';

    public $search = '';
    public $tipo_filtro = '';
    public $modalOpen = false;
    public $isEdit = false;

    // Propiedades para Modal Plantilla de Participantes por Servicio
    public $modalPlantillaOpen = false;
    public $servicioPlantilla = null;
    public $mostrarFormParticipante = false;
    public $editingPlantillaId = null;

    // Campos formulario participante plantilla
    public $plantilla_persona_id = '';
    public $plantilla_funcion = 'Músico';
    public $plantilla_concepto = 'Comisión por Evento';
    public $plantilla_tipo_calculo = 'monto_fijo'; // 'monto_fijo' | 'porcentaje'
    public $plantilla_valor = 0.00;
    public $plantilla_activo = true;
    public $plantilla_observaciones = '';

    // Modal para registrar rápidamente una nueva persona si no existe
    public $mostrarModalNuevaPersona = false;
    public $nueva_persona_nombre = '';
    public $nueva_persona_apellido = '';
    public $nueva_persona_ci_nit = '';
    public $nueva_persona_telefono = '';
    public $nueva_persona_email = '';
    public $nueva_persona_tipo = 'Músico';

    protected $rules = [
        'nombre' => 'required|string|max:255',
        'tipo_servicio' => 'required|string|max:100',
        'descripcion' => 'nullable|string',
        'precio_base' => 'required|numeric|min:0',
        'duracion_minutos' => 'required|integer|min:15',
        'observaciones' => 'nullable|string',
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
        $this->reset(['servicio_id', 'nombre', 'descripcion', 'precio_base', 'duracion_minutos', 'observaciones', 'isEdit']);
        $this->tipo_servicio = 'Musical';
        $this->activo = true;
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $s = Servicio::with('plantillaParticipantes.persona')->findOrFail($id);
        $this->servicio_id = $s->id;
        $this->nombre = $s->nombre;
        $this->tipo_servicio = $s->tipo_servicio ?: 'Musical';
        $this->descripcion = $s->descripcion;
        $this->precio_base = $s->precio_base;
        $this->duracion_minutos = $s->duracion_minutos;
        $this->activo = $s->activo;
        $this->observaciones = $s->observaciones;
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
                'observaciones' => trim($this->observaciones),
            ]);
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
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Configuración', 'Crear Servicio', 'Se creó el servicio: ' . $s->nombre);
            session()->flash('success', 'Servicio registrado correctamente. Ahora puede configurar su plantilla económica de participantes.');
        }

        $this->modalOpen = false;
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

    // =========================================================================
    // PLANTILLA DE PARTICIPANTES Y DISTRIBUCIÓN ECONÓMICA POR SERVICIO
    // =========================================================================

    public function abrirPlantilla($servicioId)
    {
        $this->servicioPlantilla = Servicio::with('plantillaParticipantes.persona')->findOrFail($servicioId);
        $this->modalPlantillaOpen = true;
        $this->cerrarFormParticipante();
        $this->mostrarModalNuevaPersona = false;
    }

    public function cerrarPlantilla()
    {
        $this->modalPlantillaOpen = false;
        $this->servicioPlantilla = null;
        $this->cerrarFormParticipante();
    }

    public function abrirFormParticipante($plantillaId = null)
    {
        if ($plantillaId) {
            $p = ServicioParticipante::findOrFail($plantillaId);
            $this->editingPlantillaId = $p->id;
            $this->plantilla_persona_id = $p->persona_id;
            $this->plantilla_funcion = $p->funcion;
            $this->plantilla_concepto = $p->concepto_pago;
            $this->plantilla_tipo_calculo = $p->tipo_calculo;
            $this->plantilla_valor = (float)$p->valor;
            $this->plantilla_activo = (bool)$p->activo;
            $this->plantilla_observaciones = $p->observaciones ?? '';
        } else {
            $this->resetFormParticipante();
        }

        $this->mostrarFormParticipante = true;
    }

    public function cerrarFormParticipante()
    {
        $this->resetFormParticipante();
        $this->mostrarFormParticipante = false;
    }

    public function resetFormParticipante()
    {
        $this->editingPlantillaId = null;
        $this->plantilla_persona_id = '';
        $this->plantilla_funcion = 'Músico';
        $this->plantilla_concepto = 'Comisión por Evento';
        $this->plantilla_tipo_calculo = 'monto_fijo';
        $this->plantilla_valor = 0.00;
        $this->plantilla_activo = true;
        $this->plantilla_observaciones = '';
        $this->resetValidation([
            'plantilla_persona_id', 'plantilla_funcion', 'plantilla_concepto',
            'plantilla_tipo_calculo', 'plantilla_valor', 'plantilla_observaciones'
        ]);
    }

    public function updatedPlantillaPersonaId($val)
    {
        if ($val && empty($this->editingPlantillaId)) {
            $persona = MusicoPersonal::find($val);
            if ($persona && $persona->tipo) {
                $this->plantilla_funcion = $persona->tipo;
            }
        }
    }

    public function guardarParticipantePlantilla()
    {
        $rules = [
            'plantilla_persona_id' => 'required|exists:musicos_personal,id',
            'plantilla_funcion' => 'required|string|max:100',
            'plantilla_concepto' => 'required|string|max:100',
            'plantilla_tipo_calculo' => 'required|in:monto_fijo,porcentaje',
            'plantilla_valor' => 'required|numeric|min:0' . ($this->plantilla_tipo_calculo === 'porcentaje' ? '|max:100' : ''),
            'plantilla_observaciones' => 'nullable|string|max:500',
        ];

        $messages = [
            'plantilla_persona_id.required' => 'Seleccione una persona del catálogo.',
            'plantilla_persona_id.exists' => 'La persona seleccionada no es válida.',
            'plantilla_funcion.required' => 'La función en el servicio es requerida.',
            'plantilla_concepto.required' => 'El concepto de pago es requerido.',
            'plantilla_valor.required' => 'Ingrese el valor o importe correspondiente.',
            'plantilla_valor.min' => 'El valor configurado no puede ser negativo.',
            'plantilla_valor.max' => 'El porcentaje no puede ser superior al 100%.',
        ];

        $this->validate($rules, $messages);

        // Validar unicidad de persona en la plantilla del servicio
        $queryDuplicate = ServicioParticipante::where('servicio_id', $this->servicioPlantilla->id)
            ->where('persona_id', $this->plantilla_persona_id);

        if ($this->editingPlantillaId) {
            $queryDuplicate->where('id', '!=', $this->editingPlantillaId);
        }

        if ($queryDuplicate->exists()) {
            session()->flash('plantilla_error', 'Esta persona ya se encuentra configurada en la plantilla del servicio. Modifique su registro existente si desea ajustar sus valores.');
            return;
        }

        $persona = MusicoPersonal::findOrFail($this->plantilla_persona_id);

        if ($this->editingPlantillaId) {
            $sp = ServicioParticipante::findOrFail($this->editingPlantillaId);
            $sp->update([
                'persona_id' => $this->plantilla_persona_id,
                'funcion' => trim($this->plantilla_funcion),
                'concepto_pago' => trim($this->plantilla_concepto),
                'tipo_calculo' => $this->plantilla_tipo_calculo,
                'valor' => $this->plantilla_valor,
                'activo' => $this->plantilla_activo,
                'observaciones' => trim($this->plantilla_observaciones),
            ]);

            $this->registrarAuditoria(
                'Configuración',
                'Editar Plantilla Servicio',
                "Se modificó la configuración de {$persona->nombre_completo} en la plantilla de '{$this->servicioPlantilla->nombre}'. Función: {$this->plantilla_funcion}, Tipo: {$this->plantilla_tipo_calculo}, Valor: {$this->plantilla_valor}"
            );

            session()->flash('plantilla_success', "Participante {$persona->nombre_completo} actualizado correctamente en la plantilla.");
        } else {
            ServicioParticipante::create([
                'servicio_id' => $this->servicioPlantilla->id,
                'persona_id' => $this->plantilla_persona_id,
                'funcion' => trim($this->plantilla_funcion),
                'concepto_pago' => trim($this->plantilla_concepto),
                'tipo_calculo' => $this->plantilla_tipo_calculo,
                'valor' => $this->plantilla_valor,
                'activo' => $this->plantilla_activo,
                'observaciones' => trim($this->plantilla_observaciones),
                'user_id' => Auth::id(),
            ]);

            $this->registrarAuditoria(
                'Configuración',
                'Agregar Plantilla Servicio',
                "Se agregó a {$persona->nombre_completo} ({$this->plantilla_funcion}) a la plantilla del servicio '{$this->servicioPlantilla->nombre}' con {$this->plantilla_tipo_calculo}: {$this->plantilla_valor}"
            );

            session()->flash('plantilla_success', "Participante {$persona->nombre_completo} agregado exitosamente a la plantilla del servicio.");
        }

        $this->cerrarFormParticipante();
        $this->servicioPlantilla = Servicio::with('plantillaParticipantes.persona')->findOrFail($this->servicioPlantilla->id);
    }

    public function toggleActivoParticipantePlantilla($id)
    {
        $sp = ServicioParticipante::with('persona')->findOrFail($id);
        $sp->update(['activo' => !$sp->activo]);

        $estadoStr = $sp->activo ? 'Activo' : 'Inactivo';
        $personaNombre = $sp->persona->nombre_completo ?? 'Participante';

        $this->registrarAuditoria(
            'Configuración',
            'Cambiar Estado Plantilla Participante',
            "Se cambió estado de {$personaNombre} a '{$estadoStr}' en la plantilla del servicio '{$this->servicioPlantilla->nombre}'."
        );

        $this->servicioPlantilla = Servicio::with('plantillaParticipantes.persona')->findOrFail($this->servicioPlantilla->id);
        session()->flash('plantilla_success', "Estado de {$personaNombre} actualizado a {$estadoStr} en la plantilla.");
    }

    public function eliminarParticipantePlantilla($id)
    {
        $sp = ServicioParticipante::with('persona')->findOrFail($id);
        $personaNombre = $sp->persona->nombre_completo ?? 'Participante';
        $sp->delete();

        $this->registrarAuditoria(
            'Configuración',
            'Remover Participante Plantilla',
            "Se removió a {$personaNombre} de la plantilla del servicio '{$this->servicioPlantilla->nombre}'."
        );

        $this->servicioPlantilla = Servicio::with('plantillaParticipantes.persona')->findOrFail($this->servicioPlantilla->id);
        session()->flash('plantilla_success', "Participante {$personaNombre} removido de la plantilla del servicio.");
    }

    // Modal Crear Nueva Persona Rápida
    public function abrirModalNuevaPersona()
    {
        $this->reset([
            'nueva_persona_nombre', 'nueva_persona_apellido',
            'nueva_persona_ci_nit', 'nueva_persona_telefono',
            'nueva_persona_email'
        ]);
        $this->nueva_persona_tipo = 'Músico';
        $this->mostrarModalNuevaPersona = true;
    }

    public function cerrarModalNuevaPersona()
    {
        $this->mostrarModalNuevaPersona = false;
    }

    public function guardarNuevaPersona()
    {
        $this->validate([
            'nueva_persona_nombre' => 'required|string|max:100',
            'nueva_persona_apellido' => 'required|string|max:100',
            'nueva_persona_ci_nit' => 'nullable|string|max:50',
            'nueva_persona_telefono' => 'nullable|string|max:50',
            'nueva_persona_email' => 'nullable|email|max:100',
            'nueva_persona_tipo' => 'required|string|max:100',
        ], [
            'nueva_persona_nombre.required' => 'El nombre es obligatorio.',
            'nueva_persona_apellido.required' => 'El apellido es obligatorio.',
        ]);

        $nombreCompleto = trim($this->nueva_persona_nombre . ' ' . $this->nueva_persona_apellido);

        $persona = MusicoPersonal::create([
            'nombre' => trim($this->nueva_persona_nombre),
            'apellido' => trim($this->nueva_persona_apellido),
            'nombre_completo' => $nombreCompleto,
            'ci_nit' => trim($this->nueva_persona_ci_nit),
            'tipo' => trim($this->nueva_persona_tipo),
            'telefono' => trim($this->nueva_persona_telefono),
            'email' => trim($this->nueva_persona_email),
            'estado' => 'Activo',
        ]);

        $this->registrarAuditoria(
            'Personas',
            'Crear Persona',
            "Se registró a la persona {$persona->nombre_completo} ({$persona->tipo}) desde el configurador de servicios."
        );

        $this->plantilla_persona_id = $persona->id;
        $this->plantilla_funcion = $persona->tipo;
        $this->mostrarModalNuevaPersona = false;
        session()->flash('plantilla_success', "Persona {$persona->nombre_completo} creada y seleccionada con éxito.");
    }

    public function render()
    {
        $query = Servicio::withCount([
            'plantillaParticipantes',
            'participantesActivos'
        ]);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('tipo_servicio', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo_filtro) {
            $query->where('tipo_servicio', $this->tipo_filtro);
        }

        $servicios = $query->latest()->paginate(10);

        // Catálogo de personas activas
        $personasDisponibles = MusicoPersonal::where('estado', 'Activo')
            ->orderBy('nombre_completo', 'asc')
            ->get();

        return view('livewire.configuracion.gestion-servicios', [
            'servicios' => $servicios,
            'personasDisponibles' => $personasDisponibles,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Servicios - Mariachi León']);
    }
}
