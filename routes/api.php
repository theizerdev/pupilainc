<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Mascotas API Routes
Route::prefix('mascotas')->group(function () {
    Route::post('/', function (Request $request) {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'especie_id' => 'required|exists:especies,id',
                'raza_id' => 'nullable|exists:razas,id',
                'sexo' => 'required|in:macho,hembra',
                'fecha_nacimiento' => 'nullable|date',
                'peso_actual_kg' => 'nullable|numeric|min:0',
                'propietario' => 'required|array',
                'propietario.nombres' => 'required|string|max:255',
                'propietario.apellidos' => 'required|string|max:255',
                'propietario.telefono' => 'required|string|max:20',
                'propietario.telefono_alternativo' => 'nullable|string|max:20',
                'propietario.email' => 'nullable|email|max:255',
            ]);

            // Crear o buscar propietario
            $propietarioData = $validated['propietario'];

            $propietario = \App\Models\Propietario::firstOrCreate(
                [
                    'telefono' => $propietarioData['telefono'],
                    'empresa_id' => 1,
                    'sucursal_id' => 1,
                ],
                [
                    'nombres' => $propietarioData['nombres'],
                    'apellidos' => $propietarioData['apellidos'],
                    'telefono_alternativo' => $propietarioData['telefono_alternativo'] ?? null,
                    'email' => $propietarioData['email'] ?? null,
                    'empresa_id' => 1,
                    'sucursal_id' => 1,
                ]
            );

            // Crear mascota
            $mascota = \App\Models\Mascota::create([
                'nombre' => $validated['nombre'],
                'especie_id' => $validated['especie_id'],
                'raza_id' => $validated['raza_id'] ?? null,
                'sexo' => $validated['sexo'],
                'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
                'peso_actual_kg' => $validated['peso_actual_kg'] ?? null,
                'propietario_id' => $propietario->id,
                'empresa_id' => 1,
                'sucursal_id' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Mascota creada exitosamente',
                'mascota_id' => $mascota->id,
                'propietario_id' => $propietario->id,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Error creando mascota via API:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
            ], 500);
        }
    });
});



// WhatsApp API Routes
Route::prefix('whatsapp')->group(function () {
    Route::get('/status', [WhatsAppController::class, 'status']);
    Route::get('/qr-code', [WhatsAppController::class, 'qrCode']);
    Route::post('/send-message', [WhatsAppController::class, 'sendMessage']);
    Route::get('/messages', [WhatsAppController::class, 'messages']);
    Route::post('/connect', [WhatsAppController::class, 'connect']);
    Route::post('/disconnect', [WhatsAppController::class, 'disconnect']);
    Route::post('/webhook', [WhatsAppController::class, 'webhook']);
});


