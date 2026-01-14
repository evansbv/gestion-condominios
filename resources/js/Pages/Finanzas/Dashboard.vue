<template>
  <AppLayout title="Dashboard Financiero">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Dashboard Financiero</h1>
            <p class="mt-2 text-sm text-gray-600">Estado de morosidad y aportes del condominio</p>
          </div>
          <div class="flex gap-2">
            <select
              v-model="añoSeleccionado"
              @change="cambiarAño"
              class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option v-for="año in años" :key="año" :value="año">
                {{ año }}
              </option>
            </select>
            <Button
              v-if="esAdmin"
              variant="secondary"
              @click="exportarPDF"
              :loading="exportando"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              Exportar PDF
            </Button>
            <Button
              v-if="esAdmin"
              variant="secondary"
              @click="exportarCSV"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Exportar CSV
            </Button>
          </div>
        </div>

        <!-- Alert de morosidad alta -->
        <Alert
          v-if="estadisticas.porcentaje_morosidad > 25"
          type="error"
          class="mb-6"
        >
          <strong>¡Atención!</strong> El nivel de morosidad es alto ({{ estadisticas.porcentaje_morosidad }}%). Se recomienda tomar acciones inmediatas.
        </Alert>

        <!-- Estadísticas Principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <Card class="bg-gradient-to-br from-red-50 to-red-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-red-600">Morosidad Actual</p>
                <p class="text-3xl font-bold text-red-900 mt-2">
                  {{ estadisticas.porcentaje_morosidad }}%
                </p>
                <p class="text-xs text-red-600 mt-1">
                  {{ estadisticas.viviendas_con_deuda }} de {{ estadisticas.total_viviendas }} viviendas
                </p>
              </div>
              <div class="p-3 bg-red-200 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-blue-600">Total Esperado</p>
                <p class="text-3xl font-bold text-blue-900 mt-2">
                  Bs. {{ formatNumber(estadisticas.total_esperado) }}
                </p>
                <p class="text-xs text-blue-600 mt-1">Aportes año {{ añoSeleccionado }}</p>
              </div>
              <div class="p-3 bg-blue-200 rounded-full">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-green-50 to-green-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-green-600">Total Recaudado</p>
                <p class="text-3xl font-bold text-green-900 mt-2">
                  Bs. {{ formatNumber(estadisticas.total_recaudado) }}
                </p>
                <p class="text-xs text-green-600 mt-1">
                  {{ estadisticas.porcentaje_al_dia }}% al día
                </p>
              </div>
              <div class="p-3 bg-green-200 rounded-full">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-orange-50 to-orange-100">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-orange-600">Deuda + Mora</p>
                <p class="text-3xl font-bold text-orange-900 mt-2">
                  Bs. {{ formatNumber(estadisticas.total_adeudado + estadisticas.total_mora) }}
                </p>
                <p class="text-xs text-orange-600 mt-1">
                  Mora: Bs. {{ formatNumber(estadisticas.total_mora) }}
                </p>
              </div>
              <div class="p-3 bg-orange-200 rounded-full">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
            </div>
          </Card>
        </div>

        <!-- Gráfico de Evolución Mensual -->
        <Card title="Evolución de Morosidad (Últimos 12 Meses)" class="mb-6">
          <LineChart
            chart-id="morosidad-chart"
            :labels="evolucionMensual.labels"
            :datasets="[{
              label: 'Morosidad (%)',
              data: evolucionMensual.data,
              borderColor: '#ef4444',
              backgroundColor: 'rgba(239, 68, 68, 0.1)',
              tension: 0.4,
              fill: true
            }]"
            height="350px"
            :options="{
              plugins: {
                legend: {
                  display: true
                },
                tooltip: {
                  callbacks: {
                    label: (context) => `Morosidad: ${context.parsed.y.toFixed(2)}%`
                  }
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  max: 100,
                  ticks: {
                    callback: (value) => value + '%'
                  }
                }
              }
            }"
          />
        </Card>

        <!-- Resumen por Actividad -->
        <Card title="Resumen por Actividad" class="mb-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actividad</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Presupuesto</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Recaudado</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">% Pagado</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deuda</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mora</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="actividad in resumenActividades"
                  :key="actividad.id"
                  :class="{'bg-yellow-50': actividad.deuda_pendiente > 0}"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ actividad.titulo }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ actividad.tipo }}</td>
                  <td class="px-4 py-3 text-sm text-right text-gray-900">Bs. {{ formatNumber(actividad.presupuesto_aprobado) }}</td>
                  <td class="px-4 py-3 text-sm text-right text-green-600 font-medium">Bs. {{ formatNumber(actividad.total_recaudado) }}</td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': actividad.porcentaje_pagado >= 80,
                        'bg-yellow-100 text-yellow-800': actividad.porcentaje_pagado >= 50 && actividad.porcentaje_pagado < 80,
                        'bg-red-100 text-red-800': actividad.porcentaje_pagado < 50
                      }"
                    >
                      {{ actividad.porcentaje_pagado.toFixed(1) }}%
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-red-600 font-medium">Bs. {{ formatNumber(actividad.deuda_pendiente) }}</td>
                  <td class="px-4 py-3 text-sm text-right text-orange-600">Bs. {{ formatNumber(actividad.mora_total) }}</td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': actividad.estado === 'COMPLETADA',
                        'bg-blue-100 text-blue-800': actividad.estado === 'EN_PROGRESO',
                        'bg-gray-100 text-gray-800': actividad.estado === 'PLANIFICADA',
                        'bg-red-100 text-red-800': actividad.estado === 'CANCELADA'
                      }"
                    >
                      {{ actividad.estado }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="resumenActividades.length === 0" class="text-center py-8 text-gray-500">
              No hay actividades registradas para este año
            </div>
          </div>
        </Card>

        <!-- Top Morosos (Solo Admin) -->
        <Card v-if="esAdmin && topMorosos.length > 0" title="Top 10 Morosos" class="mb-6">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vivienda</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Propietario</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Deuda Total</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Mora</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Días Mora</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aportes Pend.</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="(moroso, index) in topMorosos"
                  :key="index"
                  class="hover:bg-gray-50"
                >
                  <td class="px-4 py-3 text-sm font-bold text-gray-900">{{ index + 1 }}</td>
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ moroso.vivienda_numero }}</td>
                  <td class="px-4 py-3 text-sm text-gray-600">{{ moroso.propietario }}</td>
                  <td class="px-4 py-3 text-sm text-right font-bold text-red-600">Bs. {{ formatNumber(moroso.deuda_total) }}</td>
                  <td class="px-4 py-3 text-sm text-right text-orange-600">Bs. {{ formatNumber(moroso.mora_total) }}</td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-red-100 text-red-800': moroso.dias_mora > 60,
                        'bg-orange-100 text-orange-800': moroso.dias_mora > 30 && moroso.dias_mora <= 60,
                        'bg-yellow-100 text-yellow-800': moroso.dias_mora <= 30
                      }"
                    >
                      {{ moroso.dias_mora }} días
                    </span>
                  </td>
                  <td class="px-4 py-3 text-sm text-center text-gray-900">{{ moroso.aportes_pendientes }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import Alert from '@/Components/Alert.vue'
import LineChart from '@/Components/Charts/LineChart.vue'

const props = defineProps({
  estadisticas: Object,
  evolucionMensual: Object,
  resumenActividades: Array,
  topMorosos: Array,
  esAdmin: Boolean,
  añoActual: Number,
  añoSeleccionado: Number
})

const añoSeleccionado = ref(props.añoSeleccionado)
const exportando = ref(false)

// Generar lista de años (últimos 5 años)
const años = Array.from({ length: 5 }, (_, i) => props.añoActual - i)

const formatNumber = (value) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const cambiarAño = () => {
  router.visit(route('finanzas.dashboard', { año: añoSeleccionado.value }), {
    preserveState: true,
    preserveScroll: true
  })
}

const exportarPDF = () => {
  exportando.value = true
  window.location.href = route('finanzas.exportar-pdf', { año: añoSeleccionado.value })
  setTimeout(() => {
    exportando.value = false
  }, 2000)
}

const exportarCSV = () => {
  window.location.href = route('finanzas.exportar-csv', { año: añoSeleccionado.value })
}
</script>
