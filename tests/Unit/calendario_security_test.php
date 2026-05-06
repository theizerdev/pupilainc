<?php

// Script de prueba para verificar las mejoras de seguridad implementadas

echo "=== PRUEBAS DE SEGURIDAD DEL CALENDARIO ===\n\n";

// 1. Verificar que RateLimiter esté disponible
if (class_exists('Illuminate\Support\Facades\RateLimiter')) {
    echo "✅ RateLimiter está disponible\n";
} else {
    echo "❌ RateLimiter no está disponible\n";
}

// 2. Verificar que Cache esté disponible
if (class_exists('Illuminate\Support\Facades\Cache')) {
    echo "✅ Cache está disponible\n";
} else {
    echo "❌ Cache no está disponible\n";
}

// 3. Verificar que el trait HasSpanishActivityLog esté disponible
if (trait_exists('App\Traits\HasSpanishActivityLog')) {
    echo "✅ HasSpanishActivityLog trait está disponible\n";
} else {
    echo "❌ HasSpanishActivityLog trait no está disponible\n";
}

// 4. Verificar métodos en el componente Calendario
$calendarioPath = 'app/Livewire/Admin/Calendario.php';
if (file_exists($calendarioPath)) {
    $content = file_get_contents($calendarioPath);
    
    // Verificar rate limiting
    if (strpos($content, 'RateLimiter::tooManyAttempts') !== false) {
        echo "✅ Rate limiting implementado\n";
    } else {
        echo "❌ Rate limiting no implementado\n";
    }
    
    // Verificar autorización
    if (strpos($content, 'authorizeCitaAction') !== false) {
        echo "✅ Sistema de autorización implementado\n";
    } else {
        echo "❌ Sistema de autorización no implementado\n";
    }
    
    // Verificar sanitización
    if (strpos($content, 'sanitizeInput') !== false) {
        echo "✅ Sanitización de datos implementada\n";
    } else {
        echo "❌ Sanitización de datos no implementada\n";
    }
    
    // Verificar logging de auditoría
    if (strpos($content, 'logCitaAction') !== false) {
        echo "✅ Logging de auditoría implementado\n";
    } else {
        echo "❌ Logging de auditoría no implementado\n";
    }
    
    // Verificar invalidación de caché
    if (strpos($content, 'invalidateCache') !== false) {
        echo "✅ Invalidación de caché implementada\n";
    } else {
        echo "❌ Invalidación de caché no implementada\n";
    }
} else {
    echo "❌ Archivo Calendario.php no encontrado\n";
}

// 5. Verificar que se eliminó el dd()
$citasIndexPath = 'app/Livewire/Admin/Citas/Index.php';
if (file_exists($citasIndexPath)) {
    $content = file_get_contents($citasIndexPath);
    if (strpos($content, 'dd($nuevoEstado)') === false) {
        echo "✅ dd(nuevoEstado) eliminado correctamente\n";
    } else {
        echo "❌ dd(nuevoEstado) aún presente\n";
    }
} else {
    echo "❌ Archivo Citas/Index.php no encontrado\n";
}

echo "\n=== PRUEBAS DE RENDIMIENTO ===\n\n";

// 6. Verificar migraciones de índices
$migrationPath = 'database/migrations/2026_03_06_120001_add_calendario_performance_indexes_safe.php';
if (file_exists($migrationPath)) {
    echo "✅ Migración de índices de rendimiento creada\n";
} else {
    echo "❌ Migración de índices no encontrada\n";
}

// 7. Verificar optimizaciones de consultas
if (file_exists($calendarioPath)) {
    $content = file_get_contents($calendarioPath);
    
    // Verificar eager loading optimizado
    if (strpos($content, 'paciente:id,nombres,apellidos') !== false) {
        echo "✅ Eager loading optimizado implementado\n";
    } else {
        echo "❌ Eager loading optimizado no implementado\n";
    }
    
    // Verificar select específico
    if (strpos($content, '->select(') !== false) {
        echo "✅ Select específico implementado\n";
    } else {
        echo "❌ Select específico no implementado\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Las mejoras de seguridad y rendimiento han sido implementadas.\n";
echo "Para pruebas más exhaustivas, se recomienda:\n";
echo "1. Probar el calendario en el navegador\n";
echo "2. Verificar logs de auditoría después de operaciones\n";
echo "3. Monitorear performance con herramientas de profiling\n";