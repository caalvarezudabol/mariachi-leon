<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContactoWeb;
use App\Models\Servicio;
use App\Models\Cliente;
use App\Models\Empresa;
use App\Traits\Auditable;

class GestionSolicitudesWeb extends Component
{
    use WithPagination, Auditable;

    public $search = '';
    public $filtro_estado = '';
    public $filtro_servicio = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';
    public $perPage = 15;

    public $modalDetalleOpen = false;
    public $solicitudSeleccionada = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'filtro_estado' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroServicio()
    {
        $this->resetPage();
    }

    public function verDetalle($id)
    {
        $solicitud = ContactoWeb::with(['tipoEvento', 'servicio', 'paquete'])->find($id);
        if ($solicitud) {
            $this->solicitudSeleccionada = $solicitud;
            $this->modalDetalleOpen = true;

            // Si es nueva, marcarla como atendida al abrirla para revisión
            if ($solicitud->estado === 'nuevo') {
                $solicitud->update(['estado' => 'atendido']);
                $this->solicitudSeleccionada = $solicitud->fresh(['tipoEvento', 'servicio', 'paquete']);
                $this->registrarAuditoria('Sitio Web', 'Revisar Solicitud', "Se revisó la solicitud #{$id} de {$solicitud->nombre}");
            }
        }
    }

    public function cerrarModal()
    {
        $this->modalDetalleOpen = false;
        $this->solicitudSeleccionada = null;
    }

    public function cambiarEstado($id, $nuevoEstado)
    {
        if (!in_array($nuevoEstado, ['nuevo', 'atendido', 'cotizado', 'descartado'])) {
            return;
        }

        $solicitud = ContactoWeb::find($id);
        if ($solicitud) {
            $estadoAnterior = $solicitud->estado;
            $solicitud->update(['estado' => $nuevoEstado]);

            if ($this->solicitudSeleccionada && $this->solicitudSeleccionada->id === $id) {
                $this->solicitudSeleccionada->estado = $nuevoEstado;
            }

            $this->registrarAuditoria('Sitio Web', 'Cambiar Estado Solicitud', "Se cambió el estado de la solicitud #{$id} de {$estadoAnterior} a {$nuevoEstado}");
            session()->flash('success', "Estado actualizado a " . ucfirst($nuevoEstado));
        }
    }

    public function convertirEnCliente($id)
    {
        $solicitud = ContactoWeb::find($id);
        if (!$solicitud) {
            return;
        }

        // Verificar si ya existe un cliente con ese teléfono o email
        $clienteExistente = Cliente::where('telefono', $solicitud->telefono)
            ->orWhere('whatsapp', $solicitud->telefono)
            ->orWhere(function ($q) use ($solicitud) {
                if (!empty($solicitud->email)) {
                    $q->where('email', $solicitud->email);
                }
            })
            ->first();

        if ($clienteExistente) {
            $solicitud->update(['estado' => 'cotizado']);
            $this->registrarAuditoria('Sitio Web', 'Vincular Cliente', "Solicitud #{$id} vinculada al cliente existente: {$clienteExistente->nombre_completo}");
            session()->flash('info', "El cliente ya se encontraba registrado como '{$clienteExistente->nombre_completo}'. La solicitud se marcó como Cotizada.");
            return;
        }

        $nuevoCliente = Cliente::create([
            'nombre_completo' => $solicitud->nombre,
            'telefono' => $solicitud->telefono,
            'whatsapp' => $solicitud->telefono,
            'email' => $solicitud->email,
            'direccion' => 'Registrado vía Web',
            'observaciones' => "Cliente originado desde solicitud web #{$id}. Mensaje: " . $solicitud->mensaje,
            'estado' => 'activo',
        ]);

        $solicitud->update(['estado' => 'cotizado']);
        $this->registrarAuditoria('Sitio Web', 'Convertir en Cliente', "Se creó el cliente {$nuevoCliente->nombre_completo} a partir de la solicitud web #{$id}");
        session()->flash('success', "¡Cliente '{$nuevoCliente->nombre_completo}' registrado exitosamente en el catálogo comercial!");
    }

    public function eliminar($id)
    {
        $solicitud = ContactoWeb::find($id);
        if ($solicitud) {
            $nombre = $solicitud->nombre;
            $solicitud->delete();
            $this->registrarAuditoria('Sitio Web', 'Eliminar Solicitud', "Se eliminó la solicitud web #{$id} de {$nombre}");
            session()->flash('success', "Solicitud de {$nombre} eliminada del sistema.");
            if ($this->solicitudSeleccionada && $this->solicitudSeleccionada->id === $id) {
                $this->cerrarModal();
            }
        }
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtro_estado', 'filtro_servicio', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    public function render()
    {
        $query = ContactoWeb::with(['tipoEvento', 'servicio', 'paquete'])->latest();

        if (!empty($this->search)) {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                  ->orWhere('telefono', 'like', $term)
                  ->orWhere('email', 'like', $term)
                  ->orWhere('mensaje', 'like', $term);
            });
        }

        if (!empty($this->filtro_estado)) {
            $query->where('estado', $this->filtro_estado);
        }

        if (!empty($this->filtro_servicio)) {
            $query->where('servicio_id', $this->filtro_servicio);
        }

        if (!empty($this->fecha_desde)) {
            $query->whereDate('created_at', '>=', $this->fecha_desde);
        }

        if (!empty($this->fecha_hasta)) {
            $query->whereDate('created_at', '<=', $this->fecha_hasta);
        }

        $solicitudes = $query->paginate($this->perPage);

        // Contadores KPI
        $totalTodas = ContactoWeb::count();
        $totalNuevas = ContactoWeb::where('estado', 'nuevo')->count();
        $totalAtendidas = ContactoWeb::where('estado', 'atendido')->count();
        $totalCotizadas = ContactoWeb::where('estado', 'cotizado')->count();
        $totalDescartadas = ContactoWeb::where('estado', 'descartado')->count();

        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $empresa = Empresa::obtener();

        return view('livewire.admin.gestion-solicitudes-web', [
            'solicitudes' => $solicitudes,
            'totalTodas' => $totalTodas,
            'totalNuevas' => $totalNuevas,
            'totalAtendidas' => $totalAtendidas,
            'totalCotizadas' => $totalCotizadas,
            'totalDescartadas' => $totalDescartadas,
            'servicios' => $servicios,
            'empresa' => $empresa,
        ])->layout('components.layouts.app', ['title' => 'Solicitudes Web y Cotizaciones - ' . ($empresa->nombre_comercial ?? 'Mariachi León')]);
    }
}
