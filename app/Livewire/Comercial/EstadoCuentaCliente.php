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
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstadoCuentaCliente extends Component
{
    use WithPagination, Auditable;

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

    public function mount(Cliente $cliente)
    {
        $this->cliente = $cliente;

        // Si se recibe un contrato por parámetro GET en la URL
        $contratoParam = request()->query('contrato_id');
        if ($contratoParam) {
            $this->contrato_id = $contratoParam;
            $this->tipo_reporte = 'contrato';
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
        if ($contrato) {
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Consultar Estado de Cuenta Contrato',
                "Se consultó el Estado de Cuenta del Contrato N° {$contrato->numero_contrato} del cliente {$this->cliente->nombre_completo}"
            );
        }
    }

    public function limpiarFiltros()
    {
        $this->reset(['fecha_inicio', 'fecha_fin', 'metodo_pago', 'usuario_id', 'estado_pago', 'search_recibo']);
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

    public function exportarExcel(): StreamedResponse
    {
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Exportar Excel Estado de Cuenta',
            "Se exportó a Excel el Estado de Cuenta (" . strtoupper($this->tipo_reporte) . ") del cliente {$this->cliente->nombre_completo}"
        );

        $clienteNombre = str_replace(' ', '_', strtolower($this->cliente->nombre_completo));
        $tipo = $this->tipo_reporte;
        $filename = "estado_cuenta_{$tipo}_{$clienteNombre}_" . date('Ymd_His') . ".csv";

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM para que Excel abra directamente con tildes y caracteres especiales
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($this->tipo_reporte === 'contrato') {
                $contrato = Contrato::with(['evento', 'servicio'])->where('cliente_id', $this->cliente->id)->find($this->contrato_id);
                
                fputcsv($handle, ['MARIACHI LEÓN GUANAJUATO S.R.L. - ESTADO DE CUENTA POR CONTRATO']);
                fputcsv($handle, ['Fecha de Emisión:', date('d/m/Y H:i:s')]);
                fputcsv($handle, ['Cliente:', $this->cliente->nombre_completo, 'CI/NIT:', $this->cliente->ci_nit ?: 'S/N']);
                fputcsv($handle, ['Teléfono:', $this->cliente->telefono ?: $this->cliente->whatsapp, 'Correo:', $this->cliente->email]);
                fputcsv($handle, ['N° Contrato:', $contrato ? $contrato->numero_contrato : 'N/A', 'Fecha Contrato:', $contrato && $contrato->fecha_contrato ? $contrato->fecha_contrato->format('d/m/Y') : 'N/A']);
                fputcsv($handle, ['Servicio:', $contrato && $contrato->servicio ? $contrato->servicio->nombre : 'N/A', 'Estado Contrato:', $contrato ? $contrato->estado : 'N/A']);
                fputcsv($handle, []);

                // Resumen Financiero
                $montoContratado = $contrato ? (float)$contrato->monto_total : 0;
                $totalAbonado = $contrato ? (float)$contrato->pagos()->where('estado', 'Valido')->sum('monto') : 0;
                $saldoPendiente = max(0, $montoContratado - $totalAbonado);
                fputcsv($handle, ['RESUMEN FINANCIERO']);
                fputcsv($handle, ['Monto Contratado (Bs)', 'Total Abonado (Bs)', 'Saldo Pendiente (Bs)']);
                fputcsv($handle, [number_format($montoContratado, 2, '.', ''), number_format($totalAbonado, 2, '.', ''), number_format($saldoPendiente, 2, '.', '')]);
                fputcsv($handle, []);

                // Tabla de Pagos
                fputcsv($handle, ['HISTORIAL DE PAGOS']);
                fputcsv($handle, ['N°', 'Recibo', 'Fecha y Hora', 'Detalle o Concepto', 'Importe (Bs)', 'Método de Pago', 'Usuario que Cobró', 'Estado']);

                $queryPagos = $contrato ? $contrato->pagos()->with('user') : Pago::whereRaw('1=0');
                $this->aplicarFiltrosQuery($queryPagos);
                $pagos = $queryPagos->orderBy($this->sortField, $this->sortDirection)->get();

                foreach ($pagos as $idx => $p) {
                    fputcsv($handle, [
                        $idx + 1,
                        $p->numero_recibo,
                        $p->fecha_pago ? $p->fecha_pago->format('d/m/Y H:i') : '-',
                        $p->observaciones ?: 'Abono a cuenta por servicio musical',
                        number_format($p->monto, 2, '.', ''),
                        $p->metodo_pago,
                        $p->user ? $p->user->name : 'Sistema',
                        $p->estado,
                    ]);
                }
            } else {
                // Reporte General
                fputcsv($handle, ['MARIACHI LEÓN GUANAJUATO S.R.L. - ESTADO DE CUENTA GENERAL']);
                fputcsv($handle, ['Fecha de Emisión:', date('d/m/Y H:i:s')]);
                fputcsv($handle, ['Cliente:', $this->cliente->nombre_completo, 'CI/NIT:', $this->cliente->ci_nit ?: 'S/N']);
                fputcsv($handle, ['Teléfono:', $this->cliente->telefono ?: $this->cliente->whatsapp, 'Correo:', $this->cliente->email]);
                fputcsv($handle, []);

                // Contratos
                $contratos = $this->cliente->contratos()->with(['evento', 'servicio', 'pagos'])->orderBy('fecha_contrato', 'desc')->get();
                $montoContratadoTotal = (float)$contratos->sum('monto_total');
                $totalAbonadoNeto = (float)Pago::where('cliente_id', $this->cliente->id)->where('estado', 'Valido')->sum('monto');
                $saldoPendienteTotal = max(0, $montoContratadoTotal - $totalAbonadoNeto);

                fputcsv($handle, ['RESUMEN FINANCIERO CONSOLIDADO']);
                fputcsv($handle, ['Total Contratos', 'Monto Contratado Total (Bs)', 'Total Abonado Neto (Bs)', 'Saldo Pendiente Total (Bs)']);
                fputcsv($handle, [$contratos->count(), number_format($montoContratadoTotal, 2, '.', ''), number_format($totalAbonadoNeto, 2, '.', ''), number_format($saldoPendienteTotal, 2, '.', '')]);
                fputcsv($handle, []);

                fputcsv($handle, ['RESUMEN DE CONTRATOS']);
                fputcsv($handle, ['N°', 'Contrato', 'Fecha', 'Servicio', 'Monto Contratado (Bs)', 'Total Abonado (Bs)', 'Saldo Pendiente (Bs)', 'Estado']);
                foreach ($contratos as $idx => $c) {
                    $ab = (float)$c->pagos->where('estado', 'Valido')->sum('monto');
                    $sp = max(0, (float)$c->monto_total - $ab);
                    fputcsv($handle, [
                        $idx + 1,
                        $c->numero_contrato,
                        $c->fecha_contrato ? $c->fecha_contrato->format('d/m/Y') : '-',
                        $c->servicio ? $c->servicio->nombre : 'Servicio Musical',
                        number_format($c->monto_total, 2, '.', ''),
                        number_format($ab, 2, '.', ''),
                        number_format($sp, 2, '.', ''),
                        $c->estado,
                    ]);
                }
                fputcsv($handle, []);

                // Historial Consolidado de Pagos
                fputcsv($handle, ['HISTORIAL CONSOLIDADO DE PAGOS']);
                fputcsv($handle, ['N°', 'Recibo', 'Contrato', 'Fecha y Hora', 'Detalle o Concepto', 'Importe (Bs)', 'Método de Pago', 'Usuario que Cobró', 'Estado']);

                $queryPagos = Pago::with(['contrato', 'user'])->where('cliente_id', $this->cliente->id);
                $this->aplicarFiltrosQuery($queryPagos);
                $pagos = $queryPagos->orderBy($this->sortField, $this->sortDirection)->get();

                foreach ($pagos as $idx => $p) {
                    fputcsv($handle, [
                        $idx + 1,
                        $p->numero_recibo,
                        $p->contrato ? $p->contrato->numero_contrato : 'N/A',
                        $p->fecha_pago ? $p->fecha_pago->format('d/m/Y H:i') : '-',
                        $p->observaciones ?: 'Abono a cuenta por servicio musical',
                        number_format($p->monto, 2, '.', ''),
                        $p->metodo_pago,
                        $p->user ? $p->user->name : 'Sistema',
                        $p->estado,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
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
            if ($this->contrato_id) {
                // Filtro opcional por contrato dentro del consolidado si se desea
            }
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
