<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Reuniones</title>
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

        /* Filtros Aplicados */
        .filters-section {
            background-color: #f8fafc;
            border-left: 4px solid #2563eb;
            padding: 10px 12px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .filters-title {
            font-weight: bold;
            color: #1e40af;
            font-size: 11px;
            margin-bottom: 5px;
        }
        .filter-item {
            font-size: 9px;
            color: #475569;
            margin: 2px 0;
        }

        /* Tabla */
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
            vertical-align: top;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }

        /* Badges de Estado */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            white-space: nowrap;
        }
        .badge-convocada {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-realizada {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-cancelada {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Resumen */
        .summary {
            background-color: #f8fafc;
            border: 2px solid #2563eb;
            border-radius: 6px;
            padding: 12px;
            margin-top: 15px;
        }
        .summary-title {
            color: #1e40af;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: center;
        }
        .summary-content {
            font-size: 9px;
            color: #475569;
            text-align: center;
        }

        /* Footer */
        .footer {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }

        /* Row highlighting */
        .row-convocada {
            background-color: #eff6ff;
        }
        .row-realizada {
            background-color: #f0fdf4;
        }
        .row-cancelada {
            background-color: #fef2f2;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <div class="header">
        <h1>Listado de Reuniones</h1>
        <p>Sistema de Gestión de Condominios</p>
        <p>Generado: {{ $fecha_generacion }}</p>
    </div>

    <!-- Filtros Aplicados -->
    @if($filtros['estado'] !== 'Todos' || $filtros['anio'] !== 'Todos')
    <div class="filters-section">
        <div class="filters-title">Filtros Aplicados:</div>
        @if($filtros['estado'] !== 'Todos')
        <div class="filter-item">• Estado: {{ $filtros['estado'] }}</div>
        @endif
        @if($filtros['anio'] !== 'Todos')
        <div class="filter-item">• Año: {{ $filtros['anio'] }}</div>
        @endif
    </div>
    @endif

    <!-- Tabla de Reuniones -->
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Fecha y Hora</th>
                <th style="width: 25%;">Título</th>
                <th style="width: 20%;">Lugar</th>
                <th style="width: 10%;" class="text-center">Participantes</th>
                <th style="width: 10%;" class="text-center">Asistencia</th>
                <th style="width: 15%;" class="text-center">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reuniones as $reunion)
            <tr class="{{
                $reunion->estado === 'CONVOCADA' ? 'row-convocada' :
                ($reunion->estado === 'REALIZADA' ? 'row-realizada' : 'row-cancelada')
            }}">
                <td>
                    <strong>{{ $reunion->fecha_reunion->format('d/m/Y') }}</strong><br>
                    <span style="color: #64748b;">{{ $reunion->fecha_reunion->format('H:i') }}</span>
                </td>
                <td>
                    <strong style="color: #1e293b;">{{ $reunion->titulo }}</strong><br>
                    <span style="color: #64748b; font-size: 8px;">
                        {{ Str::limit($reunion->descripcion, 60) }}
                    </span>
                </td>
                <td>{{ $reunion->lugar }}</td>
                <td class="text-center">
                    <strong>{{ $reunion->total_participantes }}</strong>
                </td>
                <td class="text-center">
                    @if($reunion->total_participantes > 0)
                        <strong style="color: #059669;">{{ $reunion->participantes_asistieron }}</strong>
                        <span style="color: #64748b;">/</span>
                        <span>{{ $reunion->total_participantes }}</span><br>
                        <span style="font-size: 7px; color: #64748b;">
                            ({{ $reunion->total_participantes > 0 ? round(($reunion->participantes_asistieron / $reunion->total_participantes) * 100) : 0 }}%)
                        </span>
                    @else
                        <span style="color: #94a3b8;">N/A</span>
                    @endif
                </td>
                <td class="text-center">
                    <span class="badge badge-{{ strtolower($reunion->estado) }}">
                        {{ $reunion->estado }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">
                    No hay reuniones registradas con los filtros aplicados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Resumen -->
    <div class="summary">
        <div class="summary-title">Resumen</div>
        <div class="summary-content">
            <strong>Total de reuniones:</strong> {{ $reuniones->count() }} •
            <strong>Convocadas:</strong> {{ $reuniones->where('estado', 'CONVOCADA')->count() }} •
            <strong>Realizadas:</strong> {{ $reuniones->where('estado', 'REALIZADA')->count() }} •
            <strong>Canceladas:</strong> {{ $reuniones->where('estado', 'CANCELADA')->count() }}
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Este reporte es confidencial y de uso exclusivo para la gestión del condominio.</p>
        <p>Sistema de Gestión de Condominios - Generado automáticamente</p>
    </div>
</body>
</html>
