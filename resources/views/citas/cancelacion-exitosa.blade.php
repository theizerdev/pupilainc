<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cita Cancelada - Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .confirmation-card {
            max-width: 500px;
            margin: 100px auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .cancel-icon {
            font-size: 4rem;
            color: #ffc107;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="card confirmation-card">
            <div class="card-body text-center p-5">
                <div class="cancel-icon mb-4">○</div>
                <h2 class="card-title text-warning mb-4">Cita Cancelada</h2>
                <p class="card-text fs-5">{{ $mensaje ?? 'Su cita ha sido cancelada exitosamente.' }}</p>
                <div class="alert alert-info mt-4">
                    <strong>¿Desea reprogramar?</strong><br>
                    Llámenos al (0212) 123-4567 para agendar una nueva cita.
                </div>
                <div class="mt-4">
                    <a href="/" class="btn btn-primary">Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>