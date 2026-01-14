<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Aportes por Residente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 22px;
        }
        .header p {
            color: #64748b;
            margin: 4px 0;
            font-size: 11px;
        }
        .filters {
            background-color: #f1f5f9;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .filters p {
            margin: 3px 0;
            font-size: 10px;
        }
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            background-color: #2563eb;
            color: white;
            padding: 6px 10px;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
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
            padding: 6px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            width: 16.66%;
        }
        .stat-label {
            font-weight: bold;
            color: #64748b;
            font-size: 9px;
        }
        .stat-value {
            font-size: 16px;
            color: #1e40af;
            font-weight: bold;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 10px;
        }
        th {
            background-color: #f1f5f9;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            border: 1px solid #e5e7eb;
        }
        td {
            padding: 5px 6px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .highlight {
            background-color: #fef3c7;
        }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #64748b;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .badge-propietario {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-inquilino {
            background-color: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Aportes por Residente</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Generado: {{ $fecha_generacion }}</p>
    </div>

    @if(isset($filtros))
    <div class="filters">
        <strong>Filtros aplicados:</strong>
        @if($filtros['mes'])
        <p>Mes: {{ $filtros['mes'] }}/{{ $filtros['año'] }}</p>
        @else
        <p>Año: {{ $filtros['año'] }}</p>
        @endif
        @if($filtros['tipoResidente'])
        <p>Tipo de Residente: {{ $filtros['tipoResidente'] }}</p>
        @endif
    </div>
    @endif

    <div class="section">
        <div class="section-title">Estadísticas Globales</div>
        <div class="stats-grid">
            <div class="stat-row">
                <div class="stat-cell">
                    <div class="stat-label">Total Residentes</div>
                    <div class="stat-value">{{ $estadisticas['total_residentes'] }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Total Aportado</div>
                    <div class="stat-value" style="color: #059669;">Bs. {{ number_format($estadisticas['total_aportado'], 2) }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Total Pendiente</div>
                    <div class="stat-value" style="color: #dc2626;">Bs. {{ number_format($estadisticas['total_pendiente'], 2) }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Mora Acumulada</div>
                    <div class="stat-value" style="color: #ea580c;">Bs. {{ number_format($estadisticas['total_mora'], 2) }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Al Día</div>
                    <div class="stat-value">{{ $estadisticas['residentes_al_dia'] }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">% Cumplimiento</div>
                    <div class="stat-value">{{ $estadisticas['porcentaje_cumplimiento'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Detalle por Residente y Vivienda</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 25%;">Residente</th>
                    <th style="width: 8%;" class="text-center">Tipo</th>
                    <th style="width: 17%;">Vivienda</th>
                    <th style="width: 10%;" class="text-right">Aportado</th>
                    <th style="width: 10%;" class="text-right">Pendiente</th>
                    <th style="width: 10%;" class="text-right">Mora</th>
                    <th style="width: 7%;" class="text-center">Pagados</th>
                    <th style="width: 7%;" class="text-center">Pend.</th>
                    <th style="width: 6%;" class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aportesPorResidente as $item)
                @php
                    $totalDebe = $item['total_aportado'] + $item['total_pendiente'];
                    $porcentaje = $totalDebe > 0 ? round(($item['total_aportado'] / $totalDebe) * 100, 1) : 0;
                @endphp
                <tr class="{{ $item['total_pendiente'] > 0 ? 'highlight' : '' }}">
                    <td>{{ $item['residente_nombre'] }}</td>
                    <td class="text-center">
                        <span class="badge {{ $item['residente_tipo'] === 'PROPIETARIO' ? 'badge-propietario' : 'badge-inquilino' }}">
                            {{ $item['residente_tipo'] === 'PROPIETARIO' ? 'PROP' : 'INQ' }}
                        </span>
                    </td>
                    <td>
                        <strong>{{ $item['vivienda_numero'] }}</strong><br>
                        <span style="font-size: 9px; color: #64748b;">{{ $item['vivienda_direccion'] }}</span>
                    </td>
                    <td class="text-right">Bs. {{ number_format($item['total_aportado'], 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($item['total_pendiente'], 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($item['total_mora'], 2) }}</td>
                    <td class="text-center">{{ $item['aportes_pagados'] }}</td>
                    <td class="text-center">{{ $item['aportes_pendientes'] }}</td>
                    <td class="text-center">{{ $porcentaje }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($aportesPorResidente) === 0)
        <p style="text-align: center; padding: 20px; color: #64748b;">
            No hay datos para mostrar con los filtros seleccionados.
        </p>
        @endif
    </div>

    <div class="footer">
        <p>Este reporte es confidencial y de uso exclusivo para la gestión del condominio.</p>
        <p>Sistema de Gestión de Condominios - Generado automáticamente</p>
    </div>
</body>
</html>
