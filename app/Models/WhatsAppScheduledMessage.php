<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppScheduledMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_scheduled_messages';

    protected $fillable = [
        'template_id',
        'recipient_phone',
        'recipient_name',
        'message_content',
        'variables',
        'scheduled_at',
        'sent_at',
        'status',
        'error_message',
        'attempts',
        'max_attempts',
        'created_by',
        'cita_id',
        'empresa_id',
        'notification_type'
    ];

    protected $casts = [
        'variables' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'attempts' => 'integer',
        'max_attempts' => 'integer'
    ];

    protected $attributes = [
        'status' => 'pending',
        'attempts' => 0,
        'max_attempts' => 3
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_at', '<=', now())
                    ->where('attempts', '<', \DB::raw('max_attempts'));
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_at', '>', now());
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeParaCita($query, $citaId)
    {
        return $query->where('cita_id', $citaId);
    }

    public function scopePendientesDeCita($query, $citaId)
    {
        return $query->where('cita_id', $citaId)->where('status', 'pending');
    }

    public function canRetry(): bool
    {
        return $this->attempts < $this->max_attempts && $this->status === 'failed';
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
        
        if ($this->template) {
            $this->template->incrementUsage();
        }
    }

    public function markAsFailed(string $errorMessage = null): void
    {
        $this->increment('attempts');
        
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage
        ]);
    }

    public function retry(): void
    {
        if ($this->canRetry()) {
            $this->update([
                'status' => 'pending',
                'error_message' => null
            ]);
        }
    }

    public function cancelar(): void
    {
        if ($this->status === 'pending') {
            $this->update(['status' => 'cancelled']);
        }
    }
}