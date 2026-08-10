<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLog;

class GestionAuditoria extends Component
{
    use WithPagination;

    public $search = '';
    public $modulo = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedModulo()
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = AuditLog::with('user')
            ->when($this->modulo, function ($q) {
                $q->where('modulo', $this->modulo);
            })
            ->where(function ($q) {
                $q->where('descripcion', 'like', '%' . $this->search . '%')
                  ->orWhere('accion', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        $modulos = AuditLog::select('modulo')->distinct()->pluck('modulo');

        return view('livewire.admin.gestion-auditoria', [
            'logs' => $logs,
            'modulos' => $modulos,
        ])->layout('components.layouts.app', ['title' => 'Auditoría de Logs - Mariachi León']);
    }
}
