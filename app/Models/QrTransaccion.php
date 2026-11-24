<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrTransaccion extends Model
{
    use HasFactory;

    protected $table = 'qr_transacciones';

    protected $fillable = [
        'transaction_id',
        'qr_id',
        'origen',
        'contacto_web_id',
        'contrato_id',
        'pago_id',
        'cliente_id',
        'monto',
        'moneda',
        'descripcion',
        'due_date',
        'qr_image_base64',
        'estado',
        'expira_en',
        'pagado_en',
        'datos_pago',
        'created_by',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'due_date' => 'date',
        'expira_en' => 'datetime',
        'pagado_en' => 'datetime',
        'datos_pago' => 'array',
    ];

    public function contactoWeb()
    {
        return $this->belongsTo(ContactoWeb::class, 'contacto_web_id');
    }

    public function contrato()
    {
        return $this->belongsTo(Contrato::class, 'contrato_id');
    }

    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getNombreClienteAttribute(): string
    {
        if ($this->cliente) {
            return $this->cliente->nombre_completo;
        }
        if ($this->contactoWeb) {
            return $this->contactoWeb->nombre;
        }
        if ($this->contrato && $this->contrato->cliente) {
            return $this->contrato->cliente->nombre_completo;
        }
        if ($this->pago && $this->pago->cliente) {
            return $this->pago->cliente->nombre_completo;
        }
        if (!empty($this->datos_pago['senderName'])) {
            return $this->datos_pago['senderName'];
        }
        return 'Cliente General';
    }

    public function getTelefonoClienteAttribute(): ?string
    {
        if ($this->cliente) {
            return $this->cliente->telefono;
        }
        if ($this->contactoWeb) {
            return $this->contactoWeb->telefono;
        }
        if ($this->contrato && $this->contrato->cliente) {
            return $this->contrato->cliente->telefono;
        }
        return null;
    }

    public function getCanalOrigenTextoAttribute(): string
    {
        if ($this->origen === 'contacto_web') {
            return 'Web Pública (Cliente)';
        }
        if ($this->creador) {
            return 'Sistema (' . $this->creador->name . ')';
        }
        return 'Sistema Interno';
    }

    public function estaExpirado(): bool
    {
        if ($this->estado === 'PAGADO') {
            return false;
        }
        return $this->expira_en && $this->expira_en->isPast();
    }

    public function segundosRestantes(): int
    {
        if (!$this->expira_en || $this->estaExpirado() || $this->estado !== 'PENDIENTE') {
            return 0;
        }
        return max(0, (int) now()->diffInSeconds($this->expira_en, false));
    }
}
