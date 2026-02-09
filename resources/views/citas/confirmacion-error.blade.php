<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error en Confirmación - Sistema Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .confirmation-card {
            max-width: 500px;
            margin: 100px auto;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container">
        <div class="card confirmation-card">
            <div class="card-body text-center p-5">
                <div class="error-icon mb-4">✕</div>
                <h2 class="card-title text-danger mb-4">Error en Confirmación</h2>
                <p class="card-text fs-5">{{ $mensaje ?? 'Ocurrió un error al procesar su confirmación.' }}</p>
                <div class="alert alert-info mt-4">
                    <strong>Nota:</strong> Si necesita ayuda, contáctenos al (0212) 123-4567
                </div>
                <div class="mt-4">
                    <a href="/" class="btn btn-secondary">Volver al inicio</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>