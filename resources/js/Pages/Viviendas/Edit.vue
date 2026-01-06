<template>
  <AppLayout title="Editar Vivienda">
    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Editar Vivienda {{ vivienda.numero }}</h1>
          <p class="mt-2 text-sm text-gray-600">Actualice la información de la vivienda</p>
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
              />
            </div>
          </Card>

          <Card title="Ubicación GPS" class="mb-6">
            <p class="text-sm text-gray-600 mb-4">
              Haga clic en el mapa para actualizar la ubicación de la vivienda.
              Puede arrastrar el marcador para ajustar la posición.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <Input
                v-model="form.latitud"
                label="Latitud"
                type="number"
                step="any"
                required
                :error="form.errors.latitud"
              />

              <Input
                v-model="form.longitud"
                label="Longitud"
                type="number"
                step="any"
                required
                :error="form.errors.longitud"
              />
            </div>

            <Map
              :model-value="mapLocation"
              @update:model-value="updateLocation"
              :editable="true"
              :center="[parseFloat(vivienda.latitud), parseFloat(vivienda.longitud)]"
              :zoom="17"
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
              Actualizar Vivienda
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Input from '@/Components/Form/Input.vue'
import Select from '@/Components/Form/Select.vue'
import Textarea from '@/Components/Form/Textarea.vue'
import Alert from '@/Components/Alert.vue'
import Map from '@/Components/Map.vue'

const props = defineProps({
  vivienda: Object
})

const form = useForm({
  numero: props.vivienda.numero,
  direccion: props.vivienda.direccion,
  tipo: props.vivienda.tipo,
  area_m2: props.vivienda.area_m2,
  latitud: props.vivienda.latitud,
  longitud: props.vivienda.longitud,
  numero_habitantes: props.vivienda.numero_habitantes,
  observaciones: props.vivienda.observaciones
})

const mapLocation = computed(() => ({
  lat: parseFloat(form.latitud),
  lng: parseFloat(form.longitud)
}))

const updateLocation = (location) => {
  form.latitud = location.lat.toFixed(6)
  form.longitud = location.lng.toFixed(6)
}

const submit = () => {
  form.put(route('viviendas.update', props.vivienda.id))
}
</script>
