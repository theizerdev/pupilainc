<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Habilitar Servicio Unificado de Mensajería
    |--------------------------------------------------------------------------
    |
    | Cuando está en true, el sistema usará UnifiedNotificationService
    | para enviar notificaciones. Cuando está en false, usará el método
    | legacy directo a la API de WhatsApp Node.js.
    |
    */
    'use_unified_service' => env('MESSAGING_USE_UNIFIED_SERVICE', false),

    /*
    |--------------------------------------------------------------------------
    | Proveedor por Defecto
    |--------------------------------------------------------------------------
    |
    | Proveedor de mensajería usado cuando no se especifica uno específico.
    | Opciones: whatsapp_lite, whatsapp_meta, twilio, google_fcm
    |
    */
    'default_provider' => env('MESSAGING_DEFAULT_PROVIDER', 'whatsapp_lite'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de WhatsApp Lite
    |--------------------------------------------------------------------------
    */
    'whatsapp_lite' => [
        'api_url' => env('WHATSAPP_API_URL', 'http://82.165.213.124:8092'),
        'timeout' => env('WHATSAPP_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de WhatsApp Meta
    |--------------------------------------------------------------------------
    */
    'whatsapp_meta' => [
        'api_version' => env('WHATSAPP_META_API_VERSION', 'v18.0'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Twilio
    |--------------------------------------------------------------------------
    */
    'twilio' => [
        'timeout' => env('TWILIO_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Google FCM
    |--------------------------------------------------------------------------
    */
    'google_fcm' => [
        'timeout' => env('GOOGLE_FCM_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    |TTL de Cache (en segundos)
    |--------------------------------------------------------------------------
    */
    'cache_ttl' => env('MESSAGING_CACHE_TTL', 300),
];