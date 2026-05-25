<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppNotificationSetting extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_notification_settings';

    protected $fillable = [
        'empresa_id',
        'module_key',
        'action_key',
        'recipient_key',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeForEvent($query, int $empresaId, string $module, string $action, string $recipient)
    {
        return $query->where('empresa_id', $empresaId)
            ->where('module_key', $module)
            ->where('action_key', $action)
            ->where('recipient_key', $recipient);
    }

    public static function setValue(
        int $empresaId,
        string $module,
        string $action,
        string $recipient,
        bool $enabled
    ): self {
        return self::updateOrCreate(
            [
                'empresa_id' => $empresaId,
                'module_key' => $module,
                'action_key' => $action,
                'recipient_key' => $recipient,
            ],
            ['enabled' => $enabled]
        );
    }
}
