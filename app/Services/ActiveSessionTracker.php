<?php

namespace App\Services;

use App\Models\ActiveSession;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ActiveSessionTracker
{
    public function track(User $user, Request $request, ?float $latitude = null, ?float $longitude = null): void
    {
        try {
            $sessionId = $request->session()->getId();
            $ipAddress = $request->ip();

            $locationData = $this->getLocationData($ipAddress, $latitude, $longitude);

            ActiveSession::where('user_id', $user->id)
                ->update(['is_current' => false]);

            $sessionData = [
                'last_activity' => now(),
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
                'is_current' => true,
                'is_active' => true,
                'login_at' => now(),
                'location' => $locationData['location'] ?? null,
                'latitude' => $locationData['latitude'] ?? null,
                'longitude' => $locationData['longitude'] ?? null,
            ];

            $activeSession = ActiveSession::where('user_id', $user->id)
                ->where('session_id', $sessionId)
                ->first();

            if ($activeSession) {
                $activeSession->update($sessionData);
            } else {
                $sessionData['user_id'] = $user->id;
                $sessionData['session_id'] = $sessionId;
                ActiveSession::create($sessionData);
            }
        } catch (\Exception $e) {
            Log::warning('Error tracking user session: ' . $e->getMessage());
        }
    }

    private function getLocationData(string $ipAddress, ?float $latitude, ?float $longitude): array
    {
        $locationData = [
            'location' => null,
            'latitude' => null,
            'longitude' => null,
        ];

        if ($latitude !== null && $longitude !== null
            && $latitude >= -90 && $latitude <= 90
            && $longitude >= -180 && $longitude <= 180
        ) {
            return $this->reverseGeocode($latitude, $longitude);
        }

        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || strpos($ipAddress, '192.168.') === 0) {
            $locationData['location'] = 'Local';
            return $locationData;
        }

        $locationData['location'] = 'Ubicación desconocida';
        return $locationData;
    }

    private function reverseGeocode(float $lat, float $lon): array
    {
        $locationData = [
            'latitude' => $lat,
            'longitude' => $lon,
            'location' => null,
        ];

        try {
            $response = Http::timeout(3)
                ->withHeaders(['User-Agent' => 'larawire/1.0'])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'format' => 'json',
                    'lat' => $lat,
                    'lon' => $lon,
                    'addressdetails' => 1,
                ]);

            $data = $response->json();

            if ($data && isset($data['address'])) {
                $city = $data['address']['city'] ?? $data['address']['town'] ?? $data['address']['village'] ?? 'Desconocido';
                $state = $data['address']['state'] ?? $data['address']['region'] ?? 'Desconocido';
                $country = $data['address']['country'] ?? 'Desconocido';
                $locationData['location'] = "{$city}, {$state}, {$country}";
            } else {
                $locationData['location'] = "Lat: {$lat}, Lon: {$lon}";
            }
        } catch (\Exception $e) {
            Log::warning("Error obteniendo geolocalización para coordenadas ({$lat}, {$lon}): " . $e->getMessage());
            $locationData['location'] = "Lat: {$lat}, Lon: {$lon}";
        }

        return $locationData;
    }
}
