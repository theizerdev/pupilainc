<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleNotificationChannel extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'connection_id',
        'module_key',
        'action_key',
        'recipient_type',
        'enabled',
        'priority',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(MessagingConnection::class, 'connection_id');
    }

    public function scopeForEmpresa($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeForModule($query, string $moduleKey)
    {
        return $query->where('module_key', $moduleKey);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function scopeForEvent($query, string $moduleKey, string $actionKey, string $recipientType)
    {
        return $query->where('module_key', $moduleKey)
            ->where('action_key', $actionKey)
            ->where('recipient_type', $recipientType);
    }
}