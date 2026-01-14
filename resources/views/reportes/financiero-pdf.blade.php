<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Financiero {{ $año }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #64748b;
            margin: 5px 0;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #2563eb;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            padding: 8px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .stat-label {
            font-weight: bold;
            color: #64748b;
            font-size: 11px;
        }
        .stat-value {
            font-size: 18px;
            color: #1e40af;
            font-weight: bold;
            margin-top: 5px;
        }
        .stat-danger {
            color: #dc2626;
        }
        .stat-success {
            color: #059669;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f1f5f9;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #e5e7eb;
        }
        td {
            padding: 7px;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
        .highlight {
            background-color: #fef3c7;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Financiero y de Morosidad</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Año: {{ $año }} | Generado: {{ $fecha_generacion }}</p>
    </div>

    <div class="section">
        <div class="section-title">Estadísticas Globales</div>
        <div class="stats-grid">
            <div class="stat-row">
                <div class="stat-cell">
                    <div class="stat-label">Morosidad Actual</div>
                    <div class="stat-value stat-danger">{{ number_format($estadisticas['porcentaje_morosidad'], 2) }}%</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Total Esperado</div>
                    <div class="stat-value">Bs. {{ number_format($estadisticas['total_esperado'], 2) }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Total Recaudado</div>
                    <div class="stat-value stat-success">Bs. {{ number_format($estadisticas['total_recaudado'], 2) }}</div>
                </div>
            </div>
            <div class="stat-row">
                <div class="stat-cell">
                    <div class="stat-label">Total Adeudado</div>
                    <div class="stat-value stat-danger">Bs. {{ number_format($estadisticas['total_adeudado'], 2) }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Mora Acumulada</div>
                    <div class="stat-value stat-danger">Bs. {{ number_format($estadisticas['total_mora'], 2) }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Viviendas con Deuda</div>
                    <div class="stat-value">{{ $estadisticas['viviendas_con_deuda'] }} / {{ $estadisticas['total_viviendas'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Resumen por Actividad</div>
        <table>
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Tipo</th>
                    <th class="text-right">Presupuesto</th>
                    <th class="text-right">Recaudado</th>
                    <th class="text-center">% Pagado</th>
                    <th class="text-right">Deuda</th>
                    <th class="text-right">Mora</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resumenActividades as $actividad)
                <tr class="{{ $actividad['deuda_pendiente'] > 0 ? 'highlight' : '' }}">
                    <td>{{ $actividad['titulo'] }}</td>
                    <td>{{ $actividad['tipo'] }}</td>
                    <td class="text-right">Bs. {{ number_format($actividad['presupuesto_aprobado'], 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($actividad['total_recaudado'], 2) }}</td>
                    <td class="text-center">{{ number_format($actividad['porcentaje_pagado'], 2) }}%</td>
                    <td class="text-right">Bs. {{ number_format($actividad['deuda_pendiente'], 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($actividad['mora_total'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Top 10 Morosos</div>
        <table>
            <thead>
                <tr>
                    <th>Vivienda</th>
                    <th>Propietario</th>
                    <th class="text-right">Deuda Total</th>
                    <th class="text-right">Mora</th>
                    <th class="text-center">Días Mora</th>
                    <th class="text-center">Aportes Pend.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topMorosos as $moroso)
                <tr>
                    <td>{{ $moroso['vivienda_numero'] }}</td>
                    <td>{{ $moroso['propietario'] }}</td>
                    <td class="text-right">Bs. {{ number_format($moroso['deuda_total'], 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($moroso['mora_total'], 2) }}</td>
                    <td class="text-center">{{ $moroso['dias_mora'] }}</td>
                    <td class="text-center">{{ $moroso['aportes_pendientes'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>Este reporte es confidencial y de uso exclusivo para la administración del condominio.</p>
        <p>Sistema de Gestión de Condominios - Generado automáticamente</p>
    </div>
</body>
</html>
