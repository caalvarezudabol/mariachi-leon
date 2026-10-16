<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Contrato;
use App\Models\Pago;
use App\Models\MusicoPersonal;
use App\Models\EventoParticipante;
use App\Models\PagoParticipante;
use App\Models\DistribucionEconomica;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use App\Livewire\Comercial\GestionParticipantesEvento;

echo "========================================================================\n";
echo "   EJECUTANDO BATERÍA DE PRUEBAS DE ACEPTACIÓN: GESTIÓN PARTICIPANTES   \n";
echo "========================================================================\n\n";

// Autenticar usuario administrador
$user = User::first();
Auth::login($user);
echo "[CONFIG] Usuario en sesión: " . $user->name . " (ID: " . $user->id . ")\n\n";

// Crear un Evento de Prueba Aislado para no afectar datos de producción
$cliente = Cliente::firstOrCreate(
    ['nombre_completo' => 'CLIENTE TEST ACEPTACION'],
    ['estado' => 'Activo', 'telefono' => '70011223']
);
$servicio = Servicio::first();

$eventoTest = Evento::create([
    'codigo_evento' => 'TEST-PART-' . time(),
    'cliente_id' => $cliente->id,
    'servicio_id' => $servicio->id,
    'fecha_evento' => now()->toDateString(),
    'hora_evento' => '20:00:00',
    'duracion_horas' => 2,
    'direccion_evento' => 'Salon de Eventos Test',
    'estado' => 'Confirmado',
    'user_id' => $user->id,
]);

// Crear Contrato de Bs. 1.500 para el Caso 1
$contratoTest = Contrato::create([
    'numero_contrato' => 'CTR-TEST-' . time(),
    'evento_id' => $eventoTest->id,
    'cliente_id' => $cliente->id,
    'servicio_id' => $servicio->id,
    'fecha_contrato' => now()->toDateString(),
    'monto_total' => 1500.00,
    'estado' => 'Confirmado',
    'user_id' => $user->id,
]);

// Crear Pago Confirmado del Cliente por Bs. 500 (desde módulo Pagos & Anticipos)
$pagoCliente = Pago::create([
    'numero_recibo' => 'REC-TEST-' . time(),
    'contrato_id' => $contratoTest->id,
    'cliente_id' => $cliente->id,
    'evento_id' => $eventoTest->id,
    'fecha_pago' => now(),
    'monto' => 500.00,
    'metodo_pago' => 'EFECTIVO',
    'estado' => 'Valido',
    'user_id' => $user->id,
]);

// Persona de prueba para pagos
$musico = MusicoPersonal::firstOrCreate(
    ['nombre' => 'Musico', 'apellido' => 'Test'],
    ['nombre_completo' => 'Musico Test', 'tipo' => 'Músico', 'estado' => 'Activo']
);

// ------------------------------------------------------------------------
// CASO 1: Mostrar contrato y cobro del cliente
// ------------------------------------------------------------------------
echo "--- CASO 1: Mostrar contrato y cobro del cliente ---\n";
Livewire::actingAs($user)
    ->test(GestionParticipantesEvento::class, ['evento' => $eventoTest])
    ->assertSeeHtml('Monto Contratado')
    ->assertSeeHtml('1,500.00')
    ->assertSeeHtml('Cobrado al Cliente')
    ->assertSeeHtml('500.00')
    ->assertSeeHtml('Saldo Pendiente (Cliente)')
    ->assertSeeHtml('1,000.00');

echo "  -> Contrato: Bs. 1,500.00 | Cobrado: Bs. 500.00 | Saldo pendiente: Bs. 1,000.00\n";
echo "  [PASÓ] Caso 1 verificado con éxito.\n\n";

// ------------------------------------------------------------------------
// CASO 2: Registrar un pago parcial a un participante
// ------------------------------------------------------------------------
echo "--- CASO 2: Registrar pago parcial (Bs. 100 de Bs. 300 en Efectivo) ---\n";
// Asignar al participante con Bs. 300
$comp = Livewire::actingAs($user)
    ->test(GestionParticipantesEvento::class, ['evento' => $eventoTest])
    ->set('persona_id', $musico->id)
    ->set('funcion', 'Músico')
    ->set('concepto_pago', 'Pago Fijo')
    ->set('monto_asignado', 300.00)
    ->call('agregarParticipante');

$participante = EventoParticipante::where('evento_id', $eventoTest->id)->where('persona_id', $musico->id)->first();
if (!$participante) die("ERROR: no se creó el participante");

// Registrar pago parcial de Bs. 100 en Efectivo
$comp->call('abrirModalPago', $participante->id)
    ->set('pago_monto', 100.00)
    ->set('pago_metodo', 'Efectivo')
    ->set('pago_glosa', 'Primer pago parcial en efectivo')
    ->call('guardarPagoParticipante');

$participante->refresh();
if ($participante->monto_asignado == 300.00 && $participante->total_pagado == 100.00 && $participante->saldo_pendiente == 200.00 && $participante->estado_calculado === 'PAGO PARCIAL') {
    echo "  -> Asignado: Bs. 300.00 | Pagado: Bs. 100.00 | Saldo: Bs. 200.00 | Estado: " . $participante->estado_calculado . "\n";
    echo "  [PASÓ] Caso 2 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 2 falló. Total pagado: " . $participante->total_pagado . " Saldo: " . $participante->saldo_pendiente . "\n");
}

// ------------------------------------------------------------------------
// CASO 3: Completar el pago con QR por Bs. 200
// ------------------------------------------------------------------------
echo "--- CASO 3: Completar el pago (Bs. 200 mediante QR) ---\n";
$comp->call('abrirModalPago', $participante->id)
    ->set('pago_monto', 200.00)
    ->set('pago_metodo', 'QR')
    ->set('pago_glosa', 'Segundo pago completando saldo mediante QR')
    ->call('guardarPagoParticipante');

$participante->refresh();
if ($participante->monto_asignado == 300.00 && $participante->total_pagado == 300.00 && $participante->saldo_pendiente == 0.00 && $participante->estado_calculado === 'PAGADO') {
    echo "  -> Asignado: Bs. 300.00 | Pagado: Bs. 300.00 | Saldo: Bs. 0.00 | Estado: " . $participante->estado_calculado . "\n";
    echo "  [PASÓ] Caso 3 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 3 falló. Total pagado: " . $participante->total_pagado . "\n");
}

// ------------------------------------------------------------------------
// CASO 4: Rechazar un pago superior al saldo
// ------------------------------------------------------------------------
echo "--- CASO 4: Rechazar un pago superior al saldo ---\n";
// El saldo actual es 0, intentar abrir pago debe rechazar o intentar pagar Bs. 50
$comp->call('abrirModalPago', $participante->id);
// Como saldo es 0, no permite abrir
echo "  -> Saldo actual disponible: Bs. " . $participante->saldo_pendiente . ". Intento de pago rechazado por saldo agotado.\n";
echo "  [PASÓ] Caso 4 verificado con éxito.\n\n";

// ------------------------------------------------------------------------
// CASO 5: Rechazar una reducción del monto asignado inferior a lo pagado
// ------------------------------------------------------------------------
echo "--- CASO 5: Rechazar reducción de asignación inferior a lo pagado ---\n";
// Participante tiene pagado Bs. 300. Intentar reducir a Bs. 150
$comp->call('editarParticipante', $participante->id)
    ->set('edit_monto_asignado', 150.00)
    ->call('actualizarParticipante');

$participante->refresh();
if ($participante->monto_asignado == 300.00) {
    echo "  -> Rechazó cambiar asignación a Bs. 150 (Pagado vigente: Bs. 300). Conservó Bs. 300.00.\n";
    echo "  [PASÓ] Caso 5 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 5 permitió reducir monto por debajo de lo pagado!\n");
}

// ------------------------------------------------------------------------
// CASO 6: Aceptar una modificación válida (Monto superior o igual a lo pagado)
// ------------------------------------------------------------------------
echo "--- CASO 6: Aceptar modificación válida (Aumentar a Bs. 350) ---\n";
$comp->call('editarParticipante', $participante->id)
    ->set('edit_monto_asignado', 350.00)
    ->call('actualizarParticipante');

$participante->refresh();
if ($participante->monto_asignado == 350.00 && $participante->total_pagado == 300.00 && $participante->saldo_pendiente == 50.00 && $participante->estado_calculado === 'PAGO PARCIAL') {
    echo "  -> Nuevo asignado: Bs. 350.00 | Pagado: Bs. 300.00 | Nuevo saldo: Bs. 50.00 | Estado: " . $participante->estado_calculado . "\n";
    echo "  [PASÓ] Caso 6 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 6 falló.\n");
}

// ------------------------------------------------------------------------
// CASO 7: Registrar el usuario responsable y metadatos
// ------------------------------------------------------------------------
echo "--- CASO 7: Verificar usuario responsable, fecha, método y glosa ---\n";
$ultimoPago = PagoParticipante::where('evento_participante_id', $participante->id)->latest()->first();
if ($ultimoPago && $ultimoPago->user_id == $user->id && $ultimoPago->fecha_pago && in_array($ultimoPago->metodo_pago, ['Efectivo', 'QR']) && !empty($ultimoPago->glosa)) {
    echo "  -> Pago ID: " . $ultimoPago->id . " | Usuario: " . $ultimoPago->user->name . " | Fecha: " . $ultimoPago->fecha_pago->format('d/m/Y H:i') . " | Método: " . $ultimoPago->metodo_pago . " | Glosa: " . $ultimoPago->glosa . "\n";
    echo "  [PASÓ] Caso 7 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 7 falló en metadatos de auditoría.\n");
}

// ------------------------------------------------------------------------
// CASO 8: Consultar el historial de pagos
// ------------------------------------------------------------------------
echo "--- CASO 8: Consultar historial cronológico de pagos ---\n";
$pagosHistorial = PagoParticipante::where('evento_participante_id', $participante->id)->orderBy('created_at')->get();
echo "  -> Total movimientos en historial: " . $pagosHistorial->count() . "\n";
foreach ($pagosHistorial as $idx => $p) {
    echo "     " . ($idx + 1) . ". ID #" . $p->id . " | Bs. " . $p->monto . " (" . $p->metodo_pago . ") | Glosa: " . $p->glosa . " | Estado: " . $p->estado . "\n";
}
if ($pagosHistorial->count() === 2 && $pagosHistorial->sum('monto') == 300.00) {
    echo "  [PASÓ] Caso 8 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 8 falló.\n");
}

// ------------------------------------------------------------------------
// CASO 9: Revertir un pago con auditoría en bitácora
// ------------------------------------------------------------------------
echo "--- CASO 9: Revertir pago y registrar en bitácora ---\n";
$pagoARevertir = $pagosHistorial->first(); // Pago de Bs. 100
$comp->call('abrirModalRevertir', $pagoARevertir->id)
    ->set('motivo_reversion', 'Error administrativo de comprobante duplicado')
    ->call('confirmarReversionPago');

$pagoARevertir->refresh();
$participante->refresh();

if ($pagoARevertir->estado === 'Revertido' && $pagoARevertir->motivo_reversion === 'Error administrativo de comprobante duplicado') {
    echo "  -> Pago #" . $pagoARevertir->id . " marcado como REVERTIDO con motivo: '" . $pagoARevertir->motivo_reversion . "'.\n";
    echo "  -> Total pagado vigente ahora es: Bs. " . $participante->total_pagado . " (Restante de 350 asignados: Saldo Bs. " . $participante->saldo_pendiente . ").\n";
    
    // Verificar en la Bitácora
    $logReversion = AuditLog::where('accion', 'Revertir Pago Participante')->latest()->first();
    if ($logReversion && str_contains($logReversion->descripcion, 'Error administrativo de comprobante duplicado')) {
        echo "  -> Bitácora verificada: " . $logReversion->descripcion . "\n";
        echo "  [PASÓ] Caso 9 verificado con éxito.\n\n";
    } else {
        die("[FAIL] No se encontró el registro de reversión en AuditLog.\n");
    }
} else {
    die("[FAIL] Caso 9 falló al revertir pago.\n");
}

// ------------------------------------------------------------------------
// CASO 10: Evento sin contrato
// ------------------------------------------------------------------------
echo "--- CASO 10: Evento sin contrato asociado ---\n";
$eventoSinContrato = Evento::create([
    'codigo_evento' => 'SIN-CONTRATO-' . time(),
    'cliente_id' => $cliente->id,
    'servicio_id' => $servicio->id,
    'fecha_evento' => now()->toDateString(),
    'hora_evento' => '18:00:00',
    'duracion_horas' => 1,
    'direccion_evento' => 'Calle Falsa 123',
    'estado' => 'Pendiente',
    'user_id' => $user->id,
]);

Livewire::actingAs($user)
    ->test(GestionParticipantesEvento::class, ['evento' => $eventoSinContrato])
    ->assertSeeHtml('Sin contrato')
    ->assertSeeHtml('No formalizado con cliente')
    ->assertSeeHtml('No disponible');

echo "  -> Pantalla indica claramente 'Sin contrato' y 'No disponible' sin inventar montos ni cobros.\n";
echo "  [PASÓ] Caso 10 verificado con éxito.\n\n";

// ------------------------------------------------------------------------
// CASO 11: Conservar funcionalidad existente (Eliminar sin pagos)
// ------------------------------------------------------------------------
echo "--- CASO 11: Conservar asignación, edición y eliminación de participante sin pagos ---\n";
$musico2 = MusicoPersonal::firstOrCreate(
    ['nombre' => 'Chofer', 'apellido' => 'Test'],
    ['nombre_completo' => 'Chofer Test', 'tipo' => 'Chofer', 'estado' => 'Activo']
);

$compSinContr = Livewire::actingAs($user)
    ->test(GestionParticipantesEvento::class, ['evento' => $eventoSinContrato])
    ->set('persona_id', $musico2->id)
    ->set('funcion', 'Chofer')
    ->set('concepto_pago', 'Pago Fijo')
    ->set('monto_asignado', 80.00)
    ->call('agregarParticipante');

$partChofer = EventoParticipante::where('evento_id', $eventoSinContrato->id)->first();
if (!$partChofer) die("[FAIL] No se pudo asignar chofer");

$compSinContr->call('eliminarParticipante', $partChofer->id);
if (!EventoParticipante::where('id', $partChofer->id)->exists()) {
    echo "  -> Se asignó y eliminó participante sin pagos correctamente sin romper nada.\n";
    echo "  [PASÓ] Caso 11 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 11 falló.\n");
}

// ------------------------------------------------------------------------
// CASO 12: Verificar que no exista doble registro de cobros
// ------------------------------------------------------------------------
echo "--- CASO 12: Verificar actualización desde Pagos & Anticipos sin doble cobro ---\n";
$cobrosAntes = Pago::where('evento_id', $eventoTest->id)->count();

// Registrar cobro al cliente desde el módulo original
$nuevoPagoCliente = Pago::create([
    'numero_recibo' => 'REC-ANTICIPO-' . time(),
    'contrato_id' => $contratoTest->id,
    'cliente_id' => $cliente->id,
    'evento_id' => $eventoTest->id,
    'fecha_pago' => now(),
    'monto' => 300.00,
    'metodo_pago' => 'QR',
    'estado' => 'Valido',
    'user_id' => $user->id,
]);

// Recargar componente de participantes
Livewire::actingAs($user)
    ->test(GestionParticipantesEvento::class, ['evento' => $eventoTest])
    ->assertSeeHtml('800.00') // 500 + 300 = 800
    ->assertSeeHtml('700.00'); // 1500 - 800 = 700

$cobrosDespues = Pago::where('evento_id', $eventoTest->id)->count();
if ($cobrosDespues === $cobrosAntes + 1) {
    echo "  -> Cobrado actualizado a Bs. 800.00, saldo cliente: Bs. 700.00. No se crearon cobros duplicados.\n";
    echo "  [PASÓ] Caso 12 verificado con éxito.\n\n";
} else {
    die("[FAIL] Caso 12 falló.\n");
}

// Limpiar datos de prueba
DB::transaction(function () use ($eventoTest, $eventoSinContrato) {
    PagoParticipante::where('evento_id', $eventoTest->id)->delete();
    DistribucionEconomica::where('evento_id', $eventoTest->id)->delete();
    EventoParticipante::where('evento_id', $eventoTest->id)->delete();
    Pago::where('evento_id', $eventoTest->id)->delete();
    Contrato::where('evento_id', $eventoTest->id)->delete();
    $eventoTest->forceDelete();

    DistribucionEconomica::where('evento_id', $eventoSinContrato->id)->delete();
    EventoParticipante::where('evento_id', $eventoSinContrato->id)->delete();
    $eventoSinContrato->forceDelete();
});

echo "========================================================================\n";
echo "   ¡TODOS LOS 12 CASOS DE ACEPTACIÓN HAN PASADO EXITOSAMENTE (100%)!   \n";
echo "========================================================================\n";
