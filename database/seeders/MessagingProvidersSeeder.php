<?php

namespace Database\Seeders;

use App\Models\MessagingProvider;
use Illuminate\Database\Seeder;

class MessagingProvidersSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'name' => 'WhatsApp Lite',
                'slug' => 'whatsapp_lite',
                'icon' => 'fab fa-whatsapp',
                'config_schema' => json_encode([
                    'api_url' => [
                        'type' => 'text',
                        'required' => true,
                        'label' => 'URL del API',
                        'default' => 'http://82.165.213.124:8092',
                    ],
                    'api_key' => [
                        'type' => 'password',
                        'required' => true,
                        'label' => 'API Key',
                    ],
                    'timeout' => [
                        'type' => 'number',
                        'required' => false,
                        'label' => 'Timeout (segundos)',
                        'default' => 30,
                    ],
                ]),
                'description' => 'Integración actual con API Node.js personalizada',
                'is_active' => true,
            ],
            [
                'name' => 'WhatsApp Business API',
                'slug' => 'whatsapp_meta',
                'icon' => 'fab fa-meta',
                'config_schema' => json_encode([
                    'phone_number_id' => [
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Phone Number ID',
                    ],
                    'access_token' => [
                        'type' => 'password',
                        'required' => true,
                        'label' => 'Access Token',
                    ],
                    'business_account_id' => [
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Business Account ID',
                    ],
                    'webhook_verify_token' => [
                        'type' => 'text',
                        'required' => false,
                        'label' => 'Webhook Verify Token',
                    ],
                    'api_version' => [
                        'type' => 'select',
                        'options' => ['v18.0', 'v17.0', 'v16.0'],
                        'default' => 'v18.0',
                        'label' => 'API Version',
                    ],
                ]),
                'description' => 'API oficial de Meta para WhatsApp Business',
                'is_active' => true,
            ],
            [
                'name' => 'Twilio',
                'slug' => 'twilio',
                'icon' => 'fas fa-sms',
                'config_schema' => json_encode([
                    'account_sid' => [
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Account SID',
                    ],
                    'auth_token' => [
                        'type' => 'password',
                        'required' => true,
                        'label' => 'Auth Token',
                    ],
                    'phone_number' => [
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Número de teléfono',
                    ],
                ]),
                'description' => 'Plataforma de mensajería SMS y WhatsApp',
                'is_active' => true,
            ],
            [
                'name' => 'Google Firebase',
                'slug' => 'google_fcm',
                'icon' => 'fab fa-google',
                'config_schema' => json_encode([
                    'project_id' => [
                        'type' => 'text',
                        'required' => true,
                        'label' => 'Project ID',
                    ],
                    'private_key' => [
                        'type' => 'textarea',
                        'required' => true,
                        'label' => 'Private Key (JSON)',
                    ],
                    'client_email' => [
                        'type' => 'email',
                        'required' => true,
                        'label' => 'Client Email',
                    ],
                    'api_key' => [
                        'type' => 'password',
                        'required' => true,
                        'label' => 'Server API Key',
                    ],
                ]),
                'description' => 'Firebase Cloud Messaging para notificaciones push',
                'is_active' => true,
            ],
        ];

        foreach ($providers as $provider) {
            MessagingProvider::updateOrCreate(
                ['slug' => $provider['slug']],
                $provider
            );
        }
    }
}