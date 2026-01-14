<template>
  <AppLayout title="Aportes por Residente">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Reporte de Aportes por Residente</h1>
          <p class="mt-2 text-sm text-gray-600">
            Análisis detallado de aportes por residente, vivienda y período
          </p>
        </div>

        <!-- Filtros -->
        <Card title="Filtros" class="mb-6">
          <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <Select
              v-if="esAdmin && residentes.length > 0"
              v-model="form.residente_id"
              label="Residente"
            >
              <option value="">Todos los residentes</option>
              <option v-for="residente in residentes" :key="residente.id" :value="residente.id">
                {{ residente.nombres }} {{ residente.apellido_paterno }}
              </option>
            </Select>

            <Select
              v-if="esAdmin && viviendas.length > 0"
              v-model="form.vivienda_id"
              label="Vivienda"
            >
              <option value="">Todas las viviendas</option>
              <option v-for="vivienda in viviendas" :key="vivienda.id" :value="vivienda.id">
                {{ vivienda.numero }} - {{ vivienda.direccion }}
              </option>
            </Select>

            <Select
              v-if="esAdmin"
              v-model="form.tipo_residente"
              label="Tipo de Residente"
            >
              <option value="">Todos los tipos</option>
              <option value="PROPIETARIO">Propietarios</option>
              <option value="INQUILINO">Inquilinos</option>
              <option value="FAMILIAR">Familiares</option>
            </Select>

            <Select
              v-model="form.mes"
              label="Mes"
            >
              <option value="">Todo el año</option>
              <option v-for="(nombreMes, mes) in meses" :key="mes" :value="mes">
                {{ nombreMes }}
              </option>
            </Select>

            <Select
              v-model="form.año"
              label="Año"
            >
              <option v-for="año in años" :key="año" :value="año">
                {{ año }}
              </option>
            </Select>

            <div class="flex items-end gap-2">
              <Button
                type="submit"
                variant="primary"
                class="flex-1"
              >
                Aplicar Filtros
              </Button>
              <Button
                type="button"
                variant="ghost"
                @click="limpiarFiltros"
              >
                Limpiar
              </Button>
            </div>
          </form>
        </Card>

        <!-- Estadísticas -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
          <Card class="bg-gradient-to-br from-gray-50 to-gray-100">
            <div class="text-center">
              <p class="text-xs font-medium text-gray-600">Total Residentes</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">
                {{ estadisticas.total_residentes }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-green-50 to-green-100">
            <div class="text-center">
              <p class="text-xs font-medium text-green-600">Total Aportado</p>
              <p class="text-xl font-bold text-green-900 mt-1">
                Bs. {{ formatNumber(estadisticas.total_aportado) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-red-50 to-red-100">
            <div class="text-center">
              <p class="text-xs font-medium text-red-600">Total Pendiente</p>
              <p class="text-xl font-bold text-red-900 mt-1">
                Bs. {{ formatNumber(estadisticas.total_pendiente) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-orange-50 to-orange-100">
            <div class="text-center">
              <p class="text-xs font-medium text-orange-600">Mora Acumulada</p>
              <p class="text-xl font-bold text-orange-900 mt-1">
                Bs. {{ formatNumber(estadisticas.total_mora) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="text-center">
              <p class="text-xs font-medium text-blue-600">Al Día</p>
              <p class="text-2xl font-bold text-blue-900 mt-1">
                {{ estadisticas.residentes_al_dia }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-purple-50 to-purple-100">
            <div class="text-center">
              <p class="text-xs font-medium text-purple-600">% Cumplimiento</p>
              <p class="text-2xl font-bold text-purple-900 mt-1">
                {{ estadisticas.porcentaje_cumplimiento }}%
              </p>
            </div>
          </Card>
        </div>

        <!-- Gráfico de Evolución -->
        <Card title="Evolución Mensual de Aportes (Últimos 12 Meses)" class="mb-6">
          <LineChart
            chart-id="evolucion-residente-chart"
            :labels="evolucionMensual.labels"
            :datasets="evolucionMensual.datasets"
            height="300px"
            :options="{
              plugins: {
                tooltip: {
                  callbacks: {
                    label: (context) => `${context.dataset.label}: Bs. ${formatNumber(context.parsed.y)}`
                  }
                }
              },
              scales: {
                y: {
                  beginAtZero: true,
                  ticks: {
                    callback: (value) => 'Bs. ' + formatNumber(value)
                  }
                }
              }
            }"
          />
        </Card>

        <!-- Tabla de Aportes por Residente -->
        <Card class="mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">
              Detalle por Residente y Vivienda
            </h3>
            <div class="flex gap-2">
              <Button
                variant="secondary"
                size="sm"
                @click="exportarPDF"
                :loading="exportandoPDF"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
              </Button>
              <Button
                variant="secondary"
                size="sm"
                @click="exportarCSV"
              >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                CSV
              </Button>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Residente</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tipo</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vivienda</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aportado</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pendiente</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pagados</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Pendientes</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">% Cumpl.</th>
                  <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="(item, index) in aportesPorResidente"
                  :key="index"
                  :class="{'bg-yellow-50': item.total_pendiente > 0}"
                  class="hover:bg-gray-50"
                >
                  <td class="px-4 py-3">
                    <div class="flex items-center">
                      <div v-if="item.residente_foto" class="flex-shrink-0 h-10 w-10">
                        <img class="h-10 w-10 rounded-full object-cover" :src="`/storage/${item.residente_foto}`" :alt="item.residente_nombre">
                      </div>
                      <div v-else class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                        <span class="text-gray-600 font-semibold text-sm">
                          {{ item.residente_nombre.charAt(0) }}
                        </span>
                      </div>
                      <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ item.residente_nombre }}</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': item.residente_tipo === 'PROPIETARIO',
                        'bg-green-100 text-green-800': item.residente_tipo === 'INQUILINO',
                        'bg-purple-100 text-purple-800': item.residente_tipo === 'FAMILIAR'
                      }"
                    >
                      {{ item.residente_tipo }}
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <p class="text-sm font-medium text-gray-900">{{ item.vivienda_numero }}</p>
                    <p class="text-xs text-gray-600">{{ item.vivienda_direccion }}</p>
                    <a
                      v-if="item.vivienda_gps"
                      :href="`https://www.google.com/maps?q=${item.vivienda_gps}`"
                      target="_blank"
                      class="text-xs text-blue-600 hover:underline inline-flex items-center mt-1"
                    >
                      <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                      </svg>
                      Ver mapa
                    </a>
                  </td>
                  <td class="px-4 py-3 text-right text-sm font-medium text-green-600">
                    Bs. {{ formatNumber(item.total_aportado) }}
                  </td>
                  <td class="px-4 py-3 text-right text-sm font-medium text-red-600">
                    Bs. {{ formatNumber(item.total_pendiente) }}
                  </td>
                  <td class="px-4 py-3 text-right text-sm font-medium text-orange-600">
                    Bs. {{ formatNumber(item.total_mora) }}
                  </td>
                  <td class="px-4 py-3 text-center text-sm text-gray-900">
                    {{ item.aportes_pagados }}
                  </td>
                  <td class="px-4 py-3 text-center text-sm text-gray-900">
                    {{ item.aportes_pendientes }}
                  </td>
                  <td class="px-4 py-3 text-center">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': calcularPorcentaje(item) >= 80,
                        'bg-yellow-100 text-yellow-800': calcularPorcentaje(item) >= 50 && calcularPorcentaje(item) < 80,
                        'bg-red-100 text-red-800': calcularPorcentaje(item) < 50
                      }"
                    >
                      {{ calcularPorcentaje(item) }}%
                    </span>
                  </td>
                  <td class="px-4 py-3 text-center">
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="verDetalle(item)"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                      </svg>
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>

            <div v-if="aportesPorResidente.length === 0" class="text-center py-12 text-gray-500">
              No hay datos para mostrar con los filtros seleccionados
            </div>
          </div>
        </Card>
      </div>
    </div>

    <!-- Modal de detalle -->
    <Modal :show="mostrarDetalle" @close="mostrarDetalle = false" title="Detalle de Aportes" size="lg">
      <div v-if="detalleSeleccionado">
        <div class="mb-4">
          <h4 class="font-semibold text-gray-900">{{ detalleSeleccionado.residente_nombre }}</h4>
          <p class="text-sm text-gray-600">{{ detalleSeleccionado.vivienda_numero }} - {{ detalleSeleccionado.vivienda_direccion }}</p>
        </div>

        <div class="space-y-2">
          <div
            v-for="(aporte, index) in detalleSeleccionado.aportes"
            :key="index"
            class="border rounded-lg p-3 hover:bg-gray-50"
          >
            <div class="flex justify-between items-start">
              <div class="flex-1">
                <p class="font-medium text-gray-900">{{ aporte.actividad }}</p>
                <p class="text-xs text-gray-600 mt-1">
                  Vencimiento: {{ formatDate(aporte.fecha_vencimiento) }}
                  <span v-if="aporte.fecha_pago"> | Pagado: {{ formatDate(aporte.fecha_pago) }}</span>
                </p>
              </div>
              <div class="text-right ml-4">
                <p class="text-sm font-semibold text-gray-900">Bs. {{ formatNumber(aporte.monto) }}</p>
                <span
                  class="inline-block px-2 py-1 text-xs font-semibold rounded-full mt-1"
                  :class="{
                    'bg-green-100 text-green-800': aporte.estado === 'PAGADO',
                    'bg-yellow-100 text-yellow-800': aporte.estado === 'PARCIAL',
                    'bg-red-100 text-red-800': aporte.estado === 'VENCIDO',
                    'bg-gray-100 text-gray-800': aporte.estado === 'PENDIENTE'
                  }"
                >
                  {{ aporte.estado }}
                </span>
              </div>
            </div>
            <div v-if="aporte.monto_pendiente > 0 || aporte.mora > 0" class="mt-2 pt-2 border-t border-gray-200 text-xs">
              <div class="flex justify-between">
                <span class="text-gray-600">Pendiente:</span>
                <span class="text-red-600 font-medium">Bs. {{ formatNumber(aporte.monto_pendiente) }}</span>
              </div>
              <div v-if="aporte.mora > 0" class="flex justify-between mt-1">
                <span class="text-gray-600">Mora:</span>
                <span class="text-orange-600 font-medium">Bs. {{ formatNumber(aporte.mora) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import Select from '@/Components/Form/Select.vue'
import LineChart from '@/Components/Charts/LineChart.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  aportesPorResidente: Array,
  evolucionMensual: Object,
  estadisticas: Object,
  residentes: Array,
  viviendas: Array,
  filtros: Object,
  esAdmin: Boolean
})

const form = reactive({
  residente_id: props.filtros.residente_id || '',
  vivienda_id: props.filtros.vivienda_id || '',
  mes: props.filtros.mes || '',
  año: props.filtros.año || new Date().getFullYear(),
  tipo_residente: props.filtros.tipo_residente || ''
})

const exportandoPDF = ref(false)
const mostrarDetalle = ref(false)
const detalleSeleccionado = ref(null)

const meses = {
  '1': 'Enero',
  '2': 'Febrero',
  '3': 'Marzo',
  '4': 'Abril',
  '5': 'Mayo',
  '6': 'Junio',
  '7': 'Julio',
  '8': 'Agosto',
  '9': 'Septiembre',
  '10': 'Octubre',
  '11': 'Noviembre',
  '12': 'Diciembre'
}

const años = Array.from({ length: 5 }, (_, i) => new Date().getFullYear() - i)

const formatNumber = (value) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const calcularPorcentaje = (item) => {
  const total = item.total_aportado + item.total_pendiente
  return total > 0 ? Math.round((item.total_aportado / total) * 100) : 0
}

const aplicarFiltros = () => {
  router.get(route('reportes.aportes-por-residente'), form, {
    preserveState: true,
    preserveScroll: true
  })
}

const limpiarFiltros = () => {
  form.residente_id = ''
  form.vivienda_id = ''
  form.mes = ''
  form.año = new Date().getFullYear()
  form.tipo_residente = ''
  aplicarFiltros()
}

const exportarPDF = () => {
  exportandoPDF.value = true
  const params = new URLSearchParams(form).toString()
  window.location.href = route('reportes.aportes-por-residente.pdf') + '?' + params
  setTimeout(() => {
    exportandoPDF.value = false
  }, 2000)
}

const exportarCSV = () => {
  const params = new URLSearchParams(form).toString()
  window.location.href = route('reportes.aportes-por-residente.csv') + '?' + params
}

const verDetalle = (item) => {
  detalleSeleccionado.value = item
  mostrarDetalle.value = true
}
</script>
