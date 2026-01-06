<template>
  <AppLayout title="Actividades">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Tablero de Actividades</h1>
            <p class="mt-2 text-sm text-gray-600">Actividades y proyectos del condominio</p>
          </div>
          <Button
            v-if="canCreate"
            @click="router.visit(route('actividades.create'))"
            variant="primary"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Actividad
          </Button>
        </div>

        <Alert
          v-if="$page.props.flash.success"
          type="success"
          :message="$page.props.flash.success"
          :auto-dismiss="5000"
        />

        <Card class="mb-6">
          <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Select
              v-model="searchForm.estado"
              label="Estado"
            >
              <option value="TODOS">Todos</option>
              <option value="PLANIFICADA">Planificada</option>
              <option value="EN_PROGRESO">En Progreso</option>
              <option value="COMPLETADA">Completada</option>
              <option value="CANCELADA">Cancelada</option>
            </Select>

            <Select
              v-model="searchForm.tipo"
              label="Tipo de Actividad"
            >
              <option value="TODOS">Todos</option>
              <option value="MANTENIMIENTO_CHURRASQUERA">Mantenimiento Churrasquera</option>
              <option value="LIMPIEZA_ACERAS">Limpieza Aceras</option>
              <option value="MANTENIMIENTO_CALLES">Mantenimiento Calles</option>
              <option value="JARDINERIA">Jardinería</option>
              <option value="SEGURIDAD">Seguridad</option>
              <option value="OTRO">Otro</option>
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

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="actividad in actividades.data"
            :key="actividad.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow cursor-pointer"
            @click="router.visit(route('actividades.show', actividad.id))"
          >
            <div
              class="h-2"
              :class="{
                'bg-blue-500': actividad.estado === 'PLANIFICADA',
                'bg-yellow-500': actividad.estado === 'EN_PROGRESO',
                'bg-green-500': actividad.estado === 'COMPLETADA',
                'bg-red-500': actividad.estado === 'CANCELADA'
              }"
            ></div>

            <div class="p-6">
              <div class="flex justify-between items-start mb-3">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800': actividad.estado === 'PLANIFICADA',
                    'bg-yellow-100 text-yellow-800': actividad.estado === 'EN_PROGRESO',
                    'bg-green-100 text-green-800': actividad.estado === 'COMPLETADA',
                    'bg-red-100 text-red-800': actividad.estado === 'CANCELADA'
                  }"
                >
                  {{ actividad.estado ? actividad.estado.replace('_', ' ') : 'Sin estado' }}
                </span>
                <span class="text-xs text-gray-500">{{ actividad.tipo ? actividad.tipo.replace(/_/g, ' ') : 'Sin tipo' }}</span>
              </div>

              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ actividad.titulo }}</h3>
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ actividad.descripcion }}</p>

              <div class="space-y-2 text-xs text-gray-600">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ new Date(actividad.fecha_inicio).toLocaleDateString('es-BO') }} - {{ new Date(actividad.fecha_fin).toLocaleDateString('es-BO') }}</span>
                </div>

                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Presupuesto: Bs. {{ actividad.presupuesto_aprobado }} | Ejecutado: Bs. {{ actividad.presupuesto_ejecutado }}</span>
                </div>

                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>Responsable: {{ actividad.responsable?.name || 'No asignado' }}</span>
                </div>
              </div>

              <div class="mt-4">
                <div class="flex justify-between text-xs text-gray-600 mb-1">
                  <span>Avance</span>
                  <span>{{ actividad.porcentaje_avance || 0 }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-blue-600 h-2 rounded-full transition-all"
                    :style="{ width: (actividad.porcentaje_avance || 0) + '%' }"
                  ></div>
                </div>
              </div>

              <div v-if="actividad.total_recaudado !== undefined && actividad.total_recaudado !== null" class="mt-4 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-2 gap-2 text-center">
                  <div>
                    <p class="text-xs text-gray-500">Recaudado</p>
                    <p class="text-sm font-semibold text-green-600">Bs. {{ Number(actividad.total_recaudado || 0).toFixed(2) }}</p>
                  </div>
                  <div>
                    <p class="text-xs text-gray-500">Pendiente</p>
                    <p class="text-sm font-semibold text-red-600">Bs. {{ Number(actividad.total_pendiente || 0).toFixed(2) }}</p>
                  </div>
                </div>
              </div>

              <div v-if="canEdit" class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                <Button
                  size="sm"
                  variant="primary"
                  class="flex-1"
                  @click.stop="router.visit(route('actividades.edit', actividad.id))"
                >
                  Editar
                </Button>
                <Button
                  size="sm"
                  variant="ghost"
                  @click.stop="router.visit(route('actividades.show', actividad.id))"
                >
                  Ver
                </Button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="actividades.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay actividades</h3>
          <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva actividad para el condominio.</p>
        </div>

        <div class="mt-6">
          <Pagination
            v-if="actividades.links"
            :links="actividades.links"
            :from="actividades.from"
            :to="actividades.to"
            :total="actividades.total"
          />
        </div>
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
  actividades: Object,
  filters: Object
})

const canCreate = ref(true)
const canEdit = ref(true)

const searchForm = reactive({
  estado: props.filters?.estado || 'TODOS',
  tipo: props.filters?.tipo || 'TODOS'
})

const handleSearch = () => {
  router.get(route('actividades.index'), searchForm, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchForm.estado = 'TODOS'
  searchForm.tipo = 'TODOS'
  handleSearch()
}
</script>
