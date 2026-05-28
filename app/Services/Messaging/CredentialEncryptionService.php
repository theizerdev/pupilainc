<?php

namespace App\Services\Messaging;

use Illuminate\Support\Facades\Crypt;

class CredentialEncryptionService
{
    /**
     * Encriptar credenciales
     */
    public function encrypt(array $credentials): string
    {
        return Crypt::encryptString(json_encode($credentials));
    }

    /**
     * Desencriptar credenciales
     */
    public function decrypt(string $encrypted): array
    {
        return json_decode(Crypt::decryptString($encrypted), true);
    }

    /**
     * Encriptar credencial individual
     */
    public function encryptField(string $value): string
    {
        return Crypt::encryptString($value);
    }

    /**
     * Desencriptar credencial individual
     */
    public function decryptField(string $encrypted): string
    {
        return Crypt::decryptString($encrypted);
    }

    /**
     * Verificar si una cadena está encriptada
     */
    public function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Encriptar credenciales con soporte para valores no encriptados (legacy)
     * Si el valor ya está encriptado, lo retorna sin cambios
     */
    public function encryptIfNeeded(mixed $value): string
    {
        if (is_array($value)) {
            // Si es array, encriptar todo
            return $this->encrypt($value);
        }

        if (is_string($value)) {
            // Si ya está encriptado, retornarlo
            if ($this->isEncrypted($value)) {
                return $value;
            }
            // Si no está encriptado, encriptar solo este valor
            return $this->encryptField($value);
        }

        return $value;
    }

    /**
     * Desencriptar credenciales, manejando tanto valores encriptados como legacy
     * Retorna null si el valor no puede ser desencriptado
     */
    public function decryptIfNeeded(string $value): ?array
    {
        if ($this->isEncrypted($value)) {
            return $this->decrypt($value);
        }

        // Legacy: podría ser un valor plano (no encriptado)
        // Retornar como array con la key 'legacy_value'
        return ['legacy_value' => $value];
    }
}