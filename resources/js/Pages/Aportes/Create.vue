<template>
  <AppLayout title="Crear Aporte">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Aporte</h1>
          <p class="mt-2 text-sm text-gray-600">Complete el formulario para registrar un nuevo aporte</p>
        </div>

        <Alert
          v-if="Object.keys(form.errors).length > 0"
          type="error"
          message="Por favor corrija los errores en el formulario"
        />

        <form @submit.prevent="submit">
          <Card title="Información del Aporte" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Select
                v-model="form.actividad_id"
                label="Actividad"
                required
                :error="form.errors.actividad_id"
              >
                <option value="">Seleccione una actividad</option>
                <option v-for="actividad in actividades" :key="actividad.id" :value="actividad.id">
                  {{ actividad.titulo }} - {{ actividad.tipo.replace(/_/g, ' ') }}
                </option>
              </Select>

              <Select
                v-model="form.vivienda_id"
                label="Vivienda"
                required
                :error="form.errors.vivienda_id"
              >
                <option value="">Seleccione una vivienda</option>
                <option v-for="vivienda in viviendas" :key="vivienda.id" :value="vivienda.id">
                  {{ vivienda.numero }} - {{ vivienda.direccion }}
                </option>
              </Select>

              <Input
                v-model="form.monto"
                label="Monto (Bs.)"
                type="number"
                step="0.01"
                min="0"
                required
                :error="form.errors.monto"
              />

              <Input
                v-model="form.fecha_vencimiento"
                label="Fecha de Vencimiento"
                type="date"
                required
                :error="form.errors.fecha_vencimiento"
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

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('aportes.index'))"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Crear Aporte
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Input from '@/Components/Form/Input.vue'
import Select from '@/Components/Form/Select.vue'
import Textarea from '@/Components/Form/Textarea.vue'
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  viviendas: Array,
  actividades: Array
})

const form = useForm({
  actividad_id: '',
  vivienda_id: '',
  monto: '',
  fecha_vencimiento: '',
  observaciones: ''
})

const submit = () => {
  form.post(route('aportes.store'))
}
</script>
