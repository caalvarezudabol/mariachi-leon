<?php

namespace App\Livewire\Reportes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\QrTransaccion;
use App\Models\User;
use App\Traits\Auditable;
use Carbon\Carbon;

class ReporteCobrosQr extends Component
{
    use WithPagination, Auditable;

    protected $paginationTheme = 'tailwind';

    public $periodo_preset = 'este_mes';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $mes = '';
    public $origen_filtro = ''; // '' = Todos, 'web' = Web pública (cliente), 'sistema' = Cobro sistema
    public $usuario_id = '';
    public $estado_filtro = 'PAGADO'; // 'PAGADO', 'PENDIENTE', 'EXPIRADO', 'ANULADO', '' = Todos
    public $search = '';
    public $perPage = 15;

    public function mount()
    {
        $this->aplicarPreset('este_mes');
        $this->mes = now()->format('Y-m');

        $this->registrarAuditoria(
            'Informes y Reportes',
            'Consultar Reporte Cobros QR',
            'El usuario accedió al reporte y listado de cobros por código QR (Banco Económico).'
        );
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingOrigenFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->periodo_preset = 'personalizado';
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->periodo_preset = 'personalizado';
        $this->resetPage();
    }

    public function updatedMes($val)
    {
        if ($val) {
            $carbon = Carbon::createFromFormat('Y-m', $val)->startOfMonth();
            $this->fecha_desde = $carbon->startOfMonth()->format('Y-m-d');
            $this->fecha_hasta = $carbon->copy()->endOfMonth()->format('Y-m-d');
            $this->periodo_preset = 'personalizado';
            $this->resetPage();
        }
    }

    public function updatedOrigenFiltro($val)
    {
        if ($val === 'web') {
            $this->usuario_id = '';
        }
    }

    public function aplicarPreset($preset)
    {
        $this->periodo_preset = $preset;

        switch ($preset) {
            case 'este_mes':
                $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
                $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
                $this->mes = now()->format('Y-m');
                break;
            case 'mes_anterior':
                $this->fecha_desde = now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->fecha_hasta = now()->subMonth()->endOfMonth()->format('Y-m-d');
                $this->mes = now()->subMonth()->format('Y-m');
                break;
            case 'este_ano':
                $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
                $this->fecha_hasta = now()->endOfYear()->format('Y-m-d');
                $this->mes = '';
                break;
            case 'todo':
                $this->fecha_desde = '';
                $this->fecha_hasta = '';
                $this->mes = '';
                break;
        }

        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'origen_filtro', 'usuario_id']);
        $this->estado_filtro = 'PAGADO';
        $this->aplicarPreset('este_mes');
    }

    private function queryBase()
    {
        $query = QrTransaccion::with(['contactoWeb', 'contrato.cliente', 'pago.cliente', 'cliente', 'creador']);

        // Filtro Fechas
        if ($this->fecha_desde) {
            $query->whereDate('created_at', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->whereDate('created_at', '<=', $this->fecha_hasta);
        }

        // Filtro Origen / Usuario
        if ($this->origen_filtro === 'web') {
            $query->where('origen', 'contacto_web');
        } elseif ($this->origen_filtro === 'sistema') {
            $query->where(function ($q) {
                $q->where('origen', '!=', 'contacto_web')
                  ->orWhereNotNull('created_by');
            });
            if ($this->usuario_id) {
                $query->where('created_by', $this->usuario_id);
            }
        } else {
            // Todos los orígenes
            if ($this->usuario_id) {
                $query->where('created_by', $this->usuario_id);
            }
        }

        // Filtro Estado
        if ($this->estado_filtro !== '') {
            $query->where('estado', $this->estado_filtro);
        }

        // Filtro Búsqueda
        if (trim($this->search)) {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('transaction_id', 'like', $s)
                  ->orWhere('qr_id', 'like', $s)
                  ->orWhere('descripcion', 'like', $s)
                  ->orWhereHas('cliente', fn($c) => $c->where('nombre_completo', 'like', $s)->orWhere('telefono', 'like', $s))
                  ->orWhereHas('contactoWeb', fn($cw) => $cw->where('nombre', 'like', $s)->orWhere('telefono', 'like', $s))
                  ->orWhereHas('contrato.cliente', fn($cc) => $cc->where('nombre_completo', 'like', $s)->orWhere('telefono', 'like', $s))
                  ->orWhereHas('creador', fn($u) => $u->where('name', 'like', $s));
            });
        }

        return $query;
    }

    public function render()
    {
        $query = $this->queryBase();

        // Cálculo de KPIs sobre la consulta filtrada (respetando fechas y filtros activos)
        $cloneBase = clone $query;
        $todasFiltradas = $cloneBase->get();

        $kpis = [
            'total_cobrado' => (float) $todasFiltradas->where('estado', 'PAGADO')->sum('monto'),
            'cantidad_cobrados' => $todasFiltradas->where('estado', 'PAGADO')->count(),
            'total_web' => (float) $todasFiltradas->where('estado', 'PAGADO')->where('origen', 'contacto_web')->sum('monto'),
            'cantidad_web' => $todasFiltradas->where('estado', 'PAGADO')->where('origen', 'contacto_web')->count(),
            'total_sistema' => (float) $todasFiltradas->where('estado', 'PAGADO')->filter(fn($t) => $t->origen !== 'contacto_web' || $t->created_by !== null)->sum('monto'),
            'cantidad_sistema' => $todasFiltradas->where('estado', 'PAGADO')->filter(fn($t) => $t->origen !== 'contacto_web' || $t->created_by !== null)->count(),
            'total_pendientes' => (float) $todasFiltradas->where('estado', 'PENDIENTE')->sum('monto'),
            'cantidad_pendientes' => $todasFiltradas->where('estado', 'PENDIENTE')->count(),
            'total_general' => (float) $todasFiltradas->sum('monto'),
            'cantidad_total' => $todasFiltradas->count(),
        ];

        $transacciones = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        $usuariosSistema = User::where('activo', true)->orderBy('name')->get(['id', 'name', 'email']);

        return view('livewire.reportes.reporte-cobros-qr', [
            'transacciones' => $transacciones,
            'kpis' => $kpis,
            'usuariosSistema' => $usuariosSistema,
        ])->layout('components.layouts.app', ['title' => 'Reporte de Cobros por QR - Mariachi León']);
    }
}
