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

    protected function rules()
    {
        return [
            'tipo' => 'required|in:INGRESO,EGRESO',
            'concepto' => 'nullable|string|max:255',
            'monto' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'metodo' => 'required|in:Efectivo,QR',
            'glosa' => 'nullable|string|max:255',
            'observaciones' => 'nullable|string|max:500',
        ];
    }

    public function mount()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->fecha_desde = '';
        $this->fecha_hasta = '';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
    public function updatingFiltroTipo()
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
        $this->filtro_estado = 'Valido';
        $this->fecha_desde = '';
        $this->fecha_hasta = '';
        $this->resetPage();
    }

    /**
     * Consulta base con los filtros activos (sin relaciones, para poder usarla en sumas y conteos).
     * $conTipo / $conEstado permiten ignorar esos dos filtros en los totales.
     */
    private function movimientosQuery(bool $conTipo = true, bool $conEstado = true)
    {
        $query = FondoComunMovimiento::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('concepto', 'like', '%' . $this->search . '%')
                    ->orWhere('glosa', 'like', '%' . $this->search . '%')
                    ->orWhere('observaciones', 'like', '%' . $this->search . '%');
            });
        }

        if ($conTipo && $this->filtro_tipo) {
            $query->where('tipo', $this->filtro_tipo);
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

    public function abrirModalCrear($tipo = 'INGRESO')
    {
        $this->resetValidation();
        $this->tipo = $tipo;
        $this->fecha = now()->format('Y-m-d');
        $this->monto = '';
        $this->concepto = '';
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
        $this->concepto = trim((string) $this->concepto) ?: ($this->tipo === 'INGRESO' ? 'Aporte al fondo' : 'Retiro del fondo');

        $fondoMetodo = \App\Support\Caja::fondoPorMetodo()[$this->metodo] ?? 0;
        $disponibleCaja = \App\Support\Caja::saldos()[$this->metodo] ?? 0;

        // Retiro: solo hasta lo que hay en el fondo de ese método
        if ($this->tipo === 'EGRESO' && $montoFloat > $fondoMetodo + 0.001) {
            $this->addError('monto', 'El fondo tiene Bs ' . number_format($fondoMetodo, 2) . ' en ' . $this->metodo . '. No se puede retirar más.');
            return;
        }

        // Aporte: sale de lo disponible de ese método
        if ($this->tipo === 'INGRESO' && $montoFloat > $disponibleCaja + 0.001) {
            $this->addError('monto', 'No alcanza la caja ' . $this->metodo . ' (hay Bs ' . number_format($disponibleCaja, 2) . ').');
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
                'glosa' => trim((string) $this->glosa) ?: trim($this->concepto),
                'observaciones' => trim((string) $this->observaciones) ?: null,
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

        // Un aporte hecho al pagar a una persona se anula desde ese pago
        if ($mov->tipo === 'INGRESO' && \App\Models\PagoParticipante::where('fondo_movimiento_id', $mov->id)->where('estado', 'Valido')->exists()) {
            $this->addError('motivo_anulacion', 'Este aporte viene de un pago a personal. Anula ese pago desde Pagos a personal.');
            return;
        }

        // Si se anula un aporte, el fondo de ese método no puede quedar en negativo
        $key = str_contains(strtoupper((string) $mov->metodo), 'EFECTIVO') ? 'Efectivo' : 'QR';
        $fondoMetodo = \App\Support\Caja::fondoPorMetodo()[$key] ?? 0;
        if ($mov->tipo === 'INGRESO' && ($fondoMetodo - $mov->monto) < -0.001) {
            $this->addError('motivo_anulacion', 'No se puede anular este aporte de Bs ' . number_format($mov->monto, 2) . ': dejaría el fondo en negativo (hay Bs ' . number_format($fondoMetodo, 2) . ' en ' . $key . ').');
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
        $movimientos = $this->movimientosQuery()
            ->with(['user', 'anuladoPor'])
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(12);

        // Saldo del fondo, total y por método
        $saldoActual = FondoComunMovimiento::obtenerSaldoActual();
        $fondoMetodo = \App\Support\Caja::fondoPorMetodo();

        // Contadores para los chips de tipo (respetan búsqueda, fechas y estado)
        $contTipos = $this->movimientosQuery(false)
            ->selectRaw('tipo, COUNT(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return view('livewire.finanzas.gestion-fondo-comun', [
            'movimientos' => $movimientos,
            'saldoActual' => $saldoActual,
            'fondoMetodo' => $fondoMetodo,
            'contTipos' => $contTipos,
        ])->layout('components.layouts.app', [
            'title' => 'Fondo Común - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}