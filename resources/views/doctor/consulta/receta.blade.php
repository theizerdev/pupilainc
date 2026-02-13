<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica - {{ $paciente->nombres }} {{ $paciente->apellidos }}</title>
    <style>
        @page {
            margin: 2cm;
            font-family: Arial, sans-serif;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header-table {
            width: 100%;
        }
        .logo-container {
            width: 20%;
            text-align: left;
        }
        .logo-img {
            max-width: 100px;
            max-height: 60px;
        }
        .clinic-info {
            width: 60%;
            text-align: center;
        }
        .clinic-name {
            font-size: 18px;
            font-weight: bold;
            color: #0056b3;
            margin: 0;
        }
        .clinic-details {
            font-size: 10px;
            color: #666;
            margin: 2px 0;
        }
        .date-info {
            width: 20%;
            text-align: right;
            font-size: 11px;
        }
        .patient-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .patient-table {
            width: 100%;
        }
        .label {
            font-weight: bold;
            color: #555;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #0056b3;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .medications-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .medications-table th {
            background-color: #e9ecef;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ccc;
            font-size: 11px;
        }
        .medications-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .instructions-box {
            border: 1px solid #ddd;
            padding: 10px;
            min-height: 100px;
            margin-bottom: 30px;
            white-space: pre-wrap;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #777;
        }
        .signature-section {
            margin-top: 50px;
            text-align: center;
        }
        .signature-line {
            display: inline-block;
            width: 200px;
            border-top: 1px solid #333;
            margin-bottom: 5px;
        }
        .doctor-name {
            font-weight: bold;
            font-size: 12px;
        }
        .doctor-details {
            font-size: 11px;
        }
    </style>
</head>
<body>

    <div class="header">
        <table class="header-table">
            <tr>
                <td class="logo-container">
                    <!-- Placeholder for logo -->
                    @if(file_exists(public_path('logo/logo.png')))
                        <img src="{{ public_path('logo/logo.png') }}" class="logo-img" alt="Logo">
                    @else
                        <h3>MEDICAL</h3>
                    @endif
                </td>
                <td class="clinic-info">
                    <h1 class="clinic-name">{{ $consulta->empresa->razon_social ?? 'Clínica Oftalmológica' }}</h1>
                    <p class="clinic-details">{{ $consulta->sucursal->direccion ?? $consulta->empresa->direccion ?? 'Dirección de la clínica' }}</p>
                    <p class="clinic-details">Tel: {{ $consulta->sucursal->telefono ?? $consulta->empresa->telefono ?? '' }}</p>
                </td>
                <td class="date-info">
                    <strong>Fecha:</strong><br>
                    {{ $fecha->format('d/m/Y') }}<br>
                    <strong>Hora:</strong> {{ $fecha->format('H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="patient-info">
        <table class="patient-table">
            <tr>
                <td><span class="label">Paciente:</span> {{ $paciente->nombres }} {{ $paciente->apellidos }}</td>
                <td><span class="label">ID:</span> {{ $paciente->documento_identidad }}</td>
                <td><span class="label">Edad:</span> {{ $paciente->edad_formateada ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if(count($medicamentos) > 0)
        <div class="section-title">RECETA DE MEDICAMENTOS</div>
        <table class="medications-table">
            <thead>
                <tr>
                    <th width="30%">Medicamento</th>
                    <th width="15%">Dosis</th>
                    <th width="15%">Frecuencia</th>
                    <th width="15%">Duración</th>
                    <th width="25%">Instrucciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($medicamentos as $med)
                    <tr>
                        <td><strong>{{ $med['nombre'] }}</strong></td>
                        <td>{{ $med['dosis'] }}</td>
                        <td>{{ $med['frecuencia'] }}</td>
                        <td>{{ $med['duracion'] }}</td>
                        <td>{{ $med['instrucciones'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="section-title">INDICACIONES / PLAN DE TRATAMIENTO</div>
    <div class="instructions-box">
        {{ $plan_tratamiento ?? 'Sin indicaciones adicionales.' }}
    </div>

    <div class="signature-section">
        <br><br><br>
        <div class="signature-line"></div>
        <div class="doctor-name">Dr(a). {{ $medico->nombres }} {{ $medico->apellidos }}</div>
        <div class="doctor-details">
            @if($medico->especialidad_principal)
                {{ $medico->especialidad_principal->nombre }}<br>
            @endif
            Licencia: {{ $medico->licencia_medica }}
        </div>
    </div>

    <div class="footer">
        Este documento es una receta médica válida generada electrónicamente el {{ date('d/m/Y H:i') }}.
    </div>

</body>
</html>
