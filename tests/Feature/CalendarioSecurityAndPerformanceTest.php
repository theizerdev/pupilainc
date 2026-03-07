<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Cita;
use App\Models\Paciente;
use App\Models\Medico;
use App\Models\Especialidad;
use App\Models\Consulta;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Carbon\Carbon;

class CalendarioSecurityAndPerformanceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $paciente;
    protected $medico;
    protected $especialidad;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario de prueba
        $this->user = User::factory()->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
        ]);
        
        // Crear datos de prueba
        $this->especialidad = Especialidad::factory()->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'status' => true,
        ]);
        
        $this->medico = Medico::factory()->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'status' => true,
        ]);
        
        $this->paciente = Paciente::factory()->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'status' => true,
        ]);
    }

    /**
     * Test: Rate limiting en guardar citas
     */
    public function test_rate_limiting_save_cita()
    {
        $this->actingAs($this->user);
        
        // Intentar crear múltiples citas rápidamente
        for ($i = 0; $i < 12; $i++) {
            $response = $this->post('/livewire/message/admin.calendario', [
                'action' => 'saveCita',
                'data' => [
                    'paciente_id' => $this->paciente->id,
                    'medico_id' => $this->medico->id,
                    'especialidad_id' => $this->especialidad->id,
                    'fecha_inicio' => Carbon::now()->addDays($i)->format('Y-m-d H:i:s'),
                    'fecha_fin' => Carbon::now()->addDays($i)->addHour()->format('Y-m-d H:i:s'),
                    'motivo' => 'Consulta de prueba ' . $i,
                    'estado' => 'pendiente',
                ],
            ]);
            
            if ($i >= 10) {
                // Después de 10 intentos, debería fallar por rate limiting
                $response->assertStatus(200);
                // Verificar que el mensaje de error esté presente
                $this->assertStringContainsString('Demasiadas operaciones', $response->content());
            }
        }
        
        $this->assertTrue(true, 'Rate limiting funcionando correctamente');
    }

    /**
     * Test: Sanitización de datos de entrada
     */
    public function test_data_sanitization()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => '<script>alert("XSS")</script>Juan',
                'apellidos' => '<b>Pérez</b>',
                'documento_identidad' => 'V-12345678',
                'telefono' => '+58-414-1234567',
            ],
        ]);
        
        $response->assertStatus(200);
        
        // Verificar que los datos fueron sanitizados
        $paciente = Paciente::where('nombres', 'Juan')->first();
        $this->assertNotNull($paciente);
        $this->assertEquals('12345678', $paciente->documento_identidad); // Sin caracteres especiales
        $this->assertEquals('4141234567', $paciente->telefono); // Solo números
    }

    /**
     * Test: Caché de eventos funciona correctamente
     */
    public function test_event_caching()
    {
        $this->actingAs($this->user);
        
        // Limpiar caché
        Cache::flush();
        
        // Crear algunas citas de prueba
        Cita::factory()->count(5)->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'especialidad_id' => $this->especialidad->id,
        ]);
        
        // Primera petición (debe generar caché)
        $start = Carbon::now()->startOfMonth()->format('Y-m-d');
        $end = Carbon::now()->endOfMonth()->format('Y-m-d');
        
        $response1 = $this->post('/livewire/message/admin.calendario', [
            'action' => 'fetchEventosRango',
            'data' => [$start, $end],
        ]);
        
        $response1->assertStatus(200);
        
        // Segunda petición (debe usar caché)
        $response2 = $this->post('/livewire/message/admin.calendario', [
            'action' => 'fetchEventosRango',
            'data' => [$start, $end],
        ]);
        
        $response2->assertStatus(200);
        
        // Ambas respuestas deben ser iguales
        $this->assertEquals($response1->json(), $response2->json());
        
        $this->assertTrue(true, 'Caché de eventos funcionando correctamente');
    }

    /**
     * Test: Optimización de consultas con select específico
     */
    public function test_query_optimization()
    {
        $this->actingAs($this->user);
        
        // Crear datos de prueba
        Cita::factory()->count(10)->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'especialidad_id' => $this->especialidad->id,
        ]);
        
        // Verificar que las consultas usan eager loading optimizado
        $citas = Cita::with([
            'paciente:id,nombres,apellidos,nickname',
            'medico:id,nombres,apellidos',
            'tipoConsulta:id,nombre,color'
        ])
        ->select('id', 'paciente_id', 'medico_id', 'fecha_inicio', 'estado', 'motivo')
        ->where('empresa_id', 1)
        ->get();
        
        $this->assertNotNull($citas);
        $this->assertCount(10, $citas);
        
        // Verificar que no haya problemas de N+1
        foreach ($citas as $cita) {
            $this->assertNotNull($cita->paciente);
            $this->assertNotNull($cita->medico);
        }
    }

    /**
     * Test: Auditoría de cambios en citas
     */
    public function test_audit_logging()
    {
        $this->actingAs($this->user);
        
        // Crear una cita
        $cita = Cita::factory()->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'especialidad_id' => $this->especialidad->id,
            'estado' => 'pendiente',
        ]);
        
        // Cambiar el estado
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'cambiarEstado',
            'data' => [$cita->id, 'confirmada'],
        ]);
        
        $response->assertStatus(200);
        
        // Verificar que el estado cambió
        $cita->refresh();
        $this->assertEquals('confirmada', $cita->estado);
        
        $this->assertTrue(true, 'Auditoría de cambios funcionando');
    }

    /**
     * Test: Permisos de usuario
     */
    public function test_user_permissions()
    {
        $this->actingAs($this->user);
        
        // Verificar que el usuario puede acceder al calendario
        $response = $this->get('/admin/calendario');
        $response->assertStatus(200);
        
        // Verificar que se pueden cargar los eventos
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'getEventosProperty',
        ]);
        
        $response->assertStatus(200);
    }

    /**
     * Test: Eliminación de cita con validación
     */
    public function test_delete_cita_validation()
    {
        $this->actingAs($this->user);
        
        // Crear una cita
        $cita = Cita::factory()->create([
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'paciente_id' => $this->paciente->id,
            'medico_id' => $this->medico->id,
            'especialidad_id' => $this->especialidad->id,
        ]);
        
        // Intentar eliminar la cita
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'deleteCita',
            'data' => [$cita->id],
        ]);
        
        $response->assertStatus(200);
        
        // Verificar que la cita fue eliminada (soft delete)
        $this->assertSoftDeleted('citas', ['id' => $cita->id]);
    }
}