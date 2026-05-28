<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessagingProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'config_schema',
        'description',
        'is_active',
    ];

    protected $casts = [
        'config_schema' => 'array',
        'is_active' => 'boolean',
    ];

    public function connections(): HasMany
    {
        return $this->hasMany(MessagingConnection::class, 'provider_id');
    }

    public function getConfigSchemaAttribute($value): array
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setConfigSchemaAttribute($value): void
    {
        $this->attributes['config_schema'] = is_array($value) ? json_encode($value) : $value;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }
}