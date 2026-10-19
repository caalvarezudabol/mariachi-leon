<?php

namespace App\Livewire\Finanzas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MovimientoFinanciero;
use App\Models\Evento;
use App\Models\Contrato;
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
    public $filtro_categoria = '';
    public $filtro_estado = 'Valido'; // 'Valido', 'Anulado', 'todos'
    public $fecha_desde = '';
    public $fecha_hasta = '';

    // Modal Crear/Editar Movimiento
    public $modalOpen = false;
    public $tipo = 'EGRESO'; // 'EGRESO', 'INGRESO'
    public $clasificacion = 'OPERATIVO'; // 'OPERATIVO', 'EVENTO'
    public $categoria = 'Transporte y Combustible';
    public $evento_id = '';
    public $contrato_id = '';
    public $proveedor = '';
    public $concepto = '';
    public $descripcion = '';
    public $monto = '';
    public $metodo_pago = 'Efectivo';
    public $numero_comprobante = '';
    public $fecha = '';
    public $glosa = '';
    public $observaciones = '';

    // Modal de Anulación
    public $modalAnularOpen = false;
    public $movimiento_a_anular_id = null;
    public $movimiento_a_anular = null;
    public $motivo_anulacion = '';

    // Categorías predefinidas comunes
    public $categoriasOperativas = [
        'Alquiler de Local',
        'Servicios Básicos (Agua / Luz)',
        'Internet y Comunicaciones',
        'Mantenimiento de Instrumentos y Equipos',
        'Materiales y Papelería de Oficina',
        'Publicidad y Marketing',
        'Honorarios Profesionales',
        'Otros Gastos Operativos',
    ];

    public $categoriasEventos = [
        'Transporte y Combustible',
        'Viáticos y Pasajes',
        'Alimentación y Refrigerios del Personal',
        'Alquiler de Equipos Especiales',
        'Sonido y Microfonía Adicional',
        'Logística y Permisos',
        'Vestuario y Arreglos de Emergencia',
        'Otros Gastos del Evento',
    ];

    public $categoriasIngresos = [
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
            'concepto' => 'required|string|min:3|max:255',
            'descripcion' => 'nullable|string|max:1000',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|string|max:50',
            'numero_comprobante' => 'nullable|string|max:100',
            'fecha' => 'required|date',
            'glosa' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
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
        $this->metodo_pago = 'Efectivo';

        if ($tipo === 'INGRESO') {
            $this->categoria = 'Ingreso Operativo Extraordinario';
        } elseif ($clasificacion === 'EVENTO') {
            $this->categoria = 'Transporte y Combustible';
        } else {
            $this->categoria = 'Servicios Básicos (Agua / Luz)';
        }

        $this->modalOpen = true;
    }

    public function updatedClasificacion($val)
    {
        if ($val === 'EVENTO') {
            $this->categoria = 'Transporte y Combustible';
        } else {
            $this->categoria = 'Alquiler de Local';
            $this->evento_id = '';
            $this->contrato_id = '';
        }
    }

    public function updatedEventoId($val)
    {
        if ($val) {
            $evento = Evento::find($val);
            if ($evento && $evento->contrato) {
                $this->contrato_id = $evento->contrato->id;
            } else {
                $this->contrato_id = null;
            }
        }
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardarMovimiento()
    {
        $this->validate();

        DB::transaction(function () {
            $mov = MovimientoFinanciero::create([
                'tipo' => $this->tipo,
                'clasificacion' => $this->clasificacion,
                'categoria' => $this->categoria,
                'evento_id' => $this->clasificacion === 'EVENTO' ? $this->evento_id : null,
                'contrato_id' => $this->clasificacion === 'EVENTO' ? $this->contrato_id : null,
                'proveedor' => trim($this->proveedor) ?: null,
                'concepto' => trim($this->concepto),
                'descripcion' => trim($this->descripcion) ?: null,
                'monto' => (float)$this->monto,
                'metodo_pago' => $this->metodo_pago,
                'numero_comprobante' => trim($this->numero_comprobante) ?: null,
                'fecha' => $this->fecha,
                'glosa' => trim($this->glosa) ?: trim($this->concepto),
                'observaciones' => trim($this->observaciones) ?: null,
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
        $query = MovimientoFinanciero::with(['evento.cliente', 'user', 'anuladoPor']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('concepto', 'like', '%' . $this->search . '%')
                  ->orWhere('proveedor', 'like', '%' . $this->search . '%')
                  ->orWhere('numero_comprobante', 'like', '%' . $this->search . '%')
                  ->orWhere('glosa', 'like', '%' . $this->search . '%')
                  ->orWhereHas('evento', fn($e) => $e->where('codigo_evento', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->filtro_tipo) {
            $query->where('tipo', $this->filtro_tipo);
        }

        if ($this->filtro_clasificacion) {
            $query->where('clasificacion', $this->filtro_clasificacion);
        }

        if ($this->filtro_categoria) {
            $query->where('categoria', $this->filtro_categoria);
        }

        if ($this->filtro_estado && $this->filtro_estado !== 'todos') {
            $query->where('estado', $this->filtro_estado);
        }

        if ($this->fecha_desde) {
            $query->where('fecha', '>=', $this->fecha_desde);
        }

        if ($this->fecha_hasta) {
            $query->where('fecha', '<=', $this->fecha_hasta);
        }

        $movimientos = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->paginate(12);

        // Totales de la consulta actual
        $totalesConsulta = [
            'egresos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto'),
            'ingresos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto'),
            'operativos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->where('clasificacion', 'OPERATIVO')->sum('monto'),
            'eventos' => (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->where('clasificacion', 'EVENTO')->sum('monto'),
        ];

        $eventos = Evento::with('cliente')->orderBy('fecha_evento', 'desc')->limit(60)->get();

        return view('livewire.finanzas.gestion-movimientos', [
            'movimientos' => $movimientos,
            'totalesConsulta' => $totalesConsulta,
            'eventos' => $eventos,
            'search' => $this->search,
            'filtro_tipo' => $this->filtro_tipo,
            'filtro_clasificacion' => $this->filtro_clasificacion,
            'filtro_categoria' => $this->filtro_categoria,
            'filtro_estado' => $this->filtro_estado,
            'fecha_desde' => $this->fecha_desde,
            'fecha_hasta' => $this->fecha_hasta,
            'modalOpen' => $this->modalOpen,
            'modalAnularOpen' => $this->modalAnularOpen,
            'tipo' => $this->tipo,
            'clasificacion' => $this->clasificacion,
            'categoria' => $this->categoria,
            'categoriasOperativas' => $this->categoriasOperativas,
            'categoriasEventos' => $this->categoriasEventos,
            'categoriasIngresos' => $this->categoriasIngresos,
        ])->layout('components.layouts.app', ['title' => 'Ingresos y Gastos - Mariachi León Guanajuato']);
    }
}
