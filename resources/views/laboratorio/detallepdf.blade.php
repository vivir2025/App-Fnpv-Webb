<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Formato de Envío de Muestras</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 20px;
            margin: 5px;
            padding: 0;
            line-height: 1.2;
        }
        
        .header {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
        }
        
        .header-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .logo {
            width: 120px;
            height: auto;
            padding: 8px;
        }
        
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            padding: 10px;
        }
        
        .info-box {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11px;
            margin: 2px 0;
        }
        
        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 13px; /* Unificado a 13px como text-left */
            table-layout: fixed; /* Fija el ancho de la tabla */
        }
        
        .main-table th, .main-table td {
            border: 1px solid #000;
            padding: 6px 2px; /* Reducido padding para compensar el font-size más grande */
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
            height: 32px; /* Altura reducida para compensar */
            overflow: hidden;
            line-height: 1.1; /* Línea más compacta */
        }
        
        .main-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 9px; /* Headers más pequeños para que quepan */
            height: 18px; /* Altura para headers más compacta */
            line-height: 1.0;
            padding: 2px 1px; /* Padding más pequeño para headers */
        }
        
        /* Altura específica para filas de datos */
        .main-table tbody tr {
            height: 35px; /* Altura fija para filas de datos */
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .footer-table td {
            border: 1px solid #000;
            padding: 10px 6px; /* Aumentado padding */
            font-size: 12px; /* Aumentado de 11px a 12px */
        }
        
        .footer-label {
            font-weight: bold;
            width: 15%;
            background-color: #f8f8f8;
        }
        
        /* Colores de fondo */
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
        
        /* Ajustes para columnas específicas con anchos fijos optimizados */
        .col-numero { width: 2.2%; }
        .col-nombre { width: 11%; } /* Reducido ligeramente */
        .col-documento { width: 5.5%; } /* Reducido */
        .col-procedencia { width: 7.5%; } /* Reducido */
        .col-fecha { width: 5.5%; } /* Reducido */
        .col-tiny { width: 1.8%; }      /* Reducido para columnas muy pequeñas */
        .col-small { width: 2.2%; }     /* Reducido para columnas pequeñas */
        .col-medium { width: 2.8%; }    /* Reducido para columnas medianas */
        .col-large { width: 3.5%; }     /* Reducido para columnas grandes */
        
        /* Estilos para texto en celdas - TODOS CON 13px */
        .text-left {
            text-align: left !important;
            font-size: 13px; /* Mantenido */
            padding-left: 3px; /* Reducido padding */
        }
        
        .text-center {
            text-align: center !important;
            font-size: 13px; /* Cambiado de 10px a 13px */
        }
        
        /* Estilos para impresión */
        @media print {
            body {
                font-size: 12px;
                margin: 2px;
            }
            .main-table {
                font-size: 11px; /* Reducido para impresión */
            }
            .main-table th {
                font-size: 7px;
                height: 16px;
                padding: 1px;
            }
            .main-table th, .main-table td {
                padding: 4px 1px;
                height: 28px;
            }
            .main-table tbody tr {
                height: 30px;
            }
            .footer-table td {
                font-size: 10px;
                padding: 6px 3px;
            }
            .title {
                font-size: 14px;
            }
            .info-box {
                font-size: 9px;
            }
            .text-left, .text-center {
                font-size: 11px;
            }
        }
        
        /* Responsive para pantallas pequeñas */
        @media screen and (max-width: 1200px) {
            .main-table {
                font-size: 11px; /* Reducido */
            }
            .main-table th {
                font-size: 8px;
            }
            .main-table th, .main-table td {
                padding: 4px 1px;
                height: 28px;
            }
            .main-table tbody tr {
                height: 30px;
            }
            .text-left, .text-center {
                font-size: 11px;
            }
        }
        
        /* Para pantallas muy anchas, permitir más espacio */
        @media screen and (min-width: 1600px) {
            .main-table {
                font-size: 14px; /* Ligeramente más grande */
            }
            .main-table th {
                font-size: 10px;
                height: 20px;
            }
            .main-table th, .main-table td {
                height: 38px;
                padding: 8px 3px;
            }
            .main-table tbody tr {
                height: 40px;
            }
            .text-left, .text-center {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <!-- Cabecera -->
    <table class="header-table">
        <tr>
            <td width="20%">
                <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" class="logo" alt="Logo">
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
                <th rowspan="3" class="col-numero">N°</th>
                <th rowspan="3" class="col-nombre">NOMBRES Y APELLIDOS</th>
                <th rowspan="3" class="col-documento">DOC</th>
                <th rowspan="3" class="col-procedencia">PROCED</th>
                <th rowspan="3" class="col-fecha">F.NAC</th>
                <th colspan="2" class="bg-pink">DIAG</th>
                <th colspan="5" class="bg-blue">MUESTRAS</th>
                <th colspan="3" class="bg-purple">LILA</th>
                <th colspan="5" class="bg-yellow">AMARILLO</th>
                <th colspan="8" class="bg-green">ORINA</th>
                <th colspan="10" class="bg-yellow">NEFRO</th>
            </tr>
            <tr>
                <th rowspan="2" class="bg-lightpink col-tiny">DM</th>
                <th rowspan="2" class="bg-lightpink col-tiny">HTA</th>
                <th rowspan="2" class="bg-beige col-tiny">A</th>
                <th rowspan="2" class="bg-lavender col-tiny">M</th>
                <th rowspan="2" class="bg-mint col-tiny">OE</th>
                <th rowspan="2" class="bg-mint col-small">O24H</th>
                <th rowspan="2" class="bg-lightpink col-tiny">PO</th>
                <th rowspan="2" class="bg-lavender col-tiny">H3</th>
                <th rowspan="2" class="bg-lavender col-small">HBA1C</th>
                <th rowspan="2" class="bg-lavender col-tiny">PTH</th>
                <th rowspan="2" class="bg-lightyellow col-tiny">GLU</th>
                <th rowspan="2" class="bg-lightyellow col-small">CREA</th>
                <th rowspan="2" class="bg-lightyellow col-tiny">PL</th>
                <th rowspan="2" class="bg-lightyellow col-tiny">AU</th>
                <th rowspan="2" class="bg-lightyellow col-tiny">BUN</th>
                <th colspan="2" class="bg-mint">ESP</th>
                <th colspan="6" class="bg-mint">24H</th>
                <th colspan="8" class="bg-lightyellow">AMARILLO</th>
                <th colspan="2" class="bg-peach">FORR</th>
            </tr>
            <tr>
                <th class="bg-mint col-medium">CREA/ALB</th>
                <th class="bg-mint col-tiny">PL</th>
                <th class="bg-mint col-medium">DCRE24H</th>
                <th class="bg-mint col-medium">ALB24H</th>
                <th class="bg-mint col-medium">BUNO24H</th>
                <th class="bg-mint col-tiny">PESO</th>
                <th class="bg-mint col-tiny">TALLA</th>
                <th class="bg-mint col-small">VOL</th>
                <th class="bg-lightyellow col-tiny">FER</th>
                <th class="bg-lightyellow col-tiny">TRA</th>
                <th class="bg-lightyellow col-small">FOSF</th>
                <th class="bg-lightyellow col-tiny">ALB</th>
                <th class="bg-lightyellow col-tiny">FE</th>
                <th class="bg-lightyellow col-tiny">TSH</th>
                <th class="bg-lightyellow col-tiny">P</th>
                <th class="bg-lightyellow col-small">IONO</th>
                <th class="bg-peach col-tiny">B12</th>
                <th class="bg-peach col-small">A.FOL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($envio['detalles'] as $index => $detalle)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-left">{{ $detalle['paciente']['nombre'] ?? '' }} {{ $detalle['paciente']['apellido'] ?? '' }}</td>
                    <td class="text-center">{{ $detalle['paciente']['identificacion'] ?? 'N/A' }}</td>
                    <td class="text-left">
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
                    <td class="text-center">
                        @if(isset($detalle['paciente']['fecnacimiento']))
                            {{ \Carbon\Carbon::parse($detalle['paciente']['fecnacimiento'])->format('d/m/Y') }}
                        @else
                            N/A
                        @endif
                    </td>
                    
                    <!-- DIAGNÓSTICO -->
                    <td class="bg-lightpink text-center">{{ $detalle['dm'] ?? '' }}</td>
                    <td class="bg-lightpink text-center">{{ $detalle['hta'] ?? '' }}</td>
                    
                    <!-- MUESTRAS ENVIADAS -->
                    <td class="bg-beige text-center">{{ $detalle['a'] ?? '' }}</td>
                    <td class="bg-lavender text-center">{{ $detalle['m'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['oe'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['orina_24h'] ?? '' }}</td>
                    <td class="bg-lightpink text-center">{{ $detalle['po'] ?? '' }}</td>
                    
                    <!-- TUBO LILA -->
                    <td class="bg-lavender text-center">{{ $detalle['h3'] ?? '' }}</td>
                    <td class="bg-lavender text-center">{{ $detalle['hba1c'] ?? '' }}</td>
                    <td class="bg-lavender text-center">{{ $detalle['pth'] ?? '' }}</td>
                    
                    <!-- TUBO AMARILLO -->
                    <td class="bg-lightyellow text-center">{{ $detalle['glu'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['crea'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['pl'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['au'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['bun'] ?? '' }}</td>
                    
                    <!-- ORINA ESP -->
                    <td class="bg-mint text-center">{{ $detalle['relacion_crea_alb'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['pl'] ?? '' }}</td>
                    
                    <!-- ORINA24H -->
                    <td class="bg-mint text-center">{{ $detalle['dcre24h'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['alb24h'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['buno24h'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['peso'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['talla'] ?? '' }}</td>
                    <td class="bg-mint text-center">{{ $detalle['volumen'] ?? '' }}</td>
                    
                    <!-- TUBO AMARILLO (PACIENTES NEFRO) -->
                    <td class="bg-lightyellow text-center">{{ $detalle['fer'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['tra'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['fosfat'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['alb'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['fe'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['tsh'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['p'] ?? '' }}</td>
                    <td class="bg-lightyellow text-center">{{ $detalle['ionograma'] ?? '' }}</td>
                    
                    <!-- FORRADOS -->
                    <td class="bg-peach text-center">{{ $detalle['b12'] ?? '' }}</td>
                    <td class="bg-peach text-center">{{ $detalle['acido_folico'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pie de página -->
    <table class="footer-table">
        <tr>
            <td class="footer-label">Lugar de Toma:</td>
            <td>{{ $envio['lugar_toma_muestras'] ?? '' }}</td>
            <td class="footer-label">Hora salida:</td>
            <td>{{ $envio['hora_salida'] ? \Carbon\Carbon::parse($envio['hora_salida'])->format('H:i') : '' }}</td>
            <td class="footer-label">Fecha llegada:</td>
            <td>{{ $envio['fecha_llegada'] ? \Carbon\Carbon::parse($envio['fecha_llegada'])->format('d/m/Y') : '' }}</td>
            <td class="footer-label">T°C Llegada:</td>
            <td>{{ $envio['temperatura_llegada'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="footer-label">Responsable Toma:</td>
            <td>{{ $envio['responsable_recepcion_id'] ?? '' }}</td>
            <td class="footer-label">T°C Salida:</td>
            <td>{{ $envio['temperatura_salida'] ?? '' }}</td>
            <td class="footer-label">Hora Llegada:</td>
            <td>{{ $envio['hora_llegada'] ? \Carbon\Carbon::parse($envio['hora_llegada'])->format('H:i') : '' }}</td>
            <td class="footer-label">Lugar llegada:</td>
            <td>{{ $envio['lugar_llegada'] ?? '' }}</td>
        </tr>
        <tr>
            <td class="footer-label">Fecha salida:</td>
            <td>{{ $envio['fecha_salida'] ? \Carbon\Carbon::parse($envio['fecha_salida'])->format('d/m/Y') : '' }}</td>
            <td class="footer-label">Resp. Transporte:</td>
            <td>{{ $envio['responsable_transporte_id'] ?? '' }}</td>
            <td class="footer-label">Resp. Recepción:</td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td class="footer-label">Observaciones:</td>
            <td colspan="7">{{ $envio['observaciones'] ?? '' }}</td>
        </tr>
    </table>
</body>
</html>
