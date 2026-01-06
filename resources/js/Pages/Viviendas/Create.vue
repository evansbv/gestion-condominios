<template>
  <AppLayout title="Crear Vivienda">
    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Crear Nueva Vivienda</h1>
          <p class="mt-2 text-sm text-gray-600">Complete el formulario para registrar una nueva vivienda</p>
        </div>

        <Alert
          v-if="Object.keys(form.errors).length > 0"
          type="error"
          message="Por favor corrija los errores en el formulario"
        />

        <form @submit.prevent="submit">
          <Card title="Información Básica" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                v-model="form.numero"
                label="Número de Vivienda"
                required
                :error="form.errors.numero"
                hint="Ej: A-101, B-205, Casa 15"
              />

              <Select
                v-model="form.tipo"
                label="Tipo de Vivienda"
                required
                :error="form.errors.tipo"
              >
                <option value="">Seleccione un tipo</option>
                <option value="CASA">Casa</option>
                <option value="DEPARTAMENTO">Departamento</option>
                <option value="DUPLEX">Duplex</option>
                <option value="OTRO">Otro</option>
              </Select>
            </div>

            <div class="mt-4">
              <Input
                v-model="form.direccion"
                label="Dirección Completa"
                required
                :error="form.errors.direccion"
                hint="Calle, número, zona"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <Input
                v-model="form.area_m2"
                label="Área (m²)"
                type="number"
                step="0.01"
                min="0"
                :error="form.errors.area_m2"
              />

              <Input
                v-model="form.numero_habitantes"
                label="Número de Habitantes"
                type="number"
                min="0"
                :error="form.errors.numero_habitantes"
              />
            </div>

            <div class="mt-4">
              <Textarea
                v-model="form.observaciones"
                label="Observaciones"
                rows="3"
                :error="form.errors.observaciones"
                hint="Información adicional relevante"
              />
            </div>
          </Card>

          <Card title="Ubicación GPS" class="mb-6">
            <p class="text-sm text-gray-600 mb-4">
              Haga clic en el mapa para seleccionar la ubicación exacta de la vivienda.
              También puede arrastrar el marcador para ajustar la posición.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <Input
                v-model="form.latitud"
                label="Latitud"
                type="number"
                step="any"
                required
                :error="form.errors.latitud"
                @input="updateMapFromInputs"
              />

              <Input
                v-model="form.longitud"
                label="Longitud"
                type="number"
                step="any"
                required
                :error="form.errors.longitud"
                @input="updateMapFromInputs"
              />
            </div>

            <Map
              :model-value="mapLocation"
              @update:model-value="updateLocation"
              :editable="true"
              :center="[-16.5000, -68.1500]"
              :zoom="15"
              height="400px"
            />
          </Card>

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('viviendas.index'))"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Crear Vivienda
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Input from '@/Components/Form/Input.vue'
import Select from '@/Components/Form/Select.vue'
import Textarea from '@/Components/Form/Textarea.vue'
import Alert from '@/Components/Alert.vue'
import Map from '@/Components/Map.vue'

const form = useForm({
  numero: '',
  direccion: '',
  tipo: '',
  area_m2: '',
  latitud: -16.5000,
  longitud: -68.1500,
  numero_habitantes: '',
  observaciones: ''
})

const mapLocation = computed(() => ({
  lat: parseFloat(form.latitud) || -16.5000,
  lng: parseFloat(form.longitud) || -68.1500
}))

const updateLocation = (location) => {
  form.latitud = location.lat.toFixed(6)
  form.longitud = location.lng.toFixed(6)
}

const updateMapFromInputs = () => {
  // This will trigger the map to update when coordinates are manually entered
}

const submit = () => {
  form.post(route('viviendas.store'))
}
</script>
