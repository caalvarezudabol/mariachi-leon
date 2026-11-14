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

        // ---- Alertas (campanita) ----
        $alertas = [];
        $ahora = now();
        $porRealizar = ['Confirmado', 'Reservado', 'Pendiente', 'Cotizado'];
        $candidatos = Evento::with(['cliente', 'servicio', 'contrato'])
            ->whereIn('estado', $porRealizar)
            ->whereBetween('fecha_evento', [$hoy->copy()->subDays(30)->toDateString(), $hoy->copy()->addDays(2)->toDateString()])
            ->orderBy('fecha_evento')->orderBy('hora_evento')
            ->get();

        foreach ($candidatos as $ev) {
            $ini = $ev->fecha_evento->copy()->setTimeFromTimeString(substr($ev->hora_evento, 0, 8));
            $fin = $ini->copy()->addMinutes((int) round(((float) $ev->duracion_horas) * 60));
            $saldo = $ev->contrato ? (float) $ev->contrato->saldo_pendiente : 0.0;
            $cliente = $ev->cliente->nombre_completo ?? 'Evento';
            $extras = [];
            if ($saldo > 0) {
                $extras[] = 'saldo Bs ' . number_format($saldo, 2);
            }
            if ($ev->estado === 'Reservado' && $ini->gte($ahora)) {
                $extras[] = 'sin confirmar';
            }
            $extra = $extras ? ' - ' . implode(', ', $extras) : '';

            if ($ahora->gte($fin)) {
                $nivel = 'rojo';
                $icono = 'fa-flag-checkered';
                $texto = ($saldo > 0 ? 'Ya pasó y tiene ' . $extras[0] : 'Ya pasó: márcalo como realizado');
            } elseif ($ahora->gte($ini)) {
                $nivel = 'verde';
                $icono = 'fa-music';
                $texto = 'En curso ahora' . $extra;
            } elseif ($ahora->diffInMinutes($ini) <= 180) {
                $m = (int) $ahora->diffInMinutes($ini);
                $nivel = $m <= 60 ? 'rojo' : 'ambar';
                $icono = 'fa-clock';
                $texto = 'Empieza en ' . ($m >= 60 ? intdiv($m, 60) . 'h ' . ($m % 60) . 'm' : $m . ' min') . $extra;
            } elseif ($extras && $ini->diffInHours($ahora) <= 48) {
                $nivel = 'ambar';
                $icono = 'fa-triangle-exclamation';
                $texto = ($ini->isToday() ? 'Hoy' : ($ini->isTomorrow() ? 'Mañana' : $ini->format('d/m'))) . ' ' . $ini->format('H:i') . $extra;
            } else {
                continue;
            }
            $alertas[] = ['nivel' => $nivel, 'icono' => $icono, 'titulo' => $cliente, 'texto' => $texto, 'href' => route('admin.eventos')];
        }

        if ($contactosNuevos > 0) {
            $alertas[] = ['nivel' => 'ambar', 'icono' => 'fa-envelope', 'titulo' => $contactosNuevos . ($contactosNuevos === 1 ? ' solicitud web nueva' : ' solicitudes web nuevas'), 'texto' => 'Pendientes de responder', 'href' => route('admin.solicitudes-web')];
        }

        $orden = ['rojo' => 0, 'ambar' => 1, 'verde' => 2];
        usort($alertas, fn($a, $b) => $orden[$a['nivel']] <=> $orden[$b['nivel']]);

        $caja = $puedeFinanzas ? \App\Support\Caja::saldos() : [];
        $deudaPersonal = $puedeFinanzas ? \App\Support\Caja::deudaPersonal() : 0.0;

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
            'alertas' => $alertas,
            'caja' => $caja,
            'deudaPersonal' => $deudaPersonal,
        ])->layout('components.layouts.app', [
            'title' => 'Inicio - ' . ($empresa->nombre_comercial ?? 'Mariachi León'),
            'themed' => true,
        ]);
    }
}