<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoImagen extends Model
{
    use HasFactory;

    protected $table = 'producto_imagenes';

    protected $fillable = [
        'producto_id', 'imagen', 'titulo', 'orden', 'principal',
    ];

    protected $casts = [
        'principal' => 'boolean',
        'orden'     => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function getUrlAttribute()
    {
        return asset('storage/' . $this->imagen);
    }
}
