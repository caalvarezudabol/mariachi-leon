<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Traits\Auditable;

class ReporteEventosContratos extends Component
{
    use WithPagination, Auditable;

    public $search = '';
    public $cliente_id = '';
    public $servicio_id = '';
    public $estado_contrato = '';
    public $estado_evento = '';
    public $search_lugar = '';
    public $campo_fecha = 'fecha_evento'; // 'fecha_evento' o 'fecha_contrato'
    public $fecha_desde = '';
    public $fecha_hasta = '';

    public function mount()
    {
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Consultar Reporte de Eventos y Contratos',
            'El usuario accedió al reporte interactivo de eventos y contratos.'
        );
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingClienteId()
    {
        $this->resetPage();
    }

    public function updatingServicioId()
    {
        $this->resetPage();
    }

    public function updatingEstadoContrato()
    {
        $this->resetPage();
    }

    public function updatingEstadoEvento()
    {
        $this->resetPage();
    }

    public function updatingSearchLugar()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Evento::with(['cliente', 'servicio', 'contrato.pagos']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('codigo_evento', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', fn($c) => $c->where('nombre_completo', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('contrato', fn($ct) => $ct->where('numero_contrato', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->cliente_id) {
            $query->where('cliente_id', $this->cliente_id);
        }

        if ($this->servicio_id) {
            $query->where('servicio_id', $this->servicio_id);
        }

        if ($this->estado_evento) {
            $query->where('estado', $this->estado_evento);
        }

        if ($this->estado_contrato) {
            if ($this->estado_contrato === 'sin_contrato') {
                $query->doesntHave('contrato');
            } else {
                $query->whereHas('contrato', fn($ct) => $ct->where('estado', $this->estado_contrato));
            }
        }

        if ($this->search_lugar) {
            $query->where('direccion_evento', 'like', '%' . $this->search_lugar . '%');
        }

        // Filtro de fechas
        if ($this->campo_fecha === 'fecha_evento') {
            if ($this->fecha_desde) $query->whereDate('fecha_evento', '>=', $this->fecha_desde);
            if ($this->fecha_hasta) $query->whereDate('fecha_evento', '<=', $this->fecha_hasta);
        } else {
            if ($this->fecha_desde) {
                $query->whereHas('contrato', fn($ct) => $ct->whereDate('fecha_contrato', '>=', $this->fecha_desde));
            }
            if ($this->fecha_hasta) {
                $query->whereHas('contrato', fn($ct) => $ct->whereDate('fecha_contrato', '<=', $this->fecha_hasta));
            }
        }

        // Cálculos del resumen para la totalidad de registros que coinciden con los filtros
        $eventosTotales = (clone $query)->get();
        $totalRegistros = $eventosTotales->count();
        $totalContratado = (float)$eventosTotales->sum('monto_contratado');
        $totalCobrado = (float)$eventosTotales->sum('total_abonado');
        $totalSaldoPendiente = (float)$eventosTotales->sum('saldo_pendiente');

        $eventos = $query->orderBy('fecha_evento', 'desc')->paginate(15);

        $clientes = Cliente::orderBy('nombre_completo')->get(['id', 'nombre_completo']);
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.reportes.reporte-eventos-contratos', [
            'eventos' => $eventos,
            'clientes' => $clientes,
            'servicios' => $servicios,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'campo_fecha' => $this->campo_fecha,
            'cliente_id' => $this->cliente_id,
            'servicio_id' => $this->servicio_id,
            'estado_contrato' => $this->estado_contrato,
            'estado_evento' => $this->estado_evento,
            'search_lugar' => $this->search_lugar,
            'search' => $this->search,
            'resumen' => [
                'total_registros' => $totalRegistros,
                'total_contratado' => $totalContratado,
                'total_cobrado' => $totalCobrado,
                'total_saldo_pendiente' => $totalSaldoPendiente,
            ],
        ])->layout('components.layouts.app', ['title' => 'Reporte de Eventos y Contratos - Mariachi León Guanajuato']);
    }
}
