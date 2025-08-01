<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formato de Envío de Muestras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }
        .header {
            width: 100%;
            border-bottom: 1px solid #000;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        .logo {
            width: 150px;
            padding: 10px;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
        }
        .info-box {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 10px;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 7px;
        }
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 2px;
            text-align: center;
            vertical-align: middle;
        }
        .main-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .footer-table td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 9px;
        }
        .footer-label {
            font-weight: bold;
            width: 25%;
        }
        .bg-pink { background-color: #FFB6C1; }
        .bg-blue { background-color: #ADD8E6; }
        .bg-purple { background-color: #DDA0DD; }
        .bg-yellow { background-color: #FFFF99; }
        .bg-green { background-color: #90EE90; }
        .bg-lightpink { background-color: #FFE4E1; }
        .bg-beige { background-color: #F5F5DC; }
        .bg-lavender { background-color: #F0E6FF; }
        .bg-mint { background-color: #F0FFF0; }
        .bg-lightyellow { background-color: #FFFACD; }
        .bg-peach { background-color: #FFE4B5; }
    </style>
</head>
<body>
    <!-- Cabecera -->
    <table class="header-table">
        <tr>
            <td width="20%">
                <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" class="logo">
            </td>
            <td width="60%" class="title">
                FORMATO DE ENVÍO DE MUESTRAS A LABORATORIO CLÍNICO
            </td>
            <td width="20%">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td class="info-box">Código: {{ $envio['codigo'] ?? 'PM-CE-TM-F-01' }}</td>
                    </tr>
                    <tr>
                        <td class="info-box">Fecha: {{ isset($envio['fecha']) ? \Carbon\Carbon::parse($envio['fecha'])->format('d-m-Y') : date('d-m-Y') }}</td>
                    </tr>
                    <tr>
                        <td class="info-box">Versión: {{ $envio['version'] ?? '1' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabla Principal -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="3" style="min-width: 20px;">N</th>
                <th rowspan="3" style="min-width: 120px;">NOMBRES Y APELLIDOS</th>
                <th rowspan="3" style="min-width: 80px;">DOCUMENTO</th>
                <th rowspan="3" style="min-width: 80px;">PROCEDENCIA</th>
                <th rowspan="3" style="min-width: 80px;">FECHA DE NACIMIENTO</th>
                <th colspan="2" class="bg-pink">DIAGNÓSTICO</th>
                <th colspan="5" class="bg-blue">MUESTRAS ENVIADAS</th>
                <th colspan="3" class="bg-purple">TUBO LILA</th>
                <th colspan="5" class="bg-yellow">TUBO AMARILLO</th>
                <th colspan="8" class="bg-green">MUESTRA DE ORINA</th>
                <th colspan="10" class="bg-yellow">PACIENTES NEFRO</th>
            </tr>
            <tr>
                <th rowspan="2" class="bg-lightpink">DM</th>
                <th rowspan="2" class="bg-lightpink">HTA</th>
                <th rowspan="2" class="bg-beige">A</th>
                <th rowspan="2" class="bg-lavender">M</th>
                <th rowspan="2" class="bg-mint">OE</th>
                <th rowspan="2" class="bg-mint">O24H</th>
                <th rowspan="2" class="bg-lightpink">PO</th>
                <th rowspan="2" class="bg-lavender">H3</th>
                <th rowspan="2" class="bg-lavender">HBA1C</th>
                <th rowspan="2" class="bg-lavender">PTH</th>
                <th rowspan="2" class="bg-lightyellow">GLU</th>
                <th rowspan="2" class="bg-lightyellow">CREA</th>
                <th rowspan="2" class="bg-lightyellow">PL</th>
                <th rowspan="2" class="bg-lightyellow">AU</th>
                <th rowspan="2" class="bg-lightyellow">BUN</th>
                <th colspan="2" class="bg-mint">ORINA ESP</th>
                <th colspan="6" class="bg-mint">ORINA24H</th>
                <th colspan="8" class="bg-lightyellow">TUBO AMARILLO</th>
                <th colspan="2" class="bg-peach">FORRADOS</th>
            </tr>
            <tr>
                <th class="bg-mint">RELACION CREA/ALB</th>
                <th class="bg-mint">PL</th>
                <th class="bg-mint">DCRE24H</th>
                <th class="bg-mint">ALB24H</th>
                <th class="bg-mint">BUNO24H</th>
                <th class="bg-mint">PESO</th>
                <th class="bg-mint">TALLA</th>
                <th class="bg-mint">VOLUMEN</th>
                <th class="bg-lightyellow">FER</th>
                <th class="bg-lightyellow">TRA</th>
                <th class="bg-lightyellow">FOSFAT</th>
                <th class="bg-lightyellow">ALB</th>
                <th class="bg-lightyellow">FE</th>
                <th class="bg-lightyellow">TSH</th>
                <th class="bg-lightyellow">P</th>
                <th class="bg-lightyellow">IONOGRAMA</th>
                <th class="bg-peach">B12</th>
                <th class="bg-peach">ACIDO FOLICO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($envio['detalles'] as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detalle['paciente']['nombre'] ?? '' }} {{ $detalle['paciente']['apellido'] ?? '' }}</td>
                    <td>{{ $detalle['paciente']['identificacion'] ?? 'N/A' }}</td>
                    <td>
                        @if(isset($envio['sede']['nombre']))
                            {{ $envio['sede']['nombre'] }}
                        @elseif(isset($envio['sede']['nombresede']))
                            {{ $envio['sede']['nombresede'] }}
                        @elseif(isset($envio['nombresede']))
                            {{ $envio['nombresede'] }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td>
                        @if(isset($detalle['paciente']['fecnacimiento']))
                            {{ \Carbon\Carbon::parse($detalle['paciente']['fecnacimiento'])->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    
                    <!-- DIAGNÓSTICO -->
                    <td class="bg-lightpink">{{ $detalle['dm'] ?? '' }}</td>
                    <td class="bg-lightpink">{{ $detalle['hta'] ?? '' }}</td>
                    
                    <!-- MUESTRAS ENVIADAS -->
                    <td class="bg-beige">{{ $detalle['a'] ?? '' }}</td>
                    <td class="bg-lavender">{{ $detalle['m'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['oe'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['orina_24h'] ?? '' }}</td>
                    <td class="bg-lightpink">{{ $detalle['po'] ?? '' }}</td>
                    
                    <!-- TUBO LILA -->
                    <td class="bg-lavender">{{ $detalle['h3'] ?? '' }}</td>
                    <td class="bg-lavender">{{ $detalle['hba1c'] ?? '' }}</td>
                    <td class="bg-lavender">{{ $detalle['pth'] ?? '' }}</td>
                    
                    <!-- TUBO AMARILLO -->
                    <td class="bg-lightyellow">{{ $detalle['glu'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['crea'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['pl'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['au'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['bun'] ?? '' }}</td>
                    
                    <!-- ORINA ESP -->
                    <td class="bg-mint">{{ $detalle['relacion_crea_alb'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['pl'] ?? '' }}</td>
                    
                    <!-- ORINA24H -->
                    <td class="bg-mint">{{ $detalle['dcre24h'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['alb24h'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['buno24h'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['peso'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['talla'] ?? '' }}</td>
                    <td class="bg-mint">{{ $detalle['volumen'] ?? '' }}</td>
                    
                    <!-- TUBO AMARILLO (PACIENTES NEFRO) -->
                    <td class="bg-lightyellow">{{ $detalle['fer'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['tra'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['fosfat'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['alb'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['fe'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['tsh'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['p'] ?? '' }}</td>
                    <td class="bg-lightyellow">{{ $detalle['ionograma'] ?? '' }}</td>
                    
                    <!-- FORRADOS -->
                    <td class="bg-peach">{{ $detalle['b12'] ?? '' }}</td>
                    <td class="bg-peach">{{ $detalle['acido_folico'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pie de página -->
    <table class="footer-table">
        <tr>
            <td class="footer-label">Lugar de Toma de muestras:</td>
            <td>{{ $envio['lugar_toma_muestras'] ?? '' }}</td>
            <td class="footer-label">Hora salida:</td>
            <td>{{ $envio['hora_salida'] ? \Carbon\Carbon::parse($envio['hora_salida'])->format('H:i') : '' }}</td>
            <td class="footer-label">Fecha de llegada:</td>
            <td>{{ $envio['fecha_llegada'] ? \Carbon\Carbon::parse($envio['fecha_llegada'])->format('d/m/Y') : '' }}</td>
            <td class="footer-label">T°C de Llegada:</td>
            <td>{{ $envio['temperatura_llegada'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="footer-label">Responsable de la Toma:</td>
            <td>
                {{ $envio['responsable_recepcion_id'] ?? '' }}
            </td>
            <td class="footer-label">T°C de Salida:</td>
            <td>{{ $envio['temperatura_salida'] ?? '' }}</td>
            <td class="footer-label">Hora de Llegada:</td>
            <td>{{ $envio['hora_llegada'] ? \Carbon\Carbon::parse($envio['hora_llegada'])->format('H:i') : '' }}</td>
            <td class="footer-label">Lugar de llegada:</td>
            <td>{{ $envio['lugar_llegada'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="footer-label">Fecha de salida:</td>
            <td>{{ $envio['fecha_salida'] ? \Carbon\Carbon::parse($envio['fecha_salida'])->format('d/m/Y') : '' }}</td>
            <td class="footer-label">Respons. del Transporte:</td>
            <td>{{ $envio['responsable_transporte_id'] ?? '' }}</td>
            <td colspan="2" class="footer-label">Respons. recepción muestras:</td>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td class="footer-label">Observaciones:</td>
            <td colspan="7">{{ $envio['observaciones'] ?? '' }}</td>
        </tr>
    </table>
</body>
</html>
