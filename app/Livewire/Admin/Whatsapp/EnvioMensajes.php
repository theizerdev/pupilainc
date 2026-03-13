<?php

namespace App\Livewire\Admin\Whatsapp;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Models\Paciente;
use App\Models\Medico;

class EnvioMensajes extends Component
{
    public $sendMode = 'individual';
    
    public $to = '';
    public $message = '';
    public $sending = false;
    public $success = null;
    public $error = null;
    public $whatsappApiKey = null;
    public $companyId = null;
    public $recentMessages = [];
    public $charCount = 0;
    public $selectedTemplate = null;
    public $empresaNombre = null;
    public $whatsappPhone = null;

    public $targetGroup = 'pacientes';
    public $filterMedico = '';
    public $filterEspecialidad = '';
    public $selectedContacts = [];
    public $selectAll = false;
    public $contacts = [];
    public $sendProgress = 0;
    public $sendTotal = 0;
    public $sendResults = [];
    public $isSendingBulk = false;

    public $templates = [
        ['id' => 'saludo', 'name' => 'Saludo', 'message' => '¡Hola! Gracias por comunicarte con nuestro centro médico. ¿En qué podemos ayudarte?'],
        ['id' => 'confirmacion_cita', 'name' => 'Confirmación de Cita', 'message' => 'Le confirmamos que su cita médica ha sido agendada correctamente.'],
        ['id' => 'recordatorio_cita', 'name' => 'Recordatorio de Cita', 'message' => 'Le recordamos amablemente su cita médica programada para mañana.'],
        ['id' => 'resultados', 'name' => 'Resultados Disponibles', 'message' => 'Sus resultados médicos ya están disponibles. Por favor comuníquese con nosotros.'],
        ['id' => 'seguimiento', 'name' => 'Seguimiento Médico', 'message' => 'Le escribimos para hacer seguimiento de su tratamiento médico.'],
    ];

    protected function rules()
    {
        if ($this->sendMode === 'individual') {
            return [
                'to' => ['required', 'string', 'min:10', 'max:15', 'regex:/^[0-9]+$/'],
                'message' => ['required', 'string', 'min:1', 'max:1000']
            ];
        }
        return [
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'selectedContacts' => ['required', 'array', 'min:1']
        ];
    }

    protected $messages = [
        'to.required' => 'El número de teléfono es obligatorio.',
        'to.min' => 'El número debe tener al menos 10 dígitos.',
        'to.max' => 'El número no puede tener más de 15 dígitos.',
        'to.regex' => 'El número solo puede contener dígitos.',
        'message.required' => 'El mensaje es obligatorio.',
        'message.min' => 'El mensaje no puede estar vacío.',
        'message.max' => 'El mensaje no puede exceder 1000 caracteres.',
        'selectedContacts.required' => 'Debe seleccionar al menos un destinatario.',
        'selectedContacts.min' => 'Debe seleccionar al menos un destinatario.'
    ];

    public function mount()
    {
        $this->initializeWhatsApp();
        $this->loadRecentMessages();
    }

    /**
     * Inicializa la configuración de WhatsApp para la empresa del usuario
     */
    public function initializeWhatsApp()
    {
        $empresa = auth()->user()->empresa ?? null;
        
        if ($empresa) {
            $this->companyId = $empresa->id;
            $this->whatsappApiKey = $empresa->whatsapp_api_key;
            $this->empresaNombre = $empresa->razon_social;
            $this->whatsappPhone = $empresa->whatsapp_phone;
            
            
        } else {
            $this->error = 'Usuario sin empresa asignada.';
            \Log::error('User without assigned company', [
                'user_id' => auth()->id()
            ]);
        }
    }

    /**
     * Obtiene los headers necesarios para la API de WhatsApp
     */
    private function getApiHeaders(): array
    {
        $headers = [
            'X-API-Key' => auth()->user()->empresa->whatsapp_api_key,
            'X-Company-Id' => (string) $this->companyId,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
        
        \Log::debug('API Headers prepared', [
            'authorization_present' => !empty($this->whatsappApiKey),
            'company_id' => $this->companyId,
            'headers_keys' => array_keys($headers)
        ]);
        
        return $headers;
    }

    /**
     * Valida y formatea el número de teléfono
     */
    private function formatPhoneNumber($phone): string
    {
        // Eliminar caracteres no numéricos
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // Agregar código de país si no lo tiene (asumiendo Bolivia +591)
        if (strlen($cleanPhone) === 8) {
            $cleanPhone = '591' . $cleanPhone;
        } elseif (strlen($cleanPhone) === 9 && substr($cleanPhone, 0, 1) === '7') {
            $cleanPhone = '591' . $cleanPhone;
        }
        
        return $cleanPhone;
    }

    public function updatedMessage($value)
    {
        $this->charCount = strlen($value);
    }

    public function updatedSendMode()
    {
        $this->resetValidation();
        $this->clearMessages();
        if ($this->sendMode === 'grupal') {
            $this->loadContacts();
        }
    }

    public function updatedTargetGroup()
    {
        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->loadContacts();
    }

    public function updatedFilterMedico()
    {
        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->loadContacts();
    }

    public function updatedFilterEspecialidad()
    {
        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->loadContacts();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedContacts = collect($this->contacts)->pluck('id')->toArray();
        } else {
            $this->selectedContacts = [];
        }
    }

    public function loadContacts()
    {
        if ($this->targetGroup === 'pacientes') {
            $this->loadPacientes();
        } else {
            $this->loadMedicos();
        }
    }

    private function loadPacientes()
    {
        $query = Paciente::query()
            ->where('status', true)
            ->whereNotNull('telefono');

        if ($this->filterMedico) {
            $query->whereHas('citas', function($q) {
                $q->where('medico_id', $this->filterMedico);
            });
        }

        $this->contacts = $query->select([
            'id', 'nombres', 'apellidos', 'telefono', 'email', 'fecha_nacimiento'
        ])
        ->orderBy('apellidos')
        ->limit(100)
        ->get()
        ->map(function ($paciente) {
            $edad = $paciente->fecha_nacimiento ? 
                \Carbon\Carbon::parse($paciente->fecha_nacimiento)->age : 0;
            
            return [
                'id' => $paciente->id,
                'nombre' => $paciente->nombres . ' ' . $paciente->apellidos,
                'telefono' => $paciente->telefono,
                'email' => $paciente->email,
                'edad' => $edad,
                'tipo' => 'paciente',
                'tiene_telefono' => !empty($paciente->telefono)
            ];
        })
        ->toArray();
    }

    private function loadMedicos()
    {
        $query = Medico::query()
            ->where('status', true)
            ->whereNotNull('telefono');

        if ($this->filterEspecialidad) {
            $query->where('especialidad_id', $this->filterEspecialidad);
        }

        $this->contacts = $query->select([
            'id', 'nombres', 'apellidos', 'telefono', 'email', 'especialidad_id'
        ])
        ->orderBy('apellidos')
        ->limit(100)
        ->get()
        ->map(function ($medico) {
            return [
                'id' => $medico->id,
                'nombre' => 'Dr. ' . $medico->nombres . ' ' . $medico->apellidos,
                'telefono' => $medico->telefono,
                'email' => $medico->email,
                'especialidad' => $medico->especialidad->nombre ?? 'General',
                'tipo' => 'medico',
                'tiene_telefono' => !empty($medico->telefono)
            ];
        })
        ->toArray();
    }

    public function useTemplate($templateId)
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);
        if ($template) {
            $this->message = $template['message'];
            $this->charCount = strlen($this->message);
            $this->selectedTemplate = $templateId;
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Plantilla "' . $template['name'] . '" aplicada'
            ]);
        }
    }

    public function sendMessage()
    {
        if ($this->sendMode === 'grupal') {
            $this->sendBulkMessages();
            return;
        }

        $this->validate();

        if (!$this->whatsappApiKey) {
            $this->error = 'No se ha configurado la API Key de WhatsApp para esta empresa.';
            return;
        }

        $this->sending = true;
        $this->success = null;
        $this->error = null;

        try {
            // Formatear el número de teléfono
            $formattedPhone = $this->formatPhoneNumber($this->to);
            
            // Validar formato final
            if (strlen($formattedPhone) < 10 || strlen($formattedPhone) > 15) {
                throw new \Exception('Número de teléfono inválido después del formateo');
            }

            $baseUrl = config('whatsapp.api_url', 'http://localhost:3001');
            
            \Log::info('Attempting to send WhatsApp message', [
                'to' => $formattedPhone,
                'message_length' => strlen($this->message),
                'company_id' => $this->companyId,
                'base_url' => $baseUrl
            ]);

            // Primero verificar conexión básica
            $connectionCheck = $this->testBasicConnection($baseUrl);
            if (!$connectionCheck['success']) {
                $this->error = $connectionCheck['message'];
                $this->sending = false;
                return;
            }

            // Luego verificar autenticación
            $authCheck = $this->testAuthentication($baseUrl);
            if (!$authCheck['success']) {
                $this->error = $authCheck['message'];
                $this->sending = false;
                return;
            }

            // Si ambas verificaciones pasan, proceder con el envío
            // CORRECCIÓN: Usar el endpoint correcto /api/whatsapp/send en lugar de /api/messages/send
            $response = Http::timeout(15)
                ->withHeaders($this->getApiHeaders())
                ->post($baseUrl . '/api/whatsapp/send', [
                    'to' => $formattedPhone,
                    'message' => $this->message,
                    'type' => 'text'
                ]);

            \Log::info('WhatsApp API Response', [
                'status' => $response->status(),
                'successful' => $response->successful(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $this->success = 'Mensaje enviado exitosamente a ' . $formattedPhone;
                $this->reset(['to', 'charCount', 'selectedTemplate']);
                $this->loadRecentMessages();
                $this->dispatch('refreshWhatsapp');
                $this->dispatch('messageSent');
                
                \Log::info('WhatsApp message sent successfully', [
                    'to' => $formattedPhone,
                    'response_data' => $responseData
                ]);
            } else {
                $errorData = $response->json();
                $errorMessage = $errorData['message'] ?? $errorData['error'] ?? 'Error desconocido';
                
                // Manejo específico para errores de autenticación
                if ($response->status() === 401) {
                    $this->error = 'Token de autenticación inválido. Por favor verifique la configuración de WhatsApp en la empresa.';
                    \Log::error('WhatsApp authentication failed - Invalid token', [
                        'company_id' => $this->companyId,
                        'response_status' => $response->status(),
                        'error_details' => $errorData
                    ]);
                } elseif ($response->status() === 403) {
                    $this->error = 'Acceso denegado. El token no tiene permisos suficientes.';
                    \Log::error('WhatsApp access denied - Insufficient permissions', [
                        'company_id' => $this->companyId,
                        'response_status' => $response->status(),
                        'error_details' => $errorData
                    ]);
                } else {
                    $this->error = 'Error al enviar el mensaje: ' . $errorMessage;
                    \Log::error('WhatsApp message sending failed', [
                        'company_id' => $this->companyId,
                        'response_status' => $response->status(),
                        'error_message' => $errorMessage,
                        'error_details' => $errorData
                    ]);
                }
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->error = 'No se puede conectar al servidor de WhatsApp. Verifique que el servicio esté activo.';
            \Log::error('WhatsApp connection failed', [
                'exception' => $e->getMessage(),
                'company_id' => $this->companyId
            ]);
        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            \Log::error('Unexpected error in WhatsApp message sending', [
                'exception' => $e->getMessage(),
                'company_id' => $this->companyId
            ]);
        }

        $this->sending = false;
    }

    /**
     * Verifica la conexión básica con el servidor WhatsApp
     */
    private function testBasicConnection($baseUrl)
    {
        try {
            \Log::info('Testing basic connection to WhatsApp server', [
                'base_url' => $baseUrl
            ]);
            
            $response = Http::timeout(5)->get($baseUrl . '/health');
            
            if ($response->successful()) {
                \Log::info('Basic connection test successful', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return ['success' => true];
            } else {
                \Log::warning('Basic connection test failed', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Servidor WhatsApp respondió con error: ' . $response->status() . '. Verifique que el servicio esté correctamente configurado.'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Basic connection test failed with exception', [
                'exception' => $e->getMessage(),
                'exception_class' => get_class($e)
            ]);
            
            // Diagnóstico específico para diferentes tipos de errores
            if (strpos($e->getMessage(), 'cURL error 7') !== false) {
                return [
                    'success' => false,
                    'message' => 'No se puede conectar al servidor de WhatsApp. Posibles causas: servidor apagado, firewall bloqueando, o URL incorrecta.'
                ];
            } elseif (strpos($e->getMessage(), 'cURL error 28') !== false) {
                return [
                    'success' => false,
                    'message' => 'Tiempo de espera agotado al conectar con WhatsApp. El servidor puede estar sobrecargado o inaccesible.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error de conexión con WhatsApp: ' . $e->getMessage()
                ];
            }
        }
    }

    /**
     * Verifica la autenticación con el servidor WhatsApp
     */
    private function testAuthentication($baseUrl)
    {
        try {
            \Log::info('Testing authentication with WhatsApp server', [
                'base_url' => $baseUrl,
                'company_id' => $this->companyId
            ]);
            
            $response = Http::timeout(10)
                ->withHeaders($this->getApiHeaders())
                ->get($baseUrl . '/api/whatsapp/status');

            if ($response->successful()) {
                \Log::info('Authentication test successful', [
                    'response_status' => $response->status(),
                    'user_data' => $response->json()
                ]);
                return ['success' => true];
            } elseif ($response->status() === 401) {
                \Log::error('Authentication test failed - Invalid token', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Token de autenticación inválido. Verifique que la API Key en la configuración de la empresa sea correcta y no haya expirado.'
                ];
            } elseif ($response->status() === 403) {
                \Log::error('Authentication test failed - Insufficient permissions', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Token válido pero sin permisos suficientes para enviar mensajes.'
                ];
            } else {
                \Log::warning('Authentication test returned unexpected status', [
                    'response_status' => $response->status(),
                    'response_body' => $response->body()
                ]);
                return [
                    'success' => false,
                    'message' => 'Error de autenticación: respuesta inesperada del servidor (' . $response->status() . ')'
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Authentication test failed with exception', [
                'exception' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error al verificar autenticación: ' . $e->getMessage()
            ];
        }
    }

    public function sendBulkMessages()
    {
        $this->validate([
            'message' => ['required', 'string', 'min:1', 'max:1000'],
            'selectedContacts' => ['required', 'array', 'min:1']
        ]);

        if (!$this->whatsappApiKey) {
            $this->error = 'No se ha configurado la API Key de WhatsApp para esta empresa.';
            return;
        }

        $this->isSendingBulk = true;
        $this->sendProgress = 0;
        $this->sendResults = ['success' => 0, 'failed' => 0, 'skipped' => 0];
        $this->error = null;
        $this->success = null;

        $selectedData = collect($this->contacts)
            ->whereIn('id', $this->selectedContacts)
            ->filter(fn($c) => $c['tiene_telefono'])
            ->values();

        $this->sendTotal = $selectedData->count();
        $skipped = count($this->selectedContacts) - $this->sendTotal;
        $this->sendResults['skipped'] = $skipped;

        if ($this->sendTotal === 0) {
            $this->error = 'No hay destinatarios válidos con números de teléfono.';
            $this->isSendingBulk = false;
            return;
        }

        $baseUrl = config('whatsapp.api_url', 'http://localhost:3000');
        
        foreach ($selectedData as $index => $contact) {
            try {
                $phone = $this->formatPhoneNumber($contact['telefono']);
                
                $personalizedMessage = str_replace(
                    ['{nombre}', '{paciente}', '{medico}', '{especialidad}'],
                    [$contact['nombre'], $contact['nombre'], $contact['nombre'], $contact['especialidad'] ?? ''],
                    $this->message
                );

                // CORRECCIÓN: Usar el endpoint correcto /api/whatsapp/send en lugar de /api/messages/send
                $response = Http::timeout(10)
                    ->withHeaders($this->getApiHeaders())
                    ->post($baseUrl . '/api/whatsapp/send', [
                        'to' => $phone,
                        'message' => $personalizedMessage,
                        'type' => 'text'
                    ]);

                if ($response->successful()) {
                    $this->sendResults['success']++;
                } else {
                    $this->sendResults['failed']++;
                    $errorData = $response->json();
                    \Log::warning('Failed to send WhatsApp message in bulk', [
                        'phone' => $phone,
                        'contact_id' => $contact['id'],
                        'response_status' => $response->status(),
                        'error_message' => $errorData['message'] ?? $errorData['error'] ?? 'Unknown error'
                    ]);
                }
            } catch (\Exception $e) {
                $this->sendResults['failed']++;
                \Log::error('Exception sending WhatsApp message in bulk', [
                    'phone' => $contact['telefono'] ?? 'N/A',
                    'contact_id' => $contact['id'],
                    'error' => $e->getMessage()
                ]);
            }

            $this->sendProgress = $index + 1;
            
            // Pequeña pausa entre envíos para evitar rate limiting
            if ($index < $this->sendTotal - 1) {
                usleep(300000); // 0.3 segundos
            }
        }

        $this->isSendingBulk = false;
        
        if ($this->sendResults['success'] > 0) {
            $this->success = "Envío completado: {$this->sendResults['success']} enviados, {$this->sendResults['failed']} fallidos" . 
                ($this->sendResults['skipped'] > 0 ? ", {$this->sendResults['skipped']} sin teléfono" : "");
        } else {
            $this->error = "No se pudo enviar ningún mensaje. Verifique los números de teléfono y la conexión.";
        }

        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->loadRecentMessages();
        $this->dispatch('refreshWhatsapp');
    }

    public function loadRecentMessages()
    {
        if (!$this->whatsappApiKey) return;

        try {
            $baseUrl = config('whatsapp.api_url', 'http://localhost:3001');
            $response = Http::timeout(10)
                ->withHeaders($this->getApiHeaders())
                ->get($baseUrl . '/api/whatsapp/messages', [
                    'limit' => 10
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->recentMessages = collect($data['messages'] ?? [])
                    ->where('status', 'sent')
                    ->take(10)
                    ->values()
                    ->toArray();
            }
        } catch (\Exception $e) {
            \Log::debug('Could not load recent messages: ' . $e->getMessage());
            // Silencioso - no mostrar error al usuario
        }
    }

    public function clearMessages()
    {
        $this->reset(['success', 'error', 'sendResults']);
    }

    public function clearForm()
    {
        $this->reset([
            'to', 'message', 'charCount', 'selectedTemplate', 
            'success', 'error', 'selectedContacts', 'selectAll', 
            'sendResults', 'sendProgress', 'sendTotal'
        ]);
        $this->isSendingBulk = false;
    }

    public function resend($to, $message)
    {
        $this->sendMode = 'individual';
        $this->to = $to;
        $this->message = $message;
        $this->charCount = strlen($message);
        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Formulario precargado para reenvío'
        ]);
    }

    public function getMedicosProperty()
    {
        return Medico::where('status', true)->orderBy('apellidos')->get();
    }

    public function getEspecialidadesProperty()
    {
        return \App\Models\Especialidad::orderBy('nombre')->get();
    }

    public function getSelectedCountProperty()
    {
        return count($this->selectedContacts);
    }

    public function getSelectedWithPhoneCountProperty()
    {
        return collect($this->contacts)
            ->whereIn('id', $this->selectedContacts)
            ->filter(fn($c) => $c['tiene_telefono'])
            ->count();
    }

    public function render()
    {
        return view('livewire.admin.whatsapp.envio-mensajes', [
            'medicos' => $this->medicos,
            'especialidades' => $this->especialidades,
            'selectedCount' => $this->selectedCount,
            'selectedWithPhoneCount' => $this->selectedWithPhoneCount
        ]);
    }
}