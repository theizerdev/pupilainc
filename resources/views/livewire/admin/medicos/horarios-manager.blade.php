<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Horario de Atención</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Día</th>
                        <th>Activo</th>
                        <th>Hora Inicio</th>
                        <th>Hora Fin</th>
                        <th>Duración Cita</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($diasSemana as $dia => $nombre)
                        <tr>
                            <td>
                                <strong>{{ $nombre }}</strong>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" 
                                           wire:model="horarios.{{ $dia }}.activo" 
                                           id="dia_{{ $dia }}">
                                </div>
                            </td>
                            <td>
                                @if($horarios[$dia]['activo'])
                                    <input type="time" class="form-control form-control-sm" 
                                           wire:model="horarios.{{ $dia }}.hora_inicio">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($horarios[$dia]['activo'])
                                    <input type="time" class="form-control form-control-sm" 
                                           wire:model="horarios.{{ $dia }}.hora_fin">
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($horarios[$dia]['activo'])
                                    <select class="form-select form-select-sm" 
                                            wire:model="horarios.{{ $dia }}.duracion_cita">
                                        <option value="15">15 min</option>
                                        <option value="20">20 min</option>
                                        <option value="30">30 min</option>
                                        <option value="45">45 min</option>
                                        <option value="60">1 hora</option>
                                        <option value="90">1.5 horas</option>
                                        <option value="120">2 horas</option>
                                    </select>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        @error('horarios.*.hora_inicio')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
        @error('horarios.*.hora_fin')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
        @error('horarios.*.duracion_cita')
            <div class="text-danger small mt-2">{{ $message }}</div>
        @enderror
    </div>
</div>