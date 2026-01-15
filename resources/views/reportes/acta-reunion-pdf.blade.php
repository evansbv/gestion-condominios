<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acta de Reunión</title>
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
            border-bottom: 3px solid #059669;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #065f46;
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
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
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
            color: #065f46;
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
            background-color: #059669;
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

        /* Estadísticas de Asistencia */
        .asistencia-stats {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            width: 33.33%;
            padding: 10px;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .stat-label {
            font-size: 9px;
            color: #64748b;
            font-weight: bold;
            display: block;
            margin-bottom: 4px;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            display: block;
        }
        .value-total {
            color: #3b82f6;
        }
        .value-asistieron {
            color: #059669;
        }
        .value-porcentaje {
            color: #f59e0b;
        }

        /* Tablas */
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
        .text-center {
            text-align: center;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
        }
        .badge-asistio {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-no-asistio {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Acta Content */
        .acta-content {
            white-space: pre-wrap;
            line-height: 1.8;
            text-align: left;
            color: #1e293b;
        }

        /* Acuerdos */
        .acuerdo-item {
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
            padding: 10px;
            margin: 8px 0;
            border-radius: 4px;
        }
        .acuerdo-descripcion {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .acuerdo-meta {
            font-size: 9px;
            color: #64748b;
        }

        /* Actividades Relacionadas */
        .actividad-item {
            background-color: #fef3c7;
            border-left: 3px solid #f59e0b;
            padding: 8px;
            margin: 6px 0;
            border-radius: 3px;
            font-size: 9px;
        }

        /* Firmas */
        .firmas-section {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
        }
        .firma-grid {
            display: table;
            width: 100%;
            margin-top: 30px;
        }
        .firma-row {
            display: table-row;
        }
        .firma-cell {
            display: table-cell;
            width: 50%;
            padding: 20px;
            text-align: center;
        }
        .firma-line {
            border-top: 1px solid #333;
            width: 200px;
            margin: 50px auto 5px;
        }
        .firma-label {
            font-size: 9px;
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
        <h1>Acta de Reunión</h1>
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
            <div class="metadata-label">Fecha de Ejecución:</div>
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
        <div class="metadata-row">
            <div class="metadata-label">Estado:</div>
            <div class="metadata-value"><strong style="color: #059669;">REALIZADA</strong></div>
        </div>
    </div>

    <!-- Estadísticas de Asistencia -->
    <div class="section">
        <div class="section-title">Estadísticas de Asistencia</div>
        <div class="asistencia-stats">
            <div class="stat-row">
                <div class="stat-cell">
                    <span class="stat-label">CONVOCADOS</span>
                    <span class="stat-value value-total">{{ $reunion->total_convocados }}</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-label">ASISTIERON</span>
                    <span class="stat-value value-asistieron">{{ $reunion->total_asistieron }}</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-label">% ASISTENCIA</span>
                    <span class="stat-value value-porcentaje">
                        {{ $reunion->total_convocados > 0 ? round(($reunion->total_asistieron / $reunion->total_convocados) * 100) : 0 }}%
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Participantes -->
    @if($reunion->participantes && $reunion->participantes->count() > 0)
    <div class="section">
        <div class="section-title">Lista de Participantes</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Nombre Completo</th>
                    <th style="width: 15%;">Vivienda</th>
                    <th style="width: 10%;" class="text-center">Asistencia</th>
                    <th style="width: 35%;">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reunion->participantes as $index => $participante)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $participante->nombres }} {{ $participante->apellido_paterno }} {{ $participante->apellido_materno }}</td>
                    <td class="text-center"><strong>{{ $participante->vivienda->numero ?? 'N/A' }}</strong></td>
                    <td class="text-center">
                        <span class="badge {{ $participante->pivot->asistio ? 'badge-asistio' : 'badge-no-asistio' }}">
                            {{ $participante->pivot->asistio ? 'ASISTIÓ' : 'NO ASISTIÓ' }}
                        </span>
                    </td>
                    <td>{{ $participante->pivot->observaciones ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Orden del Día -->
    <div class="section">
        <div class="section-title">Orden del Día Tratado</div>
        <div class="section-content">
            <div class="acta-content">{{ $reunion->orden_dia }}</div>
        </div>
    </div>

    <!-- Acta -->
    @if($reunion->acta)
    <div class="section">
        <div class="section-title">Acta de la Reunión</div>
        <div class="section-content">
            <div class="acta-content">{{ $reunion->acta }}</div>
        </div>
    </div>
    @endif

    <!-- Acuerdos -->
    @if($reunion->acuerdos && count($reunion->acuerdos) > 0)
    <div class="section">
        <div class="section-title">Acuerdos y Resoluciones</div>
        @foreach($reunion->acuerdos as $index => $acuerdo)
        <div class="acuerdo-item">
            <div class="acuerdo-descripcion">{{ $index + 1 }}. {{ $acuerdo['descripcion'] }}</div>
            <div class="acuerdo-meta">
                @if(isset($acuerdo['responsable_id']))
                    <strong>Responsable:</strong> Usuario #{{ $acuerdo['responsable_id'] }}
                @endif
                @if(isset($acuerdo['fecha_limite']))
                    • <strong>Fecha límite:</strong> {{ \Carbon\Carbon::parse($acuerdo['fecha_limite'])->format('d/m/Y') }}
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Actividades Relacionadas -->
    @if($reunion->actividades && $reunion->actividades->count() > 0)
    <div class="section">
        <div class="section-title">Actividades Generadas</div>
        @foreach($reunion->actividades as $actividad)
        <div class="actividad-item">
            <strong>{{ $actividad->titulo }}</strong> ({{ $actividad->tipo }}) - Estado: {{ $actividad->estado }}
        </div>
        @endforeach
    </div>
    @endif

    <!-- Firmas -->
    <div class="firmas-section">
        <p style="text-align: center; margin-bottom: 20px;">
            <strong>FIRMAS DE APROBACIÓN</strong>
        </p>
        <p style="text-align: justify; font-size: 10px; margin-bottom: 20px;">
            Los abajo firmantes certifican que el contenido del presente acta refleja fielmente
            lo tratado y acordado en la reunión, comprometiéndose al cumplimiento de los acuerdos establecidos.
        </p>

        <div class="firma-grid">
            <div class="firma-row">
                <div class="firma-cell">
                    <div class="firma-line"></div>
                    <div class="firma-label">
                        <!--
                        <strong>{{ $reunion->convocante->name ?? 'Convocante' }}</strong><br>
                        {{ $reunion->convocante->rol ?? 'Administración' }}<br>
                        -->
                        <strong>Presidente/a</strong><br>
                        Directorio<br>
                        Firma y Sello
                    </div>
                </div>
                <div class="firma-cell">
                    <div class="firma-line"></div>
                    <div class="firma-label">
                        <strong>Secretario/a de Actas</strong><br>
                        Directorio<br>
                        Firma
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 40px; text-align: center; font-size: 9px; color: #64748b;">
            <p>Firmas adicionales de conformidad en página anexa si corresponde</p>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este documento constituye el acta oficial de la reunión y debe ser conservado en los archivos del condominio.</p>
        <p>Sistema de Gestión de Condominios - Documento generado automáticamente</p>
    </div>
</body>
</html>
