<template>
  <AppLayout title="Estadísticas de Aportes">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Estadísticas de Aportes</h1>
              <p class="mt-2 text-sm text-gray-600">Análisis completo de aportes por actividad y vivienda</p>
            </div>
            <div class="flex gap-2">
              <Button
                variant="secondary"
                @click="exportarPDF"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Exportar PDF
              </Button>
              <Button
                variant="secondary"
                @click="exportarCSV"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar CSV
              </Button>
              <Button
                variant="ghost"
                @click="router.visit(route('aportes.index'))"
              >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
              </Button>
            </div>
          </div>
        </div>

        <!-- Estadísticas Globales -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
          <Card class="bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="text-center">
              <p class="text-xs font-medium text-blue-600">Total Esperado</p>
              <p class="text-2xl font-bold text-blue-900 mt-1">
                Bs. {{ formatNumber(estadisticasGlobales.total_esperado) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-green-50 to-green-100">
            <div class="text-center">
              <p class="text-xs font-medium text-green-600">Total Pagado</p>
              <p class="text-2xl font-bold text-green-900 mt-1">
                Bs. {{ formatNumber(estadisticasGlobales.total_pagado) }}
              </p>
              <p class="text-xs text-green-700 mt-1">
                {{ estadisticasGlobales.aportes_pagados }} aportes
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-red-50 to-red-100">
            <div class="text-center">
              <p class="text-xs font-medium text-red-600">Total Pendiente</p>
              <p class="text-2xl font-bold text-red-900 mt-1">
                Bs. {{ formatNumber(estadisticasGlobales.total_pendiente) }}
              </p>
              <p class="text-xs text-red-700 mt-1">
                {{ estadisticasGlobales.aportes_pendientes }} aportes
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-orange-50 to-orange-100">
            <div class="text-center">
              <p class="text-xs font-medium text-orange-600">Mora Acumulada</p>
              <p class="text-2xl font-bold text-orange-900 mt-1">
                Bs. {{ formatNumber(estadisticasGlobales.total_mora) }}
              </p>
            </div>
          </Card>
        </div>

        <!-- Gráfico Global -->
        <Card class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribución Global de Aportes</h3>
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div>
              <DoughnutChart
                chart-id="global-chart"
                :labels="['Pagado', 'Pendiente', 'Mora']"
                :data="[
                  estadisticasGlobales.total_pagado,
                  estadisticasGlobales.total_pendiente,
                  estadisticasGlobales.total_mora
                ]"
                :background-color="['#10b981', '#ef4444', '#f97316']"
                height="300px"
              />
            </div>
            <div class="flex items-center">
              <div class="w-full space-y-3">
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-500 rounded-full mr-3"></div>
                    <span class="font-medium text-gray-700">Pagado</span>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-gray-900">Bs. {{ formatNumber(estadisticasGlobales.total_pagado) }}</p>
                    <p class="text-xs text-gray-600">{{ calcularPorcentaje(estadisticasGlobales.total_pagado) }}%</p>
                  </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-4 h-4 bg-red-500 rounded-full mr-3"></div>
                    <span class="font-medium text-gray-700">Pendiente</span>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-gray-900">Bs. {{ formatNumber(estadisticasGlobales.total_pendiente) }}</p>
                    <p class="text-xs text-gray-600">{{ calcularPorcentaje(estadisticasGlobales.total_pendiente) }}%</p>
                  </div>
                </div>
                <div class="flex items-center justify-between p-3 bg-orange-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-4 h-4 bg-orange-500 rounded-full mr-3"></div>
                    <span class="font-medium text-gray-700">Mora</span>
                  </div>
                  <div class="text-right">
                    <p class="font-bold text-gray-900">Bs. {{ formatNumber(estadisticasGlobales.total_mora) }}</p>
                    <p class="text-xs text-gray-600">{{ calcularPorcentaje(estadisticasGlobales.total_mora) }}%</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </Card>

        <!-- Estadísticas por Actividad -->
        <Card class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas por Actividad</h3>

          <div class="space-y-6">
            <div
              v-for="(actividad, index) in estadisticasPorActividad"
              :key="actividad.actividad_id"
              class="border rounded-lg p-4 hover:shadow-md transition-shadow"
            >
              <div class="flex items-center justify-between mb-3">
                <div>
                  <h4 class="font-semibold text-gray-900">{{ actividad.actividad_titulo }}</h4>
                  <p class="text-xs text-gray-600">{{ actividad.actividad_tipo }} • {{ actividad.numero_aportes }} aportes</p>
                </div>
                <div class="text-right">
                  <span class="px-3 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': actividad.porcentaje_pagado >= 80,
                      'bg-yellow-100 text-yellow-800': actividad.porcentaje_pagado >= 50 && actividad.porcentaje_pagado < 80,
                      'bg-red-100 text-red-800': actividad.porcentaje_pagado < 50
                    }"
                  >
                    {{ actividad.porcentaje_pagado }}% pagado
                  </span>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Gráfico -->
                <div>
                  <DoughnutChart
                    :chart-id="`actividad-chart-${index}`"
                    :labels="['Pagado', 'Pendiente', 'Mora']"
                    :data="[
                      actividad.total_pagado,
                      actividad.total_pendiente,
                      actividad.total_mora
                    ]"
                    :background-color="['#10b981', '#ef4444', '#f97316']"
                    height="200px"
                  />
                </div>

                <!-- Detalle -->
                <div class="space-y-2">
                  <div class="flex justify-between items-center p-2 bg-blue-50 rounded">
                    <span class="text-sm text-gray-700">Total Esperado:</span>
                    <span class="font-semibold text-gray-900">Bs. {{ formatNumber(actividad.total_esperado) }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-green-50 rounded">
                    <span class="text-sm text-gray-700">Total Pagado:</span>
                    <span class="font-semibold text-green-700">Bs. {{ formatNumber(actividad.total_pagado) }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-red-50 rounded">
                    <span class="text-sm text-gray-700">Total Pendiente:</span>
                    <span class="font-semibold text-red-700">Bs. {{ formatNumber(actividad.total_pendiente) }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-orange-50 rounded">
                    <span class="text-sm text-gray-700">Mora Acumulada:</span>
                    <span class="font-semibold text-orange-700">Bs. {{ formatNumber(actividad.total_mora) }}</span>
                  </div>
                  <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                    <span class="text-sm text-gray-700">Aportes:</span>
                    <span class="font-semibold text-gray-900">
                      <span class="text-green-600">{{ actividad.aportes_pagados }}</span> /
                      <span class="text-red-600">{{ actividad.aportes_pendientes }}</span>
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="estadisticasPorActividad.length === 0" class="text-center py-8 text-gray-500">
              No hay actividades con aportes registrados
            </div>
          </div>
        </Card>

        <!-- Estadísticas por Vivienda -->
        <Card>
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Estadísticas por Vivienda</h3>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vivienda</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dirección</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pagado</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aportes</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Estado</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="vivienda in estadisticasPorVivienda"
                  :key="vivienda.vivienda_id"
                  class="hover:bg-gray-50"
                  :class="{'bg-red-50': vivienda.total_pendiente > 0}"
                >
                  <td class="px-4 py-3 text-sm font-medium text-gray-900">
                    {{ vivienda.vivienda_numero }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-600">
                    {{ vivienda.vivienda_direccion }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-green-600 font-medium">
                    Bs. {{ formatNumber(vivienda.total_pagado) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-red-600 font-medium">
                    Bs. {{ formatNumber(vivienda.total_pendiente) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-right text-orange-600 font-medium">
                    Bs. {{ formatNumber(vivienda.total_mora) }}
                  </td>
                  <td class="px-4 py-3 text-sm text-center text-gray-900">
                    <span class="text-green-600">{{ vivienda.aportes_pagados }}</span> /
                    <span class="text-red-600">{{ vivienda.aportes_pendientes }}</span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': vivienda.total_pendiente === 0,
                        'bg-red-100 text-red-800': vivienda.total_pendiente > 0
                      }"
                    >
                      {{ vivienda.total_pendiente === 0 ? 'Al día' : 'Con deuda' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="estadisticasPorVivienda.length === 0" class="text-center py-8 text-gray-500">
              No hay viviendas con aportes registrados
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue'

const props = defineProps({
  estadisticasGlobales: Object,
  estadisticasPorActividad: Array,
  estadisticasPorVivienda: Array
})

const formatNumber = (value) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value || 0)
}

const calcularPorcentaje = (valor) => {
  const total = props.estadisticasGlobales.total_pagado +
                props.estadisticasGlobales.total_pendiente +
                props.estadisticasGlobales.total_mora

  if (total === 0) return 0
  return ((valor / total) * 100).toFixed(1)
}

const exportarPDF = () => {
  window.location.href = route('aportes.estadisticas.pdf')
}

const exportarCSV = () => {
  window.location.href = route('aportes.estadisticas.csv')
}
</script>
