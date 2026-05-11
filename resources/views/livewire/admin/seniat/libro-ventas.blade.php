<div>
    @section('title', 'Libro de Ventas SENIAT')

    @push('styles')
    <style>
        .lv-hero { background: linear-gradient(135deg, #10B981 0%, #059669 100%); color:#fff; border-radius:.75rem; padding:1.4rem 1.6rem; }
        .lv-hero h2 { color:#fff; margin:0; }
        .lv-hero p { opacity:.9; margin:0; }

        .stat-card { border:1px solid rgba(0,0,0,.06); border-radius:.65rem; padding:.9rem 1rem;
                     transition:all .2s; display:flex; align-items:center; gap:.85rem; height:100%; background:#fff; }
        .stat-card:hover { box-shadow:0 6px 18px rgba(0,0,0,.07); transform:translateY(-1px); }
        .stat-card .stat-icon { width:44px; height:44px; border-radius:11px; flex:0 0 44px;
                                display:flex; align-items:center; justify-content:center; font-size:1.15rem; }
        .stat-card .stat-value { font-size:1.35rem; font-weight:600; line-height:1; }
        .stat-card .stat-label { font-size:.72rem; color:var(--bs-secondary-color);
                                 text-transform:uppercase; letter-spacing:.4px; font-weight:600; }

        .resumen-card { border:1px solid rgba(0,0,0,.07); border-radius:.55rem; padding:.7rem .9rem;
                        text-align:center; transition:all .15s; background:#fff; }
        .resumen-card:hover { box-shadow:0 4px 12px rgba(0,0,0,.06); }
        .resumen-card .rc-label { font-size:.7rem; text-transform:uppercase; color:var(--bs-secondary-color);
                                 letter-spacing:.4px; font-weight:600; margin-bottom:.25rem; }
        .resumen-card .rc-value { font-size:1.15rem; font-weight:600; }
    </style>
    @endpush

    <div class="container-p-y">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="ri ri-home-line me-1"></i>Dashboard</a></li>
                <li class="breadcrumb-item active">Libro de Ventas SENIAT</li>
            </ol>
        </nav>

        {{-- Hero --}}
        <div class="lv-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h2 class="fw-semibold"><i class="ri ri-government-line me-2"></i>Libro de Ventas SENIAT</h2>
                <p class="mt-1">Reporte fiscal de documentos emitidos</p>
            </div>
        </div>

        {{-- Filtros compactos --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Fecha desde *</label>
                        <input type="date" class="form-control form-control-sm @error('desde') is-invalid @enderror" wire:model.live="desde">
                        @error('desde')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Fecha hasta *</label>
                        <input type="date" class="form-control form-control-sm @error('hasta') is-invalid @enderror" wire:model.live="hasta">
                        @error('hasta')<div class="invalid-feedback small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tipo documento</label>
                        <select class="form-select form-select-sm" wire:model.live="tipo_documento">
                            <option value="">Todos los tipos</option>
                            <option value="factura">Facturas</option>
                            <option value="nota_credito">Notas de Crédito</option>
                            <option value="nota_debito">Notas de Débito</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="resetFilters">
                            <i class="ri ri-refresh-line me-1"></i>Limpiar
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="ri ri-download-line me-1"></i>Exportar
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><button class="dropdown-item" wire:click="exportarTxt"><i class="ri ri-file-text-line me-2 text-primary"></i>Archivo TXT (SENIAT)</button></li>
                                <li><button class="dropdown-item" wire:click="exportarExcel"><i class="ri ri-file-excel-line me-2 text-success"></i>Archivo Excel</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIs --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dbeafe;color:#2563eb;"><i class="ri ri-file-list-3-line"></i></div>
                    <div>
                        <div class="stat-label">Total documentos</div>
                        <div class="stat-value">{{ $stats['total_documentos'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="ri ri-bill-line"></i></div>
                    <div>
                        <div class="stat-label">Facturas</div>
                        <div class="stat-value">{{ $stats['facturas'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="ri ri-arrow-go-back-line"></i></div>
                    <div>
                        <div class="stat-label">Notas de crédito</div>
                        <div class="stat-value">{{ $stats['notas_credito'] }}</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#cffafe;color:#0891b2;"><i class="ri ri-arrow-go-forward-line"></i></div>
                    <div>
                        <div class="stat-label">Notas de débito</div>
                        <div class="stat-value">{{ $stats['notas_debito'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Resumen totales --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 pb-0">
                <h6 class="mb-0"><i class="ri ri-calculator-line me-2 text-primary"></i>Resumen de totales</h6>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col">
                        <div class="resumen-card">
                            <div class="rc-label">Base imponible</div>
                            <div class="rc-value text-primary">{{ format_money($totales['base_imponible'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="resumen-card">
                            <div class="rc-label">Monto exento</div>
                            <div class="rc-value text-info">{{ format_money($totales['monto_exento'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="resumen-card">
                            <div class="rc-label">IVA</div>
                            <div class="rc-value text-warning">{{ format_money($totales['iva_monto'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="resumen-card">
                            <div class="rc-label">IGTF</div>
                            <div class="rc-value text-secondary">{{ format_money($totales['igtf_monto'], 2) }}</div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="resumen-card" style="border-color:rgba(16,185,129,.25);">
                            <div class="rc-label">Total general</div>
                            <div class="rc-value text-success">{{ format_money($totales['total'], 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 pb-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0"><i class="ri ri-file-list-3-line me-2 text-primary"></i>Documentos fiscales</h6>
                @php
                    $docsIncompletos = $documentos->filter(fn($d) => ($d->total_con_impuestos ?? 0) == 0 && ($d->total ?? 0) == 0);
                @endphp
                @if($docsIncompletos->count() > 0)
                    <button type="button" class="btn btn-warning btn-sm" wire:click="recalcularDocumentos"
                            wire:loading.attr="disabled" wire:target="recalcularDocumentos"
                            title="Recalcular {{ $docsIncompletos->count() }} documento(s) con datos fiscales incompletos">
                        <span wire:loading.remove wire:target="recalcularDocumentos">
                            <i class="ri ri-refresh-line me-1"></i>Recalcular {{ $docsIncompletos->count() }}
                        </span>
                        <span wire:loading wire:target="recalcularDocumentos">
                            <span class="spinner-border spinner-border-sm me-1"></span>Recalculando...
                        </span>
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="fw-semibold">Fecha</th>
                                <th class="fw-semibold">Tipo</th>
                                <th class="fw-semibold">Número</th>
                                <th class="fw-semibold">Control Fiscal</th>
                                <th class="fw-semibold">Cliente</th>
                                <th class="text-end fw-semibold">Base</th>
                                <th class="text-end fw-semibold">Exento</th>
                                <th class="text-end fw-semibold">IVA</th>
                                <th class="text-end fw-semibold">IGTF</th>
                                <th class="text-end fw-semibold">Total</th>
                                <th class="text-center fw-semibold">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documentos as $doc)
                            @php
                                $badgeClass = match($doc->tipo_pago) {
                                    'factura'      => 'bg-label-success',
                                    'nota_credito' => 'bg-label-warning',
                                    'nota_debito'  => 'bg-label-info',
                                    default        => 'bg-label-secondary'
                                };
                                $tipoLabel = match($doc->tipo_pago) {
                                    'factura'      => 'FAC',
                                    'nota_credito' => 'NC',
                                    'nota_debito'  => 'ND',
                                    default        => 'DOC'
                                };
                                $signo    = $doc->tipo_pago === 'nota_credito' ? -1 : 1;
                                $tasa     = (float) ($doc->tasa_cambio_usd ?: 1);
                                $isVzla   = is_venezuela_company();

                                // Todos los campos están en USD en la BD
                                $baseUsd  = $signo * (float) ($doc->base_imponible ?? 0);
                                $exentoUsd = $signo * (float) ($doc->monto_exento ?? 0);
                                $ivaUsd   = $signo * (float) ($doc->iva_monto ?? 0);
                                $igtfUsd  = $signo * (float) ($doc->igtf_monto ?? 0);
                                $totalUsd = $signo * (float) ($doc->total_con_impuestos ?: $doc->total_usd ?: $doc->total);

                                // Convertir a moneda local si es Venezuela
                                $base     = $isVzla ? $baseUsd * $tasa  : $baseUsd;
                                $exento   = $isVzla ? $exentoUsd * $tasa : $exentoUsd;
                                $iva      = $isVzla ? $ivaUsd * $tasa   : $ivaUsd;
                                $igtf     = $isVzla ? $igtfUsd * $tasa  : $igtfUsd;
                                $total    = $isVzla ? $totalUsd * $tasa  : $totalUsd;

                                $esExenta        = ($doc->base_imponible ?? 0) == 0 && ($doc->monto_exento ?? 0) > 0;
                                $fiscalIncompleto = ($doc->total_con_impuestos ?? 0) == 0 && ($doc->total_usd ?? 0) == 0 && ($doc->total ?? 0) == 0;
                            @endphp
                            <tr>
                                <td><small>{{ $doc->fecha->format('d/m/Y') }}</small></td>
                                <td><span class="badge {{ $badgeClass }}">{{ $tipoLabel }}</span></td>
                                <td class="fw-semibold">{{ $doc->numero_completo }}</td>
                                <td><code class="small">{{ $doc->numero_control_fiscal ?: 'N/A' }}</code></td>
                                <td>
                                    @if($doc->clienteFiscal)
                                        <div class="fw-medium">{{ $doc->clienteFiscal->razon_social }}</div>
                                        <small class="text-muted">{{ $doc->clienteFiscal->tipo_documento }}-{{ $doc->clienteFiscal->numero_documento }}</small>
                                    @elseif($doc->consulta?->paciente)
                                        <div class="fw-medium">{{ $doc->consulta->paciente->nombre_completo }}</div>
                                        <small class="text-muted">Paciente</small>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-end {{ $base < 0 ? 'text-danger' : '' }}">
                                    @if($fiscalIncompleto)
                                        <span class="text-muted">—</span>
                                    @elseif($esExenta)
                                        <span class="text-muted small">Exento</span>
                                    @else
                                        {{ format_money($base) }}
                                    @endif
                                </td>
                                <td class="text-end {{ $exento < 0 ? 'text-danger' : '' }}">
                                    @if($fiscalIncompleto || $exentoUsd == 0)
                                        <span class="text-muted">—</span>
                                    @else
                                        {{ format_money($exento) }}
                                    @endif
                                </td>
                                <td class="text-end {{ $iva < 0 ? 'text-danger' : '' }}">
                                    @if($fiscalIncompleto)
                                        <span class="text-muted">—</span>
                                    @elseif($esExenta)
                                        <span class="text-muted small">Exento</span>
                                    @else
                                        {{ format_money($iva) }}
                                    @endif
                                </td>
                                <td class="text-end {{ $igtf < 0 ? 'text-danger' : '' }}">
                                    @if($fiscalIncompleto || $igtfUsd == 0)
                                        <span class="text-muted">—</span>
                                    @else
                                        {{ format_money($igtf) }}
                                    @endif
                                </td>
                                <td class="text-end fw-semibold {{ $total < 0 ? 'text-danger' : 'text-success' }}">
                                    @if($fiscalIncompleto)
                                        @php $fallback = $isVzla ? (float)($doc->total_bs ?: $doc->total) : (float)($doc->total_usd ?: $doc->total); @endphp
                                        <span title="Datos fiscales incompletos">
                                            {{ format_money($fallback) }}
                                            <span class="badge bg-label-warning ms-1" style="font-size:.65rem;">⚠</span>
                                        </span>
                                    @else
                                        {{ format_money($total) }}
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($fiscalIncompleto)
                                        <span class="badge bg-label-warning" title="Datos fiscales incompletos">Incompleto</span>
                                    @elseif($esExenta)
                                        <span class="badge bg-label-info" title="Documento exento de IVA">Exento</span>
                                    @else
                                        <span class="badge bg-label-success">Fiscal</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted">
                                    <i class="ri ri-search-line" style="font-size:1.5rem;opacity:.3;"></i>
                                    <p class="mb-0 mt-1 small">No se encontraron documentos fiscales en el período seleccionado</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Script para descargar archivos -->
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('download-file', (event) => {
                const { content, filename, contentType } = event;
                const blob = new Blob([content], { type: contentType });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            });
        });
    </script>
</div>
