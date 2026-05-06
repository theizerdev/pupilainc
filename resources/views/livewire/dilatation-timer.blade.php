<div>
    @if($hasActiveDilatations)
    <div class="dilatation-timer-widget dilatation-timer-{{ $position }} dilatation-timer-{{ $size }} dilatation-timer-{{ $theme }}"
         wire:poll.10s="loadActiveDilatations">
        <!-- Botón agrupador colapsable -->
        <button class="dilatation-timer-toggle-btn" wire:click="toggleExpand">
            <div class="toggle-icon">
                <i class="ri ri-timer-flash-line"></i>
                @if($totalActive > 0)
                <span class="pulse-indicator"></span>
                @endif
            </div>
            <div class="toggle-content">
                <span class="toggle-label">Dilataciones</span>
                <span class="toggle-count">{{ $totalActive }}</span>
            </div>
            <div class="toggle-arrow {{ $isExpanded ? 'expanded' : '' }}">
                <i class="ri ri-arrow-{{ $isExpanded ? 'down' : 'up' }}-s-line"></i>
            </div>
        </button>

        <!-- Lista expandible de dilataciones -->
        @if($isExpanded)
        <div class="dilatation-timer-list-wrapper">
            <!-- Controles de configuración -->
            <div class="dilatation-timer-controls">
                <div class="control-group">
                    <button class="btn-control" wire:click="refresh" title="Actualizar">
                        <i class="ri ri-refresh-line"></i>
                    </button>
                    <button class="btn-control" wire:click="$set('filterByUser', !$filterByUser)"
                            :class="{ 'active': $filterByUser }" title="Mis consultas">
                        <i class="ri ri-user-line"></i>
                    </button>
                    <button class="btn-control" wire:click="$set('filterByMedico', !$filterByMedico)"
                            :class="{ 'active': $filterByMedico }" title="Como médico">
                        <i class="ri ri-stethoscope-line"></i>
                    </button>
                </div>
            </div>

            <div class="dilatation-timer-list">
                @foreach($activeDilatations as $index => $dilatation)
                <div class="dilatation-timer-item {{ $dilatation['urgente'] ? 'urgent' : '' }}"
                     wire:key="dilatation-{{ $dilatation['consulta_id'] }}-{{ $lastUpdate }}">

                    <!-- Información de debug -->
                    @if($showDebug)
                    <div class="debug-info">
                        <small>
                            ID: {{ $dilatation['consulta_id'] }} |
                            Espera: {{ $dilatation['tiempo_espera_minutos'] }}min |
                            Restante: {{ $dilatation['segundos_restantes'] }}s |
                            Inicio: {{ $dilatation['fecha_inicio'] }}
                        </small>
                    </div>
                    @endif

                    <div class="dilatation-info">
                        <div class="dilatation-patient">
                            <i class="ri ri-user-3-line"></i>
                            <a href="{{ route('admin.consulta.proceso', $dilatation['consulta_id']) }}" target="_blank">
                                {{ $dilatation['paciente'] }}
                            </a>
                        </div>
                        <div class="dilatation-doctor">
                            <i class="ri ri-stethoscope-line"></i>
                            {{ $dilatation['medico_nombre'] }}
                        </div>
                    </div>

                    <div class="dilatation-timer-display">
                        @if($dilatation['segundos_restantes']  0)
                            @php
                                $mins = floor($dilatation['segundos_restantes'] / 60);
                                $secs = $dilatation['segundos_restantes'] % 60;
                            @endphp
                            <div class="timer-countdown {{ $dilatation['urgente'] ? 'timer-urgent' : '' }}">
                                {{ $mins }}:{{ str_pad($secs, 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <div class="timer-progress">
                                <div class="timer-progress-bar"
                                     style="width: {{ $dilatation['porcentaje'] }}%;
                                            background: {{ $dilatation['urgente'] ? '#dc3545' : ($dilatation['porcentaje'] > 75 ? '#ffc107' : '#28a745') }}">
                                </div>
                            </div>
                        @else
                            <div class="timer-complete">
                                <i class="ri ri-check-double-line"></i>
                                <span>Completado</span>
                            </div>
                        @endif
                    </div>

                    @if($dilatation['ya_notificado'])
                    <div class="dilatation-notified-badge">
                        <i class="ri ri-notification-line"></i>
                        <span>Notificado</span>
                    </div>
                    @endif

                    <!-- Acciones rápidas -->
                    <div class="dilatation-actions">
                        <a href="{{ route('admin.consulta.proceso', $dilatation['consulta_id']) }}"
                           class="btn-action" target="_blank" title="Ver consulta">
                            <i class="ri ri-eye-line"></i>
                        </a>
                    </div>
                </div>
                @endforeach

                @if(count($activeDilatations) === 0)
                <div class="dilatation-empty">
                    <i class="ri ri-timer-flash-line"></i>
                    <span>No hay dilataciones activas</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>
    @endif

<style>
/* Posicionamiento base */
.dilatation-timer-widget {
    position: fixed;
    bottom: 80px;
    right: 20px;
    width: 320px;
    z-index: 1050;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Posiciones alternativas */
.dilatation-timer-bottom-left {
    right: auto;
    left: 20px;
}

.dilatation-timer-top-right {
    bottom: auto;
    top: 20px;
    right: 20px;
}

.dilatation-timer-top-left {
    bottom: auto;
    top: 20px;
    right: auto;
    left: 20px;
}

/* Tamaños */
.dilatation-timer-small {
    width: 280px;
}

.dilatation-timer-large {
    width: 380px;
}

/* Temas */
.dilatation-timer-dark {
    --bg-primary: #2d3748;
    --bg-secondary: #4a5568;
    --text-primary: #e2e8f0;
    --text-secondary: #a0aec0;
    --border-color: #4a5568;
}

.dilatation-timer-light {
    --bg-primary: #ffffff;
    --bg-secondary: #f7fafc;
    --text-primary: #2d3748;
    --text-secondary: #4a5568;
    --border-color: #e2e8f0;
}

/* Animaciones */
@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes expandDown {
    from {
        opacity: 0;
        max-height: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        max-height: 600px;
        transform: translateY(0);
    }
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

/* Botón agrupador */
.dilatation-timer-toggle-btn {
    width: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 12px;
    padding: 14px 18px;
    color: white;
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    transition: all 0.3s ease;
    font-weight: 600;
    animation: slideInRight 0.3s ease;
}

.dilatation-timer-toggle-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
}

.toggle-icon {
    position: relative;
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
}

.pulse-indicator {
    position: absolute;
    top: -2px;
    right: -2px;
    width: 12px;
    height: 12px;
    background: #ff6b6b;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

.toggle-content {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.toggle-label {
    font-size: 0.95rem;
    font-weight: 600;
}

.toggle-count {
    background: rgba(255, 255, 255, 0.3);
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 700;
}

.toggle-arrow {
    transition: transform 0.3s ease;
    font-size: 1.2rem;
}

/* Lista expandible */
.dilatation-timer-list-wrapper {
    margin-top: 10px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    animation: expandDown 0.3s ease;
}

.dilatation-timer-controls {
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
    background: #f8f9fa;
}

.control-group {
    display: flex;
    gap: 8px;
}

.btn-control {
    padding: 6px 8px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 6px;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.btn-control:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.btn-control.active {
    background: #667eea;
    border-color: #667eea;
    color: white;
}

.dilatation-timer-list {
    max-height: 400px;
    overflow-y: auto;
    padding: 12px;
}

.dilatation-timer-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    border-left: 4px solid #667eea;
    transition: all 0.2s ease;
}

.dilatation-timer-item:hover {
    transform: translateX(4px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.dilatation-timer-item.urgent {
    border-left-color: #dc3545;
    background: linear-gradient(135deg, #fff5f5 0%, #ffe5e5 100%);
}

.debug-info {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 4px 8px;
    margin-bottom: 8px;
    font-size: 0.75rem;
    color: #856404;
}

.dilatation-info {
    margin-bottom: 10px;
}

.dilatation-patient,
.dilatation-doctor {
    font-size: 0.85rem;
    color: #495057;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.dilatation-patient {
    font-weight: 600;
    color: #212529;
}

.dilatation-patient a {
    color: inherit;
    text-decoration: none;
}

.dilatation-patient a:hover {
    text-decoration: underline;
}

.dilatation-timer-display {
    margin-top: 8px;
}

.timer-countdown {
    font-size: 1.5rem;
    font-weight: 700;
    text-align: center;
    color: #28a745;
    font-family: 'Courier New', monospace;
}

.timer-countdown.timer-urgent {
    color: #dc3545;
    animation: pulse 1s infinite;
}

.timer-progress {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    margin-top: 8px;
    overflow: hidden;
}

.timer-progress-bar {
    height: 100%;
    transition: width 0.3s ease, background 0.3s ease;
}

.timer-complete {
    text-align: center;
    color: #28a745;
    font-weight: 600;
    font-size: 0.9rem;
    padding: 8px;
    background: rgba(40, 167, 69, 0.1);
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
}

.dilatation-notified-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #e7f3ff;
    color: #0066cc;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
    margin-top: 8px;
}

.dilatation-actions {
    margin-top: 8px;
    display: flex;
    justify-content: flex-end;
    gap: 4px;
}

.btn-action {
    padding: 4px 6px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 4px;
    color: #6c757d;
    text-decoration: none;
    font-size: 0.8rem;
    transition: all 0.2s ease;
}

.btn-action:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #495057;
}

.dilatation-empty {
    text-align: center;
    color: #6c757d;
    padding: 20px;
    font-size: 0.9rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.dilatation-empty i {
    font-size: 2rem;
    opacity: 0.5;
}

/* Scrollbar personalizado */
.dilatation-timer-list::-webkit-scrollbar {
    width: 6px;
}

.dilatation-timer-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.dilatation-timer-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.dilatation-timer-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Responsive */
@media (max-width: 768px) {
    .dilatation-timer-widget {
        width: calc(100% - 40px);
        right: 20px;
        left: 20px;
        bottom: 70px;
    }

    .dilatation-timer-bottom-left,
    .dilatation-timer-top-left {
        left: 20px;
        right: 20px;
    }
}
</style>