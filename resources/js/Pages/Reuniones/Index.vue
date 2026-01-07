<template>
  <AppLayout title="Reuniones">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Reuniones</h1>
            <p class="mt-2 text-sm text-gray-600">Gestión de reuniones y asambleas del condominio</p>
          </div>
          <Button v-if="isAdmin" 
            @click="router.visit(route('reuniones.create'))"
            variant="primary"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nueva Reunión
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
            <Select v-model="searchForm.estado" label="Estado">
              <option value="TODOS">Todos</option>
              <option value="CONVOCADA">Convocada</option>
              <option value="REALIZADA">Realizada</option>
              <option value="CANCELADA">Cancelada</option>
            </Select>

            <Select v-model="searchForm.anio" label="Año">
              <option value="TODOS">Todos</option>
              <option v-for="anio in aniosDisponibles" :key="anio" :value="anio">
                {{ anio }}
              </option>
            </Select>

            <div class="flex items-end gap-2">
              <Button type="submit" variant="primary" class="flex-1">Filtrar</Button>
              <Button type="button" variant="ghost" @click="clearFilters">Limpiar</Button>
            </div>
          </form>
        </Card>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="reunion in reuniones.data"
            :key="reunion.id"
            class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow cursor-pointer"
            @click="router.visit(route('reuniones.show', reunion.id))"
          >
            <div
              class="h-2"
              :class="{
                'bg-blue-500': reunion.estado === 'CONVOCADA',
                'bg-green-500': reunion.estado === 'REALIZADA',
                'bg-red-500': reunion.estado === 'CANCELADA'
              }"
            ></div>

            <div class="p-6">
              <div class="flex justify-between items-start mb-3">
                <span
                  class="px-2 py-1 text-xs font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800': reunion.estado === 'CONVOCADA',
                    'bg-green-100 text-green-800': reunion.estado === 'REALIZADA',
                    'bg-red-100 text-red-800': reunion.estado === 'CANCELADA'
                  }"
                >
                  {{ reunion.estado }}
                </span>
              </div>

              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ reunion.titulo }}</h3>
              <p class="text-sm text-gray-600 mb-4 line-clamp-2">{{ reunion.descripcion }}</p>

              <div class="space-y-2 text-xs text-gray-600">
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <span>{{ new Date(reunion.fecha_reunion).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                </div>

                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  </svg>
                  <span>{{ reunion.lugar }}</span>
                </div>

                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span>{{ reunion.participantes_asistieron || 0 }} / {{ reunion.total_participantes || 0 }} asistentes</span>
                </div>
              </div>

              <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                <Button v-if="isAdmin" 
                  size="sm"
                  variant="primary"
                  class="flex-1"
                  @click.stop="router.visit(route('reuniones.edit', reunion.id))"
                >
                  Editar
                </Button>
                <Button
                  size="sm"
                  variant="ghost"
                  @click.stop="router.visit(route('reuniones.show', reunion.id))"
                >
                  Ver
                </Button>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6">
          <Pagination
            :links="reuniones.links"
            :from="reuniones.from"
            :to="reuniones.to"
            :total="reuniones.total"
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
  reuniones: Object,
  aniosDisponibles: Array,
  filters: Object
})

const searchForm = reactive({
  estado: props.filters?.estado || 'TODOS',
  anio: props.filters?.anio || 'TODOS'
})

const handleSearch = () => {
  router.get(route('reuniones.index'), searchForm, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchForm.estado = 'TODOS'
  searchForm.anio = 'TODOS'
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
