<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessagingConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'provider_id',
        'name',
        'credentials',
        'configuration',
        'status',
        'is_default_for',
        'last_test_at',
        'test_result',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'configuration' => 'array',
        'is_default_for' => 'array',
        'last_test_at' => 'datetime',
        'test_result' => 'array',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(MessagingProvider::class, 'provider_id');
    }

    public function notificationChannels(): HasMany
    {
        return $this->hasMany(ModuleNotificationChannel::class, 'connection_id');
    }

    public function scopeForEmpresa($query, int $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isDefaultFor(string $moduleKey): bool
    {
        $defaults = $this->is_default_for ?? [];
        return in_array($moduleKey, $defaults);
    }

    public function markTestResult(array $result): void
    {
        $this->update([
            'last_test_at' => now(),
            'test_result' => $result,
            'status' => $result['success'] ? 'active' : 'error',
        ]);
    }
}