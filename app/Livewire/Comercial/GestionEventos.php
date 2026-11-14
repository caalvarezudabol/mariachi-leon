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

    public $estado_filtro = 'porrealizar'; // porrealizar | realizados | cancelados | '' (todos)
    public $modalMapOpen = false;
    public $eventoMapa = null;

    // Propiedades para modal de Historial de Pagos
    public $modalHistorialPagosOpen = false;
    public $eventoHistorial = null;
    public $pagosHistorial = [];
    public $resumenFinanciero = [];

    // Modal de Cobro rápido (desde la lista de eventos)
    public $modalCobroOpen = false;
    public $cobro_evento_id = null;
    public $cobro_monto = '';
    public $cobro_metodo = 'EFECTIVO';
    public $cobro_obs = '';

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
            : 'Reservado';
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

    public function abrirCobro($id)
    {
        $e = Evento::with('contrato')->findOrFail($id);

        if (!$e->contrato) {
            session()->flash('error', 'Este evento no tiene contrato. Créalo primero en Contratos.');
            return;
        }
        if ($e->estado === 'Cancelado') {
            session()->flash('error', 'El evento está cancelado: no se pueden registrar cobros.');
            return;
        }
        $saldo = (float) $e->contrato->saldo_pendiente;
        if ($saldo <= 0.001) {
            session()->flash('error', 'El contrato de ' . $e->codigo_evento . ' ya está pagado por completo.');
            return;
        }

        $this->resetValidation();
        $this->cobro_evento_id = $e->id;
        $this->cobro_monto = round($saldo, 2);
        $this->cobro_metodo = 'EFECTIVO';
        $this->cobro_obs = '';
        $this->modalCobroOpen = true;
    }

    public function cerrarCobro()
    {
        $this->modalCobroOpen = false;
        $this->cobro_evento_id = null;
        $this->resetValidation();
    }

    public function guardarCobro()
    {
        $this->validate([
            'cobro_monto' => 'required|numeric|gt:0',
            'cobro_metodo' => 'required|in:EFECTIVO,QR',
            'cobro_obs' => 'nullable|string|max:255',
        ], [
            'cobro_monto.required' => 'Ingrese el monto a cobrar.',
            'cobro_monto.numeric' => 'El monto debe ser un número.',
            'cobro_monto.gt' => 'El monto debe ser mayor a cero.',
        ]);

        $e = Evento::with('contrato')->findOrFail($this->cobro_evento_id);
        if (!$e->contrato) {
            $this->cerrarCobro();
            return;
        }

        $codigo = null;
        DB::transaction(function () use ($e, &$codigo) {
            $contrato = Contrato::lockForUpdate()->findOrFail($e->contrato->id);
            $montoPago = round((float) $this->cobro_monto, 2);

            if ($montoPago > (float) $contrato->saldo_pendiente + 0.001) {
                $this->addError('cobro_monto', 'El monto (Bs ' . number_format($montoPago, 2) . ') es mayor al saldo pendiente (Bs ' . number_format($contrato->saldo_pendiente, 2) . ').');
                return;
            }

            $pago = Pago::create([
                'numero_recibo' => $this->siguienteNumero('pagos', 'numero_recibo', 'REC-'),
                'contrato_id' => $contrato->id,
                'cliente_id' => $contrato->cliente_id,
                'evento_id' => $contrato->evento_id,
                'fecha_pago' => date('Y-m-d H:i:s'),
                'monto' => $montoPago,
                'metodo_pago' => $this->cobro_metodo,
                'observaciones' => trim((string) $this->cobro_obs),
                'codigo_verificacion' => Str::random(40),
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            $nuevoTotalPagado = (float) $contrato->total_pagado + $montoPago;
            $nuevoSaldo = max(0, (float) $contrato->monto_total - $nuevoTotalPagado);
            $nuevoEstado = in_array($contrato->estado, ['Borrador', 'Pendiente de Confirmacion'])
                ? 'Confirmado'
                : $contrato->estado;

            $contrato->update([
                'total_pagado' => $nuevoTotalPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoEstado,
            ]);

            if (in_array($e->estado, ['Pendiente', 'Reservado'])) {
                $e->update(['estado' => 'Confirmado']);
            }

            $this->registrarAuditoria('Gestión Comercial', 'Registrar Pago Cliente', 'Cobro rápido desde Eventos: recibo ' . $pago->numero_recibo . ' por Bs ' . number_format($montoPago, 2) . ' del evento ' . $e->codigo_evento);

            $codigo = $pago->codigo_verificacion;
        });

        if (!$codigo) {
            return;
        }

        $this->cerrarCobro();
        // Se queda en el listado; el recibo se abre solo si lo pides desde el aviso
        session()->flash('success', 'Cobro registrado en ' . $e->codigo_evento . '.');
        session()->flash('recibo_codigo', $codigo);
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

    private const GRUPOS_ESTADO = [
        'porrealizar' => ['Pendiente', 'Cotizado', 'Reservado', 'Confirmado'],
        'realizados' => ['Realizado', 'Finalizado'],
        'cancelados' => ['Cancelado'],
    ];

    private function filtroEstado($q)
    {
        if (isset(self::GRUPOS_ESTADO[$this->estado_filtro])) {
            $q->whereIn('estado', self::GRUPOS_ESTADO[$this->estado_filtro]);
        } elseif ($this->estado_filtro) {
            $q->where('estado', $this->estado_filtro);
        }
        return $q;
    }

    // Marcar el show como realizado (el cliente ya lo recibió)
    public function marcarRealizado($id)
    {
        $e = Evento::findOrFail($id);
        if (in_array($e->estado, ['Cancelado', 'Realizado', 'Finalizado'])) {
            return;
        }
        $e->update(['estado' => 'Realizado']);
        $this->registrarAuditoria('Gestión Comercial', 'Marcar Realizado', 'Se marcó como realizado el evento ' . $e->codigo_evento);
        session()->flash('success', 'Evento ' . $e->codigo_evento . ' marcado como realizado.');
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
            ->tap(fn($q) => $this->filtroEstado($q))
            ->orderBy('fecha_evento', $this->estado_filtro === 'porrealizar' ? 'asc' : 'desc')->orderBy('hora_evento', 'asc')
            ->paginate(10);

        $estados = $this->eventosQuery()
            ->selectRaw('estado, count(*) as total')
            ->groupBy('estado')
            ->orderBy('estado')
            ->pluck('total', 'estado');

        $conteos = [];
        foreach (self::GRUPOS_ESTADO as $clave => $lista) {
            $conteos[$clave] = (int) collect($estados)->only($lista)->sum();
        }

        // Totales de TODOS los eventos del filtro (no solo de la página). El saldo no cuenta los cancelados.
        $idsFiltro = $this->eventosQuery()
            ->tap(fn($q) => $this->filtroEstado($q))
            ->select('eventos.id');
        $idsActivos = $this->eventosQuery()
            ->tap(fn($q) => $this->filtroEstado($q))
            ->where('estado', '!=', 'Cancelado')
            ->select('eventos.id');
        $sumas = Contrato::whereIn('evento_id', $idsFiltro)
            ->selectRaw('COALESCE(SUM(monto_total),0) as contratado, COALESCE(SUM(total_pagado),0) as abonado')
            ->first();
        $totales = [
            'contratado' => (float) ($sumas->contratado ?? 0),
            'abonado' => (float) ($sumas->abonado ?? 0),
            'saldo' => (float) Contrato::whereIn('evento_id', $idsActivos)->sum('saldo_pendiente'),
        ];

        $clientes = Cliente::where('estado', 'Activo')->orderBy('nombre_completo', 'asc')->get();
        return view('livewire.comercial.gestion-eventos', [
            'totales' => $totales,
            'conteos' => $conteos,
            'eventos' => $eventos,
            'totalEventos' => $this->eventosQuery()->count(),
            'estados' => $estados,
            'clientes' => $clientes,
            'eventoCobro' => ($this->modalCobroOpen && $this->cobro_evento_id)
                ? Evento::with(['cliente', 'contrato'])->find($this->cobro_evento_id)
                : null,
        ] + $this->datosFormularioEvento())->layout('components.layouts.app', [
            'title' => 'Gestión de Eventos - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}