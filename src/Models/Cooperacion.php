<?php

namespace Kaely\VitalRecibo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Cooperaciones asignadas a propietarios
 *
 * @property int $propietario_id
 * @property int $asamblea_id
 * @property int $acuerdo_id
 * @property string $concepto
 * @property float $monto_total
 * @property \Carbon\Carbon $fecha_vencimiento
 */
class Cooperacion extends Model
{
    protected $table = 'cooperaciones';

    protected $fillable = [
        'propietario_id',
        'asamblea_id',
        'acuerdo_id',
        'concepto',
        'descripcion',
        'monto_total',
        'monto_pagado',
        'fecha_asignacion',
        'fecha_vencimiento',
        'status',
        'permite_parcialidades',
        'recargo_mora',
        'observaciones',
    ];

    protected $casts = [
        'monto_total' => 'decimal:2',
        'monto_pagado' => 'decimal:2',
        'recargo_mora' => 'decimal:2',
        'fecha_asignacion' => 'date',
        'fecha_vencimiento' => 'date',
        'permite_parcialidades' => 'boolean',
    ];

    /**
     * Estados de cooperación disponibles
     */
    public static function statusDisponibles(): array
    {
        return [
            'pendiente' => 'Pendiente',
            'parcial' => 'Pago Parcial',
            'pagada' => 'Pagada',
            'vencida' => 'Vencida',
            'cancelada' => 'Cancelada',
        ];
    }

    /**
     * Propietario al que se asigna la cooperación
     */
    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    /**
     * Asamblea donde se acordó la cooperación
     */
    public function asamblea(): BelongsTo
    {
        return $this->belongsTo(Asamblea::class);
    }

    /**
     * Acuerdo específico que genera la cooperación
     */
    public function acuerdo(): BelongsTo
    {
        return $this->belongsTo(Acuerdo::class);
    }

    /**
     * Pago principal de la cooperación
     */
    public function pago(): HasOne
    {
        return $this->hasOne(PagoCooperacion::class)->where('es_pago_completo', true);
    }

    /**
     * Todos los pagos parciales
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCooperacion::class)->orderBy('fecha_pago');
    }

    /**
     * Recibos generados
     */
    public function recibos(): HasMany
    {
        return $this->hasMany(Recibo::class);
    }

    /**
     * Scope para cooperaciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->whereIn('status', ['pendiente', 'parcial']);
    }

    /**
     * Scope para cooperaciones vencidas
     */
    public function scopeVencidas($query)
    {
        return $query->where('fecha_vencimiento', '<', now())
            ->whereIn('status', ['pendiente', 'parcial']);
    }

    /**
     * Scope para cooperaciones pagadas
     */
    public function scopePagadas($query)
    {
        return $query->where('status', 'pagada');
    }

    /**
     * Monto pendiente por pagar
     */
    public function getMontoPendienteAttribute(): float
    {
        return $this->monto_total - ($this->monto_pagado ?? 0);
    }

    /**
     * Verificar si está vencida
     */
    public function getEstaVencidaAttribute(): bool
    {
        return $this->fecha_vencimiento &&
               $this->fecha_vencimiento->isPast() &&
               in_array($this->status, ['pendiente', 'parcial']);
    }

    /**
     * Días de mora
     */
    public function getDiasMoraAttribute(): int
    {
        if (!$this->esta_vencida) {
            return 0;
        }

        return $this->fecha_vencimiento->diffInDays(now());
    }

    /**
     * Calcular recargo por mora
     */
    public function getMontoRecargoAttribute(): float
    {
        if ($this->dias_mora <= 0 || !$this->recargo_mora) {
            return 0;
        }

        // Recargo por día de mora
        return $this->monto_pendiente * ($this->recargo_mora / 100) * $this->dias_mora;
    }

    /**
     * Monto total con recargo
     */
    public function getMontoTotalConRecargoAttribute(): float
    {
        return $this->monto_pendiente + $this->monto_recargo;
    }

    /**
     * Porcentaje de avance de pago
     */
    public function getPorcentajePagoAttribute(): float
    {
        if ($this->monto_total == 0) {
            return 0;
        }

        return round((($this->monto_pagado ?? 0) / $this->monto_total) * 100, 2);
    }

    /**
     * Obtener el status formateado
     */
    public function getStatusFormateadoAttribute(): string
    {
        return static::statusDisponibles()[$this->status] ?? ucfirst($this->status);
    }
}