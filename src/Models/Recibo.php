<?php

namespace Kaely\VitalRecibo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Recibos digitales generados por pagos
 *
 * @property string $numero_recibo
 * @property int $pago_cooperacion_id
 * @property int $propietario_id
 * @property float $monto_recibo
 * @property \Carbon\Carbon $fecha_emision
 * @property string $concepto_recibo
 */
class Recibo extends Model
{
    protected $table = 'recibos';

    protected $fillable = [
        'numero_recibo',
        'pago_cooperacion_id',
        'cooperacion_id',
        'propietario_id',
        'monto_recibo',
        'fecha_emision',
        'concepto_recibo',
        'serie',
        'folio',
        'emitido_por',
        'qr_code',
        'url_publica',
        'observaciones',
        'anulado',
        'motivo_anulacion',
    ];

    protected $casts = [
        'monto_recibo' => 'decimal:2',
        'fecha_emision' => 'datetime',
        'anulado' => 'boolean',
        'folio' => 'integer',
    ];

    /**
     * Pago que origina este recibo
     */
    public function pagoCooperacion(): BelongsTo
    {
        return $this->belongsTo(PagoCooperacion::class);
    }

    /**
     * Cooperación relacionada
     */
    public function cooperacion(): BelongsTo
    {
        return $this->belongsTo(Cooperacion::class);
    }

    /**
     * Propietario que paga
     */
    public function propietario(): BelongsTo
    {
        return $this->belongsTo(Propietario::class);
    }

    /**
     * Directivo que emitió el recibo
     */
    public function emitidoPor(): BelongsTo
    {
        return $this->belongsTo(Directivo::class, 'emitido_por');
    }

    /**
     * Scope para recibos vigentes (no anulados)
     */
    public function scopeVigentes($query)
    {
        return $query->where('anulado', false);
    }

    /**
     * Scope para recibos anulados
     */
    public function scopeAnulados($query)
    {
        return $query->where('anulado', true);
    }

    /**
     * Scope por serie
     */
    public function scopeBySerie($query, string $serie)
    {
        return $query->where('serie', $serie);
    }

    /**
     * Generar número de recibo automáticamente
     */
    public function generarNumeroRecibo(): string
    {
        $serie = $this->serie ?? 'REC';
        $año = $this->fecha_emision->year;

        // Obtener el siguiente folio para esta serie y año
        $ultimoFolio = static::where('serie', $serie)
            ->whereYear('fecha_emision', $año)
            ->max('folio') ?? 0;

        $nuevoFolio = $ultimoFolio + 1;
        $this->folio = $nuevoFolio;

        // Formato: SERIE-AÑO-FOLIO (ej: REC-2026-00001)
        $numeroRecibo = sprintf('%s-%d-%05d', $serie, $año, $nuevoFolio);
        $this->numero_recibo = $numeroRecibo;

        return $numeroRecibo;
    }

    /**
     * Generar código QR para validación
     */
    public function generarQrCode(): string
    {
        // URL de validación del recibo
        $urlValidacion = route('recibos.validar', [
            'numero' => $this->numero_recibo,
            'hash' => $this->getHashValidacion()
        ]);

        $this->url_publica = $urlValidacion;

        // Aquí podrías usar una librería como SimpleQR o QR Code Generator
        $qrContent = "RECIBO:{$this->numero_recibo}|PROPIETARIO:{$this->propietario->nombre}|MONTO:{$this->monto_recibo}|FECHA:{$this->fecha_emision->format('Y-m-d')}|VALIDAR:{$urlValidacion}";

        return $qrContent;
    }

    /**
     * Hash de validación para el recibo
     */
    public function getHashValidacion(): string
    {
        return hash('sha256',
            $this->numero_recibo .
            $this->propietario_id .
            $this->monto_recibo .
            $this->fecha_emision->format('Y-m-d H:i:s') .
            config('app.key')
        );
    }

    /**
     * Verificar validez del recibo
     */
    public function esValido(string $hash): bool
    {
        return !$this->anulado && hash_equals($this->getHashValidacion(), $hash);
    }

    /**
     * Anular recibo
     */
    public function anular(string $motivo, int $anulado_por = null): bool
    {
        $this->anulado = true;
        $this->motivo_anulacion = $motivo;

        if ($anulado_por) {
            $this->observaciones = ($this->observaciones ?? '') .
                "\nAnulado por directivo ID: {$anulado_por} el " . now()->format('Y-m-d H:i:s');
        }

        return $this->save();
    }

    /**
     * Obtener el año fiscal del recibo
     */
    public function getAñoFiscalAttribute(): int
    {
        return $this->fecha_emision->year;
    }

    /**
     * Verificar si es el recibo más reciente del propietario
     */
    public function getEsUltimoReciboAttribute(): bool
    {
        $ultimoRecibo = static::where('propietario_id', $this->propietario_id)
            ->where('anulado', false)
            ->orderBy('fecha_emision', 'desc')
            ->first();

        return $ultimoRecibo && $ultimoRecibo->id === $this->id;
    }
}