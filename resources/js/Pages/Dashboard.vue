<template>
    <AppLayout>
        <div class="space-y-6">
            <h1 class="text-3xl font-bold text-gray-900">Panel de Control</h1>

            <!-- Estadísticas generales -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Viviendas
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ estadisticas.total_viviendas }}
                        </dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Total Residentes
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-gray-900">
                            {{ estadisticas.total_residentes }}
                        </dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Reuniones Pendientes
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-yellow-600">
                            {{ estadisticas.reuniones_pendientes }}
                        </dd>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-sm font-medium text-gray-500 truncate">
                            Actividades Activas
                        </dt>
                        <dd class="mt-1 text-3xl font-semibold text-green-600">
                            {{ estadisticas.actividades_activas }}
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de mora -->
            <div class="bg-white shadow rounded-lg p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">Estado de Aportes</h2>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Deuda</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-600">
                            Bs. {{ estadisticasMora.total_deuda.toFixed(2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Mora</dt>
                        <dd class="mt-1 text-2xl font-semibold text-red-500">
                            Bs. {{ estadisticasMora.total_mora.toFixed(2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Aportes Pagados</dt>
                        <dd class="mt-1 text-2xl font-semibold text-green-600">
                            {{ estadisticasMora.porcentaje_pagados }}%
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Datos de vivienda (si es residente) -->
            <div v-if="datosVivienda" class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h2 class="text-lg font-medium text-blue-900 mb-4">
                    Mi Vivienda - {{ datosVivienda.vivienda_numero }}
                </h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div>
                        <dt class="text-sm font-medium text-blue-700">Total Pendiente</dt>
                        <dd class="mt-1 text-xl font-semibold text-blue-900">
                            Bs. {{ datosVivienda.total_pendiente.toFixed(2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-blue-700">Mora</dt>
                        <dd class="mt-1 text-xl font-semibold text-red-600">
                            Bs. {{ datosVivienda.total_mora.toFixed(2) }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-blue-700">Total a Pagar</dt>
                        <dd class="mt-1 text-xl font-semibold text-blue-900">
                            Bs. {{ datosVivienda.total_deuda.toFixed(2) }}
                        </dd>
                    </div>
                </div>
            </div>

            <!-- Próximas reuniones y actividades recientes -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Próximas reuniones -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Próximas Reuniones</h2>
                    <div v-if="proximasReuniones.length > 0" class="space-y-3">
                        <div
                            v-for="reunion in proximasReuniones"
                            :key="reunion.id"
                            class="border-l-4 border-blue-500 pl-3"
                        >
                            <p class="font-medium text-gray-900">{{ reunion.titulo }}</p>
                            <p class="text-sm text-gray-600">
                                {{ new Date(reunion.fecha_reunion).toLocaleString('es-BO') }}
                            </p>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">No hay reuniones próximas</p>
                </div>

                <!-- Últimas comunicaciones -->
                <div class="bg-white shadow rounded-lg p-6">
                    <h2 class="text-lg font-medium text-gray-900 mb-4">Últimas Comunicaciones</h2>
                    <div v-if="ultimasComunicaciones.length > 0" class="space-y-3">
                        <div
                            v-for="comunicacion in ultimasComunicaciones"
                            :key="comunicacion.id"
                            class="border-l-4 border-green-500 pl-3"
                        >
                            <p class="font-medium text-gray-900">{{ comunicacion.asunto }}</p>
                            <p class="text-sm text-gray-600">{{ comunicacion.tipo }}</p>
                        </div>
                    </div>
                    <p v-else class="text-gray-500">No hay comunicaciones recientes</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    estadisticas: Object,
    estadisticasMora: Object,
    ultimasComunicaciones: Array,
    proximasReuniones: Array,
    actividadesRecientes: Array,
    datosVivienda: Object,
});
</script>
