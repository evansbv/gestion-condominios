<template>
  <AppLayout title="Detalle Actividad">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ actividad.titulo }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ actividad.tipo ? actividad.tipo.replace(/_/g, ' ') : 'Sin tipo' }}</p>
          </div>
          <div class="flex gap-2">
            <Button v-if="isAdmin"
              variant="primary"
              @click="router.visit(route('actividades.edit', actividad.id))"
            >
              Editar
            </Button>
            <Button
              variant="ghost"
              @click="router.visit(route('actividades.index'))"
            >
              Volver
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <Card>
              <div class="flex items-center justify-between mb-4">
                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800': actividad.estado === 'PLANIFICADA',
                    'bg-yellow-100 text-yellow-800': actividad.estado === 'EN_PROGRESO',
                    'bg-green-100 text-green-800': actividad.estado === 'COMPLETADA',
                    'bg-red-100 text-red-800': actividad.estado === 'CANCELADA'
                  }"
                >
                  {{ actividad.estado ? actividad.estado.replace('_', ' ') : 'Sin estado' }}
                </span>
                <div class="text-right">
                  <p class="text-sm text-gray-500">Avance</p>
                  <p class="text-2xl font-bold text-gray-900">{{ actividad.porcentaje_avance || 0 }}%</p>
                </div>
              </div>

              <div class="w-full bg-gray-200 rounded-full h-3 mb-6">
                <div
                  class="bg-blue-600 h-3 rounded-full transition-all"
                  :style="{ width: (actividad.porcentaje_avance || 0) + '%' }"
                ></div>
              </div>

              <div class="prose max-w-none">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Descripción</h3>
                <p class="text-gray-700">{{ actividad.descripcion }}</p>
              </div>

              <div v-if="actividad.observaciones" class="mt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Observaciones</h3>
                <p class="text-gray-700">{{ actividad.observaciones }}</p>
              </div>
            </Card>

            <Card title="Información de Fechas y Presupuesto">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="text-sm font-medium text-gray-500">Fecha de Inicio</label>
                  <p class="text-base text-gray-900">{{ new Date(actividad.fecha_inicio).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Fecha de Finalización</label>
                  <p class="text-base text-gray-900">{{ new Date(actividad.fecha_fin).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Presupuesto Aprobado</label>
                  <p class="text-2xl font-bold text-blue-600">Bs. {{ actividad.presupuesto_aprobado }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Presupuesto Ejecutado</label>
                  <p class="text-2xl font-bold text-green-600">Bs. {{ actividad.presupuesto_ejecutado }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Saldo Presupuesto</label>
                  <p class="text-xl font-semibold" :class="(Number(actividad.presupuesto_aprobado || 0) - Number(actividad.presupuesto_ejecutado || 0)) >= 0 ? 'text-green-600' : 'text-red-600'">
                    Bs. {{ (Number(actividad.presupuesto_aprobado || 0) - Number(actividad.presupuesto_ejecutado || 0)).toFixed(2) }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Responsable</label>
                  <p class="text-base text-gray-900">{{ actividad.responsable?.name || 'No asignado' }}</p>
                  <p v-if="actividad.responsable" class="text-sm text-gray-500">{{ actividad.responsable.email }}</p>
                </div>
              </div>
            </Card>

            <Card v-if="actividad.reunion" title="Reunión Asociada">
              <div class="flex items-start justify-between">
                <div>
                  <h4 class="font-medium text-gray-900">{{ actividad.reunion.titulo }}</h4>
                  <p class="text-sm text-gray-600 mt-1">
                    Fecha: {{ new Date(actividad.reunion.fecha_reunion).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                  </p>
                  <p class="text-sm text-gray-600">Lugar: {{ actividad.reunion.lugar }}</p>
                </div>
                <Button
                  size="sm"
                  variant="ghost"
                  @click="router.visit(route('reuniones.show', actividad.reunion.id))"
                >
                  Ver Reunión
                </Button>
              </div>
            </Card>

            <Card title="Aportes Asociados">
              <div v-if="actividad.aportes && actividad.aportes.length > 0">
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vivienda</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Monto</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pagado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vencimiento</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                      <tr v-for="aporte in actividad.aportes" :key="aporte.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ aporte.vivienda?.numero }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">Bs. {{ aporte.monto }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">Bs. {{ aporte.monto_pagado }}</td>
                        <td class="px-4 py-3 text-sm">
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
                        <td class="px-4 py-3 text-sm text-gray-900">
                          {{ new Date(aporte.fecha_vencimiento).toLocaleDateString('es-BO') }}
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay aportes asociados a esta actividad
              </div>
            </Card>
          </div>

          <div class="space-y-6">
            <Card title="Resumen Financiero">
              <div class="space-y-4">
                <div class="bg-blue-50 rounded-lg p-4">
                  <p class="text-xs text-blue-600 font-medium uppercase">Total Recaudado</p>
                  <p class="text-2xl font-bold text-blue-900">Bs. {{ Number(actividad.total_recaudado || 0).toFixed(2) }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                  <p class="text-xs text-red-600 font-medium uppercase">Total Pendiente</p>
                  <p class="text-2xl font-bold text-red-900">Bs. {{ Number(actividad.total_pendiente || 0).toFixed(2) }}</p>
                </div>
                <div class="border-t-2 border-gray-200 pt-4">
                  <p class="text-xs text-gray-600 font-medium uppercase">Total Esperado</p>
                  <p class="text-2xl font-bold text-gray-900">
                    Bs. {{ (Number(actividad.total_recaudado || 0) + Number(actividad.total_pendiente || 0)).toFixed(2) }}
                  </p>
                </div>
              </div>
            </Card>

            <Card title="Estadísticas">
              <div class="space-y-3">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total Aportes</span>
                  <span class="font-semibold text-gray-900">{{ actividad.aportes?.length || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Aportes Pagados</span>
                  <span class="font-semibold text-green-600">
                    {{ actividad.aportes?.filter(a => a.estado === 'PAGADO').length || 0 }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Aportes Pendientes</span>
                  <span class="font-semibold text-yellow-600">
                    {{ actividad.aportes?.filter(a => a.estado === 'PENDIENTE').length || 0 }}
                  </span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Aportes Vencidos</span>
                  <span class="font-semibold text-red-600">
                    {{ actividad.aportes?.filter(a => a.estado === 'VENCIDO').length || 0 }}
                  </span>
                </div>
              </div>
            </Card>

            <Card title="Duración">
              <div class="space-y-3">
                <div>
                  <p class="text-sm text-gray-600">Días Totales</p>
                  <p class="text-xl font-bold text-gray-900">
                    {{ Math.ceil((new Date(actividad.fecha_fin) - new Date(actividad.fecha_inicio)) / (1000 * 60 * 60 * 24)) }} días
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Días Transcurridos</p>
                  <p class="text-xl font-bold text-gray-900">
                    {{ Math.max(0, Math.ceil((new Date() - new Date(actividad.fecha_inicio)) / (1000 * 60 * 60 * 24))) }} días
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Días Restantes</p>
                  <p class="text-xl font-bold" :class="Math.ceil((new Date(actividad.fecha_fin) - new Date()) / (1000 * 60 * 60 * 24)) < 0 ? 'text-red-600' : 'text-green-600'">
                    {{ Math.ceil((new Date(actividad.fecha_fin) - new Date()) / (1000 * 60 * 60 * 24)) }} días
                  </p>
                </div>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'

const props = defineProps({
  actividad: Object
})

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
