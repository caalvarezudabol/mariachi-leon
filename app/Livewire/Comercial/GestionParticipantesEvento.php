<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use App\Models\Evento;
use App\Models\EventoParticipante;
use App\Models\MusicoPersonal;
use App\Models\DistribucionEconomica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionParticipantesEvento extends Component
{
    public $eventoId;
    public $evento;

    // Formulario para agregar participante
    public $persona_id = '';
    public $funcion = 'Músico';
    public $concepto_pago = 'Comisión por Evento';
    public $monto_asignado = 0.00;
    public $observaciones = '';

    // Sugerencias
    public $personasSugeridas = [];

    // Modal Crear Nueva Persona
    public $mostrarModalPersona = false;
    public $nuevo_nombre = '';
    public $nuevo_apellido = '';
    public $nuevo_ci_nit = '';
    public $nuevo_telefono = '';
    public $nuevo_email = '';
    public $nuevo_cargo = 'Músico';
    public $nueva_direccion = '';

    // Modal Editar Participante
    public $editingParticipanteId = null;
    public $edit_funcion = 'Músico';
    public $edit_concepto_pago = 'Comisión por Evento';
    public $edit_monto_asignado = 0.00;
    public $edit_observaciones = '';

    protected $rules = [
        'persona_id' => 'required|exists:musicos_personal,id',
        'funcion' => 'required|string|max:100',
        'concepto_pago' => 'required|string|max:100',
        'monto_asignado' => 'required|numeric|min:0',
        'observaciones' => 'nullable|string|max:500',
    ];

    public function mount($evento)
    {
        $this->eventoId = is_numeric($evento) ? $evento : $evento->id;
        $this->cargarEvento();
        $this->cargarSugerencias();
    }

    public function cargarEvento()
    {
        $this->evento = Evento::with(['cliente', 'servicio', 'participantes.persona', 'distribuciones.persona'])
            ->findOrFail($this->eventoId);
    }

    public function cargarSugerencias()
    {
        if (!$this->evento || !$this->evento->servicio_id) {
            $this->personasSugeridas = [];
            return;
        }

        $yaAsignadosIds = $this->evento->participantes->pluck('persona_id')->toArray();

        // Buscar personas que participaron en otros eventos con el mismo servicio
        $sugeridosQuery = EventoParticipante::query()
            ->join('eventos', 'evento_participantes.evento_id', '=', 'eventos.id')
            ->where('eventos.servicio_id', $this->evento->servicio_id)
            ->where('eventos.id', '!=', $this->eventoId)
            ->whereNotIn('evento_participantes.persona_id', $yaAsignadosIds)
            ->select('evento_participantes.persona_id', DB::raw('count(*) as frecuencia'))
            ->groupBy('evento_participantes.persona_id')
            ->orderByDesc('frecuencia')
            ->take(6)
            ->get();

        $personaIds = $sugeridosQuery->pluck('persona_id')->toArray();

        if (!empty($personaIds)) {
            $this->personasSugeridas = MusicoPersonal::whereIn('id', $personaIds)
                ->where('estado', 'Activo')
                ->get();
        } else {
            // Si no hay historial suficiente, sugerir las primeras 5 personas activas
            $this->personasSugeridas = MusicoPersonal::where('estado', 'Activo')
                ->whereNotIn('id', $yaAsignadosIds)
                ->take(5)
                ->get();
        }
    }

    public function seleccionarSugerido($personaId)
    {
        $this->persona_id = $personaId;
        $persona = MusicoPersonal::find($personaId);
        if ($persona) {
            $this->funcion = $persona->cargo ?? 'Músico';
        }
    }

    public function agregarParticipante()
    {
        $this->validate();

        // Validar que no esté duplicado
        $existe = EventoParticipante::where('evento_id', $this->eventoId)
            ->where('persona_id', $this->persona_id)
            ->exists();

        if ($existe) {
            session()->flash('error', 'La persona seleccionada ya está asignada a este evento.');
            return;
        }

        $participante = EventoParticipante::create([
            'evento_id' => $this->eventoId,
            'persona_id' => $this->persona_id,
            'funcion' => $this->funcion,
            'concepto_pago' => $this->concepto_pago,
            'monto_asignado' => $this->monto_asignado,
            'estado_distribucion' => 'Pendiente',
            'observaciones' => $this->observaciones,
            'user_id' => Auth::id(),
        ]);

        // Auto-crear o sincronizar la entrada en distribuciones_economicas
        DistribucionEconomica::create([
            'evento_id' => $this->eventoId,
            'contrato_id' => optional($this->evento->contrato)->id,
            'persona_id' => $this->persona_id,
            'evento_participante_id' => $participante->id,
            'funcion' => $this->funcion,
            'concepto' => $this->concepto_pago,
            'monto' => $this->monto_asignado,
            'fecha_distribucion' => now(),
            'estado' => 'Pendiente',
            'user_id' => Auth::id(),
        ]);

        $this->reset(['persona_id', 'monto_asignado', 'observaciones']);
        $this->funcion = 'Músico';
        $this->concepto_pago = 'Comisión por Evento';

        $this->cargarEvento();
        $this->cargarSugerencias();

        session()->flash('message', 'Participante asignado correctamente al evento.');
    }

    public function editarParticipante($id)
    {
        $part = EventoParticipante::findOrFail($id);
        $this->editingParticipanteId = $part->id;
        $this->edit_funcion = $part->funcion;
        $this->edit_concepto_pago = $part->concepto_pago;
        $this->edit_monto_asignado = $part->monto_asignado;
        $this->edit_observaciones = $part->observaciones ?? '';
    }

    public function actualizarParticipante()
    {
        $this->validate([
            'edit_funcion' => 'required|string|max:100',
            'edit_concepto_pago' => 'required|string|max:100',
            'edit_monto_asignado' => 'required|numeric|min:0',
            'edit_observaciones' => 'nullable|string|max:500',
        ]);

        $part = EventoParticipante::findOrFail($this->editingParticipanteId);
        $part->update([
            'funcion' => $this->edit_funcion,
            'concepto_pago' => $this->edit_concepto_pago,
            'monto_asignado' => $this->edit_monto_asignado,
            'observaciones' => $this->edit_observaciones,
        ]);

        // Actualizar distribucion asociada
        DistribucionEconomica::where('evento_participante_id', $part->id)->update([
            'funcion' => $this->edit_funcion,
            'concepto' => $this->edit_concepto_pago,
            'monto' => $this->edit_monto_asignado,
        ]);

        $this->editingParticipanteId = null;
        $this->cargarEvento();
        session()->flash('message', 'Datos del participante actualizados correctamente.');
    }

    public function cancelarEdicion()
    {
        $this->editingParticipanteId = null;
    }

    public function eliminarParticipante($id)
    {
        $part = EventoParticipante::findOrFail($id);
        
        // Eliminar distribucion asociada
        DistribucionEconomica::where('evento_participante_id', $part->id)->delete();
        $part->delete();

        $this->cargarEvento();
        $this->cargarSugerencias();

        session()->flash('message', 'Participante removido del evento.');
    }

    public function marcarDistribucionPagada($distribucionId)
    {
        $dist = DistribucionEconomica::findOrFail($distribucionId);
        $dist->update(['estado' => 'Distribuido']);

        if ($dist->evento_participante_id) {
            EventoParticipante::where('id', $dist->evento_participante_id)
                ->update(['estado_distribucion' => 'Distribuido']);
        }

        $this->cargarEvento();
        session()->flash('message', 'Distribución económica marcada como DISTRIBUIDA / PAGADA.');
    }

    // Modal Crear Nueva Persona
    public function abrirModalPersona()
    {
        $this->reset(['nuevo_nombre', 'nuevo_apellido', 'nuevo_ci_nit', 'nuevo_telefono', 'nuevo_email', 'nueva_direccion']);
        $this->nuevo_cargo = 'Músico';
        $this->mostrarModalPersona = true;
    }

    public function cerrarModalPersona()
    {
        $this->mostrarModalPersona = false;
    }

    public function guardarNuevaPersona()
    {
        $this->validate([
            'nuevo_nombre' => 'required|string|max:100',
            'nuevo_apellido' => 'required|string|max:100',
            'nuevo_cargo' => 'required|string|max:100',
            'nuevo_telefono' => 'nullable|string|max:20',
            'nuevo_ci_nit' => 'nullable|string|max:20',
            'nuevo_email' => 'nullable|email|max:100',
        ]);

        $persona = MusicoPersonal::create([
            'nombres' => $this->nuevo_nombre,
            'apellidos' => $this->nuevo_apellido,
            'cargo' => $this->nuevo_cargo,
            'telefono' => $this->nuevo_telefono,
            'ci_nit' => $this->nuevo_ci_nit,
            'email' => $this->nuevo_email,
            'direccion' => $this->nueva_direccion,
            'estado' => 'Activo',
        ]);

        $this->persona_id = $persona->id;
        $this->funcion = $persona->cargo;
        $this->cerrarModalPersona();

        session()->flash('message', "Persona ({$persona->nombres} {$persona->apellidos}) registrada en el catálogo y seleccionada.");
    }

    public function render()
    {
        $todasPersonas = MusicoPersonal::where('estado', 'Activo')
            ->orderBy('nombres')
            ->get();

        $totalAsignadoParticipantes = $this->evento->participantes->sum('monto_asignado');
        $montoEvento = $this->evento->monto_total ?? 0;
        $balanceMargen = $montoEvento - $totalAsignadoParticipantes;

        return view('livewire.comercial.gestion-participantes-evento', [
            'todasPersonas' => $todasPersonas,
            'totalAsignadoParticipantes' => $totalAsignadoParticipantes,
            'montoEvento' => $montoEvento,
            'balanceMargen' => $balanceMargen,
        ]);
    }
}
