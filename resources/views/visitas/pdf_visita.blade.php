<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Visita Domiciliaria - {{ $visita['nombre_apellido'] }}</title>
    <style type="text/css">
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        fieldset {
            border: 1px ridge #0f0fef;
            margin-bottom: 15px;
            padding: 10px;
        }
        legend {
            text-align: left;
            width: inherit;
            padding: 0 10px;
            border-bottom: none;
            font-size: 16px;
            font-weight: bold;
        }
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 10px;
            text-align: center;
        }
        .form-group {
            padding: 5px;
            box-sizing: border-box;
        }
        .col-md-3 {
            width: 25%;
        }
        .col-md-4 {
            width: 33.33%;
        }
        .col-md-5 {
            width: 41.66%;
        }
        .col-md-6 {
            width: 50%;
        }
        .col-md-7 {
            width: 58.33%;
        }
        .col-md-12 {
            width: 100%;
        }
        strong {
            display: block;
            margin-bottom: 5px;
        }
        .header {
            border: ridge #0f0fef 1px;
            text-align: center;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 25%;
        }
        .header-title {
            width: 50%;
            text-align: center;
        }
        .header-title h3 {
            margin: 5px 0;
        }
        img.firma-img, img.foto-riesgo-img {
            max-width: 300px;
            max-height: 400px;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">Imprimir PDF</button>
        <button onclick="window.history.back()" style="padding: 10px; background: #f44336; color: white; border: none; border-radius: 4px; cursor: pointer;">Regresar</button>
    </div>

    <div class="bg-white">
        <div class="header">
            <div class="header-logo">
                <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" width="200px" />
            </div>
            <div class="header-title">
                <h3>FUNDACIÓN NACER PARA<br>VIVIR IPS</h3>
                <small>VISITA DOMICILIARIA</small>
            </div>
            <div class="header-logo">
                <img src="https://nacerparavivir.org/wp-content/uploads/2023/12/Logo_Section1home-8.png" width="200px" />
            </div>
        </div>

        <div id="data_visitas">
            <fieldset>
                <legend>INFORMACIÓN</legend>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <strong>Nombre del Paciente</strong>
                        {{ $visita['nombre_apellido'] }}
                    </div>
                    <div class="form-group col-md-3">
                        <strong>Identificación</strong>
                        {{ $visita['identificacion'] }}
                    </div>
                    <div class="form-group col-md-3">
                        <strong>Fecha de visita</strong>
                        {{ \Carbon\Carbon::parse($visita['fecha'])->format('d/m/Y') }}
                    </div>
                    <div class="form-group col-md-3">
                        <strong>TELÉFONO</strong>
                        {{ $visita['telefono'] ?? 'No registrado' }}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <strong>ZONA</strong>
                        {{ $visita['zona'] ?? 'No especificada' }}
                    </div>
                    <div class="form-group col-md-3">
                        <strong>FAMILIAR</strong>
                        {{ $visita['familiar'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>ANTECEDENTES</legend>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <strong>HTA</strong>
                        {{ $visita['hta'] ?? 'No registrado' }}
                    </div>
                    <div class="form-group col-md-4">
                        <strong>DM</strong>
                        {{ $visita['dm'] ?? 'No registrado' }}
                    </div>
                    <div class="form-group col-md-4">
                        <strong>GLUCOMETRÍA</strong>
                        {{ $visita['glucometria'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>MEDIDAS ANTROPOLÓGICAS</legend>
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <strong>PESO</strong>
                        {{ $visita['peso'] ?? 'No registrado' }} kg
                    </div>
                    <div class="form-group col-md-3">
                        <strong>TALLA</strong>
                        {{ $visita['talla'] ?? 'No registrado' }} cm
                    </div>
                    <div class="form-group col-md-3">
                        <strong>IMC</strong>
                        {{ $visita['imc'] ?? 'No registrado' }}
                    </div>
                    <div class="form-group col-md-3">
                        <strong>PERIMETRO ABDOMINAL</strong>
                        {{ $visita['perimetro_abdominal'] ?? 'No registrado' }} cm
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>SIGNOS VITALES</legend>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <strong>FRECUENCIA CARDIACA</strong>
                        {{ $visita['frecuencia_cardiaca'] ?? 'No registrado' }}
                    </div>
                    <div class="form-group col-md-4">
                        <strong>FRECUENCIA RESPIRATORIA</strong>
                        {{ $visita['frecuencia_respiratoria'] ?? 'No registrado' }}
                    </div>
                    <div class="form-group col-md-4">
                        <strong>TENSIÓN ARTERIAL</strong>
                        {{ $visita['tension_arterial'] ?? 'No registrado' }}
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <strong>TEMPERATURA</strong>
                        {{ $visita['temperatura'] ?? 'No registrado' }} °C
                    </div>
                    <div class="form-group col-md-4">
                        <strong>ABANDONO SOCIAL</strong>
                        {{ $visita['abandono_social'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>MOTIVOS POR EL CUAL NO ASISTE</legend>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        {{ $visita['motivo'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>MEDICAMENTOS QUE SE ENCUENTRAN</legend>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        @if(isset($visita['medicamentos']) && is_array($visita['medicamentos']) && count($visita['medicamentos']) > 0)
                            <ul style="list-style-type: none; padding: 0; text-align: left;">
                                @foreach($visita['medicamentos'] as $medicamento)
                                    <li>
                                        <strong style="display: inline;">{{ is_array($medicamento) && isset($medicamento['nombmedicamento']) ? $medicamento['nombmedicamento'] : 'Medicamento sin nombre' }}</strong>
                                        @if(is_array($medicamento) && isset($medicamento['pivot']) && is_array($medicamento['pivot']) && isset($medicamento['pivot']['indicaciones']))
                                            - {{ $medicamento['pivot']['indicaciones'] }}
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            No se registraron medicamentos
                        @endif
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>FACTORES DE RIESGO</legend>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        {{ $visita['factores'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>CONDUCTAS</legend>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        {{ $visita['conductas'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>NOVEDADES</legend>
                <div class="form-row">
                    <div class="form-group col-md-12">
                        {{ $visita['novedades'] ?? 'No registrado' }}
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>RESPONSABLE</legend>
                <div class="form-row">
                    <div class="form-group col-md-7">
                        <strong>ENCARGADO DE LA VISITA</strong>
                        {{ $visita['encargado'] ?? auth()->user()->name ?? 'No registrado' }}
                    </div>
                    <div class="form-group col-md-5">
                        <strong>PRÓXIMO CONTROL</strong>
                        @if(isset($visita['proximo_control']))
                            {{ \Carbon\Carbon::parse($visita['proximo_control'])->format('d/m/Y') }}
                        @else
                            No registrado
                        @endif
                    </div>
                </div>
            </fieldset>

            <fieldset>
                <legend>RIESGO_FIRMA</legend>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <strong>Riesgo_Fotográfico</strong>
                        @if(isset($visita['riesgo_fotografico_url']) && $visita['riesgo_fotografico_url'])
                            <img src="{{ $visita['riesgo_fotografico_url'] }}" alt="Foto de riesgo" class="foto-riesgo-img">
                        @else
                            No disponible
                        @endif
                    </div>
                    <div class="form-group col-md-6">
                        <strong>Firma_foto</strong>
                        @if(isset($visita['firma_url']) && $visita['firma_url'])
                            <img src="{{ $visita['firma_url'] }}" alt="Firma del paciente" class="firma-img">
                        @else
                            No disponible
                        @endif
                    </div>
                </div>
            </fieldset>
        </div>
    </div>
</body>
</html>
