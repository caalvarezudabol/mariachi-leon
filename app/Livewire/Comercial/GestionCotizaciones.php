<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cotizacion;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Contrato;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Auth;

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
