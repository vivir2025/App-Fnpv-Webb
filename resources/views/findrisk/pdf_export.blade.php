<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Tests FINDRISK</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Reporte de Tests FINDRISK</div>
        <div class="subtitle">{{ $sede }}</div>
    </div>
    
    <div class="info">
        <p><strong>Período:</strong> {{ $fechaInicio }} - {{ $fechaFin }}</p>
        <p><strong>Nivel de riesgo:</strong> {{ $nivel_riesgo }}</p>
        <p><strong>Total de tests:</strong> {{ count($tests) }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Identificación</th>
                <th>Paciente</th>
                <th>Edad</th>
                <th>Sede</th>
                <th>IMC</th>
                <th>Perímetro</th>
                <th>Puntaje</th>
                <th>Nivel de Riesgo</th>
                <th>% Riesgo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tests as $test)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($test['created_at'])->format('d/m/Y') }}</td>
                    <td>{{ $test['paciente']['identificacion'] }}</td>
                    <td>{{ $test['paciente']['nombre'] }} {{ $test['paciente']['apellido'] }}</td>
                    <td>{{ $test['edad_calculada'] }}</td>
                    <td>{{ $test['sede']['nombresede'] }}</td>
                    <td>{{ $test['imc'] }}</td>
                    <td>{{ $test['perimetro_abdominal'] }}</td>
                    <td>{{ $test['puntaje_final'] }}</td>
                    <td>{{ $test['interpretacion']['nivel'] }}</td>
                    <td>{{ $test['interpretacion']['riesgo'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Reporte generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
x|