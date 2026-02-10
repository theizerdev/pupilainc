<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $connection = 'whatsapp_api';
    
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'messageId',
        'from',
        'to',
        'message',
        'type',
        'status',
        'mediaUrl',
        'retryCount',
        'errorMessage',
        'companyId',
    ];

    protected $casts = [
        'message' => 'array',
        'createdAt' => 'datetime',
        'updatedAt' => 'datetime',
        'retryCount' => 'integer',
        'companyId' => 'integer',
    ];

    protected $attributes = [
        'status' => 'pending',
        'type' => 'text',
        'retryCount' => 0,
    ];

    // Mapeo de columnas para compatibilidad
    public function getMessageIdAttribute()
    {
        return $this->attributes['messageId'] ?? null;
    }

    public function getRecipientPhoneAttribute()
    {
        return $this->attributes['to'] ?? null;
    }

    public function getRecipientNameAttribute()
    {
        return $this->attributes['recipient_name'] ?? null;
    }

    public function getMessageContentAttribute()
    {
        if (isset($this->attributes['message'])) {
            $msg = json_decode($this->attributes['message'], true);
            return $msg['text'] ?? $this->attributes['message'];
        }
        return null;
    }

    public function getErrorMessageAttribute()
    {
        return $this->attributes['errorMessage'] ?? null;
    }

    public function getRetryCountAttribute()
    {
        return $this->attributes['retryCount'] ?? 0;
    }

    public function getCreatedAtAttribute()
    {
        return $this->attributes['createdAt'] ?? null;
    }

    public function getUpdatedAtAttribute()
    {
        return $this->attributes['updatedAt'] ?? null;
    }

    // Scopes
    public function scopeOutbound($query)
    {
        return $query->whereNotNull('from');
    }

    public function scopeInbound($query)
    {
        return $query->whereNotNull('to');
    }

    public function scopeToPhone($query, $phone)
    {
        return $query->where('to', $phone);
    }

    public function scopeFromPhone($query, $phone)
    {
        return $query->where('from', $phone);
    }

    public function scopeRetryable($query)
    {
        return $query->where(function ($query) {
            $query->where('status', 'failed')
                  ->orWhereNotNull('errorMessage');
        })
        ->where('retryCount', '<', 3);
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('createdAt', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('createdAt', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('createdAt', now()->month);
    }

    public function scopeForCompany($query, $companyId)
    {
        return $query->where('companyId', $companyId);
    }

    // Métodos de estado
    public function isSent(): bool
    {
        return in_array($this->status, ['sent', 'delivered', 'read']);
    }

    public function isDelivered(): bool
    {
        return in_array($this->status, ['delivered', 'read']);
    }

    public function isRead(): bool
    {
        return $this->status === 'read';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRetryable(): bool
    {
        if ($this->isFailed() || !empty($this->errorMessage)) {
            return ($this->retryCount ?? 0) < 3;
        }
        return false;
    }

    // Métodos de actualización
    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
        ]);
    }

    public function markAsRead(): void
    {
        $this->update([
            'status' => 'read',
        ]);
    }

    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => 'failed',
            'errorMessage' => $errorMessage,
        ]);
    }

    public function incrementRetryCount(): void
    {
        $this->increment('retryCount');
    }
}
