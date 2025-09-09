<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada - 404</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
        }
        .error-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #dc3545; }
        p { color: #6c757d; margin: 20px 0; }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
        }
        .btn:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>404 - Página no encontrada</h1>
        <p>La página que buscas no existe o ha sido movida.</p>
        <p><strong>Ruta solicitada:</strong> {{ $path ?? 'No disponible' }}</p>
        
        <div>
            <a href="{{ route('dashboard') }}" class="btn">Ir al Dashboard</a>
            <a href="javascript:history.back()" class="btn" style="background-color: #6c757d;">Volver Atrás</a>
        </div>
    </div>
</body>
</html>
