<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Afinamientos</title>
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
        <div class="title">Reporte de Afinamientos</div>
    </div>
    
    <div class="info">
        <p><strong>Período:</strong> {{ $fechaInicio }} - {{ $fechaFin }}</p>
        <p><strong>Total de afinamientos:</strong> {{ count($afinamientos) }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Fecha Tamizaje</th>
                <th>Identificación</th>
                <th>Paciente</th>
                <th>Edad</th>
                <th>P.A. Tamizaje</th>
                <th>Prom. Sistólica</th>
                <th>Prom. Diastólica</th>
                <th>Conducta</th>
                <th>Procedencia</th>
                <th>Promotor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($afinamientos as $afinamiento)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($afinamiento['fecha_tamizaje'])->format('d/m/Y') }}</td>
                    <td>{{ $afinamiento['identificacion_paciente'] }}</td>
                    <td>{{ $afinamiento['nombre_paciente'] }}</td>
                    <td>{{ $afinamiento['edad_paciente'] }}</td>
                    <td>{{ $afinamiento['presion_arterial_tamiz'] }}</td>
                    <td>{{ $afinamiento['presion_sistolica_promedio'] }}</td>
                    <td>{{ $afinamiento['presion_diastolica_promedio'] }}</td>
                    <td>{{ $afinamiento['conducta'] }}</td>
                    <td>{{ $afinamiento['procedencia'] }}</td>
                    <td>{{ $afinamiento['promotor_vida'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Reporte generado el {{ date('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
