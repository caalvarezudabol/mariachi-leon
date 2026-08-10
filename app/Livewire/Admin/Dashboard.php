<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use App\Models\TipoEvento;
use App\Models\Servicio;
use App\Models\AuditLog;
use App\Models\ContactoWeb;

class Dashboard extends Component
{
    public function render()
    {
        $totalUsuarios = User::count();
        $totalTiposEvento = TipoEvento::count();
        $totalServicios = Servicio::count();
        $contactosNuevos = ContactoWeb::where('estado', 'nuevo')->count();
        $ultimosLogs = AuditLog::with('user')->latest()->take(6)->get();

        return view('livewire.admin.dashboard', [
            'totalUsuarios' => $totalUsuarios,
            'totalTiposEvento' => $totalTiposEvento,
            'totalServicios' => $totalServicios,
            'contactosNuevos' => $contactosNuevos,
            'ultimosLogs' => $ultimosLogs,
        ])->layout('components.layouts.app', ['title' => 'Dashboard Gerencial - Mariachi León']);
    }
}
