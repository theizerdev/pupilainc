<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

class PacienteModalValidationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $empresa = Empresa::factory()->create();
        $sucursal = Sucursal::factory()->create(['empresa_id' => $empresa->id]);
        
        $this->user = User::factory()->create([
            'empresa_id' => $empresa->id,
            'sucursal_id' => $sucursal->id,
        ]);
    }

    /** @test */
    public function test_validacion_fecha_nacimiento_exitosa()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'fecha_nacimiento' => '1990-01-15',
                'documento_identidad' => '12345678',
                'telefono' => '04121234567',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => true,
                            'message' => 'Paciente creado exitosamente.'
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_fecha_nacimiento_vacia_muestra_error()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'fecha_nacimiento' => '',
                'documento_identidad' => '12345678',
                'telefono' => '04121234567',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'fecha_nacimiento' => 'La fecha de nacimiento no es válida.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_fecha_nacimiento_futura_muestra_error()
    {
        $this->actingAs($this->user);
        
        $fechaFutura = now()->addDay()->format('Y-m-d');
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'fecha_nacimiento' => $fechaFutura,
                'documento_identidad' => '12345678',
                'telefono' => '04121234567',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'fecha_nacimiento' => 'La fecha de nacimiento no puede ser futura.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_fecha_nacimiento_muy_antigua_muestra_error()
    {
        $this->actingAs($this->user);
        
        $fechaAntigua = now()->subYears(121)->format('Y-m-d');
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'fecha_nacimiento' => $fechaAntigua,
                'documento_identidad' => '12345678',
                'telefono' => '04121234567',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'fecha_nacimiento' => 'La fecha de nacimiento no puede ser mayor a 120 años.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_campos_obligatorios_nombres_apellidos()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => '',
                'apellidos' => '',
                'fecha_nacimiento' => '1990-01-15',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'nombres' => 'Los nombres son obligatorios.',
                                'apellidos' => 'Los apellidos son obligatorios.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_formato_nombres_apellidos()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => 'Juan123',
                'apellidos' => 'Pérez!@#',
                'fecha_nacimiento' => '1990-01-15',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'nombres' => 'Los nombres solo pueden contener letras y espacios.',
                                'apellidos' => 'Los apellidos solo pueden contener letras y espacios.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_longitud_campos()
    {
        $this->actingAs($this->user);
        
        $nombreLargo = str_repeat('A', 101);
        $apellidoLargo = str_repeat('B', 101);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => $nombreLargo,
                'apellidos' => $apellidoLargo,
                'fecha_nacimiento' => '1990-01-15',
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'nombres' => 'Los nombres no pueden exceder 100 caracteres.',
                                'apellidos' => 'Los apellidos no pueden exceder 100 caracteres.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_validacion_documento_telefono_formato()
    {
        $this->actingAs($this->user);
        
        $response = $this->post('/livewire/message/admin.calendario', [
            'action' => 'crearPacienteRapido',
            'data' => [
                'nombres' => 'Juan',
                'apellidos' => 'Pérez',
                'fecha_nacimiento' => '1990-01-15',
                'documento_identidad' => '12345', // Muy corto
                'telefono' => '123456', // Muy corto
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Por favor, corrija los errores en el formulario.',
                            'errors' => [
                                'documento_identidad' => 'El documento debe tener entre 6 y 12 dígitos.',
                                'telefono' => 'El teléfono debe tener entre 7 y 15 dígitos.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }

    /** @test */
    public function test_rate_limiting_funciona_correctamente()
    {
        $this->actingAs($this->user);
        
        // Intentar crear más de 15 pacientes rápidamente
        for ($i = 0; $i < 16; $i++) {
            $response = $this->post('/livewire/message/admin.calendario', [
                'action' => 'crearPacienteRapido',
                'data' => [
                    'nombres' => 'Juan',
                    'apellidos' => 'Pérez',
                    'fecha_nacimiento' => '1990-01-15',
                ]
            ]);
        }

        // El último intento debe ser bloqueado por rate limiting
        $response->assertStatus(200);
        $response->assertJson([
            'effects' => [
                'dispatches' => [
                    [
                        'name' => 'paciente-creado',
                        'params' => [
                            'success' => false,
                            'message' => 'Demasiados intentos de crear pacientes. Por favor, espere un momento.',
                            'errors' => [
                                'general' => 'Demasiados intentos. Por favor, espere.'
                            ]
                        ]
                    ]
                ]
            ]
        ]);
    }
}