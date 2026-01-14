<template>
  <AppLayout title="Mis Aportes">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Mis Aportes</h1>
          <p class="mt-2 text-sm text-gray-600">
            Vivienda: {{ vivienda.numero }} - {{ vivienda.direccion }}
          </p>
        </div>

        <!-- Filtros de Fecha -->
        <Card class="mb-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Filtros de Período</h3>
          <form @submit.prevent="aplicarFiltros" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Período</label>
              <select
                v-model="formFiltros.filtro_tipo"
                @change="onFiltroTipoChange"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="todos">Todos los aportes</option>
                <option value="año_actual">Año actual ({{ añoActual }})</option>
                <option value="mes_actual">Mes actual ({{ mesActual }})</option>
                <option value="rango">Rango de fechas</option>
              </select>
            </div>

            <div v-if="formFiltros.filtro_tipo === 'rango'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
              <input
                type="date"
                v-model="formFiltros.fecha_inicio"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div v-if="formFiltros.filtro_tipo === 'rango'">
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
              <input
                type="date"
                v-model="formFiltros.fecha_fin"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

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

        <!-- Estadísticas Personales -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
          <Card class="bg-gradient-to-br from-red-50 to-red-100">
            <div class="text-center">
              <p class="text-sm font-medium text-red-600">Total Adeudado</p>
              <p class="text-2xl font-bold text-red-900 mt-2">
                Bs. {{ formatNumber(estadisticas.total_adeudado) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-orange-50 to-orange-100">
            <div class="text-center">
              <p class="text-sm font-medium text-orange-600">Mora Acumulada</p>
              <p class="text-2xl font-bold text-orange-900 mt-2">
                Bs. {{ formatNumber(estadisticas.total_mora) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-green-50 to-green-100">
            <div class="text-center">
              <p class="text-sm font-medium text-green-600">Total Pagado</p>
              <p class="text-2xl font-bold text-green-900 mt-2">
                Bs. {{ formatNumber(estadisticas.total_pagado) }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-blue-50 to-blue-100">
            <div class="text-center">
              <p class="text-sm font-medium text-blue-600">Aportes Pendientes</p>
              <p class="text-2xl font-bold text-blue-900 mt-2">
                {{ estadisticas.aportes_pendientes }}
              </p>
            </div>
          </Card>

          <Card class="bg-gradient-to-br from-purple-50 to-purple-100">
            <div class="text-center">
              <p class="text-sm font-medium text-purple-600">Aportes Pagados</p>
              <p class="text-2xl font-bold text-purple-900 mt-2">
                {{ estadisticas.aportes_pagados }}
              </p>
            </div>
          </Card>
        </div>

        <!-- Alertas -->
        <Alert
          v-if="estadisticas.total_adeudado > 0"
          type="warning"
          class="mb-6"
        >
          <strong>Recordatorio:</strong> Tiene aportes pendientes de pago. Por favor, regularice su situación a la brevedad posible.
        </Alert>

        <Alert
          v-else
          type="success"
          class="mb-6"
        >
          <strong>¡Felicitaciones!</strong> Se encuentra al día con todos sus aportes.
        </Alert>

        <!-- Listado de Aportes -->
        <Card class="mb-6">
          <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
              <h3 class="text-lg font-semibold text-gray-900">Detalle de Aportes</h3>
              <div class="flex gap-2">
                <Button
                  variant="secondary"
                  size="sm"
                  @click="exportarPDF"
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
          </template>

          <div class="mb-4 flex gap-2">
            <Button
              :variant="filtro === 'todos' ? 'primary' : 'ghost'"
              size="sm"
              @click="filtro = 'todos'"
            >
              Todos
            </Button>
            <Button
              :variant="filtro === 'pendientes' ? 'primary' : 'ghost'"
              size="sm"
              @click="filtro = 'pendientes'"
            >
              Pendientes
            </Button>
            <Button
              :variant="filtro === 'pagados' ? 'primary' : 'ghost'"
              size="sm"
              @click="filtro = 'pagados'"
            >
              Pagados
            </Button>
            <Button
              :variant="filtro === 'vencidos' ? 'primary' : 'ghost'"
              size="sm"
              @click="filtro = 'vencidos'"
            >
              Vencidos
            </Button>
          </div>

          <div class="space-y-3">
            <div
              v-for="aporte in aportesFiltrados"
              :key="aporte.id"
              class="border rounded-lg p-4 hover:shadow-md transition-shadow"
              :class="{
                'border-red-200 bg-red-50': aporte.estado === 'VENCIDO',
                'border-yellow-200 bg-yellow-50': aporte.estado === 'PARCIAL',
                'border-green-200 bg-green-50': aporte.estado === 'PAGADO',
                'border-gray-200': aporte.estado === 'PENDIENTE'
              }"
            >
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div class="flex-1">
                  <h3 class="font-semibold text-gray-900 text-lg">
                    {{ aporte.actividad?.titulo || 'Sin actividad' }}
                  </h3>
                  <p class="text-sm text-gray-600 mt-1">
                    {{ aporte.actividad?.descripcion }}
                  </p>
                  <div class="flex flex-wrap gap-3 mt-3 text-sm">
                    <div class="flex items-center text-gray-600">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      Vence: {{ formatDate(aporte.fecha_vencimiento) }}
                    </div>
                    <div v-if="aporte.fecha_pago" class="flex items-center text-green-600">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      Pagado: {{ formatDate(aporte.fecha_pago) }}
                    </div>
                    <div v-if="aporte.dias_mora > 0" class="flex items-center text-red-600 font-medium">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                      {{ aporte.dias_mora }} días de mora
                    </div>
                  </div>
                </div>

                <div class="flex flex-col items-end gap-2">
                  <span
                    class="px-3 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-800': aporte.estado === 'PAGADO',
                      'bg-yellow-100 text-yellow-800': aporte.estado === 'PARCIAL',
                      'bg-red-100 text-red-800': aporte.estado === 'VENCIDO',
                      'bg-gray-100 text-gray-800': aporte.estado === 'PENDIENTE'
                    }"
                  >
                    {{ aporte.estado }}
                  </span>

                  <div class="text-right">
                    <div class="text-sm text-gray-600">Monto:</div>
                    <div class="text-xl font-bold text-gray-900">
                      Bs. {{ formatNumber(aporte.monto) }}
                    </div>
                  </div>

                  <div v-if="aporte.monto_pagado > 0" class="text-right">
                    <div class="text-xs text-gray-600">Pagado:</div>
                    <div class="text-sm font-semibold text-green-600">
                      Bs. {{ formatNumber(aporte.monto_pagado) }}
                    </div>
                  </div>

                  <div v-if="aporte.estado !== 'PAGADO'" class="text-right">
                    <div class="text-xs text-gray-600">Pendiente:</div>
                    <div class="text-sm font-semibold text-red-600">
                      Bs. {{ formatNumber(aporte.monto - aporte.monto_pagado) }}
                    </div>
                  </div>

                  <div v-if="aporte.mora_actualizada > 0" class="text-right">
                    <div class="text-xs text-gray-600">Mora:</div>
                    <div class="text-sm font-semibold text-orange-600">
                      Bs. {{ formatNumber(aporte.mora_actualizada) }}
                    </div>
                  </div>

                  <div v-if="aporte.estado !== 'PAGADO'" class="text-right mt-2 pt-2 border-t border-gray-300">
                    <div class="text-xs text-gray-600">Total a pagar:</div>
                    <div class="text-lg font-bold text-red-600">
                      Bs. {{ formatNumber((aporte.monto - aporte.monto_pagado) + aporte.mora_actualizada) }}
                    </div>
                  </div>

                  <div v-if="aporte.observaciones" class="mt-2 text-xs text-gray-500 italic">
                    {{ aporte.observaciones }}
                  </div>
                </div>
              </div>
            </div>

            <div v-if="aportesFiltrados.length === 0" class="text-center py-8 text-gray-500">
              No hay aportes {{ filtro === 'todos' ? '' : filtro }} para mostrar
            </div>
          </div>
        </Card>

        <!-- Información de Contacto -->
        <Card class="bg-blue-50 border border-blue-200">
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800">Información de Pagos</h3>
              <div class="mt-2 text-sm text-blue-700">
                <p>Para realizar pagos o consultas sobre sus aportes, por favor contacte a la administración del condominio.</p>
                <p class="mt-2">Los pagos pueden realizarse a través de:</p>
                <ul class="list-disc list-inside mt-1 ml-2">
                  <li>Transferencia bancaria</li>
                  <li>Depósito en cuenta</li>
                  <li>Pago en oficina</li>
                </ul>
              </div>
            </div>
          </div>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Card from '@/Components/Card.vue'
import Button from '@/Components/Button.vue'
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  aportes: Array,
  estadisticas: Object,
  vivienda: Object,
  residente: Object,
  filtros: Object
})

const filtro = ref('todos')

// Filtros de fecha
const formFiltros = reactive({
  filtro_tipo: props.filtros?.filtro_tipo || 'todos',
  fecha_inicio: props.filtros?.fecha_inicio || '',
  fecha_fin: props.filtros?.fecha_fin || ''
})

const añoActual = new Date().getFullYear()
const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre']
const mesActual = meses[new Date().getMonth()]

const aportesFiltrados = computed(() => {
  switch (filtro.value) {
    case 'pendientes':
      return props.aportes.filter(a => ['PENDIENTE', 'PARCIAL'].includes(a.estado))
    case 'pagados':
      return props.aportes.filter(a => a.estado === 'PAGADO')
    case 'vencidos':
      return props.aportes.filter(a => a.estado === 'VENCIDO')
    default:
      return props.aportes
  }
})

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
    month: 'long',
    day: 'numeric'
  })
}

const aplicarFiltros = () => {
  router.get(route('mis-aportes'), formFiltros, {
    preserveState: true,
    preserveScroll: true
  })
}

const limpiarFiltros = () => {
  formFiltros.filtro_tipo = 'todos'
  formFiltros.fecha_inicio = ''
  formFiltros.fecha_fin = ''
  aplicarFiltros()
}

const onFiltroTipoChange = () => {
  if (formFiltros.filtro_tipo !== 'rango') {
    formFiltros.fecha_inicio = ''
    formFiltros.fecha_fin = ''
  }
}

const exportarPDF = () => {
  const params = new URLSearchParams(formFiltros).toString()
  window.location.href = route('mis-aportes.pdf') + '?' + params
}

const exportarCSV = () => {
  const params = new URLSearchParams(formFiltros).toString()
  window.location.href = route('mis-aportes.csv') + '?' + params
}
</script>
