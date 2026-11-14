<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Pago;
use App\Models\Contrato;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GestionPagos extends Component
{
    use WithPagination, Auditable;

    public $pago_id = null;
    public $numero_recibo = '';
    public $contrato_id = '';
    public $fecha_pago = '';
    public $monto = 0.00;
    public $metodo_pago = 'EFECTIVO';
    public $comprobante_referencia = '';
    public $observaciones = '';

    public $search = '';
    public $metodo_filtro = '';
    public $estado_filtro = '';
    public $modalOpen = false;

    // Vista: por contrato (por defecto) o detalle de cada pago
    public $verDetalle = false;
    public $contrato_estado = 'saldo'; // saldo | pagados | '' (todos) - solo en la vista por contrato
    public $contrato_filtro = null;    // al ver los pagos de un contrato puntual
    public $abiertos = [];             // contratos desplegados (flechita >) en la vista por contrato

    // Modal de Anulación
    public $modalAnularOpen = false;
    public $pagoIdAnular = null;
    public $pagoParaAnular = null;
    public $motivoAnulacion = '';
    public $ultimoRecibo = null;

    protected function rules()
    {
        return [
            'numero_recibo' => 'required|string|max:50|unique:pagos,numero_recibo,' . ($this->pago_id ?? 'NULL') . ',id',
            'contrato_id' => 'required|exists:contratos,id',
            'fecha_pago' => 'required',
            'monto' => 'required|numeric|gt:0',
            'metodo_pago' => 'required|in:EFECTIVO,QR',
            'comprobante_referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingMetodoFiltro()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function updatingVerDetalle()
    {
        $this->resetPage();
    }

    public function updatingContratoEstado()
    {
        $this->resetPage();
    }

    // Flechita >: despliega o cierra el detalle de cobros de un contrato
    public function toggleContrato($id)
    {
        $id = (int) $id;
        $this->abiertos = in_array($id, $this->abiertos)
            ? array_values(array_diff($this->abiertos, [$id]))
            : array_merge($this->abiertos, [$id]);
    }

    // Abre "Nuevo pago" con el contrato ya elegido y el saldo como monto sugerido
    public function cobrarContrato($id)
    {
        $this->abrirModal();
        $this->contrato_id = $id;
        $this->updatedContratoId($id);
    }

    // Pasa al detalle mostrando solo los pagos de ese contrato
    public function verPagosDeContrato($id)
    {
        $this->contrato_filtro = $id;
        $this->estado_filtro = '';
        $this->metodo_filtro = '';
        $this->search = '';
        $this->verDetalle = true;
        $this->resetPage();
    }

    public function quitarFiltroContrato()
    {
        $this->contrato_filtro = null;
        $this->resetPage();
    }

    public function generarNumeroAutomatico()
    {
        return $this->siguienteNumero('pagos', 'numero_recibo', 'REC-');
    }

    // Siguiente número libre: toma el mayor que existe (aunque haya borrados) y verifica que no se repita
    private function siguienteNumero(string $tabla, string $columna, string $prefijo): string
    {
        $max = 0;
        foreach (\Illuminate\Support\Facades\DB::table($tabla)->pluck($columna) as $numero) {
            if (preg_match('/(\d+)\s*$/', (string) $numero, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }
        $n = $max + 1;
        do {
            $candidato = $prefijo . str_pad($n, 5, '0', STR_PAD_LEFT);
            $n++;
        } while (\Illuminate\Support\Facades\DB::table($tabla)->where($columna, $candidato)->exists());

        return $candidato;
    }

    public function abrirModal()
    {
        $this->reset(['pago_id', 'numero_recibo', 'contrato_id', 'monto', 'comprobante_referencia', 'observaciones']);
        $this->numero_recibo = $this->generarNumeroAutomatico();
        $this->fecha_pago = date('Y-m-d H:i');
        $this->metodo_pago = 'EFECTIVO';
        $this->modalOpen = true;
    }

    public function updatedContratoId($value)
    {
        if ($value) {
            $contrato = Contrato::find($value);
            if ($contrato) {
                // Sugiere por defecto el saldo pendiente como monto a pagar
                $this->monto = $contrato->saldo_pendiente;
            }
        }
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            $contrato = Contrato::lockForUpdate()->findOrFail($this->contrato_id);
            $montoPago = (float)$this->monto;

            if ($montoPago > (float)$contrato->saldo_pendiente) {
                $this->addError('monto', 'El monto a pagar (Bs ' . number_format($montoPago, 2) . ') no puede ser mayor al saldo pendiente del contrato (Bs ' . number_format($contrato->saldo_pendiente, 2) . ').');
                return;
            }

            // Registrar Pago (Inmutable)
            $pago = Pago::create([
                'numero_recibo' => strtoupper(trim($this->numero_recibo)),
                'contrato_id' => $contrato->id,
                'cliente_id' => $contrato->cliente_id,
                'evento_id' => $contrato->evento_id,
                'fecha_pago' => $this->fecha_pago,
                'monto' => $montoPago,
                'metodo_pago' => $this->metodo_pago,
                'comprobante_referencia' => trim($this->comprobante_referencia),
                'observaciones' => trim($this->observaciones),
                'codigo_verificacion' => Str::random(40),
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            // Actualizar saldos del contrato
            $nuevoTotalPagado = (float)$contrato->total_pagado + $montoPago;
            $nuevoSaldo = max(0, (float)$contrato->monto_total - $nuevoTotalPagado);
            $nuevoEstado = $contrato->estado;
            if (in_array($contrato->estado, ['Borrador', 'Pendiente de Confirmacion'])) {
                $nuevoEstado = 'Confirmado';
            }

            $contrato->update([
                'total_pagado' => $nuevoTotalPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoEstado,
            ]);

            if ($contrato->evento && $contrato->evento->estado === 'Reservado' && $nuevoTotalPagado > 0) {
                $contrato->evento->update(['estado' => 'Confirmado']);
            }

            $this->registrarAuditoria('Gestión Comercial', 'Registrar Pago Cliente', 'Se registró el pago recibo N° ' . $pago->numero_recibo . ' por Bs ' . number_format($montoPago, 2) . ' para el contrato N° ' . $contrato->numero_contrato);

            $this->ultimoRecibo = [
                'id' => $pago->id,
                'codigo_verificacion' => $pago->codigo_verificacion,
                'numero' => $pago->numero_recibo,
                'monto' => $pago->monto,
            ];

            session()->flash('success_pago', 'Pago ' . $pago->numero_recibo . ' registrado exitosamente por Bs ' . number_format($montoPago, 2) . '.');
            $this->modalOpen = false;
        });
    }

    public function abrirModalAnular($id)
    {
        $user = Auth::user();
        if (!$user->can('gestionar-pagos') && !$user->hasRole('Administrador')) {
            session()->flash('error', 'No tiene los permisos requeridos (gestionar-pagos) para anular pagos.');
            return;
        }

        $pago = Pago::with(['evento', 'contrato.evento', 'cliente'])->findOrFail($id);

        if ($pago->estado === 'Anulado') {
            session()->flash('error', 'Este pago ya se encuentra anulado.');
            return;
        }

        $evento = $pago->evento ?: ($pago->contrato ? $pago->contrato->evento : null);
        if ($evento && in_array($evento->estado, ['Realizado', 'Finalizado', 'Concluido'])) {
            session()->flash('error', 'No se puede anular el pago porque el evento (' . $evento->codigo_evento . ') ya ha sido concluido/realizado.');
            return;
        }

        $this->pagoIdAnular = $pago->id;
        $this->pagoParaAnular = $pago;
        $this->motivoAnulacion = '';
        $this->modalAnularOpen = true;
    }

    public function confirmarAnulacion()
    {
        $user = Auth::user();
        if (!$user->can('gestionar-pagos') && !$user->hasRole('Administrador')) {
            session()->flash('error', 'No tiene los permisos requeridos para anular pagos.');
            $this->modalAnularOpen = false;
            return;
        }

        $this->validate([
            'motivoAnulacion' => 'required|string|min:5|max:255',
        ], [
            'motivoAnulacion.required' => 'Debe ingresar obligatoriamente un motivo para anular el pago.',
            'motivoAnulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        DB::transaction(function () {
            $pago = Pago::lockForUpdate()->findOrFail($this->pagoIdAnular);

            $evento = $pago->evento ?: ($pago->contrato ? $pago->contrato->evento : null);
            if ($evento && in_array($evento->estado, ['Realizado', 'Finalizado', 'Concluido'])) {
                session()->flash('error', 'No se puede anular el pago porque el evento ya concluyó.');
                $this->modalAnularOpen = false;
                return;
            }

            $pago->update([
                'estado' => 'Anulado',
                'motivo_anulacion' => trim($this->motivoAnulacion),
                'fecha_anulacion' => now(),
                'anulado_por' => Auth::id(),
            ]);

            // Revertir saldos en el contrato
            $contrato = $pago->contrato;
            if ($contrato) {
                $nuevoTotalPagado = max(0, (float)$contrato->total_pagado - (float)$pago->monto);
                $nuevoSaldo = min((float)$contrato->monto_total, (float)$contrato->saldo_pendiente + (float)$pago->monto);
                $nuevoEstado = $contrato->estado;
                if ($nuevoTotalPagado <= 0 && $contrato->estado === 'Confirmado') {
                    $nuevoEstado = 'Pendiente de Confirmacion';
                }

                $contrato->update([
                    'total_pagado' => $nuevoTotalPagado,
                    'saldo_pendiente' => $nuevoSaldo,
                    'estado' => $nuevoEstado,
                ]);
            }

            // Registrar en Bitácora del sistema
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Anular Pago Cliente',
                'Se anuló el recibo de pago N° ' . $pago->numero_recibo . ' por Bs ' . number_format($pago->monto, 2) . ' del cliente ' . ($pago->cliente ? $pago->cliente->nombre_completo : 'N/A') . '. Motivo: ' . $this->motivoAnulacion . '. Anulado por: ' . Auth::user()->name
            );

            session()->flash('success', 'El pago ' . $pago->numero_recibo . ' ha sido anulado correctamente y los saldos del contrato fueron revertidos.');
            $this->modalAnularOpen = false;
        });
    }

    /**
     * Consulta base de pagos (aplica buscador y método). Se reutiliza para la lista y para
     * los contadores de los chips de estado.
     */
    private function pagosQuery()
    {
        $query = Pago::with(['contrato.evento', 'cliente', 'evento', 'anuladoPor']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_recibo', 'like', '%' . $this->search . '%')
                    ->orWhere('comprobante_referencia', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cliente', function ($c) {
                        $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                    })
                    ->orWhereHas('contrato', function ($ct) {
                        $ct->where('numero_contrato', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->metodo_filtro) {
            $query->where('metodo_pago', $this->metodo_filtro);
        }

        if ($this->contrato_filtro) {
            $query->where('contrato_id', $this->contrato_filtro);
        }

        return $query;
    }

    /** Consulta base de contratos para la vista por contrato (aplica el buscador). */
    private function contratosQuery(bool $extras = true)
    {
        $query = Contrato::query();
        if ($extras) {
            $query->with(['cliente', 'servicio', 'evento', 'pagos' => fn($q) => $q->orderBy('fecha_pago')])
                ->withCount(['pagos as pagos_validos' => fn($q) => $q->where('estado', 'Valido')])
                ->withMax(['pagos as ultimo_pago' => fn($q) => $q->where('estado', 'Valido')], 'fecha_pago');
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_contrato', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cliente', fn($c) => $c->where('nombre_completo', 'like', '%' . $this->search . '%'))
                    ->orWhereHas('evento', fn($e) => $e->where('codigo_evento', 'like', '%' . $this->search . '%'));
            });
        }

        return $query;
    }

    public function render()
    {
        // Contadores por estado para los chips (respetan buscador y método, no el filtro de estado)
        $estados = $this->pagosQuery()
            ->reorder()
            ->selectRaw('estado, COUNT(*) as total')
            ->groupBy('estado')
            ->pluck('total', 'estado');

        $query = $this->pagosQuery();

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $pagos = $this->verDetalle
            ? $query->orderBy('fecha_pago', 'desc')->paginate(10)
            : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

        // Total de los pagos válidos del filtro (vista detalle)
        $totalPagosValidos = 0.0;
        if ($this->verDetalle) {
            $totalPagosValidos = (float) $this->pagosQuery()->setEagerLoads([])->reorder()
                ->when($this->estado_filtro, fn($q) => $q->where('estado', $this->estado_filtro))
                ->where('estado', 'Valido')->sum('monto');
        }

        // Vista por contrato
        $contratosLista = null;
        $totalesContratos = ['contratado' => 0.0, 'abonado' => 0.0, 'saldo' => 0.0];
        $conteosContratos = ['saldo' => 0, 'pagados' => 0, 'todos' => 0];
        if (!$this->verDetalle) {
            $conteosContratos = [
                'saldo' => $this->contratosQuery(false)->where('saldo_pendiente', '>', 0)->count(),
                'pagados' => $this->contratosQuery(false)->where('saldo_pendiente', '<=', 0)->count(),
                'todos' => $this->contratosQuery(false)->count(),
            ];
            $filtroEstado = function ($q) {
                if ($this->contrato_estado === 'saldo') {
                    $q->where('saldo_pendiente', '>', 0);
                } elseif ($this->contrato_estado === 'pagados') {
                    $q->where('saldo_pendiente', '<=', 0);
                }
                return $q;
            };
            $contratosLista = $filtroEstado($this->contratosQuery())->orderBy('fecha_contrato', 'desc')->paginate(10);

            // Totales de TODOS los contratos del filtro (el saldo no cuenta los cancelados)
            $sumas = $filtroEstado($this->contratosQuery(false))
                ->selectRaw('COALESCE(SUM(monto_total),0) as contratado, COALESCE(SUM(total_pagado),0) as abonado')
                ->first();
            $totalesContratos = [
                'contratado' => (float) ($sumas->contratado ?? 0),
                'abonado' => (float) ($sumas->abonado ?? 0),
                'saldo' => (float) $filtroEstado($this->contratosQuery(false))
                    ->whereDoesntHave('evento', fn($e) => $e->where('estado', 'Cancelado'))
                    ->sum('saldo_pendiente'),
            ];
        }

        // Opciones del selector con buscador: solo se cargan cuando el modal está abierto
        $contratosOptions = [];
        if ($this->modalOpen) {
            $contratosOptions = Contrato::with(['cliente', 'evento'])
                ->where('saldo_pendiente', '>', 0)
                ->whereDoesntHave('evento', fn($e) => $e->where('estado', 'Cancelado'))
                ->orderBy('numero_contrato', 'asc')
                ->get()
                ->map(function ($ctr) {
                    $cliente = $ctr->cliente->nombre_completo ?? 'N/A';
                    $evento = $ctr->evento->codigo_evento ?? '';

                    return [
                        'id' => $ctr->id,
                        'label' => $ctr->numero_contrato . ' - ' . $cliente,
                        'meta' => $evento,
                        'aside' => 'Bs ' . number_format($ctr->saldo_pendiente, 2),
                        'search' => mb_strtolower($ctr->numero_contrato . ' ' . $cliente . ' ' . $evento),
                    ];
                })
                ->values()
                ->all();
        }

        return view('livewire.comercial.gestion-pagos', [
            'pagos' => $pagos,
            'contratosLista' => $contratosLista,
            'totalesContratos' => $totalesContratos,
            'conteosContratos' => $conteosContratos,
            'totalPagosValidos' => $totalPagosValidos,
            'estados' => $estados,
            'contratosOptions' => $contratosOptions,
        ])->layout('components.layouts.app', [
            'title' => 'Gestión de Pagos - Mariachi León Guanajuato',
            'themed' => true,
        ]);
    }
}