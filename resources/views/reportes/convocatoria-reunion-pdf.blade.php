<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocatoria de Reunión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0 0 8px 0;
            font-size: 26px;
            text-transform: uppercase;
        }
        .header p {
            color: #64748b;
            margin: 3px 0;
            font-size: 11px;
        }

        /* Metadata Section */
        .metadata {
            background-color: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .metadata-row {
            margin: 8px 0;
            display: flex;
            align-items: flex-start;
        }
        .metadata-label {
            font-weight: bold;
            color: #1e40af;
            width: 150px;
            flex-shrink: 0;
        }
        .metadata-value {
            color: #1e293b;
            flex-grow: 1;
        }

        /* Sections */
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #2563eb;
            color: white;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 12px;
            border-radius: 4px;
        }
        .section-content {
            padding: 10px;
            background-color: #f8fafc;
            border-radius: 4px;
            border: 1px solid #e5e7eb;
        }

        /* Orden del Día */
        .orden-dia {
            white-space: pre-wrap;
            line-height: 1.8;
            color: #1e293b;
        }

        /* Tabla de Convocados */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 9px;
        }
        th {
            background-color: #f1f5f9;
            padding: 6px 4px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #e5e7eb;
        }
        td {
            padding: 5px 4px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }

        /* Instrucciones */
        .instrucciones {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 12px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .instrucciones-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
            font-size: 12px;
        }
        .instrucciones ul {
            margin: 5px 0;
            padding-left: 20px;
            color: #78350f;
        }
        .instrucciones li {
            margin: 4px 0;
        }

        /* Firma */
        .firma-section {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
        }
        .firma-box {
            margin-top: 50px;
            text-align: center;
        }
        .firma-line {
            border-top: 1px solid #333;
            width: 250px;
            margin: 0 auto 5px;
        }
        .firma-label {
            font-size: 10px;
            color: #64748b;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h1>Convocatoria de Reunión</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Documento generado: {{ $fecha_generacion }}</p>
    </div>

    <!-- Metadata -->
    <div class="metadata">
        <div class="metadata-row">
            <div class="metadata-label">Título:</div>
            <div class="metadata-value"><strong>{{ $reunion->titulo }}</strong></div>
        </div>
        <div class="metadata-row">
            <div class="metadata-label">Fecha de Convocatoria:</div>
            <div class="metadata-value">{{ $reunion->fecha_convocatoria->format('d/m/Y') }}</div>
        </div>
        <div class="metadata-row">
            <div class="metadata-label">Fecha de Reunión:</div>
            <div class="metadata-value">
                <strong>{{ $reunion->fecha_reunion->format('d/m/Y') }}</strong> a las
                <strong>{{ $reunion->fecha_reunion->format('H:i') }}</strong>
            </div>
        </div>
        <div class="metadata-row">
            <div class="metadata-label">Lugar:</div>
            <div class="metadata-value">{{ $reunion->lugar }}</div>
        </div>
        <div class="metadata-row">
            <div class="metadata-label">Convocada por:</div>
            <div class="metadata-value">{{ $reunion->convocante->name ?? 'N/A' }}</div>
        </div>
    </div>

    <!-- Descripción -->
    <div class="section">
        <div class="section-title">Descripción</div>
        <div class="section-content">
            {{ $reunion->descripcion }}
        </div>
    </div>

    <!-- Orden del Día -->
    <div class="section">
        <div class="section-title">Orden del Día</div>
        <div class="section-content">
            <div class="orden-dia">{{ $reunion->orden_dia }}</div>
        </div>
    </div>

    <!-- Instrucciones -->
    <div class="instrucciones">
        <div class="instrucciones-title">Instrucciones Importantes:</div>
        <ul>
            <li>Se solicita puntualidad para el inicio de la reunión.</li>
            <li>Favor confirmar su asistencia con anticipación.</li>
            <li>En caso de no poder asistir, se ruega notificar a la administración.</li>
            <li>Traer copia del presente documento el día de la reunión.</li>
        </ul>
    </div>

    <!-- Lista de Convocados -->
    @if($reunion->participantes && $reunion->participantes->count() > 0)
    <div class="section">
        <div class="section-title">Lista de Convocados ({{ $reunion->participantes->count() }})</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 40%;">Nombre Completo</th>
                    <th style="width: 25%;">Vivienda</th>
                    <th style="width: 30%;">Dirección</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reunion->participantes as $index => $participante)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $participante->nombres }} {{ $participante->apellido_paterno }} {{ $participante->apellido_materno }}</td>
                    <td style="text-align: center;"><strong>{{ $participante->vivienda->numero ?? 'N/A' }}</strong></td>
                    <td>{{ $participante->vivienda->direccion ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Firma -->
    <div class="firma-section">
        <p style="text-align: center; margin-bottom: 10px;">
            Sin otro particular, le saludo atentamente.
        </p>

        <div class="firma-box">
            <div class="firma-line"></div>
            <div class="firma-label">
                <strong>{{ $reunion->convocante->name ?? 'Convocante' }}</strong><br>
                {{ $reunion->convocante->rol ?? 'Administración' }}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Esta convocatoria es de carácter oficial y debe ser conservada por el destinatario.</p>
        <p>Sistema de Gestión de Condominios - Documento generado automáticamente</p>
    </div>
</body>
</html>
