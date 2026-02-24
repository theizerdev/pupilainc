<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Ramsey\Uuid\Uuid;

trait FiscalAuditable
{
    public static function bootFiscalAuditable(): void
    {
        static::updating(function ($model) {
            $model->registrarTrazabilidad('actualización', $model->getOriginal(), $model->getDirty());
        });

        static::deleting(function ($model) {
            $model->registrarTrazabilidad('eliminación', $model->getOriginal(), []);
        });
    }

    protected function registrarTrazabilidad(string $operacion, array $valoresAnteriores, array $valoresNuevos): void
    {
        try {
            AuditLog::create([
                'id' => Uuid::uuid4()->toString(),
                'user_id' => Auth::id(),
                'action' => "trazabilidad.{$operacion}",
                'auditable_type' => get_class($this),
                'auditable_id' => $this->getKey(),
                'old_values' => $valoresAnteriores,
                'new_values' => $valoresNuevos,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
                'tags' => ['trazabilidad', $operacion, 'model:' . class_basename($this)],
                'metadata' => [
                    'tabla_afectada' => $this->getTable(),
                    'operacion' => $operacion,
                    'fecha_hora' => now()->format('Y-m-d H:i:s'),
                    'usuario_id' => Auth::id(),
                    'usuario_nombre' => Auth::user()?->name,
                    'ip' => Request::ip(),
                    'mac' => static::obtenerMac(),
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en trazabilidad fiscal: ' . $e->getMessage());
        }
    }

    protected static function obtenerMac(): ?string
    {
        try {
            $ip = Request::ip();
            if (PHP_OS_FAMILY === 'Windows') {
                $output = @shell_exec("arp -a {$ip}");
            } else {
                $output = @shell_exec("arp -n {$ip} 2>/dev/null");
            }
            if ($output && preg_match('/([0-9a-fA-F]{2}[:-]){5}[0-9a-fA-F]{2}/', $output, $matches)) {
                return $matches[0];
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        return null;
    }
}
