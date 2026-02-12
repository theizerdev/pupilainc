<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carnet - {{ $paciente->nombres }} {{ $paciente->apellidos }}</title>
    <style>
        @page { size: 85mm 55mm; margin: 0; }
        html, body { margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; }
        * { box-sizing: border-box; }
        .wrap { width: 85mm; height: 55mm; }

        .card { width: 85mm; height: 55mm; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; background: #ffffff; position: relative; }
        .header { position: absolute; top: 0; left: 0; right: 0; height: 12mm; padding: 1.6mm 2.2mm; }
        .body { position: absolute; top: 12mm; bottom: 12mm; left: 0; right: 0; padding: 0 2.2mm; }
        .band { position: absolute; bottom: 0; left: 0; right: 0; height: 12mm; background: #2563eb; padding: 1.8mm 2.2mm; }

        .logo { width: 18mm; height: 10mm; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; text-align: center; vertical-align: middle; }
        .logo img { height: 10mm; }

        .brand-name { font-weight: 800; font-size: 10px; line-height: 1.05; }
        .branch { font-size: 8px; color: #6b7280; line-height: 1.05; }
        .role { font-weight: 800; font-size: 8.8px; color: #6b7280; text-transform: uppercase; letter-spacing: .06em; text-align: right; line-height: 1.05; }

        .photo { width: 22mm; height: 22mm; border-radius: 10px; border: 1px solid #e5e7eb; background: #f3f4f6; overflow: hidden; text-align: center; vertical-align: middle; }
        .photo img { width: 22mm; height: 22mm; }
        .initials { font-weight: 800; font-size: 16px; color: #2563eb; }

        .doc { font-size: 8.4px; color: #6b7280; font-weight: 700; line-height: 1.05; }
        .name { font-size: 11.6px; font-weight: 900; line-height: 1.02; }
        .subline { font-size: 8.2px; color: #6b7280; font-style: italic; font-weight: 600; margin-top: 1px; line-height: 1.05; }

        .meta-k { font-size: 7.2px; color: #6b7280; font-weight: 800; text-transform: uppercase; letter-spacing: .08em; width: 13mm; }
        .meta-v { font-size: 8px; font-weight: 800; line-height: 1.05; }

        .qr { width: 16mm; height: 16mm; border-radius: 8px; border: 1px solid #e5e7eb; background: #fff; overflow: hidden; }
        .qr img { width: 16mm; height: 16mm; }

        .band-text { font-size: 7.1px; color: #ffffff; font-weight: 700; line-height: 1.08; }
        .band-id { font-size: 6.9px; color: rgba(255,255,255,.95); font-weight: 900; letter-spacing: .04em; line-height: 1.08; }
        .band-right { text-align: right; }
    </style>
</head>
<body>
@php
    $edad = $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->age : null;
    $fotoUrl = null;
    if (!empty($fotoDataUri)) {
        $fotoUrl = $fotoDataUri;
    } elseif (!empty($paciente->foto)) {
        $fotoUrl = filter_var($paciente->foto, FILTER_VALIDATE_URL) ? $paciente->foto : url(\Illuminate\Support\Facades\Storage::url($paciente->foto));
    }
    $iniciales = strtoupper(substr((string) $paciente->nombres, 0, 1) . substr((string) $paciente->apellidos, 0, 1));
@endphp

<div class="wrap">
    <div class="card">
        <div class="header">
            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                <tr>
                    <td style="width: 22mm; vertical-align: middle;">
                        <div class="logo">
                            <img src="{{ $logoUrl }}" alt="Logo">
                        </div>
                    </td>
                    <td style="vertical-align: middle;">
                        <div class="brand-name">{{ $empresaNombre }}</div>
                        <div class="branch">{{ $sucursalNombre ?: 'Sucursal' }}</div>
                    </td>
                    <td style="width: 26mm; vertical-align: middle;">
                        <div class="role">Carnet<br>Paciente menor</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="body">
            <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                <tr>
                    <td style="width: 24mm; vertical-align: middle;">
                        <div class="photo">
                            @if($fotoUrl)
                                <img src="{{ $fotoUrl }}" alt="Foto">
                            @else
                                <div style="width:22mm;height:22mm;display:table-cell;vertical-align:middle;text-align:center;">
                                    <span class="initials">{{ $iniciales }}</span>
                                </div>
                            @endif
                        </div>
                        <div style="margin-top: 1.5mm; font-size: 7.4px; color: #6b7280; font-weight: 700;">ID: {{ $paciente->id }}</div>
                    </td>
                    <td style="vertical-align: top; padding-left: 2mm;">
                        <div class="doc">{{ $paciente->documento_identidad ?: 'Documento: —' }}</div>
                        <div class="name">{{ $paciente->nombres }} {{ $paciente->apellidos }}</div>
                        <div class="subline">{{ $paciente->nickname ?: 'Registro de paciente' }}</div>

                        <table width="100%" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-top: 2mm;">
                            <tr>
                                <td class="meta-k">Nac.</td>
                                <td class="meta-v">{{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : '—' }}@if($edad !== null) ({{ $edad }}) @endif</td>
                            </tr>
                            <tr>
                                <td class="meta-k">Tutor</td>
                                <td class="meta-v">{{ $paciente->tutor ? ($paciente->tutor->nombres . ' ' . $paciente->tutor->apellidos) : '—' }}</td>
                            </tr>
                        </table>
                    </td>
                    <td style="width: 18mm; vertical-align: middle; text-align: right;">
                        <div class="qr">
                            <img src="{{ $qrDataUri }}" alt="QR">
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div>
            <table >

            </table>
        </div>
    </div>
</div>
</body>
</html>

