<?php

namespace App\Livewire\Finanzas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MovimientoFinanciero;
use App\Models\Evento;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionMovimientos extends Component
{
    use WithPagination, Auditable;

    // Filtros de búsqueda
    public $search = '';
    public $filtro_tipo = ''; // '', 'EGRESO', 'INGRESO'
    public $filtro_clasificacion = ''; // '', 'OPERATIVO', 'EVENTO'
    public $filtro_estado = 'Valido'; // 'Valido', 'Anulado', 'todos'
    public $fecha_desde = '';
    public $fecha_hasta = '';

    // Modal Crear Movimiento
    public $modalOpen = false;
    public $tipo = 'EGRESO'; // 'EGRESO', 'INGRESO'
    public $clasificacion = 'OPERATIVO'; // 'OPERATIVO', 'EVENTO'
    public $categoria = '';
    public $evento_id = '';
    public $contrato_id = '';
    public $proveedor = '';
    public $concepto = '';
    public $descripcion = '';
    public $monto = '';
    public $metodo_pago = 'EFECTIVO';
    public $numero_comprobante = '';
    public $fecha = '';
    public $glosa = '';
    public $observaciones = '';

    // Modal de Anulación
    public $modalAnularOpen = false;
    public $movimiento_a_anular_id = null;
    public $movimiento_a_anular = null;
    public $motivo_anulacion = '';

    // Categorías predefinidas (no viajan al navegador en cada request)
    protected $categoriasOperativas = [
        'Alquiler de Local',
        'Servicios Básicos (Agua / Luz)',
        'Internet y Comunicaciones',
        'Mantenimiento de Instrumentos y Equipos',
        'Materiales y Papelería de Oficina',
        'Publicidad y Marketing',
        'Honorarios Profesionales',
        'Otros Gastos Operativos',
    ];

    protected $categoriasEventos = [
        'Transporte y Combustible',
        'Viáticos y Pasajes',
        'Alimentación y Refrigerios del Personal',
        'Alquiler de Equipos Especiales',
        'Sonido y Microfonía Adicional',
        'Logística y Permisos',
        'Vestuario y Arreglos de Emergencia',
        'Otros Gastos del Evento',
    ];

    protected $categoriasIngresos = [
        'Ingreso Operativo Extraordinario',
        'Venta de Mercancía / Discos / Souvenirs',
        'Ajuste a Favor',
        'Otros Ingresos',
    ];

    protected function rules()
    {
        return [
            'tipo' => 'required|in:EGRESO,INGRESO',
            'clasificacion' => 'required|in:OPERATIVO,EVENTO',
            'categoria' => 'required|string|max:100',
            'evento_id' => 'nullable|required_if:clasificacion,EVENTO|exists:eventos,id',
            'proveedor' => 'nullable|string|max:150',
            'concepto' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|in:EFECTIVO,QR',
            'numero_comprobante' => 'nullable|string|max:100',
            'fecha' => 'required|date',
            'glosa' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    protected function messages()
    {
        return [
            'evento_id.required_if' => 'Elige el show de este gasto.',
            'categoria.required' => 'Elige o escribe una categoría.',
        ];
    }

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }
    public function updatingFiltroClasificacion()
    {
        $this->resetPage();
    }
    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }
    public function updatingFechaDesde()
    {
        $this->resetPage();
    }
    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtro_tipo = '';
        $this->filtro_clasificacion = '';
        $this->filtro_estado = 'Valido';
        $this->fecha_desde = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
        $this->resetPage();
    }

    /**
     * Consulta base con los filtros activos (sin relaciones, para poder usarla en sumas y conteos).
     * $conTipo / $conEstado permiten ignorar esos dos filtros en los totales.
     */
    private function movimientosQuery(bool $conTipo = true, bool $conEstado = true)
    {
        $query = MovimientoFinanciero::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('concepto', 'like', '%' . $this->search . '%')
                    ->orWhere('proveedor', 'like', '%' . $this->search . '%')
                    ->orWhere('numero_comprobante', 'like', '%' . $this->search . '%')
                    ->orWhere('glosa', 'like', '%' . $this->search . '%')
                    ->orWhereHas('evento', fn($e) => $e->where('codigo_evento', 'like', '%' . $this->search . '%'));
            });
        }

        if ($conTipo && $this->filtro_tipo) {
            $query->where('tipo', $this->filtro_tipo);
        }

        if ($this->filtro_clasificacion) {
            $query->where('clasificacion', $this->filtro_clasificacion);
        }

        if ($conEstado && $this->filtro_estado && $this->filtro_estado !== 'todos') {
            $query->where('estado', $this->filtro_estado);
        }

        if ($this->fecha_desde) {
            $query->where('fecha', '>=', $this->fecha_desde);
        }

        if ($this->fecha_hasta) {
            $query->where('fecha', '<=', $this->fecha_hasta);
        }

        return $query;
    }

    public function abrirModalCrear($tipo = 'EGRESO', $clasificacion = 'OPERATIVO')
    {
        $this->resetValidation();
        $this->tipo = $tipo;
        $this->clasificacion = ($tipo === 'INGRESO') ? 'OPERATIVO' : $clasificacion;
        $this->fecha = now()->format('Y-m-d');
        $this->monto = '';
        $this->concepto = '';
        $this->descripcion = '';
        $this->proveedor = '';
        $this->numero_comprobante = '';
        $this->glosa = '';
        $this->observaciones = '';
        $this->evento_id = '';
        $this->contrato_id = '';
        $this->metodo_pago = 'EFECTIVO';
        $this->categoria = '';

        $this->modalOpen = true;
    }

    public function updatedClasificacion($val)
    {
        if ($val !== 'EVENTO') {
            $this->evento_id = '';
            $this->contrato_id = '';
        }
        $this->categoria = '';
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardarMovimiento()
    {
        $this->validate();

        $this->categoria = trim($this->categoria);
        $this->concepto = trim((string) $this->concepto) ?: $this->categoria;

        // El contrato se toma del evento al guardar (no depende de ningún evento de la pantalla)
        $contratoId = null;
        if ($this->clasificacion === 'EVENTO') {
            $evento = Evento::with('contrato')->find($this->evento_id);
            $contratoId = $evento && $evento->contrato ? $evento->contrato->id : null;
        }

        DB::transaction(function () use ($contratoId) {
            $mov = MovimientoFinanciero::create([
                'tipo' => $this->tipo,
                'clasificacion' => $this->clasificacion,
                'categoria' => $this->categoria,
                'evento_id' => $this->clasificacion === 'EVENTO' ? $this->evento_id : null,
                'contrato_id' => $contratoId,
                'proveedor' => trim((string) $this->proveedor) ?: null,
                'concepto' => trim($this->concepto),
                'descripcion' => trim((string) $this->descripcion) ?: null,
                'monto' => (float) $this->monto,
                'metodo_pago' => $this->metodo_pago,
                'numero_comprobante' => trim((string) $this->numero_comprobante) ?: null,
                'fecha' => $this->fecha,
                'glosa' => trim((string) $this->glosa) ?: trim($this->concepto),
                'observaciones' => trim((string) $this->observaciones) ?: null,
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            $eventoRef = $mov->evento ? " (Evento: {$mov->evento->codigo_evento})" : '';
            $this->registrarAuditoria(
                'Gestión Financiera',
                "Registrar {$this->tipo} Financiero",
                "Se registró {$this->tipo} ({$this->clasificacion}): '{$mov->concepto}' por Bs. " . number_format($mov->monto, 2) . " en categoría {$mov->categoria}{$eventoRef}."
            );
        });

        $this->cerrarModal();
        session()->flash('message', 'Movimiento financiero registrado correctamente.');
    }

    public function abrirModalAnular($id)
    {
        $this->movimiento_a_anular = MovimientoFinanciero::with(['evento', 'user'])->findOrFail($id);
        $this->movimiento_a_anular_id = $id;
        $this->motivo_anulacion = '';
        $this->resetValidation('motivo_anulacion');
        $this->modalAnularOpen = true;
    }

    public function cerrarModalAnular()
    {
        $this->modalAnularOpen = false;
        $this->movimiento_a_anular_id = null;
        $this->movimiento_a_anular = null;
        $this->motivo_anulacion = '';
        $this->resetValidation('motivo_anulacion');
    }

    public function confirmarAnulacion()
    {
        $this->validate([
            'motivo_anulacion' => 'required|string|min:5|max:255',
        ], [
            'motivo_anulacion.required' => 'Debe ingresar un motivo para anular este movimiento.',
            'motivo_anulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $mov = MovimientoFinanciero::findOrFail($this->movimiento_a_anular_id);

        if ($mov->estado === 'Anulado') {
            session()->flash('error', 'Este movimiento ya se encuentra anulado.');
            $this->cerrarModalAnular();
            return;
        }

        DB::transaction(function () use ($mov) {
            $mov->update([
                'estado' => 'Anulado',
                'motivo_anulacion' => trim($this->motivo_anulacion),
                'fecha_anulacion' => now(),
                'anulado_por' => Auth::id(),
            ]);

            $this->registrarAuditoria(
                'Gestión Financiera',
                "Anular {$mov->tipo} Financiero",
                "Se anuló el movimiento ID #{$mov->id} ('{$mov->concepto}', Bs. " . number_format($mov->monto, 2) . "). Motivo: {$this->motivo_anulacion}."
            );
        });

        $this->cerrarModalAnular();
        session()->flash('message', 'El movimiento ha sido anulado exitosamente y excluido de los balances vigentes.');
    }

    public function render()
    {
        $movimientos = $this->movimientosQuery()
            ->with(['evento.cliente', 'user', 'anuladoPor'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(12);

        // Totales del resumen: ignoran el filtro de tipo y solo cuentan movimientos válidos
        $sumas = $this->movimientosQuery(false, false)
            ->where('estado', 'Valido')
            ->selectRaw('tipo, clasificacion, SUM(monto) as total')
            ->groupBy('tipo', 'clasificacion')
            ->get();

        $suma = fn($tipo, $clasif = null) => (float) $sumas
            ->where('tipo', $tipo)
            ->when($clasif, fn($c) => $c->where('clasificacion', $clasif))
            ->sum('total');

        $totalesConsulta = [
            'egresos' => $suma('EGRESO'),
            'ingresos' => $suma('INGRESO'),
            'operativos' => $suma('EGRESO', 'OPERATIVO'),
            'eventos' => $suma('EGRESO', 'EVENTO'),
        ];

        // Contadores para los chips de tipo (respetan búsqueda, clasificación, fechas y estado)
        $contTipos = $this->movimientosQuery(false)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        // Datos del modal (solo se cargan cuando hacen falta)
        $eventosOptions = [];
        if ($this->modalOpen && $this->clasificacion === 'EVENTO') {
            $eventosOptions = Evento::with('cliente')
                ->orderBy('fecha_evento', 'desc')
                ->limit(200)
                ->get()
                ->map(function ($e) {
                    $cliente = $e->cliente->nombre_completo ?? 'Cliente S/N';
                    $fecha = \Carbon\Carbon::parse($e->fecha_evento)->format('d/m/Y');
                    return [
                        'id' => $e->id,
                        'label' => $e->codigo_evento,
                        'meta' => $fecha . ' - ' . $cliente,
                        'search' => mb_strtolower($e->codigo_evento . ' ' . $cliente . ' ' . $fecha),
                    ];
                })
                ->values()
                ->all();
        }

        // Categorías: las sugeridas + las que ya se escribieron antes (así lo nuevo queda disponible solo)
        $categoriasModal = [];
        if ($this->modalOpen) {
            $base = $this->tipo === 'INGRESO'
                ? $this->categoriasIngresos
                : ($this->clasificacion === 'EVENTO' ? $this->categoriasEventos : $this->categoriasOperativas);
            $usadas = MovimientoFinanciero::where('tipo', $this->tipo)
                ->where('clasificacion', $this->clasificacion)
                ->where('estado', 'Valido')
                ->distinct()->orderBy('categoria')->limit(60)->pluck('categoria')->all();
            $categoriasModal = array_values(array_unique(array_merge($base, $usadas)));
        }

        return view('livewire.finanzas.gestion-movimientos', [
            'movimientos' => $movimientos,
            'totalesConsulta' => $totalesConsulta,
            'contTipos' => $contTipos,
            'eventosOptions' => $eventosOptions,
            'categoriasModal' => $categoriasModal,
        ])->layout('components.layouts.app', [
            'title' => 'Ingresos y Gastos - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}