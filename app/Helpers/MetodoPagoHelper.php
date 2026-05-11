<?php

namespace App\Helpers;

/**
 * Helper para métodos de pago
 * Centraliza la configuración de nombres amigables, iconos y categorías
 */
class MetodoPagoHelper
{
    /**
     * Mapeo de métodos de pago a nombres amigables
     * Agrega nuevos métodos aquí y aparecerán automáticamente en todo el sistema
     */
    const NOMBRES_AMIGABLES = [
        // Efectivo
        'efectivo' => 'Efectivo',
        'efectivo_bs' => 'Efectivo Bs',
        'efectivo_usd' => 'Efectivo USD',

        // Transferencias
        'transferencia' => 'Transferencia',
        'transferencia_bs' => 'Transferencia Bs',
        'transferencia_usd' => 'Transferencia USD',
        'pago_movil' => 'Pago Móvil',
        'zelle' => 'Zelle',
        'paypal' => 'PayPal',
        'usdt' => 'USDT',

        // Tarjetas genéricas
        'tarjeta' => 'Tarjeta',
        'tarjeta_debito' => 'Tarjeta Débito',
        'tarjeta_credito' => 'Tarjeta Crédito',

        // Bancos específicos - Débito
        'bbva_dr' => 'BBVA Débito',
        'mercantil_dr' => 'Mercantil Débito',
        'banesco_dr' => 'Banesco Débito',
        'provincial_dr' => 'Provincial Débito',
        'bod_dr' => 'BOD Débito',

        // Bancos específicos - Crédito
        'bbva_cr' => 'BBVA Crédito',
        'mercantil_cr' => 'Mercantil Crédito',
        'banesco_cr' => 'Banesco Crédito',
        'provincial_cr' => 'Provincial Crédito',
        'bod_cr' => 'BOD Crédito',
    ];

    /**
     * Mapeo de métodos de pago a iconos (Remix Icon classes)
     */
    const ICONOS = [
        // Efectivo
        'efectivo' => 'ri ri-money-dollar-circle-line text-success',
        'efectivo_bs' => 'ri ri-money-dollar-circle-line text-success',
        'efectivo_usd' => 'ri ri-money-dollar-circle-line text-success',

        // Transferencias
        'transferencia' => 'ri ri-bank-line text-info',
        'transferencia_bs' => 'ri ri-bank-line text-info',
        'transferencia_usd' => 'ri ri-bank-line text-info',
        'pago_movil' => 'ri ri-smartphone-line text-primary',
        'zelle' => 'ri ri-bank-card-line text-purple',
        'paypal' => 'ri ri-paypal-line text-info',
        'usdt' => 'ri ri-bitcoin-line text-warning',

        // Tarjetas
        'tarjeta' => 'ri ri-bank-card-line text-warning',
        'tarjeta_debito' => 'ri ri-bank-card-line text-primary',
        'tarjeta_credito' => 'ri ri-bank-card-line text-warning',

        // Bancos - Débito (azul)
        'bbva_dr' => 'ri ri-bank-card-line text-primary',
        'mercantil_dr' => 'ri ri-bank-card-line text-primary',
        'banesco_dr' => 'ri ri-bank-card-line text-primary',
        'provincial_dr' => 'ri ri-bank-card-line text-primary',
        'bod_dr' => 'ri ri-bank-card-line text-primary',

        // Bancos - Crédito (naranja/amarillo)
        'bbva_cr' => 'ri ri-bank-card-line text-warning',
        'mercantil_cr' => 'ri ri-bank-card-line text-warning',
        'banesco_cr' => 'ri ri-bank-card-line text-warning',
        'provincial_cr' => 'ri ri-bank-card-line text-warning',
        'bod_cr' => 'ri ri-bank-card-line text-warning',
    ];

    /**
     * Categorías de métodos de pago
     */
    const CATEGORIAS = [
        'efectivo' => 'EFECTIVO',
        'efectivo_bs' => 'EFECTIVO',
        'efectivo_usd' => 'EFECTIVO',

        'transferencia' => 'TRANSFERENCIAS',
        'transferencia_bs' => 'TRANSFERENCIAS',
        'transferencia_usd' => 'TRANSFERENCIAS',
        'pago_movil' => 'TRANSFERENCIAS',
        'zelle' => 'TRANSFERENCIAS',
        'paypal' => 'TRANSFERENCIAS',
        'usdt' => 'TRANSFERENCIAS',

        'tarjeta_credito' => 'TARJETA CRÉDITO',
        'bbva_cr' => 'TARJETA CRÉDITO',
        'mercantil_cr' => 'TARJETA CRÉDITO',
        'banesco_cr' => 'TARJETA CRÉDITO',
        'provincial_cr' => 'TARJETA CRÉDITO',
        'bod_cr' => 'TARJETA CRÉDITO',

        'tarjeta_debito' => 'TARJETA DÉBITO',
        'bbva_dr' => 'TARJETA DÉBITO',
        'mercantil_dr' => 'TARJETA DÉBITO',
        'banesco_dr' => 'TARJETA DÉBITO',
        'provincial_dr' => 'TARJETA DÉBITO',
        'bod_dr' => 'TARJETA DÉBITO',

        'tarjeta' => 'TARJETA CRÉDITO', // Legacy
    ];

    /**
     * Obtener nombre amigable de un método de pago
     * Si no existe en el mapeo, convierte snake_case a Title Case automáticamente
     */
    public static function getNombreAmigable(string $metodo): string
    {
        return self::NOMBRES_AMIGABLES[$metodo] ??
               ucfirst(str_replace('_', ' ', $metodo));
    }

    /**
     * Obtener icono de un método de pago
     * Si no existe en el mapeo, devuelve un icono genérico
     */
    public static function getIcono(string $metodo): string
    {
        return self::ICONOS[$metodo] ?? 'ri ri-question-line text-muted';
    }

    /**
     * Obtener categoría de un método de pago
     * Si no existe en el mapeo, devuelve 'OTROS'
     */
    public static function getCategoria(string $metodo): string
    {
        return self::CATEGORIAS[$metodo] ?? 'OTROS';
    }

    /**
     * Verificar si un método de pago está configurado
     */
    public static function estaConfigurado(string $metodo): bool
    {
        return isset(self::NOMBRES_AMIGABLES[$metodo]);
    }

    /**
     * Obtener todos los métodos de pago configurados
     */
    public static function getTodosMetodos(): array
    {
        return self::NOMBRES_AMIGABLES;
    }
}
