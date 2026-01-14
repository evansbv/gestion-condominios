<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Aportes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0 0 5px 0;
            font-size: 24px;
        }
        .header p {
            color: #64748b;
            margin: 3px 0;
            font-size: 11px;
        }

        /* Estadísticas Globales */
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            width: 25%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            text-align: center;
            vertical-align: top;
        }
        .stat-label {
            font-weight: bold;
            color: #64748b;
            font-size: 9px;
            display: block;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            display: block;
        }
        .stat-count {
            font-size: 8px;
            color: #64748b;
            display: block;
            margin-top: 3px;
        }
        .value-blue {
            color: #2563eb;
        }
        .value-green {
            color: #059669;
        }
        .value-red {
            color: #dc2626;
        }
        .value-orange {
            color: #ea580c;
        }

        /* Secciones */
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #2563eb;
            color: white;
            padding: 8px 10px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        /* Tablas */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-green {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-yellow {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Cards de actividad */
        .actividad-card {
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .actividad-header {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e5e7eb;
        }
        .actividad-title {
            font-weight: bold;
            font-size: 11px;
            color: #1e293b;
            margin: 0;
        }
        .actividad-meta {
            font-size: 8px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .actividad-body {
            display: table;
            width: 100%;
        }
        .actividad-col {
            display: table-cell;
            width: 50%;
            padding: 5px;
            vertical-align: top;
        }
        .metric-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 6px;
            margin: 2px 0;
            border-radius: 3px;
            font-size: 9px;
        }
        .metric-label {
            color: #475569;
        }
        .metric-value {
            font-weight: bold;
        }
        .bg-blue {
            background-color: #dbeafe;
        }
        .bg-green {
            background-color: #d1fae5;
        }
        .bg-red {
            background-color: #fee2e2;
        }
        .bg-orange {
            background-color: #fed7aa;
        }
        .bg-gray {
            background-color: #f1f5f9;
        }

        /* Highlights */
        .highlight-red {
            background-color: #fef2f2;
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
        <h1>Estadísticas de Aportes por Actividad</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Generado: {{ $fecha_generacion }}</p>
    </div>

    <!-- Estadísticas Globales -->
    <div class="section">
        <div class="section-title">Resumen Global</div>
        <div class="stats-grid">
            <div class="stat-row">
                <div class="stat-cell">
                    <span class="stat-label">Total Esperado</span>
                    <span class="stat-value value-blue">Bs. {{ number_format($estadisticasGlobales['total_esperado'], 2) }}</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-label">Total Pagado</span>
                    <span class="stat-value value-green">Bs. {{ number_format($estadisticasGlobales['total_pagado'], 2) }}</span>
                    <span class="stat-count">{{ $estadisticasGlobales['aportes_pagados'] }} aportes</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-label">Total Pendiente</span>
                    <span class="stat-value value-red">Bs. {{ number_format($estadisticasGlobales['total_pendiente'], 2) }}</span>
                    <span class="stat-count">{{ $estadisticasGlobales['aportes_pendientes'] }} aportes</span>
                </div>
                <div class="stat-cell">
                    <span class="stat-label">Mora Acumulada</span>
                    <span class="stat-value value-orange">Bs. {{ number_format($estadisticasGlobales['total_mora'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas por Actividad -->
    <div class="section">
        <div class="section-title">Estadísticas por Actividad</div>

        @forelse($estadisticasPorActividad as $actividad)
        <div class="actividad-card">
            <div class="actividad-header">
                <p class="actividad-title">{{ $actividad['actividad_titulo'] }}</p>
                <p class="actividad-meta">
                    {{ $actividad['actividad_tipo'] }} • {{ $actividad['numero_aportes'] }} aportes •
                    <span class="badge
                        @if($actividad['porcentaje_pagado'] >= 80) badge-green
                        @elseif($actividad['porcentaje_pagado'] >= 50) badge-yellow
                        @else badge-red
                        @endif
                    ">
                        {{ $actividad['porcentaje_pagado'] }}% pagado
                    </span>
                </p>
            </div>
            <div class="actividad-body">
                <div class="actividad-col">
                    <div class="metric-row bg-blue">
                        <span class="metric-label">Total Esperado:</span>
                        <span class="metric-value">Bs. {{ number_format($actividad['total_esperado'], 2) }}</span>
                    </div>
                    <div class="metric-row bg-green">
                        <span class="metric-label">Total Pagado:</span>
                        <span class="metric-value" style="color: #059669;">Bs. {{ number_format($actividad['total_pagado'], 2) }}</span>
                    </div>
                    <div class="metric-row bg-red">
                        <span class="metric-label">Total Pendiente:</span>
                        <span class="metric-value" style="color: #dc2626;">Bs. {{ number_format($actividad['total_pendiente'], 2) }}</span>
                    </div>
                </div>
                <div class="actividad-col">
                    <div class="metric-row bg-orange">
                        <span class="metric-label">Mora Acumulada:</span>
                        <span class="metric-value" style="color: #ea580c;">Bs. {{ number_format($actividad['total_mora'], 2) }}</span>
                    </div>
                    <div class="metric-row bg-gray">
                        <span class="metric-label">Aportes Pagados:</span>
                        <span class="metric-value" style="color: #059669;">{{ $actividad['aportes_pagados'] }}</span>
                    </div>
                    <div class="metric-row bg-gray">
                        <span class="metric-label">Aportes Pendientes:</span>
                        <span class="metric-value" style="color: #dc2626;">{{ $actividad['aportes_pendientes'] }}</span>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <p style="text-align: center; padding: 15px; color: #64748b;">
            No hay actividades con aportes registrados.
        </p>
        @endforelse
    </div>

    <!-- Estadísticas por Vivienda -->
    <div class="section">
        <div class="section-title">Estadísticas por Vivienda</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Vivienda</th>
                    <th style="width: 28%;">Dirección</th>
                    <th style="width: 15%;" class="text-right">Pagado</th>
                    <th style="width: 15%;" class="text-right">Pendiente</th>
                    <th style="width: 15%;" class="text-right">Mora</th>
                    <th style="width: 10%;" class="text-center">Aportes</th>
                    <th style="width: 5%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($estadisticasPorVivienda as $vivienda)
                <tr class="{{ $vivienda['total_pendiente'] > 0 ? 'highlight-red' : '' }}">
                    <td class="text-center"><strong>{{ $vivienda['vivienda_numero'] }}</strong></td>
                    <td>{{ $vivienda['vivienda_direccion'] }}</td>
                    <td class="text-right" style="color: #059669; font-weight: bold;">
                        Bs. {{ number_format($vivienda['total_pagado'], 2) }}
                    </td>
                    <td class="text-right" style="color: #dc2626; font-weight: bold;">
                        Bs. {{ number_format($vivienda['total_pendiente'], 2) }}
                    </td>
                    <td class="text-right" style="color: #ea580c; font-weight: bold;">
                        Bs. {{ number_format($vivienda['total_mora'], 2) }}
                    </td>
                    <td class="text-center">
                        <span style="color: #059669;">{{ $vivienda['aportes_pagados'] }}</span> /
                        <span style="color: #dc2626;">{{ $vivienda['aportes_pendientes'] }}</span>
                    </td>
                    <td class="text-center">
                        <span class="badge {{ $vivienda['total_pendiente'] === 0 ? 'badge-green' : 'badge-red' }}">
                            {{ $vivienda['total_pendiente'] === 0 ? 'Al día' : 'Deuda' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 15px; color: #64748b;">
                        No hay viviendas con aportes registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este reporte es confidencial y de uso exclusivo para la gestión del condominio.</p>
        <p>Sistema de Gestión de Condominios - Generado automáticamente</p>
    </div>
</body>
</html>
