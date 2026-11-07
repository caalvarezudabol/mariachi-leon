<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Pago;
use App\Models\Contrato;
use Illuminate\Support\Str;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use App\Models\DistribucionEconomica;
use App\Traits\Auditable;
use App\Traits\ManejaFormularioEvento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class GestionEventos extends Component
{
    use WithPagination, Auditable, ManejaFormularioEvento;

    public $search = '';

    #[Url(as: 'cliente_id')]
    public $cliente_filtro = '';

    public $estado_filtro = '';
    public $modalMapOpen = false;
    public $eventoMapa = null;

    // Propiedades para modal de Historial de Pagos
    public $modalHistorialPagosOpen = false;
    public $eventoHistorial = null;
    public $pagosHistorial = [];
    public $resumenFinanciero = [];

    public function mount()
    {
        if (request()->has('cliente_id')) {
            $param = request()->get('cliente_id');
            $decoded = \App\Services\Security\DocumentToken::decodeId($param, 'cliente');
            $this->cliente_filtro = $decoded ?: $param;
        }
    }

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

    public function verMapa($id)
    {
        $this->eventoMapa = Evento::with(['cliente', 'servicio'])->findOrFail($id);
        $this->modalMapOpen = true;
    }

    public function cancelarEvento($id)
    {
        $e = Evento::findOrFail($id);
        if ($e->estado === 'Cancelado') {
            return;
        }

        $e->update(['estado' => 'Cancelado']);
        $this->registrarAuditoria('Gestión Comercial', 'Cancelar Evento', 'Se canceló el evento ' . $e->codigo_evento);
        session()->flash('success', 'Evento ' . $e->codigo_evento . ' cancelado. Ya no ocupa la agenda; los cobros registrados se mantienen.');
    }

    public function reactivarEvento($id)
    {
        $e = Evento::with('contrato')->findOrFail($id);
        if ($e->estado !== 'Cancelado') {
            return;
        }

        $cruces = $this->eventosQueSeCruzan($e->fecha_evento->format('Y-m-d'), $e->hora_evento, $e->duracion_horas, $e->id);
        if (!empty($cruces)) {
            session()->flash('error', 'No se puede reactivar: el horario se cruza con ' . implode('; ', $cruces) . '.');
            return;
        }

        $nuevo = $e->contrato
            ? ((float) $e->contrato->total_pagado > 0 ? 'Confirmado' : 'Reservado')
            : 'Pendiente';
        $e->update(['estado' => $nuevo]);
        $this->registrarAuditoria('Gestión Comercial', 'Reactivar Evento', 'Se reactivó el evento ' . $e->codigo_evento . ' como ' . $nuevo);
        session()->flash('success', 'Evento ' . $e->codigo_evento . ' reactivado como ' . $nuevo . '.');
    }

    // Eliminar solo si no tiene ningún cobro ni pago a personal (si no, usar Cancelar)
    public function eliminarEvento($id)
    {
        $e = Evento::with('contrato')->findOrFail($id);

        $tieneCobros = Pago::where('evento_id', $e->id)
            ->orWhere(fn($q) => $e->contrato ? $q->where('contrato_id', $e->contrato->id) : $q->whereRaw('1 = 0'))
            ->exists();
        if ($tieneCobros) {
            session()->flash('error', 'No se puede eliminar ' . $e->codigo_evento . ': tiene cobros registrados. Use "Cancelar evento".');
            return;
        }

        $tienePagosPersonal = EventoParticipante::with('pagosValidos')->where('evento_id', $e->id)->get()
            ->contains(fn($ep) => $ep->pagosValidos->sum('monto') > 0);
        if ($tienePagosPersonal) {
            session()->flash('error', 'No se puede eliminar ' . $e->codigo_evento . ': ya se pagó a personal. Use "Cancelar evento".');
            return;
        }

        try {
            DB::transaction(function () use ($e) {
                DistribucionEconomica::where('evento_id', $e->id)->delete();
                EventoParticipante::where('evento_id', $e->id)->delete();
                if ($e->contrato) {
                    $e->contrato->delete();
                }
                $e->delete();
            });
        } catch (\Throwable $ex) {
            \Illuminate\Support\Facades\Log::error('Error al eliminar evento: ' . $ex->getMessage());
            session()->flash('error', 'No se pudo eliminar ' . $e->codigo_evento . ' porque tiene datos relacionados. Use "Cancelar evento".');
            return;
        }

        $this->registrarAuditoria('Gestión Comercial', 'Eliminar Evento', 'Se eliminó el evento ' . $e->codigo_evento);
        session()->flash('success', 'Evento ' . $e->codigo_evento . ' eliminado.');
    }

    public function abrirHistorialPagos($id)
    {
        $this->eventoHistorial = Evento::with(['cliente', 'servicio', 'contrato'])->findOrFail($id);

        $contrato = $this->eventoHistorial->contrato;
        $montoTotal = $contrato ? (float)$contrato->monto_total : 0;

        $pagos = Pago::where(function ($q) use ($id, $contrato) {
            $q->where('evento_id', $id);
            if ($contrato) {
                $q->orWhere('contrato_id', $contrato->id);
            }
        })
            ->with('user')
            ->orderBy('fecha_pago', 'asc')
            ->get();

        $totalAbonado = (float)$pagos->where('estado', 'Valido')->sum('monto');
        $saldoPendiente = max(0, $montoTotal - $totalAbonado);

        $this->pagosHistorial = $pagos;
        $this->resumenFinanciero = [
            'monto_total' => $montoTotal,
            'total_abonado' => $totalAbonado,
            'saldo_pendiente' => $saldoPendiente,
            'tiene_contrato' => !empty($contrato),
            'numero_contrato' => $contrato?->numero_contrato,
            'estado_contrato' => $contrato?->estado,
            'contrato_id' => $contrato?->id,
        ];

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Consultar Historial de Pagos Evento',
            "Se consultó el historial de pagos del evento {$this->eventoHistorial->codigo_evento} (Cliente: " . ($this->eventoHistorial->cliente->nombre_completo ?? 'N/A') . ")"
        );

        $this->modalHistorialPagosOpen = true;
    }

    public function cerrarHistorialPagos()
    {
        $this->modalHistorialPagosOpen = false;
        $this->eventoHistorial = null;
        $this->pagosHistorial = [];
        $this->resumenFinanciero = [];
    }

    private function eventosQuery()
    {
        return Evento::query()
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('codigo_evento', 'like', '%' . $this->search . '%')
                        ->orWhere('contacto_evento', 'like', '%' . $this->search . '%')
                        ->orWhere('direccion_evento', 'like', '%' . $this->search . '%')
                        ->orWhereHas('cliente', function ($c) {
                            $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('servicio', function ($s) {
                            $s->where('nombre', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->cliente_filtro, fn($q) => $q->where('cliente_id', $this->cliente_filtro));
    }

    public function render()
    {
        $eventos = $this->eventosQuery()
            ->with(['cliente', 'servicio', 'user', 'contrato.pagos', 'pagos', 'participantes.persona'])
            ->when($this->estado_filtro, fn($q) => $q->where('estado', $this->estado_filtro))
            ->orderBy('fecha_evento', 'desc')->orderBy('hora_evento', 'asc')
            ->paginate(10);

        $estados = $this->eventosQuery()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->orderBy('estado')
            ->pluck('total', 'estado');

        $clientes = Cliente::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();
        return view('livewire.comercial.gestion-eventos', [
            'eventos' => $eventos,
            'totalEventos' => $this->eventosQuery()->count(),
            'estados' => $estados,
            'clientes' => $clientes,
        ] + $this->datosFormularioEvento())->layout('components.layouts.app', [
            'title' => 'Gestión de Eventos - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}