<?php

namespace App\Livewire\Comercial;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;
use App\Traits\Auditable;
use App\Services\Comercial\ClienteExcelService;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;

class GestionClientes extends Component
{
    use WithPagination, Auditable;

    public $cliente_id = null;
    public $nombre_completo = '';
    public $ci_nit = '';
    public $telefono = '';
    public $whatsapp = '';
    public $email = '';
    public $direccion = '';
    public $observaciones = '';
    public $estado = 'Activo';

    public $search = '';
    public $estado_filtro = '';
    public $modalOpen = false;
    public $isEdit = false;

    protected function rules()
    {
        return [
            'nombre_completo' => 'required|string|max:255',
            'ci_nit' => 'nullable|string|max:50',
            'telefono' => 'nullable|string|max:50',
            'whatsapp' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'direccion' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:Activo,Inactivo',
        ];
    }

    protected $messages = [
        'nombre_completo.required' => 'El nombre completo del cliente es obligatorio.',
        'email.email' => 'Ingrese un correo electrónico válido.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingEstadoFiltro()
    {
        $this->resetPage();
    }

    public function abrirModal()
    {
        $this->reset(['cliente_id', 'nombre_completo', 'ci_nit', 'telefono', 'whatsapp', 'email', 'direccion', 'observaciones', 'isEdit']);
        $this->estado = 'Activo';
        $this->modalOpen = true;
    }

    public function editar($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->cliente_id = $cliente->id;
        $this->nombre_completo = $cliente->nombre_completo;
        $this->ci_nit = $cliente->ci_nit;
        $this->telefono = $cliente->telefono;
        $this->whatsapp = $cliente->whatsapp;
        $this->email = $cliente->email;
        $this->direccion = $cliente->direccion;
        $this->observaciones = $cliente->observaciones;
        $this->estado = $cliente->estado;
        $this->isEdit = true;
        $this->modalOpen = true;
    }

    public function guardar()
    {
        $this->validate();

        if ($this->isEdit) {
            $cliente = Cliente::findOrFail($this->cliente_id);
            $cliente->update([
                'nombre_completo' => trim($this->nombre_completo),
                'ci_nit' => trim($this->ci_nit),
                'telefono' => trim($this->telefono),
                'whatsapp' => trim($this->whatsapp),
                'email' => trim($this->email),
                'direccion' => trim($this->direccion),
                'observaciones' => trim($this->observaciones),
                'estado' => $this->estado,
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Editar Cliente', 'Se actualizó al cliente ' . $cliente->nombre_completo);
            session()->flash('success', 'Cliente actualizado correctamente.');
        } else {
            $cliente = Cliente::create([
                'nombre_completo' => trim($this->nombre_completo),
                'ci_nit' => trim($this->ci_nit),
                'telefono' => trim($this->telefono),
                'whatsapp' => trim($this->whatsapp),
                'email' => trim($this->email),
                'direccion' => trim($this->direccion),
                'observaciones' => trim($this->observaciones),
                'estado' => $this->estado,
            ]);
            $this->registrarAuditoria('Gestión Comercial', 'Crear Cliente', 'Se registró al cliente ' . $cliente->nombre_completo);
            session()->flash('success', 'Cliente registrado exitosamente.');
        }

        $this->modalOpen = false;
    }

    public function cambiarEstado($id)
    {
        $cliente = Cliente::findOrFail($id);
        $nuevoEstado = ($cliente->estado === 'Activo') ? 'Inactivo' : 'Activo';
        $cliente->update(['estado' => $nuevoEstado]);
        $this->registrarAuditoria('Gestión Comercial', 'Cambiar Estado Cliente', 'Se cambió el estado del cliente ' . $cliente->nombre_completo . ' a ' . $nuevoEstado);
        session()->flash('success', 'Estado del cliente actualizado a ' . $nuevoEstado . '.');
    }

    public function exportarExcel(ClienteExcelService $excelService)
    {
        $estado = $this->estado_filtro ?: null;
        $search = $this->search ?: null;

        $spreadsheet = $excelService->generar($estado, $search);
        $nombreArchivo = $excelService->getNombreArchivo($estado);
        $totalClientes = $excelService->getUltimoConteo();

        $descFiltro = $estado ? "estado: {$estado}" : "todos los estados (activos e inactivos)";
        if ($search) {
            $descFiltro .= ", búsqueda: '{$search}'";
        }

        $nombreUsuario = Auth::user()?->name ?? 'Usuario del Sistema';

        // Registro detallado en la Bitácora de Operaciones
        $this->registrarAuditoria(
            'Gestión Comercial',
            'Descargar Reporte Excel Clientes',
            "El usuario {$nombreUsuario} descargó el reporte oficial en formato Excel del directorio de clientes ({$totalClientes} registros, {$descFiltro}). Archivo: {$nombreArchivo}"
        );

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function render()
    {
        $query = Cliente::withCount('eventos');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nombre_completo', 'like', '%' . $this->search . '%')
                  ->orWhere('ci_nit', 'like', '%' . $this->search . '%')
                  ->orWhere('telefono', 'like', '%' . $this->search . '%')
                  ->orWhere('whatsapp', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->estado_filtro) {
            $query->where('estado', $this->estado_filtro);
        }

        $clientes = $query->orderBy('nombre_completo', 'asc')->paginate(10);

        return view('livewire.comercial.gestion-clientes', [
            'clientes' => $clientes,
        ])->layout('components.layouts.app', ['title' => 'Gestión de Clientes - Mariachi León Guanajuato']);
    }
}
