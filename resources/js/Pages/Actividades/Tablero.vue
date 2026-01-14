<template>
  <AppLayout title="Tablero de Actividades">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Tablero de Actividades</h1>
            <p class="mt-2 text-sm text-gray-600">Vista general de actividades y su estado de ejecución</p>
          </div>
          <div class="flex gap-2">
            <select
              v-model="añoSeleccionado"
              @change="actualizarFiltros"
              class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            >
              <option v-for="año in años" :key="año" :value="año">
                {{ año }}
              </option>
            </select>
            <select
              v-model="tipoSeleccionado"
              @change="actualizarFiltros"
              class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            >
              <option value="">Todos los tipos</option>
              <option v-for="tipo in tipos" :key="tipo" :value="tipo">
                {{ tipo }}
              </option>
            </select>
          </div>
        </div>

        <!-- Estadísticas Principales -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
          <Card class="bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="text-center">
              <p class="text-xs font-medium text-gray-600">Total</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ estadisticas.total }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-yellow-50 to-yellow-100">
            <div class="text-center">
              <p class="text-xs font-medium text-yellow-600">Planificadas</p>
              <p class="text-2xl font-bold text-yellow-900 mt-1">
                {{ estadisticas.planificadas }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="text-center">
              <p class="text-xs font-medium text-blue-600">En Progreso</p>
              <p class="text-2xl font-bold text-blue-900 mt-1">
                {{ estadisticas.en_progreso }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-green-50 to-green-100">
            <div class="text-center">
              <p class="text-xs font-medium text-green-600">Completadas</p>
              <p class="text-2xl font-bold text-green-900 mt-1">
                {{ estadisticas.completadas }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-purple-50 to-purple-100">
            <div class="text-center">
              <p class="text-xs font-medium text-purple-600">% Completadas</p>
              <p class="text-2xl font-bold text-purple-900 mt-1">
                {{ estadisticas.porcentaje_completadas }}%
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-teal-50 to-teal-100">
            <div class="text-center">
              <p class="text-xs font-medium text-teal-600">% A Tiempo</p>
              <p class="text-2xl font-bold text-teal-900 mt-1">
                {{ estadisticas.porcentaje_a_tiempo }}%
              </p>
            </div>
          </Card>
        </div>

        <!-- Distribución por Tipo -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
          <Card title="Distribución por Tipo" class="lg:col-span-1">
            <DoughnutChart
              chart-id="tipo-chart"
              :labels="distribucionPorTipo.map(t => t.tipo)"
              :data="distribucionPorTipo.map(t => t.count)"
              height="280px"
            />
          </Card>

          <Card title="Resumen por Tipo" class="lg:col-span-2">
            <div class="space-y-2">
              <div
                v-for="tipo in distribucionPorTipo"
                :key="tipo.tipo"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-1">
                  <p class="font-medium text-gray-900">{{ tipo.tipo }}</p>
                  <p class="text-xs text-gray-600 mt-1">
                    Total: {{ tipo.count }} |
                    Completadas: {{ tipo.completadas }} |
                    En progreso: {{ tipo.en_progreso }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-semibold text-gray-900">
                    Bs. {{ formatNumber(tipo.presupuesto) }}
                  </p>
                  <p class="text-xs text-gray-600">Presupuesto</p>
                </div>
              </div>
            </div>
          </Card>
        </div>

        <!-- Vista Kanban -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
          <!-- Planificadas -->
          <div class="bg-yellow-50 rounded-lg p-4 border-2 border-yellow-200">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-yellow-900">Planificadas</h3>
              <span class="px-2 py-1 text-xs font-bold bg-yellow-200 text-yellow-900 rounded-full">
                {{ actividadesPorEstado.PLANIFICADA.length }}
              </span>
            </div>
            <div class="space-y-3">
              <ActividadCard
                v-for="actividad in actividadesPorEstado.PLANIFICADA"
                :key="actividad.id"
                :actividad="actividad"
                estado-color="yellow"
              />
              <div v-if="actividadesPorEstado.PLANIFICADA.length === 0" class="text-center py-8 text-yellow-700 text-sm">
                No hay actividades planificadas
              </div>
            </div>
          </div>

          <!-- En Progreso -->
          <div class="bg-blue-50 rounded-lg p-4 border-2 border-blue-200">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-blue-900">En Progreso</h3>
              <span class="px-2 py-1 text-xs font-bold bg-blue-200 text-blue-900 rounded-full">
                {{ actividadesPorEstado.EN_PROGRESO.length }}
              </span>
            </div>
            <div class="space-y-3">
              <ActividadCard
                v-for="actividad in actividadesPorEstado.EN_PROGRESO"
                :key="actividad.id"
                :actividad="actividad"
                estado-color="blue"
              />
              <div v-if="actividadesPorEstado.EN_PROGRESO.length === 0" class="text-center py-8 text-blue-700 text-sm">
                No hay actividades en progreso
              </div>
            </div>
          </div>

          <!-- Completadas -->
          <div class="bg-green-50 rounded-lg p-4 border-2 border-green-200">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-green-900">Completadas</h3>
              <span class="px-2 py-1 text-xs font-bold bg-green-200 text-green-900 rounded-full">
                {{ actividadesPorEstado.COMPLETADA.length }}
              </span>
            </div>
            <div class="space-y-3 max-h-[600px] overflow-y-auto">
              <ActividadCard
                v-for="actividad in actividadesPorEstado.COMPLETADA"
                :key="actividad.id"
                :actividad="actividad"
                estado-color="green"
              />
              <div v-if="actividadesPorEstado.COMPLETADA.length === 0" class="text-center py-8 text-green-700 text-sm">
                No hay actividades completadas
              </div>
            </div>
          </div>

          <!-- Canceladas -->
          <div class="bg-red-50 rounded-lg p-4 border-2 border-red-200">
            <div class="flex items-center justify-between mb-4">
              <h3 class="font-semibold text-red-900">Canceladas</h3>
              <span class="px-2 py-1 text-xs font-bold bg-red-200 text-red-900 rounded-full">
                {{ actividadesPorEstado.CANCELADA.length }}
              </span>
            </div>
            <div class="space-y-3">
              <ActividadCard
                v-for="actividad in actividadesPorEstado.CANCELADA"
                :key="actividad.id"
                :actividad="actividad"
                estado-color="red"
              />
              <div v-if="actividadesPorEstado.CANCELADA.length === 0" class="text-center py-8 text-red-700 text-sm">
                No hay actividades canceladas
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import DoughnutChart from '@/Components/Charts/DoughnutChart.vue'
import ActividadCard from '@/Components/ActividadCard.vue'

const props = defineProps({
  actividadesPorEstado: Object,
  estadisticas: Object,
  distribucionPorTipo: Array,
  tipos: Array,
  añoActual: Number,
  añoSeleccionado: Number,
  tipoSeleccionado: String
})

const añoSeleccionado = ref(props.añoSeleccionado)
const tipoSeleccionado = ref(props.tipoSeleccionado || '')

// Generar lista de años
const años = Array.from({ length: 5 }, (_, i) => props.añoActual - i)

const formatNumber = (value) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const actualizarFiltros = () => {
  router.visit(route('actividades.tablero', {
    año: añoSeleccionado.value,
    tipo: tipoSeleccionado.value || undefined
  }), {
    preserveState: true,
    preserveScroll: true
  })
}
</script>
