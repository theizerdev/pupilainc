<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido al Sistema Médico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #ddd;
        }
        .credentials {
            background-color: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #4CAF50;
        }
        .warning {
            background-color: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #ffc107;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
        .highlight {
            font-weight: bold;
            color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>¡Bienvenido al Sistema Médico!</h1>
        <p>{{ $empresa->razon_social }} - {{ $sucursal->nombre }}</p>
    </div>

    <div class="content">
        <h2>Estimado/a Dr/a. {{ $medico->nombres }} {{ $medico->apellidos }},</h2>
        
        <p>Nos complace darle la bienvenida a nuestro sistema médico. A continuación, encontrará la información necesaria para acceder a su cuenta:</p>

        <div class="credentials">
            <h3>Datos de Acceso:</h3>
            <p><span class="highlight">Usuario:</span> {{ $user->email }}</p>
            <p><span class="highlight">Contraseña temporal:</span> {{ $password }}</p>
            <p><span class="highlight">URL del sistema:</span> {{ url('/') }}</p>
        </div>

        <div class="warning">
            <strong>⚠️ Importante:</strong>
            <ul>
                <li>Por su seguridad, le recomendamos cambiar su contraseña después del primer inicio de sesión.</li>
                <li>Mantenga sus credenciales de acceso de forma segura y no las comparta con terceros.</li>
                <li>Su cuenta está asociada a la empresa <strong>{{ $empresa->razon_social }}</strong> y la sucursal <strong>{{ $sucursal->nombre }}</strong>.</li>
            </ul>
        </div>

        <h3>Información de su Perfil Médico:</h3>
        <ul>
            <li><strong>Licencia Médica:</strong> {{ $medico->licencia_medica }}</li>
            <li><strong>Años de Experiencia:</strong> {{ $medico->anios_experiencia }}</li>
            <li><strong>Nivel de Experiencia:</strong> {{ $medico->nivel_experiencia }}</li>
            @if($medico->telefono)
                <li><strong>Teléfono:</strong> {{ $medico->telefono }}</li>
            @endif
            @if($medico->direccion)
                <li><strong>Dirección:</strong> {{ $medico->direccion }}</li>
            @endif
        </ul>

        <p>Para cualquier consulta o asistencia técnica, no dude en contactar a nuestro equipo de soporte.</p>

        <p>¡Gracias por formar parte de nuestro equipo médico!</p>

        <p>Atentamente,<br>
        El equipo de {{ $empresa->razon_social }}</p>
    </div>

    <div class="footer">
        <p>Este correo fue generado automáticamente. Por favor no responda a este mensaje.</p>
        <p>Si no solicitó este acceso, por favor contacte al administrador del sistema inmediatamente.</p>
    </div>
</body>
</html>