<?php

return [
    /*
    |--------------------------------------------------------------------------
    | VitalRecibo Configuration
    |--------------------------------------------------------------------------
    |
    | Sistema de recibos digitales con QR y validación anti-fraude
    |
    */

    'version' => '1.0.0',

    /*
    |--------------------------------------------------------------------------
    | Configuración de Recibos
    |--------------------------------------------------------------------------
    */
    'recibos' => [
        'serie_default' => 'REC',
        'numeracion_consecutiva' => true,
        'formato_numero' => '{SERIE}-{YEAR}-{FOLIO:5}', // REC-2026-00001
        'qr_enabled' => true,
        'validacion_publica' => true,
        'anulacion_permitida' => true,
        'vigencia_maxima_dias' => 365 * 5, // 5 años
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de QR
    |--------------------------------------------------------------------------
    */
    'qr' => [
        'size' => 200, // pixels
        'margin' => 4,
        'format' => 'png',
        'error_correction' => 'M', // L, M, Q, H
        'incluir_logo' => false,
        'template' => 'RECIBO:{numero}|PROPIETARIO:{propietario}|MONTO:{monto}|FECHA:{fecha}|VALIDAR:{url}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Cooperaciones
    |--------------------------------------------------------------------------
    */
    'cooperaciones' => [
        'parcialidades_permitidas' => true,
        'recargo_mora_porcentaje' => 2.0, // 2% mensual
        'dias_gracia' => 5, // Días antes de aplicar recargo
        'notificacion_vencimiento_dias' => [7, 3, 1], // Días antes de vencer
        'metodos_pago_disponibles' => [
            'efectivo',
            'transferencia',
            'cheque',
            'deposito',
            'spei',
            'oxxo',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Pagos
    |--------------------------------------------------------------------------
    */
    'pagos' => [
        'confirmacion_automatica' => [
            'efectivo' => true,
            'transferencia' => false,
            'cheque' => false,
            'deposito' => false,
            'spei' => false,
        ],
        'comprobante_requerido' => [
            'transferencia',
            'cheque',
            'deposito',
        ],
        'parcialidad_minima_porcentaje' => 10, // Mínimo 10% del total
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de PDF
    |--------------------------------------------------------------------------
    */
    'pdf' => [
        'formato' => 'A4',
        'orientacion' => 'portrait',
        'margenes' => [
            'top' => 15,
            'right' => 15,
            'bottom' => 15,
            'left' => 15,
        ],
        'incluir_header' => true,
        'incluir_footer' => true,
        'logo_path' => 'storage/logos/comite.png',
        'firma_digital' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Validación
    |--------------------------------------------------------------------------
    */
    'validacion' => [
        'hash_algorithm' => 'sha256',
        'url_publica' => env('VITALRECIBO_VALIDATION_URL', '/recibos/validar'),
        'cache_ttl' => 3600, // 1 hora
        'rate_limit' => 100, // 100 validaciones por minuto por IP
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Email
    |--------------------------------------------------------------------------
    */
    'email' => [
        'envio_automatico' => true,
        'template' => 'vitalrecibo::emails.recibo',
        'adjuntar_pdf' => true,
        'remitente' => [
            'nombre' => 'Comité de Propietarios',
            'email' => env('VITALRECIBO_FROM_EMAIL', 'recibos@victania.com'),
        ],
        'asunto_template' => 'Recibo #{numero} - {concepto}',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Reportes
    |--------------------------------------------------------------------------
    */
    'reportes' => [
        'formatos_disponibles' => ['pdf', 'excel'],
        'incluir_graficos' => true,
        'periodos_predefinidos' => [
            'mes_actual',
            'trimestre_actual',
            'semestre_actual',
            'año_actual',
            'personalizado',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de UI
    |--------------------------------------------------------------------------
    */
    'ui' => [
        'tema_colores' => [
            'primario' => '#0ea5e9',     // Azul cielo
            'exito' => '#10b981',        // Verde
            'advertencia' => '#f59e0b',   // Ámbar
            'peligro' => '#ef4444',      // Rojo
            'pendiente' => '#6b7280',    // Gris
        ],
        'iconos' => [
            'recibo' => 'heroicon-o-document-text',
            'cooperacion' => 'heroicon-o-currency-dollar',
            'pago' => 'heroicon-o-credit-card',
            'qr' => 'heroicon-o-qr-code',
        ],
    ],
];