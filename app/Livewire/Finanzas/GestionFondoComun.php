<?php

namespace App\Livewire\Finanzas;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FondoComunMovimiento;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GestionFondoComun extends Component
{
    use WithPagination, Auditable;

    // Filtros
    public $search = '';
    public $filtro_tipo = ''; // '', 'INGRESO', 'EGRESO'
    public $filtro_estado = 'Valido'; // 'Valido', 'Anulado', 'todos'
    public $fecha_desde = '';
    public $fecha_hasta = '';

    // Modal Crear (Aporte / Retiro)
    public $modalOpen = false;
    public $tipo = 'INGRESO'; // 'INGRESO' (Aporte), 'EGRESO' (Retiro)
    public $concepto = '';
    public $monto = '';
    public $fecha = '';
    public $metodo = 'Efectivo';
    public $glosa = '';
    public $observaciones = '';

    // Modal Anular
    public $modalAnularOpen = false;
    public $movimiento_a_anular_id = null;
    public $movimiento_a_anular = null;
    public $motivo_anulacion = '';

    // Conceptos sugeridos
    public $conceptosAporte = [
        'Aporte Regular de Integrantes',
        'Porcentaje Retenido de Contrato',
        'Fondo de Reserva de Evento',
        'Rendimiento o Ajuste a Favor',
        'Otro Aporte al Fondo Común',
    ];

    public $conceptosRetiro = [
        'Mantenimiento / Reparación de Equipos de la Agrupación',
        'Compra de Insumos o Accesorios Comunes',
        'Fondo de Emergencia para Integrante',
        'Viáticos Extraordinarios de Gira',
        'Gastos Institucionales no Imputables a Evento',
        'Otro Retiro del Fondo Común',
    ];

    protected function rules()
    {
        return [
            'tipo' => 'required|in:INGRESO,EGRESO',
            'concepto' => 'required|string|min:3|max:255',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo' => 'required|string|max:50',
            'glosa' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->fecha_desde = now()->startOfYear()->format('Y-m-d');
        $this->fecha_hasta = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function abrirModalCrear($tipo = 'INGRESO')
    {
        $this->resetValidation();
        $this->tipo = $tipo;
        $this->fecha = now()->format('Y-m-d');
        $this->monto = '';
        $this->concepto = ($tipo === 'INGRESO') ? $this->conceptosAporte[0] : $this->conceptosRetiro[0];
        $this->metodo = 'Efectivo';
        $this->glosa = '';
        $this->observaciones = '';
        $this->modalOpen = true;
    }

    public function cerrarModal()
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardarMovimiento()
    {
        $this->validate();

        $saldoActual = FondoComunMovimiento::obtenerSaldoActual();
        $montoFloat = (float) $this->monto;

        // Validación de sobregiro para retiros
        if ($this->tipo === 'EGRESO' && $montoFloat > $saldoActual) {
            $this->addError('monto', "El monto a retirar (Bs. " . number_format($montoFloat, 2) . ") supera el saldo actual del fondo común (Bs. " . number_format($saldoActual, 2) . "). No se permiten saldos negativos.");
            return;
        }

        DB::transaction(function () use ($saldoActual, $montoFloat) {
            $nuevoSaldo = ($this->tipo === 'INGRESO') 
                ? $saldoActual + $montoFloat 
                : $saldoActual - $montoFloat;

            $mov = FondoComunMovimiento::create([
                'tipo' => $this->tipo,
                'concepto' => trim($this->concepto),
                'monto' => $montoFloat,
                'saldo_anterior' => $saldoActual,
                'saldo_resultante' => $nuevoSaldo,
                'fecha' => $this->fecha,
                'metodo' => $this->metodo,
                'glosa' => trim($this->glosa) ?: trim($this->concepto),
                'observaciones' => trim($this->observaciones) ?: null,
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            $accion = $this->tipo === 'INGRESO' ? 'Aporte a Fondo Común' : 'Retiro de Fondo Común';
            $this->registrarAuditoria(
                'Fondo Común',
                $accion,
                "Se registró {$this->tipo}: '{$mov->concepto}' por Bs. " . number_format($mov->monto, 2) . ". Saldo resultante: Bs. " . number_format($nuevoSaldo, 2) . "."
            );
        });

        $this->cerrarModal();
        session()->flash('message', 'Movimiento de fondo común registrado exitosamente.');
    }

    public function abrirModalAnular($id)
    {
        $this->movimiento_a_anular = FondoComunMovimiento::with('user')->findOrFail($id);
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

        $mov = FondoComunMovimiento::findOrFail($this->movimiento_a_anular_id);

        if ($mov->estado === 'Anulado') {
            session()->flash('error', 'Este movimiento ya se encuentra anulado.');
            $this->cerrarModalAnular();
            return;
        }

        $saldoActual = FondoComunMovimiento::obtenerSaldoActual();

        // Si se anula un aporte (INGRESO), verificar que el saldo actual cubra la anulación sin quedar en negativo
        if ($mov->tipo === 'INGRESO' && ($saldoActual - $mov->monto) < 0) {
            $this->addError('motivo_anulacion', "No es posible anular este aporte de Bs. " . number_format($mov->monto, 2) . " porque dejaría el fondo común con saldo negativo (Saldo disponible: Bs. " . number_format($saldoActual, 2) . ").");
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
                'Fondo Común',
                "Anular Movimiento de Fondo Común",
                "Se anuló el movimiento ID #{$mov->id} ({$mov->tipo} por Bs. " . number_format($mov->monto, 2) . "). Motivo: {$this->motivo_anulacion}."
            );
        });

        $this->cerrarModalAnular();
        session()->flash('message', 'Movimiento de fondo común anulado correctamente y recalculado el balance.');
    }

    public function render()
    {
        $query = FondoComunMovimiento::with(['user', 'anuladoPor']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('concepto', 'like', '%' . $this->search . '%')
                  ->orWhere('glosa', 'like', '%' . $this->search . '%')
                  ->orWhere('observaciones', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtro_tipo) {
            $query->where('tipo', $this->filtro_tipo);
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

        // Saldo global real del fondo común
        $saldoActual = FondoComunMovimiento::obtenerSaldoActual();
        $totalAportesHistorico = (float) FondoComunMovimiento::where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto');
        $totalRetirosHistorico = (float) FondoComunMovimiento::where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto');

        // Totales del período filtrado
        $aportesPeriodo = (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'INGRESO')->sum('monto');
        $retirosPeriodo = (float) (clone $query)->where('estado', 'Valido')->where('tipo', 'EGRESO')->sum('monto');

        return view('livewire.finanzas.gestion-fondo-comun', [
            'movimientos' => $movimientos,
            'saldoActual' => $saldoActual,
            'totalAportesHistorico' => $totalAportesHistorico,
            'totalRetirosHistorico' => $totalRetirosHistorico,
            'aportesPeriodo' => $aportesPeriodo,
            'retirosPeriodo' => $retirosPeriodo,
        ])->layout('components.layouts.app', ['title' => 'Fondo Común - Mariachi León Guanajuato']);
    }
}
