<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cotizacion;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Contrato;
use App\Models\Pago;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GestionCotizaciones extends Component
{
    use WithPagination, Auditable;

    public $cotizacion_id = null;
    public $numero_cotizacion = '';
    public $evento_id = '';
    public $cliente_id = '';
    public $servicio_id = '';
    public $fecha_cotizacion = '';
    public $precio_base = 0.00;
    public $descuento = 0.00;
    public $monto_total = 0.00;
    public $estado = 'Borrador';
    public $observaciones = '';

    public $search = '';
    public $estado_filtro = '';
    public $modalOpen = false;
    public $isEdit = false;

    // Modal Pago Rápido
    public $modalPagoRapidoOpen = false;
    public $pagoContratoId = null;
    public $pagoContrato = null;
    public $tipoPago = 'acuenta'; // 'acuenta' | 'saldo'
    public $pagoMonto = 0.00;
    public $pagoMetodo = 'Efectivo';
    public $pagoReferencia = '';
    public $pagoObservaciones = '';
    public $pagoFecha = '';
    public $ultimoRecibo = null;

    protected function rules()
    {
        return [
            'numero_cotizacion' => 'required|string|max:50|unique:cotizaciones,numero_cotizacion,' . ($this->cotizacion_id ?? 'NULL') . ',id',
            'evento_id' => 'required|exists:eventos,id',
            'fecha_cotizacion' => 'required|date',
            'precio_base' => 'required|numeric|min:0',
            'descuento' => 'required|numeric|min:0',
            'monto_total' => 'required|numeric|min:0',
            'estado' => 'required|in:Borrador,Enviada,Aceptada,Rechazada,Convertida en Contrato',
            'observaciones' => 'nullable|string',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function generarNumeroAutomatico()
    {
        $last = Cotizacion::orderBy('id', 'desc')->first();
        $nextNum = $last ? ($last->id + 1) : 1;
        return 'COT-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);
    }

    public function abrirModal()
    {
        $this->reset(['cotizacion_id', 'numero_cotizacion', 'evento_id', 'cliente_id', 'servicio_id', 'precio_base', 'descuento', 'monto_total', 'observaciones', 'isEdit']);
        $this->numero_cotizacion = $this->generarNumeroAutomatico();
        $this->fecha_cotizacion = date('Y-m-d');
        $this->estado = 'Borrador';
        $this->modalOpen = true;
    }

    public function updatedEventoId($value)
    {
        if ($value) {
            $evento = Evento::with(['cliente', 'servicio'])->find($value);
            if ($evento) {
                $this->cliente_id = $evento->cliente_id;
                $this->servicio_id = $evento->servicio_id;
                $this->precio_base = $evento->servicio ? $evento->servicio->precio_base : 0;
                $this->recalcularTotal();
            }
        }
    }

    public function updatedPrecioBase()
    {
        $this->recalcularTotal();
    }

    public function updatedDescuento()
    {
        $this->recalcularTotal();
    }

    public function recalcularTotal()
    {
        $pb = (float)$this->precio_base;
        $desc = (float)$this->descuento;
        $this->monto_total = max(0, $pb - $desc);
    }

    public function editar($id)
    {
        $c = Cotizacion::findOrFail($id);
        $this->cotizacion_id = $c->id;
        $this->numero_cotizacion = $c->numero_cotizacion;
        $this->evento_id = $c->evento_id;
        $this->cliente_id = $c->cliente_id;
        $this->servicio_id = $c->servicio_id;
        $this->fecha_cotizacion = $c->fecha_cotizacion ? $c->fecha_cotizacion->format('Y-m-d') : '';
        $this->precio_base = $c->precio_base;
        $this->descuento = $c->descuento;
        $this->monto_total = $c->monto_total;
        $this->estado = $c->estado;
        $this->observaciones = $c->observaciones;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $c = Cotizacion::findOrFail($this->cotizacion_id);
            $c->update([
                'numero_cotizacion' => strtoupper(trim($this->numero_cotizacion)),
                'evento_id' => $this->evento_id,
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'fecha_cotizacion' => $this->fecha_cotizacion,
                'precio_base' => $this->precio_base,
                'descuento' => $this->descuento,
                'monto_total' => $this->monto_total,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Editar Cotización', 'Se actualizó la cotización ' . $c->numero_cotizacion);
            session()->flash('success', 'Cotización actualizada correctamente.');
        } else {
            $c = Cotizacion::create([
                'numero_cotizacion' => strtoupper(trim($this->numero_cotizacion)),
                'evento_id' => $this->evento_id,
                'cliente_id' => $this->cliente_id,
                'servicio_id' => $this->servicio_id,
                'fecha_cotizacion' => $this->fecha_cotizacion,
                'precio_base' => $this->precio_base,
                'descuento' => $this->descuento,
                'monto_total' => $this->monto_total,
                'estado' => $this->estado,
                'observaciones' => trim($this->observaciones),
                'user_id' => Auth::id(),
            ]);

            // Actualiza el estado del evento a 'Cotizado'
            $evento = Evento::find($this->evento_id);
            if ($evento && $evento->estado === 'Pendiente') {
                $evento->update(['estado' => 'Cotizado']);
            }

            $this->registrarAuditoria('Gestión Comercial', 'Crear Cotización', 'Se registró la cotización ' . $c->numero_cotizacion);
            session()->flash('success', 'Cotización registrada exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function convertirEnContrato($id)
    {
        $c = Cotizacion::with(['evento', 'cliente', 'servicio'])->findOrFail($id);

        if ($c->contrato) {
            session()->flash('error', 'Esta cotización ya fue convertida previamente en contrato.');
            return;
        }

        // Crear Contrato automáticamente
        $lastContrato = Contrato::orderBy('id', 'desc')->first();
        $nextNum = $lastContrato ? ($lastContrato->id + 1) : 1;
        $numContrato = 'CTR-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

        $contrato = Contrato::create([
            'numero_contrato' => $numContrato,
            'cliente_id' => $c->cliente_id,
            'evento_id' => $c->evento_id,
            'servicio_id' => $c->servicio_id,
            'cotizacion_id' => $c->id,
            'fecha_contrato' => date('Y-m-d'),
            'hora_contrato' => date('H:i:s'),
            'monto_total' => $c->monto_total,
            'pago_inicial' => 0.00,
            'total_pagado' => 0.00,
            'saldo_pendiente' => $c->monto_total,
            'estado' => 'Pendiente de Confirmacion',
            'observaciones' => 'Generado a partir de la cotización ' . $c->numero_cotizacion,
            'user_id' => Auth::id(),
        ]);

        $c->update(['estado' => 'Convertida en Contrato']);
        if ($c->evento) {
            $c->evento->update(['estado' => 'Reservado']);
        }

        $this->registrarAuditoria('Gestión Comercial', 'Convertir Cotización en Contrato', 'Se convirtió la cotización ' . $c->numero_cotizacion . ' en el contrato ' . $numContrato);
        session()->flash('success', 'Cotización ' . $c->numero_cotizacion . ' convertida exitosamente en el contrato ' . $numContrato . '.');
    }

    public function abrirModalPagoRapido($contratoId)
    {
        $this->pagoContrato = Contrato::with(['cliente', 'evento', 'servicio'])->findOrFail($contratoId);
        $this->pagoContratoId = $this->pagoContrato->id;
        $this->tipoPago = 'acuenta';
        $this->pagoMonto = min(500, (float)$this->pagoContrato->saldo_pendiente);
        if ($this->pagoMonto <= 0) {
            $this->pagoMonto = (float)$this->pagoContrato->saldo_pendiente;
        }
        $this->pagoMetodo = 'Efectivo';
        $this->pagoReferencia = '';
        $this->pagoObservaciones = 'Abono a cuenta por servicio musical';
        $this->pagoFecha = date('Y-m-d\TH:i');
        $this->modalPagoRapidoOpen = true;
    }

    public function seleccionarTipoPago($tipo)
    {
        $this->tipoPago = $tipo;
        if ($tipo === 'saldo') {
            $this->pagoMonto = (float)$this->pagoContrato->saldo_pendiente;
            $this->pagoObservaciones = 'Cancelación total de saldo pendiente';
        } else {
            $this->pagoMonto = min(500, (float)$this->pagoContrato->saldo_pendiente);
            $this->pagoObservaciones = 'Abono a cuenta por servicio musical';
        }
    }

    public function guardarPagoRapido()
    {
        if (!$this->pagoContrato) {
            return;
        }

        $saldoPendiente = (float)$this->pagoContrato->saldo_pendiente;

        $this->validate([
            'pagoMonto' => 'required|numeric|min:1|max:' . $saldoPendiente,
            'pagoMetodo' => 'required|string|in:Efectivo,Transferencia,QR,Tarjeta,Otro',
            'pagoReferencia' => 'nullable|string|max:100',
            'pagoObservaciones' => 'nullable|string|max:255',
            'pagoFecha' => 'required',
        ], [
            'pagoMonto.max' => 'El monto no puede superar el saldo pendiente actual de Bs ' . number_format($saldoPendiente, 2),
            'pagoMonto.min' => 'El monto debe ser de al menos Bs 1.00',
        ]);

        DB::transaction(function () {
            $lastPago = Pago::orderBy('id', 'desc')->first();
            $nextNum = $lastPago ? ($lastPago->id + 1) : 1;
            $numRecibo = 'REC-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            $pago = Pago::create([
                'numero_recibo' => $numRecibo,
                'contrato_id' => $this->pagoContrato->id,
                'cliente_id' => $this->pagoContrato->cliente_id,
                'evento_id' => $this->pagoContrato->evento_id,
                'fecha_pago' => $this->pagoFecha,
                'monto' => $this->pagoMonto,
                'metodo_pago' => $this->pagoMetodo,
                'comprobante_referencia' => $this->pagoReferencia ? trim($this->pagoReferencia) : null,
                'observaciones' => $this->pagoObservaciones ? trim($this->pagoObservaciones) : 'Pago registrado desde Cotizaciones',
                'codigo_verificacion' => Str::random(40),
                'estado' => 'Valido',
                'user_id' => Auth::id(),
            ]);

            $nuevoTotalPagado = (float)$this->pagoContrato->total_pagado + (float)$this->pagoMonto;
            $nuevoSaldo = max(0, (float)$this->pagoContrato->monto_total - $nuevoTotalPagado);
            $nuevoEstado = $this->pagoContrato->estado;
            if (in_array($this->pagoContrato->estado, ['Borrador', 'Pendiente de Confirmacion'])) {
                $nuevoEstado = 'Confirmado';
            }

            $this->pagoContrato->update([
                'total_pagado' => $nuevoTotalPagado,
                'saldo_pendiente' => $nuevoSaldo,
                'estado' => $nuevoEstado,
            ]);

            if ($this->pagoContrato->evento && $this->pagoContrato->evento->estado === 'Reservado' && $nuevoTotalPagado > 0) {
                $this->pagoContrato->evento->update(['estado' => 'Confirmado']);
            }

            // Auditoría obligatoria
            $this->registrarAuditoria(
                'Gestión Comercial',
                'Registrar Pago Rápido',
                'Se registró el pago ' . $numRecibo . ' por Bs ' . number_format($this->pagoMonto, 2) . ' para el contrato ' . $this->pagoContrato->numero_contrato . ' del cliente ' . ($this->pagoContrato->cliente ? $this->pagoContrato->cliente->nombre_completo : 'N/A')
            );

            $this->ultimoRecibo = [
                'id' => $pago->id,
                'codigo_verificacion' => $pago->codigo_verificacion,
                'numero' => $numRecibo,
                'monto' => $pago->monto,
            ];
        });

        session()->flash('success_pago', 'Pago ' . $this->ultimoRecibo['numero'] . ' registrado con éxito por Bs ' . number_format($this->ultimoRecibo['monto'], 2) . '.');
        $this->modalPagoRapidoOpen = false;

        return redirect()->route('comercial.pagos.recibo', $this->ultimoRecibo['codigo_verificacion']);
    }

    public function render()
    {
        $query = Cotizacion::with(['cliente', 'servicio', 'evento', 'contrato']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('numero_cotizacion', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function($c) {
                      $c->where('nombre_completo', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('evento', function($e) {
                      $e->where('codigo_evento', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $cotizaciones = $query->orderBy('fecha_cotizacion', 'desc')->paginate(10);
        $eventos = Evento::with(['cliente', 'servicio'])->orderBy('fecha_evento', 'desc')->get();

        return view('livewire.comercial.gestion-cotizaciones', [
            'cotizaciones' => $cotizaciones,
            'eventos' => $eventos,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Cotizaciones - Mariachi León Guanajuato']);
    }
}
