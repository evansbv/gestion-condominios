<template>
  <AppLayout title="Comunicaciones">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Comunicaciones</h1>
            <p class="mt-2 text-sm text-gray-600">Avisos, convocatorias y comunicados del condominio</p>
          </div>
          <Button
            @click="router.visit(route('comunicaciones.create'))"
            variant="primary"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Comunicación
          </Button>
        </div>

        <Alert
          v-if="$page.props.flash.success"
          type="success"
          :message="$page.props.flash.success"
          :auto-dismiss="5000"
        />

        <Card class="mb-6">
          <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <Select v-model="searchForm.tipo" label="Tipo">
              <option value="TODOS">Todos</option>
              <option value="COMUNICADO">Comunicado</option>
              <option value="CONVOCATORIA">Convocatoria</option>
              <option value="QUEJA">Queja</option>
              <option value="RECOMENDACION">Recomendación</option>
            </Select>

            <Select v-model="searchForm.prioridad" label="Prioridad">
              <option value="TODOS">Todas</option>
              <option value="BAJA">Baja</option>
              <option value="MEDIA">Media</option>
              <option value="ALTA">Alta</option>
              <option value="URGENTE">Urgente</option>
            </Select>

            <Select v-model="searchForm.estado" label="Estado">
              <option value="TODOS">Todos</option>
              <option value="BORRADOR">Borrador</option>
              <option value="ENVIADO">Enviado</option>
            </Select>

            <div class="flex items-end gap-2">
              <Button type="submit" variant="primary" class="flex-1">Filtrar</Button>
              <Button type="button" variant="ghost" @click="clearFilters">Limpiar</Button>
            </div>
          </form>
        </Card>

        <div class="space-y-4">
          <div
            v-for="comunicacion in comunicaciones.data"
            :key="comunicacion.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow cursor-pointer"
            @click="router.visit(route('comunicaciones.show', comunicacion.id))"
          >
            <div class="p-6">
              <div class="flex items-start justify-between mb-3">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': comunicacion.tipo === 'COMUNICADO',
                        'bg-purple-100 text-purple-800': comunicacion.tipo === 'CONVOCATORIA',
                        'bg-red-100 text-red-800': comunicacion.tipo === 'QUEJA',
                        'bg-green-100 text-green-800': comunicacion.tipo === 'RECOMENDACION'
                      }"
                    >
                      {{ comunicacion.tipo }}
                    </span>
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-gray-100 text-gray-800': comunicacion.prioridad === 'BAJA',
                        'bg-yellow-100 text-yellow-800': comunicacion.prioridad === 'MEDIA',
                        'bg-orange-100 text-orange-800': comunicacion.prioridad === 'ALTA',
                        'bg-red-100 text-red-800': comunicacion.prioridad === 'URGENTE'
                      }"
                    >
                      {{ comunicacion.prioridad }}
                    </span>
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-yellow-100 text-yellow-800': comunicacion.estado === 'BORRADOR',
                        'bg-green-100 text-green-800': comunicacion.estado === 'ENVIADO'
                      }"
                    >
                      {{ comunicacion.estado }}
                    </span>
                    <span
                      v-if="!comunicacion.leida && comunicacion.estado === 'ENVIADO'"
                      class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-500 text-white"
                    >
                      NUEVO
                    </span>
                  </div>

                  <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ comunicacion.asunto }}</h3>
                  <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ comunicacion.contenido }}</p>

                  <div class="flex items-center gap-4 text-xs text-gray-600">
                    <div class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                      </svg>
                      <span>{{ comunicacion.remitente?.name }}</span>
                    </div>
                    <div v-if="comunicacion.fecha_envio" class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                      </svg>
                      <span>{{ new Date(comunicacion.fecha_envio).toLocaleDateString('es-BO') }}</span>
                    </div>
                    <div v-if="comunicacion.total_destinatarios" class="flex items-center">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                      </svg>
                      <span>{{ comunicacion.total_leidas }} / {{ comunicacion.total_destinatarios }} leídas</span>
                    </div>
                    <div v-if="comunicacion.archivo_adjunto" class="flex items-center text-blue-600">
                      <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                      </svg>
                      <span>Adjunto</span>
                    </div>
                  </div>
                </div>

                <div class="flex gap-2 ml-4">
                  <Button
                    v-if="comunicacion.estado === 'BORRADOR'"
                    size="sm"
                    variant="primary"
                    @click.stop="router.visit(route('comunicaciones.edit', comunicacion.id))"
                  >
                    Editar
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    @click.stop="router.visit(route('comunicaciones.show', comunicacion.id))"
                  >
                    Ver
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="comunicaciones.data.length === 0" class="text-center py-12">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900">No hay comunicaciones</h3>
          <p class="mt-1 text-sm text-gray-500">Comienza creando una nueva comunicación.</p>
        </div>

        <div class="mt-6">
          <Pagination
            :links="comunicaciones.links"
            :from="comunicaciones.from"
            :to="comunicaciones.to"
            :total="comunicaciones.total"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Select from '@/Components/Form/Select.vue'
import Alert from '@/Components/Alert.vue'
import Pagination from '@/Components/Pagination.vue'

const props = defineProps({
  comunicaciones: Object,
  filters: Object
})

const searchForm = reactive({
  tipo: props.filters?.tipo || 'TODOS',
  prioridad: props.filters?.prioridad || 'TODOS',
  estado: props.filters?.estado || 'TODOS'
})

const handleSearch = () => {
  router.get(route('comunicaciones.index'), searchForm, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchForm.tipo = 'TODOS'
  searchForm.prioridad = 'TODOS'
  searchForm.estado = 'TODOS'
  handleSearch()
}
</script>
