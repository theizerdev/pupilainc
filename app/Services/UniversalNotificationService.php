<?php

namespace App\Services;

use App\Models\User;
use App\Models\Medico;
use App\Models\Enfermero;
use App\Models\Paciente;
use App\Services\Messaging\UnifiedNotificationService;
use App\Services\WhatsAppNotificationGate;
use Illuminate\Support\Facades\Log;

class UniversalNotificationService
{
    protected ?UnifiedNotificationService $unifiedService = null;
    protected bool $useUnifiedService = false;

    public function __construct()
    {
        $this->useUnifiedService = config('messaging.use_unified_service', false);
        if ($this->useUnifiedService) {
            $this->unifiedService = new UnifiedNotificationService();
        }
    }

    /**
     * Send welcome message to a user
     */
    public function sendWelcomeMessage(User $user, string $password = null): bool
    {
        $empresaId = $user->empresa_id;
        
        // Verify if notification is allowed
        if (!WhatsAppNotificationGate::allows($empresaId, 'usuarios', 'bienvenida', 'usuario')) {
            Log::info('Welcome notification disabled for company', ['empresa_id' => $empresaId]);
            return false;
        }

        // Get user's phone number from related models
        $phoneNumber = $this->getUserPhoneNumber($user);
        if (empty($phoneNumber)) {
            Log::warning('No phone number found for user', ['user_id' => $user->id]);
            return false;
        }

        $mensaje = $this->buildWelcomeMessage($user, $password);

        return $this->sendNotification($empresaId, 'usuarios', 'bienvenida', 'usuario', $phoneNumber, $mensaje);
    }

    /**
     * Send welcome message to a doctor
     */
    public function sendDoctorWelcomeMessage(Medico $medico): bool
    {
        $empresaId = $medico->empresa_id;
        
        // Verify if notification is allowed
        if (!WhatsAppNotificationGate::allows($empresaId, 'medicos', 'bienvenida', 'medico')) {
            Log::info('Doctor welcome notification disabled for company', ['empresa_id' => $empresaId]);
            return false;
        }

        $phoneNumber = $medico->telefono;
        if (empty($phoneNumber)) {
            Log::warning('No phone number found for doctor', ['medico_id' => $medico->id]);
            return false;
        }

        $mensaje = $this->buildDoctorWelcomeMessage($medico);

        return $this->sendNotification($empresaId, 'medicos', 'bienvenida', 'medico', $phoneNumber, $mensaje);
    }

    /**
     * Send welcome message to a nurse
     */
    public function sendNurseWelcomeMessage(Enfermero $enfermero): bool
    {
        $empresaId = $enfermero->empresa_id;
        
        // Verify if notification is allowed
        if (!WhatsAppNotificationGate::allows($empresaId, 'enfermeros', 'bienvenida', 'enfermero')) {
            Log::info('Nurse welcome notification disabled for company', ['empresa_id' => $empresaId]);
            return false;
        }

        $phoneNumber = $enfermero->telefono;
        if (empty($phoneNumber)) {
            Log::warning('No phone number found for nurse', ['enfermero_id' => $enfermero->id]);
            return false;
        }

        $mensaje = $this->buildNurseWelcomeMessage($enfermero);

        return $this->sendNotification($empresaId, 'enfermeros', 'bienvenida', 'enfermero', $phoneNumber, $mensaje);
    }

    /**
     * Send status change notification to user
     */
    public function sendStatusChangeMessage(User $user, string $newStatus): bool
    {
        $empresaId = $user->empresa_id;
        
        // Verify if notification is allowed
        $action = $newStatus === 'active' ? 'cuenta_activada' : 'cuenta_desactivada';
        if (!WhatsAppNotificationGate::allows($empresaId, 'usuarios', $action, 'usuario')) {
            Log::info('Status change notification disabled for company', ['empresa_id' => $empresaId, 'action' => $action]);
            return false;
        }

        $phoneNumber = $this->getUserPhoneNumber($user);
        if (empty($phoneNumber)) {
            Log::warning('No phone number found for user', ['user_id' => $user->id]);
            return false;
        }

        $mensaje = $this->buildStatusChangeMessage($user, $newStatus);

        return $this->sendNotification($empresaId, 'usuarios', $action, 'usuario', $phoneNumber, $mensaje);
    }

    /**
     * Send generic notification to any entity
     */
    public function sendGenericNotification(int $empresaId, string $module, string $action, string $recipientType, $entity, string $customMessage): bool
    {
        if (!WhatsAppNotificationGate::allows($empresaId, $module, $action, $recipientType)) {
            Log::info('Generic notification disabled for company', ['empresa_id' => $empresaId, 'module' => $module, 'action' => $action, 'recipient' => $recipientType]);
            return false;
        }

        $phoneNumber = $this->extractPhoneNumber($entity, $recipientType);
        if (empty($phoneNumber)) {
            Log::warning('No phone number found for entity', ['module' => $module, 'entity' => $entity?->id ?? 'unknown']);
            return false;
        }

        return $this->sendNotification($empresaId, $module, $action, $recipientType, $phoneNumber, $customMessage);
    }

    /**
     * Internal method to send notification using unified service or fallback
     */
    protected function sendNotification(int $empresaId, string $module, string $action, string $recipientType, string $phoneNumber, string $message): bool
    {
        if ($this->shouldUseUnifiedService()) {
            return $this->sendViaUnifiedService($empresaId, $module, $action, $recipientType, $phoneNumber, $message);
        }

        return $this->sendViaLegacyService($empresaId, $phoneNumber, $message);
    }

    protected function shouldUseUnifiedService(): bool
    {
        if ($this->useUnifiedService && $this->unifiedService) {
            return true;
        }

        return false;
    }

    protected function sendViaUnifiedService(int $empresaId, string $module, string $action, string $recipientType, string $phoneNumber, string $message): bool
    {
        try {
            return $this->unifiedService->notify($empresaId, $module, $action, $recipientType, $phoneNumber, $message);
        } catch (\Exception $e) {
            Log::error('UniversalNotificationService: Error with UnifiedNotificationService', [
                'empresa_id' => $empresaId,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            // Fallback to legacy service
            return $this->sendViaLegacyService($empresaId, $phoneNumber, $message);
        }
    }

    protected function sendViaLegacyService(int $empresaId, string $phoneNumber, string $message): bool
    {
        try {
            $whatsappService = \App\Services\WhatsAppService::forCompany($empresaId);
            return $whatsappService->sendMessage($phoneNumber, $message)['success'] ?? false;
        } catch (\Exception $e) {
            Log::error('UniversalNotificationService: Error sending via legacy service', [
                'empresa_id' => $empresaId,
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Extract phone number from different entity types
     */
    protected function extractPhoneNumber($entity, string $recipientType): string
    {
        switch ($recipientType) {
            case 'usuario':
                return $this->getUserPhoneNumber($entity);
            case 'medico':
                return $entity->telefono ?? '';
            case 'enfermero':
                return $entity->telefono ?? '';
            case 'paciente':
                return $entity->telefono ?? '';
            default:
                return '';
        }
    }

    /**
     * Get phone number from user's related entities
     */
    protected function getUserPhoneNumber(User $user): string
    {
        // Try to get phone from related entities
        if ($user->medico) {
            return $user->medico->telefono ?? '';
        }
        
        if ($user->enfermero) {
            return $user->enfermero->telefono ?? '';
        }
        
        if ($user->paciente) {
            return $user->paciente->telefono ?? '';
        }
        
        // Return user's own phone if available
        return $user->phone ?? '';
    }

    /**
     * Build welcome message for user
     */
    protected function buildWelcomeMessage(User $user, string $password = null): string
    {
        $empresa = $user->empresa;
        $sucursal = $user->sucursal;
        $passwordToShow = $password ?: ($user->documento_identidad ?? 'su_documento');

        $mensaje = "🏥 ¡Bienvenido/a {$user->name}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema.\n\n";
        $mensaje .= "📋 *Datos de acceso:*\n";
        $mensaje .= "• Usuario: " . ($user->username ?: $user->email) . "\n";
        $mensaje .= "• Email: {$user->email}\n";
        $mensaje .= "• Contraseña temporal: {$passwordToShow}\n\n";

        if ($empresa) {
            $mensaje .= "🏥 *Información institucional:*\n";
            $mensaje .= "• Empresa: {$empresa->razon_social}\n";
            if ($sucursal) {
                $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
            }
        }

        $mensaje .= "\n🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        
        if ($empresa && $empresa->telefono) {
            $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        }
        
        $mensaje .= "¡Gracias por formar parte de nuestro equipo! 👥";

        return $mensaje;
    }

    /**
     * Build welcome message for doctor
     */
    protected function buildDoctorWelcomeMessage(Medico $medico): string
    {
        $user = $medico->user;
        $empresa = $medico->empresa;
        $sucursal = $medico->sucursal;
        $passwordToShow = $medico->documento_identidad;

        $mensaje = "🩺 ¡Bienvenido/a Dr./Dra. {$medico->nombres} {$medico->apellidos}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema médico.\n\n";

        if ($user) {
            $mensaje .= "📋 *Datos de acceso:*\n";
            $mensaje .= "• Usuario: " . ($user->username ?: $user->email) . "\n";
            $mensaje .= "• Email: {$user->email}\n";
            $mensaje .= "• Contraseña temporal: {$passwordToShow}\n\n";
        }

        $mensaje .= "🏥 *Información institucional:*\n";
        if ($empresa) {
            $mensaje .= "• Empresa: {$empresa->razon_social}\n";
        }
        if ($sucursal) {
            $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
        }

        // Get specialties
        $especialidades = $medico->especialidades()->pluck('nombre')->toArray();
        if (count($especialidades) > 0) {
            $mensaje .= "• Especialidades: " . implode(', ', $especialidades) . "\n";
        }

        $mensaje .= "\n🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        
        if ($empresa && $empresa->telefono) {
            $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        }
        
        $mensaje .= "¡Gracias por formar parte de nuestro equipo médico! 🏥";

        return $mensaje;
    }

    /**
     * Build welcome message for nurse
     */
    protected function buildNurseWelcomeMessage(Enfermero $enfermero): string
    {
        $user = $enfermero->user;
        $empresa = $enfermero->empresa;
        $sucursal = $enfermero->sucursal;
        $passwordToShow = $enfermero->documento_identidad;

        $mensaje = "🏥 ¡Bienvenido/a {$enfermero->nombres} {$enfermero->apellidos}!\n\n";
        $mensaje .= "✅ Su cuenta ha sido creada exitosamente en nuestro sistema de salud.\n\n";

        if ($user) {
            $mensaje .= "📋 *Datos de acceso:*\n";
            $mensaje .= "• Usuario: " . ($user->username ?: $user->email) . "\n";
            $mensaje .= "• Email: {$user->email}\n";
            $mensaje .= "• Contraseña temporal: {$passwordToShow}\n\n";
        }

        $mensaje .= "🏥 *Información institucional:*\n";
        if ($empresa) {
            $mensaje .= "• Empresa: {$empresa->razon_social}\n";
        }
        if ($sucursal) {
            $mensaje .= "• Sucursal: {$sucursal->nombre}\n";
        }
        $mensaje .= "• Tipo de enfermero: {$enfermero->tipo_enfermero}\n";

        // Get specialties if applicable
        $especialidades = $enfermero->especialidades()->pluck('nombre')->toArray();
        if (count($especialidades) > 0) {
            $mensaje .= "• Especialidades: " . implode(', ', $especialidades) . "\n";
        }

        $mensaje .= "\n🔐 *Importante:* Por seguridad, le recomendamos cambiar su contraseña al iniciar sesión.\n\n";
        
        if ($empresa && $empresa->telefono) {
            $mensaje .= "📱 ¿Preguntas? Contáctenos al {$empresa->telefono}\n\n";
        }
        
        $mensaje .= "¡Gracias por formar parte de nuestro equipo de enfermería! 🏥💉";

        return $mensaje;
    }

    /**
     * Build status change message for user
     */
    protected function buildStatusChangeMessage(User $user, string $newStatus): string
    {
        $statusText = $newStatus === 'active' ? 'ACTIVADO' : 'DESACTIVADO';
        $actionText = $newStatus === 'active' ? 'activado' : 'desactivado';

        $mensaje = "ℹ️ *Notificación de Cuenta*\n\n";
        $mensaje .= "Hola {$user->name},\n\n";
        $mensaje .= "Su cuenta ha sido {$actionText} exitosamente.\n\n";
        $mensaje .= "📌 *Nuevo estado:* {$statusText}\n\n";

        if ($newStatus === 'active') {
            $mensaje .= "Ya puede acceder al sistema con sus credenciales habituales.\n\n";
        } else {
            $mensaje .= "Ya no podrá acceder al sistema hasta que sea re-activado.\n\n";
        }

        $mensaje .= "Si tiene alguna pregunta, comuníquese con el administrador del sistema.";

        return $mensaje;
    }
}