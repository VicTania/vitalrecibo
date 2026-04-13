<?php

namespace Kaely\VitalRecibo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Pagos realizados para cooperaciones
 *
 * @property int $cooperacion_id
 * @property int $propietario_id
 * @property float $monto_pagado
 * @property \Carbon\Carbon $fecha_pago
 * @property string $metodo_pago
 * @property string $referencia_pago
 */
class PagoCooperacion extends Model
{
    protected $table = 'pagos_cooperaciones';

    protected $fillable = [
        'cooperacion_id',
        'propietario_id',
        'monto_pagado',
        'fecha_pago',
        'metodo_pago',
        'referencia_pago',
        'es_pago_completo',
        'numero_parcialidad',
        'comprobante_pago',
        'recibido_por',
        'observaciones',
        'status',
    ];

    protected $casts = [
        'monto_pagado' => 'decimal:2',
        'fecha_pago' => 'datetime',
        'es_pago_completo' => 'boolean',
        'numero_parcialidad' => 'integer',
    ];

    /**
     * Métodos de pago disponibles
     */
    public static function metodosPago(): array
    {
        return [
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia Bancaria',
            'cheque' => 'Cheque',
            'deposito' => 'Depósito Bancario',
            'tarjeta' => 'Tarjeta de Débito/Crédito',
            'spei' => 'SPEI',
            'oxxo' => 'OXXO Pay',
        ];
    }

    /**
     * Estados de pago disponibles
     */
    public static function statusDisponibles(): array
    {
        return [
            'pendiente' => 'Pendiente de Confirmación',
            'confirmado' => 'Confirmado',
            'rechazado' => 'Rechazado',
            'revertido' => 'Revertido',
        ];
    }

    /**
     * Cooperación a la que se aplica el pago
     */
    public function cooperacion(): BelongsTo
    {
        return $this->belongsTo(Cooperacion::class);
    }

    /**
     * Propietario que realizó el pago
     */
    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    /**
     * Directivo que recibió el pago
     */
    public function recibidoPor(): BelongsTo
    {
        return $this->belongsTo(Directivo::class, 'recibido_por');
    }

    /**
     * Recibo generado para este pago
     */
    public function recibo(): HasOne
    {
        return $this->hasOne(Recibo::class);
    }

    /**
     * Scope para pagos confirmados
     */
    public function scopeConfirmados($query)
    {
        return $query->where('status', 'confirmado');
    }

    /**
     * Scope para pagos pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('status', 'pendiente');
    }

    /**
     * Scope para pagos completos
     */
    public function scopeCompletos($query)
    {
        return $query->where('es_pago_completo', true);
    }

    /**
     * Scope para parcialidades
     */
    public function scopeParcialidades($query)
    {
        return $query->where('es_pago_completo', false)
            ->whereNotNull('numero_parcialidad');
    }

    /**
     * Verificar si el pago está pendiente de confirmación
     */
    public function getRequiereConfirmacionAttribute(): bool
    {
        return $this->status === 'pendiente' &&
               in_array($this->metodo_pago, ['transferencia', 'cheque', 'deposito']);
    }

    /**
     * Verificar si es pago en efectivo
     */
    public function getEsEfectivoAttribute(): bool
    {
        return $this->metodo_pago === 'efectivo';
    }

    /**
     * Obtener el método de pago formateado
     */
    public function getMetodoPagoFormateadoAttribute(): string
    {
        return static::metodosPago()[$this->metodo_pago] ?? ucfirst($this->metodo_pago);
    }

    /**
     * Obtener el status formateado
     */
    public function getStatusFormateadoAttribute(): string
    {
        return static::statusDisponibles()[$this->status] ?? ucfirst($this->status);
    }

    /**
     * Generar número de parcialidad automáticamente
     */
    public function asignarNumeroParcialidad(): void
    {
        if ($this->es_pago_completo) {
            $this->numero_parcialidad = null;
            return;
        }

        $ultimaParcialidad = static::where('cooperacion_id', $this->cooperacion_id)
            ->where('es_pago_completo', false)
            ->max('numero_parcialidad') ?? 0;

        $this->numero_parcialidad = $ultimaParcialidad + 1;
    }
}