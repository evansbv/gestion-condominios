<template>
    <AppLayout title="Aportes por Actividad">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Reporte de Aportes por Actividad
            </h2>
        </template>

        <div class="py-6 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 sm:p-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Filtros de Búsqueda</h3>
                        <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <!-- Selector de Actividad -->
                            <div class="col-span-1 md:col-span-2 lg:col-span-4">
                                <label for="actividad" class="block text-sm font-medium text-gray-700 mb-1">
                                    Actividad *
                                </label>
                                <select
                                    id="actividad"
                                    v-model="form.actividad_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    required
                                >
                                    <option :value="null">Seleccione una actividad...</option>
                                    <option
                                        v-for="actividad in actividades"
                                        :key="actividad.id"
                                        :value="actividad.id"
                                    >
                                        {{ actividad.titulo }} ({{ actividad.tipo }})
                                    </option>
                                </select>
                            </div>

                            <!-- Fecha Inicio -->
                            <div>
                                <label for="fecha_inicio" class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Inicio
                                </label>
                                <input
                                    type="date"
                                    id="fecha_inicio"
                                    v-model="form.fecha_inicio"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Fecha Fin -->
                            <div>
                                <label for="fecha_fin" class="block text-sm font-medium text-gray-700 mb-1">
                                    Fecha Fin
                                </label>
                                <input
                                    type="date"
                                    id="fecha_fin"
                                    v-model="form.fecha_fin"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                />
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado
                                </label>
                                <select
                                    id="estado"
                                    v-model="form.estado"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option :value="null">Todos</option>
                                    <option value="PAGADO">Pagado</option>
                                    <option value="PENDIENTE">Pendiente</option>
                                    <option value="VENCIDO">Vencido</option>
                                    <option value="PARCIAL">Parcial</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="flex items-end space-x-2">
                                <button
                                    type="submit"
                                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150"
                                >
                                    Aplicar Filtros
                                </button>
                                <button
                                    type="button"
                                    @click="limpiarFiltros"
                                    class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition duration-150"
                                >
                                    Limpiar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Información de la Actividad Seleccionada -->
                <div v-if="actividadSeleccionada" class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 sm:p-6 mb-6 border-l-4 border-blue-600">
                    <h3 class="text-xl font-bold text-blue-900 mb-3">{{ actividadSeleccionada.titulo }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                        <p><span class="font-semibold text-gray-700">Descripción:</span> <span class="text-gray-600">{{ actividadSeleccionada.descripcion }}</span></p>
                        <p><span class="font-semibold text-gray-700">Tipo:</span> <span class="text-gray-600">{{ actividadSeleccionada.tipo }}</span></p>
                        <p>
                            <span class="font-semibold text-gray-700">Fechas:</span>
                            <span class="text-gray-600">
                                {{ actividadSeleccionada.fecha_inicio ? formatDate(actividadSeleccionada.fecha_inicio) : 'No definida' }}
                                -
                                {{ actividadSeleccionada.fecha_fin ? formatDate(actividadSeleccionada.fecha_fin) : 'No definida' }}
                            </span>
                        </p>
                        <p v-if="actividadSeleccionada.responsable">
                            <span class="font-semibold text-gray-700">Responsable:</span>
                            <span class="text-gray-600">{{ actividadSeleccionada.responsable.name }}</span>
                        </p>
                        <p v-if="actividadSeleccionada.reunion">
                            <span class="font-semibold text-gray-700">Reunión de aprobación:</span>
                            <a
                                :href="`/reuniones/${actividadSeleccionada.reunion.id}`"
                                class="text-blue-600 hover:text-blue-800 underline"
                            >
                                {{ actividadSeleccionada.reunion.titulo }} ({{ formatDate(actividadSeleccionada.reunion.fecha_reunion) }})
                            </a>
                        </p>
                        <p><span class="font-semibold text-gray-700">Presupuesto Aprobado:</span> <span class="text-gray-600">Bs. {{ formatNumber(actividadSeleccionada.presupuesto_aprobado || 0) }}</span></p>
                        <p><span class="font-semibold text-gray-700">Estado:</span>
                            <span :class="estadoClass(actividadSeleccionada.estado)" class="px-2 py-1 rounded text-xs font-semibold">
                                {{ actividadSeleccionada.estado }}
                            </span>
                        </p>
                    </div>
                </div>

                <!-- Estadísticas -->
                <div v-if="actividadSeleccionada" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-5">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Recaudado</dt>
                                    <dd class="mt-1 text-lg sm:text-2xl font-semibold text-green-600">
                                        Bs. {{ formatNumber(estadisticas.total_recaudado) }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-5">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Total Pendiente</dt>
                                    <dd class="mt-1 text-lg sm:text-2xl font-semibold text-red-600">
                                        Bs. {{ formatNumber(estadisticas.total_pendiente) }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-5">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Mora Acumulada</dt>
                                    <dd class="mt-1 text-lg sm:text-2xl font-semibold text-orange-600">
                                        Bs. {{ formatNumber(estadisticas.total_mora) }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-5">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Nº Aportantes</dt>
                                    <dd class="mt-1 text-lg sm:text-2xl font-semibold text-blue-600">
                                        {{ estadisticas.numero_aportantes }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-5">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Aportes Pagados</dt>
                                    <dd class="mt-1 text-lg sm:text-2xl font-semibold text-green-600">
                                        {{ estadisticas.aportes_pagados }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-3 sm:p-5">
                            <div class="flex items-center">
                                <div class="flex-1">
                                    <dt class="text-xs sm:text-sm font-medium text-gray-500 truncate">Aportes Pend.</dt>
                                    <dd class="mt-1 text-lg sm:text-2xl font-semibold text-red-600">
                                        {{ estadisticas.aportes_pendientes }}
                                    </dd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de Distribución y Tabla Resumen -->
                <div v-if="actividadSeleccionada && distribucionPorResidente.length > 0" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Gráfico de Distribución por Residente -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Distribución por Residente</h3>
                        <DoughnutChart
                            chart-id="distribucion-residente-chart"
                            :labels="distribucionPorResidente.map(d => d.residente_nombre)"
                            :data="distribucionPorResidente.map(d => d.total_aportado)"
                            :background-color="chartColors"
                            height="300px"
                        />
                    </div>

                    <!-- Tabla de Distribución -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Top Aportantes</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Vivienda</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Aportado</th>
                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">%</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in distribucionPorResidente.slice(0, 5)" :key="index">
                                        <td class="px-3 py-2 text-sm text-gray-900">{{ item.residente_nombre }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-center">{{ item.vivienda_numero }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right font-medium">Bs. {{ formatNumber(item.total_aportado) }}</td>
                                        <td class="px-3 py-2 text-sm text-gray-900 text-right">
                                            {{ calcularPorcentaje(item.total_aportado, item.total_pendiente) }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Detalle de Aportes -->
                <div v-if="actividadSeleccionada" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-2 sm:mb-0">Detalle de Aportes</h3>
                            <div class="flex space-x-2">
                                <a
                                    :href="exportPdfUrl"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/>
                                    </svg>
                                    PDF
                                </a>
                                <a
                                    :href="exportCsvUrl"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 8V2H7v6H2l8 8 8-8h-5zM0 18h20v2H0v-2z"/>
                                    </svg>
                                    CSV
                                </a>
                            </div>
                        </div>

                        <div v-if="aportes.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vivienda</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Residente</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Aporte</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monto</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pagado</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pendiente</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mora</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="aporte in aportes" :key="aporte.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center font-medium">
                                            {{ aporte.vivienda?.numero || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ getResidenteNombre(aporte.vivienda) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                            {{ formatDate(aporte.created_at) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-center">
                                            {{ formatDate(aporte.fecha_vencimiento) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                                            Bs. {{ formatNumber(aporte.monto) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-green-600 text-right font-medium">
                                            Bs. {{ formatNumber(aporte.monto_pagado) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-red-600 text-right font-medium">
                                            Bs. {{ formatNumber(aporte.monto - aporte.monto_pagado) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-orange-600 text-right font-medium">
                                            Bs. {{ formatNumber(aporte.mora_actualizada) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-center">
                                            <span :class="estadoBadgeClass(aporte.estado)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
                                                {{ aporte.estado }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div v-else class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No hay aportes registrados</h3>
                            <p class="mt-1 text-sm text-gray-500">No se encontraron aportes para esta actividad con los filtros aplicados.</p>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay actividad seleccionada -->
                <div v-else class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">Seleccione una actividad</h3>
                        <p class="mt-2 text-sm text-gray-500">
                            Por favor, seleccione una actividad del selector de arriba para ver el reporte de aportes.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue';

const props = defineProps({
    actividades: Array,
    actividadSeleccionada: Object,
    aportes: Array,
    distribucionPorResidente: Array,
    estadisticas: Object,
    filtros: Object,
    esAdmin: Boolean
});

const form = ref({
    actividad_id: props.filtros.actividad_id || null,
    fecha_inicio: props.filtros.fecha_inicio || null,
    fecha_fin: props.filtros.fecha_fin || null,
    estado: props.filtros.estado || null
});

const chartColors = [
    '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
    '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1'
];

const aplicarFiltros = () => {
    router.get('/reportes/aportes-por-actividad', form.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const limpiarFiltros = () => {
    form.value = {
        actividad_id: null,
        fecha_inicio: null,
        fecha_fin: null,
        estado: null
    };
    router.get('/reportes/aportes-por-actividad');
};

const exportPdfUrl = computed(() => {
    const params = new URLSearchParams();
    if (form.value.actividad_id) params.append('actividad_id', form.value.actividad_id);
    if (form.value.fecha_inicio) params.append('fecha_inicio', form.value.fecha_inicio);
    if (form.value.fecha_fin) params.append('fecha_fin', form.value.fecha_fin);
    if (form.value.estado) params.append('estado', form.value.estado);
    return `/reportes/aportes-por-actividad/pdf?${params.toString()}`;
});

const exportCsvUrl = computed(() => {
    const params = new URLSearchParams();
    if (form.value.actividad_id) params.append('actividad_id', form.value.actividad_id);
    if (form.value.fecha_inicio) params.append('fecha_inicio', form.value.fecha_inicio);
    if (form.value.fecha_fin) params.append('fecha_fin', form.value.fecha_fin);
    if (form.value.estado) params.append('estado', form.value.estado);
    return `/reportes/aportes-por-actividad/csv?${params.toString()}`;
});

const formatNumber = (value) => {
    return new Intl.NumberFormat('es-BO', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value || 0);
};

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-BO', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    }).format(date);
};

const calcularPorcentaje = (aportado, pendiente) => {
    const total = aportado + pendiente;
    if (total === 0) return 0;
    return ((aportado / total) * 100).toFixed(1);
};

const getResidenteNombre = (vivienda) => {
    if (!vivienda || !vivienda.residentes) return 'N/A';
    const propietario = vivienda.residentes.find(r => r.tipo_residente === 'PROPIETARIO');
    if (propietario) {
        return `${propietario.nombres} ${propietario.apellido_paterno}`;
    }
    return 'N/A';
};

const estadoClass = (estado) => {
    const classes = {
        'PLANIFICADA': 'bg-blue-100 text-blue-800',
        'EN_PROGRESO': 'bg-yellow-100 text-yellow-800',
        'COMPLETADA': 'bg-green-100 text-green-800',
        'CANCELADA': 'bg-red-100 text-red-800'
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};

const estadoBadgeClass = (estado) => {
    const classes = {
        'PAGADO': 'bg-green-100 text-green-800',
        'PENDIENTE': 'bg-red-100 text-red-800',
        'PARCIAL': 'bg-yellow-100 text-yellow-800',
        'VENCIDO': 'bg-red-200 text-red-900'
    };
    return classes[estado] || 'bg-gray-100 text-gray-800';
};
</script>
