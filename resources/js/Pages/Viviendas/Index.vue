<template>
  <AppLayout title="Viviendas">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Viviendas</h1>
          <div class="flex gap-3">
            <div class="flex bg-gray-100 rounded-lg p-1">
              <button
                @click="changeView('lista')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                :class="vistaMode === 'lista' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
              >
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                Lista
              </button>
              <button
                @click="changeView('mapa')"
                class="px-4 py-2 rounded-md text-sm font-medium transition-colors"
                :class="vistaMode === 'mapa' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600 hover:text-gray-900'"
              >
                <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                </svg>
                Mapa
              </button>
            </div>
            <Button
              @click="router.visit(route('viviendas.create'))"
              variant="primary"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
              </svg>
              Nueva Vivienda
            </Button>
          </div>
        </div>

        <Alert
          v-if="$page.props.flash.success"
          type="success"
          :message="$page.props.flash.success"
          :auto-dismiss="5000"
        />

        <Alert
          v-if="$page.props.flash.error"
          type="error"
          :message="$page.props.flash.error"
        />

        <Card class="mb-6">
          <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Input
              v-model="searchForm.search"
              label="Buscar"
              placeholder="Número, dirección..."
              type="text"
            />

            <Select
              v-model="searchForm.tipo"
              label="Tipo de Vivienda"
            >
              <option value="TODOS">Todos</option>
              <option value="CASA">Casa</option>
              <option value="DEPARTAMENTO">Departamento</option>
              <option value="DUPLEX">Duplex</option>
              <option value="OTRO">Otro</option>
            </Select>

            <div class="flex items-end gap-2">
              <Button type="submit" variant="primary" class="flex-1">
                Buscar
              </Button>
              <Button type="button" variant="ghost" @click="clearFilters">
                Limpiar
              </Button>
            </div>
          </form>
        </Card>

        <!-- Vista de Mapa -->
        <Card v-if="vistaMode === 'mapa'" title="Mapa de Viviendas">
          <Map
            :markers="mapMarkers"
            :center="[-16.5000, -68.1500]"
            :zoom="15"
            height="600px"
            @marker-click="handleMarkerClick"
          />
        </Card>

        <!-- Vista de Lista -->
        <Card v-else>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
              v-for="vivienda in viviendas.data"
              :key="vivienda.id"
              class="border border-gray-200 rounded-lg p-5 hover:shadow-lg transition-shadow cursor-pointer"
              @click="router.visit(route('viviendas.show', vivienda.id))"
            >
              <div class="flex justify-between items-start mb-3">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ vivienda.numero }}</h3>
                  <span
                    class="px-2 py-1 text-xs font-semibold rounded-full mt-1 inline-block"
                    :class="{
                      'bg-blue-100 text-blue-800': vivienda.tipo === 'CASA',
                      'bg-green-100 text-green-800': vivienda.tipo === 'DEPARTAMENTO',
                      'bg-purple-100 text-purple-800': vivienda.tipo === 'DUPLEX',
                      'bg-gray-100 text-gray-800': vivienda.tipo === 'OTRO'
                    }"
                  >
                    {{ vivienda.tipo }}
                  </span>
                </div>
                <button
                  @click.stop="viewOnMap(vivienda)"
                  class="text-gray-400 hover:text-blue-600 transition-colors"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
              </div>

              <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-start">
                  <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  </svg>
                  <span>{{ vivienda.direccion }}</span>
                </div>

                <div v-if="vivienda.area_m2" class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                  </svg>
                  <span>{{ vivienda.area_m2 }} m²</span>
                </div>

                <div v-if="vivienda.numero_habitantes" class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <span>{{ vivienda.numero_habitantes }} habitantes</span>
                </div>

                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>{{ vivienda.residentes?.length || 0 }} residentes activos</span>
                </div>
              </div>

              <div class="mt-4 pt-4 border-t border-gray-200 flex gap-2">
                <Button
                  size="sm"
                  variant="primary"
                  class="flex-1"
                  @click.stop="router.visit(route('viviendas.edit', vivienda.id))"
                >
                  Editar
                </Button>
                <Button
                  size="sm"
                  variant="ghost"
                  @click.stop="router.visit(route('viviendas.show', vivienda.id))"
                >
                  Ver
                </Button>
              </div>
            </div>
          </div>

          <template #footer>
            <Pagination
              v-if="viviendas.links"
              :links="viviendas.links"
              :from="viviendas.from"
              :to="viviendas.to"
              :total="viviendas.total"
            />
          </template>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Input from '@/Components/Form/Input.vue'
import Select from '@/Components/Form/Select.vue'
import Alert from '@/Components/Alert.vue'
import Pagination from '@/Components/Pagination.vue'
import Map from '@/Components/Map.vue'

const props = defineProps({
  viviendas: [Object, Array],
  filters: Object,
  vistaMode: {
    type: String,
    default: 'lista'
  }
})

const searchForm = reactive({
  search: props.filters?.search || '',
  tipo: props.filters?.tipo || 'TODOS'
})

const mapMarkers = computed(() => {
  const data = Array.isArray(props.viviendas) ? props.viviendas : props.viviendas.data || []
  return data.map(vivienda => ({
    lat: parseFloat(vivienda.latitud),
    lng: parseFloat(vivienda.longitud),
    popup: `
      <div class="text-sm">
        <strong class="block mb-1">${vivienda.numero}</strong>
        <span class="block text-gray-600">${vivienda.direccion}</span>
        <span class="block text-gray-600 mt-1">${vivienda.tipo}</span>
      </div>
    `,
    data: vivienda,
    clickable: true
  }))
})

const handleSearch = () => {
  router.get(route('viviendas.index'), {
    ...searchForm,
    vista: props.vistaMode
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchForm.search = ''
  searchForm.tipo = 'TODOS'
  handleSearch()
}

const changeView = (mode) => {
  router.get(route('viviendas.index'), {
    ...searchForm,
    vista: mode
  }, {
    preserveState: true,
    preserveScroll: true
  })
}

const viewOnMap = (vivienda) => {
  changeView('mapa')
}

const handleMarkerClick = (marker) => {
  if (marker.data) {
    router.visit(route('viviendas.show', marker.data.id))
  }
}
</script>
