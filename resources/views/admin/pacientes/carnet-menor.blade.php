<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carnet - {{ $paciente->nombres }} {{ $paciente->apellidos }}</title>
    <style>
        @page { size: 85mm 55mm; margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .screen-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f5f6fa; padding: 20px; }
        .toolbar { display: flex; gap: 8px; justify-content: center; margin-bottom: 12px; }
        .btn { appearance: none; border: 1px solid #d5d7de; background: #fff; color: #111827; padding: 8px 12px; border-radius: 10px; font-size: 13px; cursor: pointer; }
        .btn-primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .btn:active { transform: translateY(1px); }
        @media print { .screen-wrap { padding: 0; background: transparent; } .toolbar { display: none; } }
        body.pdf .screen-wrap { padding: 0; background: transparent; min-height: auto; }
        body.pdf .toolbar { display: none; }

        .id-card { width: 85mm; height: 55mm; border-radius: 12px; overflow: hidden; position: relative; background: #fff; box-shadow: 0 10px 40px rgba(17, 24, 39, .12); }
        .id-surface { position: absolute; inset: 0; background: #fff; }
        .id-frame { position: relative; height: 100%; display: grid; grid-template-rows: 12mm 1fr 12mm; }

        .id-header { display: flex; align-items: center; justify-content: space-between; padding: 7px 10px; }
        .id-brand { display: flex; align-items: center; gap: 8px; min-width: 0; }
        .id-logo { width: 18mm; height: 10mm;  display: flex; align-items: center; justify-content: center; overflow: hidden;  }
        .id-logo img { height: 20mm; width: auto; display: block; }
        .id-brand-text { min-width: 0; }
        .id-brand-name { font-weight: 900; font-size: 10.5px; letter-spacing: .2px; color: #111827; line-height: 1.05; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 44mm; }
        .id-branch { font-size: 8.6px; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 44mm; }
        .id-role { text-align: right; font-weight: 900; font-size: 10px; letter-spacing: .08em; text-transform: uppercase; color: #6b7280; line-height: 1.05; }

        .id-body { padding: 0 10px; display: grid; grid-template-columns: 22mm 1fr 16mm; gap: 10px; align-items: center; }
        .id-photo { width: 22mm; height: 22mm; border-radius: 10px; overflow: hidden; background: #f3f4f6; border: 1px solid rgba(17,24,39,.10); display: flex; align-items: center; justify-content: center; }
        .id-photo img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .id-initials { font-weight: 900; font-size: 16px; color: #2563eb; }

        .id-info { min-width: 0; }
        .id-doc { font-size: 9.3px; color: #6b7280; font-weight: 700; letter-spacing: .02em; }
        .id-name { font-size: 12.5px; font-weight: 950; color: #111827; line-height: 1.05; }
        .id-subline { font-size: 9px; color: #6b7280; font-style: italic; font-weight: 600; margin-top: 2px; }
        .id-meta { margin-top: 6px; display: grid; gap: 2px; }
        .id-meta-row { display: flex; gap: 6px; font-size: 8.6px; color: #111827; }
        .id-meta-row .k { color: #6b7280; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; font-size: 7.8px; width: 16mm; flex: 0 0 16mm; }
        .id-meta-row .v { font-weight: 800; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        .id-code { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; }
        .id-qr { width: 16mm; height: 16mm; border-radius: 8px; overflow: hidden; background: #fff; border: 1px solid rgba(17,24,39,.10); }
        .id-qr img { width: 100%; height: 100%; display: block; }
        .id-code-label { font-size: 7.5px; color: #6b7280; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .id-code-id { font-size: 8.2px; color: #111827; font-weight: 900; }

        .id-band { position: relative; overflow: hidden; }
        .id-band::before { content: ""; position: absolute; inset: 0; background:
            radial-gradient(circle at 10% 30%, rgba(255,255,255,.55) 0 2px, transparent 3px),
            radial-gradient(circle at 25% 60%, rgba(255,255,255,.45) 0 3px, transparent 4px),
            radial-gradient(circle at 45% 35%, rgba(255,255,255,.35) 0 2px, transparent 3px),
            radial-gradient(circle at 65% 70%, rgba(255,255,255,.42) 0 3px, transparent 4px),
            radial-gradient(circle at 85% 40%, rgba(255,255,255,.50) 0 2px, transparent 3px),
            linear-gradient(90deg, rgba(37,99,235,.95), rgba(59,130,246,.75));
            opacity: .95;
        }
        .id-band-inner { position: relative; height: 100%; display: flex; align-items: center; justify-content: space-between; padding: 0 10px; }
        .id-lost { font-size: 8px; color: rgba(255,255,255,.92); font-weight: 700; }
        .id-contact { font-size: 8px; color: rgba(255,255,255,.92); font-weight: 700; text-align: right; }
    </style>
</head>
<body class="{{ !empty($pdfMode) ? 'pdf' : '' }}">
    @php
        use Illuminate\Support\Facades\Storage;
    @endphp
    @php
        $edad = $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age : null;
        $esMenor = $edad !== null && $edad < 18;
        $fotoUrl = null;
        if (!empty($fotoDataUri)) {
            $fotoUrl = $fotoDataUri;
        } elseif (!empty($paciente->foto)) {
            $fotoUrl = filter_var($paciente->foto, FILTER_VALIDATE_URL) ? $paciente->foto : url(Storage::url($paciente->foto));
        }
        $iniciales = strtoupper(substr((string) $paciente->nombres, 0, 1) . substr((string) $paciente->apellidos, 0, 1));
    @endphp

    <div class="screen-wrap">
        <div>
            <div class="toolbar">
                <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir</button>
                <button class="btn" type="button" onclick="window.close()">Cerrar</button>
            </div>

            @if(!$esMenor)
                <div class="card" style="display:flex;align-items:center;justify-content:center;padding:12px;box-sizing:border-box;">
                    <div style="text-align:center;font-size:12px;color:#111827;">
                        Este carnet está disponible solo para pacientes menores de edad.
                    </div>
                </div>
            @else
                <div class="id-card">
                    <div class="id-surface"></div>
                    <div class="id-frame">
                        <div class="id-header">
                            <div class="id-brand">
                                <div class="id-logo">
                                    <img src="{{ $logoUrl }}" alt="Logo">
                                </div>
                                <div class="id-brand-text">
                                    <div class="id-brand-name">{{ $empresaNombre }}</div>
                                    <div class="id-branch">{{ $sucursalNombre ?: 'Sucursal' }}</div>
                                </div>
                            </div>
                            <div class="id-role">Carnet<br></div>
                        </div>

                        <div class="id-body">
                            <div class="id-photo">
                                @if($fotoUrl)
                                    <img src="{{ $fotoUrl }}" alt="Foto">
                                @else
                                    <div class="id-initials">{{ $iniciales }}</div>
                                @endif
                            </div>

                            <div class="id-info">
                                <div class="id-doc">{{ $paciente->documento_identidad ?: 'Documento: —' }}</div>
                                <div class="id-name">{{ $paciente->nombres }} {{ $paciente->apellidos }}</div>
                                <div class="id-subline">{{ $paciente->nickname ?: 'Registro de paciente' }}</div>

                                <div class="id-meta">
                                    <div class="id-meta-row">
                                        <div class="k">Nac.</div>
                                        <div class="v">{{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : '—' }}@if($edad !== null) ({{ $edad }}) @endif</div>
                                    </div>
                                    <div class="id-meta-row">
                                        <div class="k">Tutor</div>
                                        <div class="v">{{ $paciente->tutor ? ($paciente->tutor->nombres . ' ' . $paciente->tutor->apellidos) : '—' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="id-code">
                                <div class="id-qr">
                                    <img src="{{ $qrDataUri }}" alt="QR">
                                </div>
                                <div class="id-code-label">ID</div>
                                <div class="id-code-id">{{ $paciente->id }}</div>
                            </div>
                        </div>

                        <div class="id-band">
                            <div class="id-band-inner">
                                <div class="id-lost">En caso de extravío, escanee el QR</div>
                                <div class="id-contact">
                                    @if($contactTel) Tel: {{ $contactTel }}<br>@endif
                                    @if($contactEmail) {{ $contactEmail }}@endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                try { window.print(); } catch (e) {}
            }, 250);
        });
    </script>
</body>
</html>
