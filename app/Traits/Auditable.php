<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public function registrarAuditoria(string $modulo, string $accion, string $descripcion): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'modulo' => $modulo,
            'accion' => $accion,
            'descripcion' => $descripcion,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }
}
