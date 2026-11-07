<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;
use App\Models\User;

class GestionAuditoria extends Component
{
    use WithPagination;

    public $search = '';
    public $modulo = '';
    public $usuario = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedModulo()
    {
        $this->resetPage();
    }

    public function updatedUsuario()
    {
        $this->resetPage();
    }

    public function updatedFechaDesde()
    {
        $this->resetPage();
    }

    public function updatedFechaHasta()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'modulo', 'usuario', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->modulo, fn($q) => $q->where('modulo', $this->modulo))
            ->when($this->usuario, fn($q) => $q->where('user_id', $this->usuario))
            ->when($this->fecha_desde, fn($q) => $q->whereDate('created_at', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta, fn($q) => $q->whereDate('created_at', '<=', $this->fecha_hasta))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('descripcion', 'like', $term)
                        ->orWhere('accion', 'like', $term)
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(15);

        $modulos = AuditLog::select('modulo')->distinct()->orderBy('modulo')->pluck('modulo');

        $usuarios = User::whereIn('id', AuditLog::whereNotNull('user_id')->select('user_id')->distinct())
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.admin.gestion-auditoria', [
            'logs' => $logs,
            'modulos' => $modulos,
            'usuarios' => $usuarios,
        ])->layout('components.layouts.app', [
            'title' => 'Auditoría de Logs - Mariachi León',
            'themed' => true,
        ]);
    }
}