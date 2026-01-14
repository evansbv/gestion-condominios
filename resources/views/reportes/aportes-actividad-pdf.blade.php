<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Aportes por Actividad</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 20px;
        }
        .header p {
            color: #64748b;
            margin: 3px 0;
            font-size: 10px;
        }
        .actividad-info {
            background-color: #f1f5f9;
            padding: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #2563eb;
        }
        .actividad-info h2 {
            margin: 0 0 5px 0;
            font-size: 14px;
            color: #1e40af;
        }
        .actividad-info p {
            margin: 2px 0;
            font-size: 9px;
        }
        .section {
            margin-bottom: 15px;
        }
        .section-title {
            background-color: #2563eb;
            color: white;
            padding: 5px 8px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 6px;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 12px;
        }
        .stat-row {
            display: table-row;
        }
        .stat-cell {
            display: table-cell;
            padding: 5px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            width: 16.66%;
        }
        .stat-label {
            font-weight: bold;
            color: #64748b;
            font-size: 8px;
        }
        .stat-value {
            font-size: 14px;
            color: #1e40af;
            font-weight: bold;
            margin-top: 2px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            font-size: 9px;
        }
        th {
            background-color: #f1f5f9;
            padding: 5px;
            text-align: left;
            font-weight: bold;
            font-size: 9px;
            border: 1px solid #e5e7eb;
        }
        td {
            padding: 4px 5px;
            border: 1px solid #e5e7eb;
            font-size: 9px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-pagado {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-pendiente {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .badge-parcial {
            background-color: #fef3c7;
            color: #92400e;
        }
        .badge-vencido {
            background-color: #fee2e2;
            color: #7f1d1d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Aportes por Actividad</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Generado: {{ $fecha_generacion }}</p>
    </div>

    <div class="actividad-info">
        <h2>{{ $actividad->titulo }}</h2>
        <p><strong>Descripción:</strong> {{ $actividad->descripcion }}</p>
        <p><strong>Tipo:</strong> {{ $actividad->tipo }}</p>
        <p>
            <strong>Fechas:</strong>
            {{ $actividad->fecha_inicio ? $actividad->fecha_inicio->format('d/m/Y') : 'No definida' }}
            -
            {{ $actividad->fecha_fin ? $actividad->fecha_fin->format('d/m/Y') : 'No definida' }}
        </p>
        @if($actividad->responsable)
        <p><strong>Responsable:</strong> {{ $actividad->responsable->name }}</p>
        @endif
        @if($actividad->reunion)
        <p><strong>Reunión de aprobación:</strong> {{ $actividad->reunion->titulo }} ({{ $actividad->reunion->fecha_reunion->format('d/m/Y') }})</p>
        @endif
        <p><strong>Presupuesto Aprobado:</strong> Bs. {{ number_format($actividad->presupuesto_aprobado ?? 0, 2) }}</p>
        <p><strong>Estado:</strong> {{ $actividad->estado }}</p>
    </div>

    <div class="section">
        <div class="section-title">Estadísticas de Aportes</div>
        <div class="stats-grid">
            <div class="stat-row">
                <div class="stat-cell">
                    <div class="stat-label">Total Recaudado</div>
                    <div class="stat-value" style="color: #059669;">Bs. {{ number_format($estadisticas['total_recaudado'], 2) }}</div>
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
                    <div class="stat-label">Nº Aportantes</div>
                    <div class="stat-value">{{ $estadisticas['numero_aportantes'] }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Aportes Pagados</div>
                    <div class="stat-value" style="color: #059669;">{{ $estadisticas['aportes_pagados'] }}</div>
                </div>
                <div class="stat-cell">
                    <div class="stat-label">Aportes Pend.</div>
                    <div class="stat-value" style="color: #dc2626;">{{ $estadisticas['aportes_pendientes'] }}</div>
                </div>
            </div>
        </div>
    </div>

    @if(count($distribucionPorResidente) > 0)
    <div class="section">
        <div class="section-title">Distribución por Residente</div>
        <table>
            <thead>
                <tr>
                    <th>Residente</th>
                    <th>Vivienda</th>
                    <th class="text-right">Total Aportado</th>
                    <th class="text-right">Total Pendiente</th>
                    <th class="text-center">%</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distribucionPorResidente as $item)
                @php
                    $total = $item['total_aportado'] + $item['total_pendiente'];
                    $porcentaje = $total > 0 ? round(($item['total_aportado'] / $total) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ $item['residente_nombre'] }}</td>
                    <td class="text-center">{{ $item['vivienda_numero'] }}</td>
                    <td class="text-right">Bs. {{ number_format($item['total_aportado'], 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($item['total_pendiente'], 2) }}</td>
                    <td class="text-center">{{ $porcentaje }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Detalle de Aportes</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Vivienda</th>
                    <th style="width: 20%;">Residente</th>
                    <th style="width: 10%;" class="text-center">Fecha Aporte</th>
                    <th style="width: 10%;" class="text-center">Vencimiento</th>
                    <th style="width: 10%;" class="text-right">Monto</th>
                    <th style="width: 10%;" class="text-right">Pagado</th>
                    <th style="width: 10%;" class="text-right">Pendiente</th>
                    <th style="width: 8%;" class="text-right">Mora</th>
                    <th style="width: 10%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($aportes as $aporte)
                @php
                    $vivienda = $aporte->vivienda;
                    $propietario = $vivienda ? $vivienda->residentes->where('tipo_residente', 'PROPIETARIO')->first() : null;
                    $residenteNombre = $propietario ? $propietario->nombres . ' ' . $propietario->apellido_paterno : 'N/A';
                @endphp
                <tr>
                    <td class="text-center">{{ $vivienda ? $vivienda->numero : 'N/A' }}</td>
                    <td>{{ $residenteNombre }}</td>
                    <td class="text-center">{{ $aporte->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $aporte->fecha_vencimiento->format('d/m/Y') }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->monto, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->monto_pagado, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->monto - $aporte->monto_pagado, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->mora_actualizada, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ strtolower($aporte->estado) }}">
                            {{ $aporte->estado }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($aportes) === 0)
        <p style="text-align: center; padding: 15px; color: #64748b;">
            No hay aportes registrados para esta actividad.
        </p>
        @endif
    </div>

    <div class="footer">
        <p>Este reporte es confidencial y de uso exclusivo para la gestión del condominio.</p>
        <p>Sistema de Gestión de Condominios - Generado automáticamente</p>
    </div>
</body>
</html>
