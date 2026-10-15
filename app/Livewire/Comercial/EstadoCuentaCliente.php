<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\User;
use App\Models\Empresa;
use App\Traits\Auditable;
use App\Services\Security\DocumentToken;

class EstadoCuentaCliente extends Component
{
    use WithPagination, Auditable;

    public $token = '';
    public Cliente $cliente;
    public $tipo_reporte = 'general'; // 'general' | 'contrato'
    public $contrato_id = null;

    // Filtros
    public $fecha_inicio = '';
    public $fecha_fin = '';
    public $metodo_pago = '';
    public $usuario_id = '';
    public $estado_pago = 'Valido'; // 'Todos', 'Valido', 'Anulado'
    public $search_recibo = '';

    // Ordenamiento
    public $sortField = 'fecha_pago';
    public $sortDirection = 'asc';

    public function mount($token)
    {
        // 1. Rechazar accesos que utilicen IDs numéricos simples directos (anti-enumeración)
        if (DocumentToken::isPlainNumeric($token) || ctype_digit((string)$token)) {
            return redirect()->route('web.home');
        }

        // 2. Verificar parámetro contrato en URL si se envía numérico simple
        $contratoParam = request()->query('contrato_id') ?? request()->query('contrato');
        if ($contratoParam !== null && (ctype_digit((string)$contratoParam) || is_numeric($contratoParam))) {
            return redirect()->route('web.home');
        }

        // 3. Decodificar el token del cliente
        $clienteId = DocumentToken::decodeId($token, 'cliente');
        if (!$clienteId) {
            return redirect()->route('web.home');
        }

        $clienteObj = Cliente::find($clienteId);
        if (!$clienteObj) {
            return redirect()->route('web.home');
        }
        $this->cliente = $clienteObj;
        $this->token = $token;

        // 4. Si se recibe un contrato por parámetro en la URL
        if ($contratoParam) {
            $decodedContratoId = DocumentToken::decodeId($contratoParam, 'contrato');
            if ($decodedContratoId) {
                $this->contrato_id = $decodedContratoId;
            } else {
                $contratoObj = Contrato::where('codigo_verificacion', $contratoParam)
                    ->where('cliente_id', $this->cliente->id)
                    ->first();
                if ($contratoObj) {
                    $this->contrato_id = $contratoObj->id;
                }
            }

            if ($this->contrato_id) {
                $this->tipo_reporte = 'contrato';
            }
        } else {
            // Seleccionar por defecto el primer contrato si existe para cuando se cambie a modo contrato
            $primerContrato = $this->cliente->contratos()->first();
            if ($primerContrato) {
                $this->contrato_id = $primerContrato->id;
            }
        }

        // Auditoría obligatoria de acceso a la vista
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Consultar Estado de Cuenta',
            "Se consultó el Estado de Cuenta (" . strtoupper($this->tipo_reporte) . ") del cliente {$this->cliente->nombre_completo} (CI/NIT: " . ($this->cliente->ci_nit ?: 'S/N') . ")"
        );
    }

    public function cambiarTipoReporte($tipo)
    {
        $this->tipo_reporte = $tipo;
        $this->resetPage();

        $this->registrarAuditoria(
            'Gestión Comercial',
            'Cambiar Tipo Estado de Cuenta',
            "Se cambió la vista a Estado de Cuenta " . strtoupper($tipo) . " para el cliente {$this->cliente->nombre_completo}"
        );
    }

    public function seleccionarContrato($id)
    {
        $this->contrato_id = $id;
        $this->tipo_reporte = 'contrato';
        $this->resetPage();

        $contrato = Contrato::find($id);
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Seleccionar Contrato Estado Cuenta',
            "Se seleccionó el Contrato N° " . ($contrato ? $contrato->numero_contrato : $id) . " para el cliente {$this->cliente->nombre_completo}"
        );
    }

    public function updatingSearchRecibo()
    {
        $this->resetPage();
    }

    public function updatingFechaInicio()
    {
        $this->resetPage();
    }

    public function updatingFechaFin()
    {
        $this->resetPage();
    }

    public function updatingMetodoPago()
    {
        $this->resetPage();
    }

    public function updatingUsuarioId()
    {
        $this->resetPage();
    }

    public function updatingEstadoPago()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'fecha_inicio',
            'fecha_fin',
            'metodo_pago',
            'usuario_id',
            'estado_pago',
            'search_recibo'
        ]);
        $this->estado_pago = 'Valido';
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    protected function aplicarFiltrosQuery($query)
    {
        if ($this->fecha_inicio) {
            $query->whereDate('fecha_pago', '>=', $this->fecha_inicio);
        }
        if ($this->fecha_fin) {
            $query->whereDate('fecha_pago', '<=', $this->fecha_fin);
        }
        if ($this->metodo_pago) {
            $query->where('metodo_pago', $this->metodo_pago);
        }
        if ($this->usuario_id) {
            $query->where('user_id', $this->usuario_id);
        }
        if ($this->estado_pago && $this->estado_pago !== 'Todos') {
            $query->where('estado', $this->estado_pago);
        }
        if ($this->search_recibo) {
            $query->where('numero_recibo', 'like', "%{$this->search_recibo}%");
        }
    }

    public function render()
    {
        $contratosCliente = $this->cliente->contratos()
            ->with(['evento', 'servicio', 'pagos'])
            ->orderBy('fecha_contrato', 'desc')
            ->get();

        $empresa = Empresa::obtener();
        $usuariosCobradores = User::orderBy('name')->get();

        if ($this->tipo_reporte === 'contrato') {
            // Contrato actual seleccionado
            $contratoSeleccionado = $contratosCliente->firstWhere('id', $this->contrato_id) ?: $contratosCliente->first();
            
            if ($contratoSeleccionado) {
                $this->contrato_id = $contratoSeleccionado->id;
                $montoContratado = (float)$contratoSeleccionado->monto_total;
                $totalAbonado = (float)$contratoSeleccionado->pagos->where('estado', 'Valido')->sum('monto');
                $saldoPendiente = max(0, $montoContratado - $totalAbonado);
                $cantidadPagosValidos = $contratoSeleccionado->pagos->where('estado', 'Valido')->count();
                $ultimoPago = $contratoSeleccionado->pagos->where('estado', 'Valido')->sortByDesc('fecha_pago')->first();

                // Consulta de pagos con filtros y paginación
                $queryPagos = $contratoSeleccionado->pagos()->with('user');
                $this->aplicarFiltrosQuery($queryPagos);
                $pagos = $queryPagos->orderBy($this->sortField, $this->sortDirection)->paginate(15);
            } else {
                $contratoSeleccionado = null;
                $montoContratado = 0;
                $totalAbonado = 0;
                $saldoPendiente = 0;
                $cantidadPagosValidos = 0;
                $ultimoPago = null;
                $pagos = collect();
            }

            return view('livewire.comercial.estado-cuenta-cliente', [
                'empresa' => $empresa,
                'contratosCliente' => $contratosCliente,
                'contratoSeleccionado' => $contratoSeleccionado,
                'montoContratado' => $montoContratado,
                'totalAbonado' => $totalAbonado,
                'saldoPendiente' => $saldoPendiente,
                'cantidadPagosValidos' => $cantidadPagosValidos,
                'ultimoPago' => $ultimoPago,
                'pagos' => $pagos,
                'usuariosCobradores' => $usuariosCobradores,
            ])->layout('components.layouts.app', ['title' => 'Estado de Cuenta - ' . $this->cliente->nombre_completo]);

        } else {
            // Estado de Cuenta General
            $totalContratos = $contratosCliente->count();
            $montoContratadoTotal = (float)$contratosCliente->sum('monto_total');
            $totalAbonadoNeto = (float)Pago::where('cliente_id', $this->cliente->id)->where('estado', 'Valido')->sum('monto');
            $saldoPendienteTotal = max(0, $montoContratadoTotal - $totalAbonadoNeto);
            $cantidadPagosValidosTotal = Pago::where('cliente_id', $this->cliente->id)->where('estado', 'Valido')->count();
            $ultimoPagoTotal = Pago::where('cliente_id', $this->cliente->id)->where('estado', 'Valido')->orderBy('fecha_pago', 'desc')->first();

            // Consulta de pagos consolidados con filtros
            $queryPagos = Pago::with(['contrato', 'user'])->where('cliente_id', $this->cliente->id);
            $this->aplicarFiltrosQuery($queryPagos);
            $pagos = $queryPagos->orderBy($this->sortField, $this->sortDirection)->paginate(15);

            return view('livewire.comercial.estado-cuenta-cliente', [
                'empresa' => $empresa,
                'contratosCliente' => $contratosCliente,
                'totalContratos' => $totalContratos,
                'montoContratadoTotal' => $montoContratadoTotal,
                'totalAbonadoNeto' => $totalAbonadoNeto,
                'saldoPendienteTotal' => $saldoPendienteTotal,
                'cantidadPagosValidosTotal' => $cantidadPagosValidosTotal,
                'ultimoPagoTotal' => $ultimoPagoTotal,
                'pagos' => $pagos,
                'usuariosCobradores' => $usuariosCobradores,
            ])->layout('components.layouts.app', ['title' => 'Estado de Cuenta General - ' . $this->cliente->nombre_completo]);
        }
    }
}
