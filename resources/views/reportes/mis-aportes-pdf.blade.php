<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Aportes</title>
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

        /* Sección de Datos del Residente y Vivienda */
        .info-section {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .info-card {
            display: table-cell;
            width: 50%;
            padding: 12px;
            vertical-align: top;
        }
        .info-card:first-child {
            padding-right: 10px;
        }
        .info-card:last-child {
            padding-left: 10px;
        }
        .info-card-inner {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 12px;
            border-radius: 4px;
        }
        .info-card h3 {
            color: #1e40af;
            margin: 0 0 10px 0;
            font-size: 13px;
            font-weight: bold;
        }
        .info-row {
            margin: 5px 0;
            font-size: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            display: inline-block;
            width: 100px;
        }
        .info-value {
            color: #1e293b;
        }

        /* Tabla de Aportes */
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

        /* Badges de Estado */
        .badge {
            display: inline-block;
            padding: 2px 6px;
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

        /* Resumen Final */
        .summary {
            background-color: #f8fafc;
            border: 2px solid #2563eb;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
        }
        .summary-title {
            color: #1e40af;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            text-align: center;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-cell {
            display: table-cell;
            width: 33.33%;
            padding: 8px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
        }
        .summary-cell:last-child {
            border-right: none;
        }
        .summary-label {
            font-weight: bold;
            color: #64748b;
            font-size: 9px;
            display: block;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 16px;
            font-weight: bold;
            display: block;
        }
        .summary-count {
            font-size: 10px;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }
        .value-pagado {
            color: #059669;
        }
        .value-pendiente {
            color: #dc2626;
        }
        .value-vencido {
            color: #ea580c;
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
        <h1>Mis Aportes</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Generado: {{ $fecha_generacion }}</p>
        @if(isset($filtro_descripcion))
        <p style="color: #2563eb; font-weight: bold; margin-top: 5px;">{{ $filtro_descripcion }}</p>
        @endif
    </div>

    <!-- Datos del Residente y Vivienda -->
    <div class="info-section">
        <div class="info-card">
            <div class="info-card-inner">
                <h3>Datos del Residente</h3>
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $residente->nombres }} {{ $residente->apellido_paterno }} {{ $residente->apellido_materno }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">CI:</span>
                    <span class="info-value">{{ $residente->ci }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value">{{ $residente->tipo_residente }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $residente->telefono ?? 'No registrado' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $residente->email ?? 'No registrado' }}</span>
                </div>
            </div>
        </div>
        <div class="info-card">
            <div class="info-card-inner">
                <h3>Datos de la Vivienda</h3>
                <div class="info-row">
                    <span class="info-label">Número:</span>
                    <span class="info-value">{{ $vivienda->numero }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">{{ $vivienda->direccion }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tipo:</span>
                    <span class="info-value">{{ $vivienda->tipo }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Área:</span>
                    <span class="info-value">{{ $vivienda->area_metros ?? 'No registrado' }} m²</span>
                </div>
                @if($vivienda->ubicacion_gps)
                <div class="info-row">
                    <span class="info-label">GPS:</span>
                    <span class="info-value">{{ $vivienda->ubicacion_gps }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Detalle de Aportes -->
    <div class="section">
        <div class="section-title">Detalle de Aportes</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Fecha Aporte</th>
                    <th style="width: 12%;">Vencimiento</th>
                    <th style="width: 30%;">Actividad</th>
                    <th style="width: 12%;" class="text-right">Monto</th>
                    <th style="width: 12%;" class="text-right">Pagado</th>
                    <th style="width: 10%;" class="text-right">Mora</th>
                    <th style="width: 12%;" class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aportes as $aporte)
                <tr>
                    <td class="text-center">{{ $aporte->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">{{ $aporte->fecha_vencimiento->format('d/m/Y') }}</td>
                    <td>{{ $aporte->actividad ? $aporte->actividad->titulo : 'Sin actividad' }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->monto, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->monto_pagado, 2) }}</td>
                    <td class="text-right">Bs. {{ number_format($aporte->mora_actualizada, 2) }}</td>
                    <td class="text-center">
                        <span class="badge badge-{{ strtolower($aporte->estado) }}">
                            {{ $aporte->estado }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 20px; color: #64748b;">
                        No hay aportes registrados para esta vivienda.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Resumen por Estado -->
    <div class="summary">
        <div class="summary-title">Resumen por Estado</div>
        <div class="summary-grid">
            <div class="summary-row">
                <div class="summary-cell">
                    <span class="summary-label">Aportes Pagados</span>
                    <span class="summary-value value-pagado">Bs. {{ number_format($estadisticas['total_pagado'], 2) }}</span>
                    <span class="summary-count">({{ $estadisticas['aportes_pagados'] }} aportes)</span>
                </div>
                <div class="summary-cell">
                    <span class="summary-label">Aportes Pendientes</span>
                    <span class="summary-value value-pendiente">Bs. {{ number_format($estadisticas['monto_pendiente'], 2) }}</span>
                    <span class="summary-count">({{ $estadisticas['aportes_pendientes'] }} aportes)</span>
                </div>
                <div class="summary-cell">
                    <span class="summary-label">Aportes Vencidos/Mora</span>
                    <span class="summary-value value-vencido">Bs. {{ number_format($estadisticas['monto_vencido'] + $estadisticas['total_mora'], 2) }}</span>
                    <span class="summary-count">({{ $estadisticas['aportes_vencidos'] }} aportes - Mora: Bs. {{ number_format($estadisticas['total_mora'], 2) }})</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este reporte es de carácter personal y confidencial.</p>
        <p>Sistema de Gestión de Condominios - Generado automáticamente</p>
    </div>
</body>
</html>
