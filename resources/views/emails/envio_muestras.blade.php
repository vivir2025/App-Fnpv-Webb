<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Envío de Muestras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
        }
        h1 {
            color: #2c3e50;
        }
        .info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" alt="Logo" class="logo">
            <h1>Envío de Muestras a Laboratorio</h1>
        </div>
        
        <div class="info">
            <p><strong>Código:</strong> {{ $envio['codigo'] ?? 'N/A' }}</p>
            <p><strong>Fecha:</strong> {{ isset($envio['fecha']) ? \Carbon\Carbon::parse($envio['fecha'])->format('d/m/Y') : 'N/A' }}</p>
            <p><strong>Sede:</strong> 
                @if(isset($envio['sede']['nombre']))
                    {{ $envio['sede']['nombre'] }}
                @elseif(isset($envio['sede']['nombresede']))
                    {{ $envio['sede']['nombresede'] }}
                @elseif(isset($envio['nombresede']))
                    {{ $envio['nombresede'] }}
                @else
                    N/A
                @endif
            </p>
            <p><strong>Cantidad de muestras:</strong> {{ count($envio['detalles'] ?? []) }}</p>
            <p><strong>Fecha de salida:</strong> {{ isset($envio['fecha_salida']) ? \Carbon\Carbon::parse($envio['fecha_salida'])->format('d/m/Y') : 'No registrada' }}</p>
        </div>
        
        <p>Adjunto a este correo encontrará el formato de envío de muestras a laboratorio clínico.</p>
        <p>Por favor, revise el documento adjunto para obtener información detallada sobre las muestras enviadas.</p>
        
        <div class="footer">
            <p>Este es un correo automático, por favor no responda a este mensaje.</p>
            <p>© {{ date('Y') }} Fundación Nacer Para Vivir IPS. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
