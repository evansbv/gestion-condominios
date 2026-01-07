<template>
  <AppLayout title="Aportes">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Aportes y Pagos</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de aportes económicos del condominio</p>
          </div>
          <div  v-if="isAdmin" class="flex gap-2">
            <Button
              v-if="canCreate"
              variant="secondary"
              @click="router.visit(route('aportes.estadisticas'))"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              Estadísticas
            </Button>
            <Button
              v-if="canCreate"
              @click="router.visit(route('aportes.create'))"
              variant="primary"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nuevo Aporte
            </Button>
          </div>
        </div>

        <Alert
          v-if="$page.props.flash.success"
          type="success"
          :message="$page.props.flash.success"
          :auto-dismiss="5000"
        />

        <Card class="mb-6">
          <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Select
              v-model="searchForm.estado"
              label="Estado"
            >
              <option value="TODOS">Todos</option>
              <option value="PENDIENTE">Pendiente</option>
              <option value="PAGADO">Pagado</option>
              <option value="VENCIDO">Vencido</option>
              <option value="PARCIAL">Parcial</option>
            </Select>

            <Select
              v-if="viviendas && viviendas.length > 0"
              v-model="searchForm.vivienda_id"
              label="Vivienda"
            >
              <option value="TODOS">Todas</option>
              <option v-for="vivienda in viviendas" :key="vivienda.id" :value="vivienda.id">
                {{ vivienda.numero }} - {{ vivienda.direccion }}
              </option>
            </Select>

            <Select
              v-if="actividades && actividades.length > 0"
              v-model="searchForm.actividad_id"
              label="Actividad"
            >
              <option value="TODOS">Todas</option>
              <option v-for="actividad in actividades" :key="actividad.id" :value="actividad.id">
                {{ actividad.titulo }}
              </option>
            </Select>

            <div class="flex items-end gap-2">
              <Button type="submit" variant="primary" class="flex-1">
                Filtrar
              </Button>
              <Button type="button" variant="ghost" @click="clearFilters">
                Limpiar
              </Button>
            </div>
          </form>
        </Card>

        <Card>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vivienda</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actividad</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Monto</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Pagado</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Mora</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Adeudado</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="aporte in aportes.data" :key="aporte.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ aporte.vivienda?.numero }}</div>
                    <div class="text-xs text-gray-500">{{ aporte.vivienda?.direccion }}</div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-900">{{ aporte.actividad?.titulo }}</div>
                    <div class="text-xs text-gray-500">{{ aporte.actividad?.tipo?.replace(/_/g, ' ') }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-900">
                    Bs. {{ aporte.monto }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-green-600 font-medium">
                    Bs. {{ aporte.monto_pagado }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                    Bs. {{ aporte.mora_actualizada?.toFixed(2) || '0.00' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-bold text-gray-900">
                    Bs. {{ aporte.total_adeudado?.toFixed(2) || '0.00' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ new Date(aporte.fecha_vencimiento).toLocaleDateString('es-BO') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': aporte.estado === 'PAGADO',
                        'bg-yellow-100 text-yellow-800': aporte.estado === 'PENDIENTE',
                        'bg-red-100 text-red-800': aporte.estado === 'VENCIDO',
                        'bg-blue-100 text-blue-800': aporte.estado === 'PARCIAL'
                      }"
                    >
                      {{ aporte.estado }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <Button
                        size="sm"
                        variant="ghost"
                        @click="router.visit(route('aportes.show', aporte.id))"
                      >
                        Ver
                      </Button>
                      <Button
                        v-if="canEdit && aporte.estado !== 'PAGADO'"
                        size="sm"
                        variant="success"
                        @click="router.visit(route('aportes.show', aporte.id))"
                      >
                        Registrar Pago
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <template #footer>
            <Pagination
              :links="aportes.links"
              :from="aportes.from"
              :to="aportes.to"
              :total="aportes.total"
            />
          </template>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Select from '@/Components/Form/Select.vue'
import Alert from '@/Components/Alert.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  aportes: Object,
  viviendas: Array,
  actividades: Array,
  filters: Object
})

const canCreate = ref(true)
const canEdit = ref(true)

const searchForm = reactive({
  estado: props.filters?.estado || 'TODOS',
  vivienda_id: props.filters?.vivienda_id || 'TODOS',
  actividad_id: props.filters?.actividad_id || 'TODOS'
})

const handleSearch = () => {
  router.get(route('aportes.index'), searchForm, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchForm.estado = 'TODOS'
  searchForm.vivienda_id = 'TODOS'
  searchForm.actividad_id = 'TODOS'
  handleSearch()
}


//validar si es admin
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
// Usuario autenticado desde Inertia
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Computed correcto
const isAdmin = computed(() => {
    return user.value?.rol === 'ADMINISTRADOR'
})
</script>
