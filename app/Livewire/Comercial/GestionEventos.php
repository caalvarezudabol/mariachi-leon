<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;

class GestionEventos extends Component
{
    use WithPagination, Auditable;

    public $evento_id = null;
    public $codigo_evento = '';
    public $cliente_id = '';
    public $servicio_id = '';
    public $contacto_evento = '';
    public $telefono_contacto = '';
    public $fecha_evento = '';
    public $hora_evento = '';
    public $duracion_horas = 1.00;
    public $direccion_evento = '';
    public $latitud = null;
    public $longitud = null;
    public $estado = 'Pendiente';
    public $observaciones = '';

    public $search = '';
    public $cliente_filtro = '';
    public $estado_filtro = '';
    public $modalOpen = false;
    public $modalMapOpen = false;
    public $eventoMapa = null;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'codigo_evento' => 'required|string|max:50|unique:eventos,codigo_evento,' . ($this->evento_id ?? 'NULL') . ',id',
            'cliente_id' => 'required|exists:clientes,id',
            'servicio_id' => 'required|exists:servicios,id',
            'contacto_evento' => 'nullable|string|max:255',
            'telefono_contacto' => 'nullable|string|max:50',
            'fecha_evento' => 'required|date',
            'hora_evento' => 'required',
            'duracion_horas' => 'required|numeric|min:0.5',
            'direccion_evento' => 'required|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'estado' => 'required|in:Pendiente,Cotizado,Reservado,Confirmado,Realizado,Cancelado,Finalizado',
            'observaciones' => 'nullable|string',
        ];
    }

    protected $messages = [
        'cliente_id.required' => 'Seleccione un cliente para el evento.',
        'servicio_id.required' => 'Seleccione el servicio a contratar.',
        'fecha_evento.required' => 'La fecha del evento es obligatoria.',
        'hora_evento.required' => 'La hora del evento es obligatoria.',
        'direccion_evento.required' => 'Ingrese la dirección completa del evento.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClienteFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function generarCodigoAutomatico()
    {
        $last = Evento::orderBy('id', 'desc')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        return 'EVT-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    public function abrirModal()
    {
        $this->reset([
            'evento_id', 'codigo_evento', 'cliente_id', 'servicio_id',
            'contacto_evento', 'telefono_contacto', 'fecha_evento', 'hora_evento',
            'duracion_horas', 'direccion_evento', 'latitud', 'longitud', 'observaciones', 'isEdit'
        ]);
        $this->codigo_evento = $this->generarCodigoAutomatico();
        $this->fecha_evento = date('Y-m-d');
        $this->hora_evento = '20:00';
        $this->duracion_horas = 1.00;
        $this->estado = 'Pendiente';
        $this->modalOpen = true;
    }

    public function seleccionarCliente($id)
    {
        $this->cliente_id = $id;
        $this->updatedClienteId($id);
    }

    public function updatedClienteId($value)
    {
        if ($value) {
            $cliente = Cliente::find($value);
            if ($cliente) {
                $this->contacto_evento = $cliente->nombre_completo;
                $this->telefono_contacto = $cliente->telefono ?: $cliente->whatsapp;
                if (!$this->direccion_evento) {
                    $this->direccion_evento = $cliente->direccion;
                }
            }
        }
    }

    public function editar($id)
    {
        $e = Evento::findOrFail($id);
        $this->evento_id = $e->id;
        $this->codigo_evento = $e->codigo_evento;
        $this->cliente_id = $e->cliente_id;
        $this->servicio_id = $e->servicio_id;
        $this->contacto_evento = $e->contacto_evento;
        $this->telefono_contacto = $e->telefono_contacto;
        $this->fecha_evento = $e->fecha_evento ? $e->fecha_evento->format('Y-m-d') : '';
        $this->hora_evento = $e->hora_evento;
        $this->duracion_horas = $e->duracion_horas;
        $this->direccion_evento = $e->direccion_evento;
        $this->latitud = $e->latitud;
        $this->longitud = $e->longitud;
        $this->estado = $e->estado;
        $this->observaciones = $e->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function verMapa($id)
    {
        $this->eventoMapa = Evento::with(['cliente', 'servicio'])->findOrFail($id);
        $this->modalMapOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $e = Evento::findOrFail($this->evento_id);
            $e->update([
                'codigo_evento' => strtoupper(trim($this->codigo_evento)),
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'contacto_evento' => trim($this->contacto_evento),
                'telefono_contacto' => trim($this->telefono_contacto),
                'fecha_evento' => $this->fecha_evento,
                'hora_evento' => $this->hora_evento,
                'duracion_horas' => $this->duracion_horas,
                'direccion_evento' => trim($this->direccion_evento),
                'latitud' => $this->latitud ?: null,
                'longitud' => $this->longitud ?: null,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Editar Evento', 'Se actualizó el evento ' . $e->codigo_evento);
            session()->flash('success', 'Evento actualizado correctamente.');
        } else {
            $e = Evento::create([
                'codigo_evento' => strtoupper(trim($this->codigo_evento)),
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'contacto_evento' => trim($this->contacto_evento),
                'telefono_contacto' => trim($this->telefono_contacto),
                'fecha_evento' => $this->fecha_evento,
                'hora_evento' => $this->hora_evento,
                'duracion_horas' => $this->duracion_horas,
                'direccion_evento' => trim($this->direccion_evento),
                'latitud' => $this->latitud ?: null,
                'longitud' => $this->longitud ?: null,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
                'user_id' => Auth::id(),
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Crear Evento', 'Se registró el evento ' . $e->codigo_evento);
            session()->flash('success', 'Evento registrado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        $e = Evento::findOrFail($id);
        $e->update(['estado' => $nuevoEstado]);
        $this->registrarAuditoria('Gestión Comercial', 'Cambiar Estado Evento', 'Se cambió estado de evento ' . $e->codigo_evento . ' a ' . $nuevoEstado);
        session()->flash('success', 'Estado del evento ' . $e->codigo_evento . ' actualizado a ' . $nuevoEstado . '.');
    }

    public function render()
    {
        $query = Evento::with(['cliente', 'servicio', 'user', 'contrato']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo_evento', 'like', '%' . $this->search . '%')
                  ->orWhere('contacto_evento', 'like', '%' . $this->search . '%')
                  ->orWhere('direccion_evento', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function($c) {
                      $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('servicio', function($s) {
                      $s->where('nombre', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->cliente_filtro) {
            $query->where('cliente_id', $this->cliente_filtro);
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $eventos = $query->orderBy('fecha_evento', 'desc')->orderBy('hora_evento', 'asc')->paginate(10);
        $clientes = Cliente::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre', 'asc')->get();

        return view('livewire.comercial.gestion-eventos', [
            'eventos' => $eventos,
            'clientes' => $clientes,
            'servicios' => $servicios,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Eventos - Mariachi León Guanajuato']);
    }
}
