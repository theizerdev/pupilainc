<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\Consulta;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class DilatacionService
{
    public function processPendingDilataciones(): int
    {
        $now = Carbon::now();

        $consultas = Consulta::where('estado', Consulta::ESTADO_EN_GOTAS)
            ->with([
                'medico',
                'paciente',
                'gotasAplicadas' => function ($query) {
                    $query->where('estado', 'aplicada')
                        ->where('notificado', false)
                        ->orderByDesc('id');
                },
            ])
            ->get();

        $processed = 0;

        foreach ($consultas as $consulta) {
            $gota = $consulta->gotasAplicadas->first();
            if (!$gota || !$gota->tiempo_espera) {
                continue;
            }

            $fechaInicio = $gota->updated_at ?? $gota->created_at;
            if (!$fechaInicio) {
                continue;
            }

            $fechaInicio = Carbon::parse($fechaInicio);
            $fechaFin = $fechaInicio->copy()->addMinutes($gota->tiempo_espera);

            if ($fechaFin->gt($now)) {
                continue;
            }

            if ($this->processConsultaDilatada($consulta, $gota)) {
                $processed++;
            }
        }

        return $processed;
    }

    public function processConsultaDilatada(Consulta $consulta, $gota = null): bool
    {
        if (!$gota) {
            $gota = $consulta->gotasAplicadas()
                ->where('estado', 'aplicada')
                ->latest('updated_at')
                ->first();
        }

        if (!$consulta->estado || $consulta->estado !== Consulta::ESTADO_EN_GOTAS) {
            return false;
        }

        if (!$gota || !$gota->tiempo_espera || $gota->notificado) {
            return false;
        }

        return $this->executeProcessConsultaDilatada($consulta, $gota);
    }

    protected function executeProcessConsultaDilatada(Consulta $consulta, $gota): bool
    {
        $medico = $consulta->medico;
        if (!$medico || !$medico->user_id) {
            return false;
        }

        $doctorUserId = $medico->user_id;
        $senderId = 1;

        $pacienteNombre = $consulta->paciente?->nombre_completo ?? 'Paciente';
        $consultaId = $consulta->id;

        $chatMessage = "🔔 El paciente {$pacienteNombre} (Consulta #{$consultaId}) ya se encuentra dilatado.";
        $notificationTitle = 'Paciente dilatado';
        $notificationText = "El paciente {$pacienteNombre} ya finalizó el tiempo de dilatación y la consulta se marcó como dilatado.";

        if ($senderId) {
            ChatMessage::create([
                'sender_id' => $senderId,
                'receiver_id' => $consulta->doctor?->user_id ?? $senderId,
                'empresa_id' => $consulta->empresa_id,
                'message' => $chatMessage,
                'is_read' => false,
            ]);
        }

        Notification::create([
            'user_id' => $doctorUserId,
            'type' => 'warning',
            'title' => $notificationTitle,
            'message' => $notificationText,
            'data' => [
                'icon' => 'ri-eye-line',
                'icon_type' => 'warning',
                'consulta_id' => $consultaId,
                'paciente' => $pacienteNombre,
                'action_url' => route('admin.consulta.proceso', $consultaId),
            ],
        ]);

        $consulta->cambiarEstado(Consulta::ESTADO_DILATADO);
        $gota->update(['notificado' => true]);

        $cita = $consulta->cita;
        if ($cita) {
            $cita->update(['estado' => 'dilatado']);
        }

        return true;
    }

    protected function resolveChatSenderId(Consulta $consulta, int $fallbackUserId): ?int
    {
        $sender = User::where('empresa_id', $consulta->empresa_id)
            ->where('sucursal_id', $consulta->sucursal_id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Recepción');
            })
            ->first();

        if ($sender) {
            return $sender->id;
        }

        $sender = User::where('empresa_id', $consulta->empresa_id)
            ->where('sucursal_id', $consulta->sucursal_id)
            ->first();

        return $sender?->id ?? $fallbackUserId;
    }
}
