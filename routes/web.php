<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CitaConfirmationController;
use App\Livewire\Dashboard;
use App\Livewire\Auth\TwoFactorLogin;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;


Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['es', 'en'])) {
        Session::put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

Route::get('/', function () {
   if (\Auth::check() && \Auth::user()->id == 1) {
      return redirect()->to('superadmin/dashboard');
   } elseif (\Auth::check()) {
      // Verificar si es médico
      $medico = \App\Models\Medico::where('user_id', \Auth::user()->id)->first();
      if ($medico) {
         return redirect()->to('admin/doctor/' . $medico->id . '/dashboard');
      }
      // Verificar si es administrador
      if (\Auth::user()->hasRole('Administrador')) {
         return redirect()->to('admin/dashboard');
      }
      // Verificar si es recepcion
      if (\Auth::user()->hasRole('Recepcion')) {
         return redirect()->route('admin.recepcion.dashboard');
      }
   }
   return redirect()->to('admin/dashboard');
})->middleware('auth');

// Rutas de autenticación con Livewire
Route::middleware('guest')->group(function () {
    Route::get('login', \App\Livewire\Auth\Login::class)->name('login');
    Route::get('register', \App\Livewire\Auth\Register::class)->name('register');
    Route::get('password/reset', \App\Livewire\Auth\ForgotPassword::class)->name('password.request');
    Route::get('password/reset/{token}', \App\Livewire\Auth\ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', \App\Livewire\Auth\VerifyCode::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', function (string $id, string $hash) {
        // Esta ruta se usa para la verificación real del correo electrónico
        // Pero como estamos usando Livewire, simplemente redirigimos al componente Verify
        return redirect()->route('verification.notice');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
});

// Ruta de logout
Route::post('logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');




// Ruta para verificación 2FA
Route::get('/two-factor-login', \App\Livewire\Auth\TwoFactorLogin::class)->name('two-factor.login');

// Super Admin routes
Route::group(['prefix' => 'superadmin', 'as' => 'superadmin.', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/dashboard', SuperAdminDashboard::class)->name('dashboard');
});

// Admin routes
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
   Route::middleware(['auth', 'verified'])->group(function () {
        Route::get('/dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
        require __DIR__.'/admin.php';
   });
});

// Doctor routes
Route::group(['prefix' => 'admin/doctor', 'as' => 'doctor.', 'middleware' => ['auth', 'verified']], function () {
    Route::get('/{id}/dashboard', \App\Livewire\Doctor\Dashboard::class)->name('dashboard');
});

Route::get('/admin/template-customization', \App\Livewire\Admin\TemplateCustomization\Index::class)
    ->middleware(['auth'])
    ->name('admin.template-customization');

// Rutas para confirmación de citas
Route::prefix('citas')->group(function () {
    Route::get('/confirmar', [CitaConfirmationController::class, 'confirmar'])
         ->name('citas.confirmar');

    Route::get('/cancelar', [CitaConfirmationController::class, 'cancelar'])
         ->name('citas.cancelar');
});

// Webhook para respuestas de WhatsApp
Route::post('/webhook/whatsapp/confirmations', [CitaConfirmationController::class, 'processWhatsAppResponse'])
     ->name('webhook.whatsapp.confirmations');

// Rutas públicas para pre-consulta (sin autenticación)
Route::prefix('preconsulta')->name('preconsulta.')->group(function () {
    Route::get('/formulario/{token}', \App\Livewire\PreconsultaForm::class)->name('formulario');
});

// Test WhatsApp API
Route::get('/test-whatsapp', function () {
    try {
        $health = Http::timeout(5)->get('http://localhost:3001/health');
        $status = Http::withHeaders(['X-API-Key' => 'test-api-key-vargas-centro'])->timeout(10)->get('http://localhost:3001/api/whatsapp/status');

        return response()->json([
            'health' => ['success' => $health->successful(), 'status' => $health->status(), 'body' => $health->json()],
            'status' => ['success' => $status->successful(), 'status' => $status->status(), 'body' => $status->json()]
        ]);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});


// Ruta de prueba para configuración regional
Route::get('/test/regional-configuration', \App\Livewire\TestRegionalConfiguration::class)
    ->middleware(['auth'])
    ->name('test.regional-configuration');
