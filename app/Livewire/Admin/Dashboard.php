<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ContactoWeb;
use App\Models\Contrato;
use App\Models\Empresa;
use App\Models\Evento;
use App\Models\MusicoPersonal;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    public function render()
    {
        Carbon::setLocale('es');

        $usuario = Auth::user();
        $puedeFinanzas = $usuario && ($usuario->can('gestionar-pagos') || $usuario->hasRole('Administrador'));

        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth();
        $finMes = $hoy->copy()->endOfMonth();

        // ---- Operación ----
        $eventosMes = Evento::whereBetween('fecha_evento', [$inicioMes->toDateString(), $finMes->toDateString()])
            ->where('estado', '!=', 'Cancelado')
            ->count();

        $eventosSemana = Evento::whereBetween('fecha_evento', [$hoy->toDateString(), $hoy->copy()->addDays(7)->toDateString()])
            ->where('estado', '!=', 'Cancelado')
            ->count();

        $contactosNuevos = ContactoWeb::where('estado', 'nuevo')->count();
        $personasActivas = MusicoPersonal::where('estado', 'Activo')->count();

        $proximosEventos = Evento::with(['cliente', 'servicio'])
            ->whereDate('fecha_evento', '>=', $hoy->toDateString())
            ->where('estado', '!=', 'Cancelado')
            ->orderBy('fecha_evento')
            ->orderBy('hora_evento')
            ->take(6)
            ->get();

        // ---- Finanzas (solo con permiso) ----
        $cobradoMes = 0.0;
        $porCobrar = 0.0;
        $contratosConSaldo = 0;
        $contratosSaldo = collect();

        if ($puedeFinanzas) {
            $cobradoMes = (float) Pago::where('estado', 'Valido')
                ->whereBetween('fecha_pago', [$inicioMes->copy()->startOfDay()->toDateTimeString(), $finMes->copy()->endOfDay()->toDateTimeString()])
                ->sum('monto');

            // Los contratos de eventos cancelados no cuentan como "por cobrar"
            $vigentes = fn($q) => $q->whereDoesntHave('evento', fn($e) => $e->where('estado', 'Cancelado'));

            $porCobrar = (float) Contrato::where('saldo_pendiente', '>', 0)->where($vigentes)->sum('saldo_pendiente');
            $contratosConSaldo = Contrato::where('saldo_pendiente', '>', 0)->where($vigentes)->count();

            $contratosSaldo = Contrato::with('cliente')
                ->where('saldo_pendiente', '>', 0)
                ->where($vigentes)
                ->orderBy('saldo_pendiente', 'desc')
                ->take(5)
                ->get();
        }

        $empresa = Empresa::obtener();

        return view('livewire.admin.dashboard', [
            'empresa' => $empresa,
            'puedeFinanzas' => $puedeFinanzas,
            'eventosMes' => $eventosMes,
            'eventosSemana' => $eventosSemana,
            'contactosNuevos' => $contactosNuevos,
            'personasActivas' => $personasActivas,
            'proximosEventos' => $proximosEventos,
            'cobradoMes' => $cobradoMes,
            'porCobrar' => $porCobrar,
            'contratosConSaldo' => $contratosConSaldo,
            'contratosSaldo' => $contratosSaldo,
        ])->layout('components.layouts.app', [
            'title' => 'Inicio - ' . ($empresa->nombre_comercial ?? 'Mariachi León'),
            'themed' => true,
        ]);
    }
}