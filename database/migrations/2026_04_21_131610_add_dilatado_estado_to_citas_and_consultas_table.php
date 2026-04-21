<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No necesitamos modificar la estructura de las tablas
        // Solo documentamos que el estado 'dilatado' es válido
        // Los estados se manejan como strings en las columnas existentes
    }

    public function down(): void
    {
        // No hay cambios que revertir
    }
};
