<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';

use App\Models\Consulta;

echo "=== VERIFICACIÓN DE ESPECIALIDADES EN CONSULTAS ===\n\n";

// Verificar consultas en sala de espera
echo "1. CONSULTAS EN SALA DE ESPERA:\n";
echo "----------------------------------------\n";
$consultasSalaEspera = Consulta::where('estado', 'sala_espera')
    ->with(['paciente', 'medico', 'especialidad'])
    ->limit(10)
    ->get();

foreach($consultasSalaEspera as $consulta) {
    echo "ID: {$consulta->id} | ";
    echo "Paciente: {$consulta->paciente->nombre_completo} | ";
    echo "Médico: {$consulta->medico->nombre_completo} | ";
    echo "Especialidad: " . ($consulta->especialidad ? $consulta->especialidad->nombre : 'SIN ESPECIALIDAD') . " | ";
    echo "Especialidad ID: " . ($consulta->especialidad_id ?? 'NULL') . "\n";
}

echo "\n2. CONSULTAS EN ENFERMERÍA:\n";
echo "----------------------------------------\n";
$consultasEnfermeria = Consulta::where('estado', 'en_enfermeria')
    ->with(['paciente', 'medico', 'especialidad'])
    ->limit(10)
    ->get();

foreach($consultasEnfermeria as $consulta) {
    echo "ID: {$consulta->id} | ";
    echo "Paciente: {$consulta->paciente->nombre_completo} | ";
    echo "Médico: {$consulta->medico->nombre_completo} | ";
    echo "Especialidad: " . ($consulta->especialidad ? $consulta->especialidad->nombre : 'SIN ESPECIALIDAD') . " | ";
    echo "Especialidad ID: " . ($consulta->especialidad_id ?? 'NULL') . "\n";
}

echo "\n3. RESUMEN ESTADISTICAS:\n";
echo "----------------------------------------\n";

// Total de consultas por estado
$totalSalaEspera = Consulta::where('estado', 'sala_espera')->count();
$totalEnfermeria = Consulta::where('estado', 'en_enfermeria')->count();

// Consultas sin especialidad
$sinEspecialidadSala = Consulta::where('estado', 'sala_espera')
    ->whereNull('especialidad_id')
    ->count();
    
$sinEspecialidadEnfermeria = Consulta::where('estado', 'en_enfermeria')
    ->whereNull('especialidad_id')
    ->count();

echo "Total en sala de espera: {$totalSalaEspera}\n";
echo "Sin especialidad en sala de espera: {$sinEspecialidadSala}\n";
echo "Con especialidad en sala de espera: " . ($totalSalaEspera - $sinEspecialidadSala) . "\n\n";

echo "Total en enfermería: {$totalEnfermeria}\n";
echo "Sin especialidad en enfermería: {$sinEspecialidadEnfermeria}\n";
echo "Con especialidad en enfermería: " . ($totalEnfermeria - $sinEspecialidadEnfermeria) . "\n\n";

// Verificar el flujo de estados
echo "4. FLUJO DE ESTADOS:\n";
echo "----------------------------------------\n";

$ejemploConsulta = Consulta::where('estado', 'en_enfermeria')
    ->whereNull('especialidad_id')
    ->first();

if ($ejemploConsulta) {
    echo "Ejemplo de consulta sin especialidad:\n";
    echo "ID: {$ejemploConsulta->id}\n";
    echo "Estado actual: {$ejemploConsulta->estado}\n";
    echo "Médico asignado: {$ejemploConsulta->medico->nombre_completo}\n";
    echo "¿El médico tiene especialidad? " . ($ejemploConsulta->medico->especialidad ? 'SÍ' : 'NO') . "\n";
    if ($ejemploConsulta->medico->especialidad) {
        echo "Especialidad del médico: {$ejemploConsulta->medico->especialidad->nombre}\n";
    }
}

echo "\n=== FIN DEL ANÁLISIS ===\n";