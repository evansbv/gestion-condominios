<template>
  <AppLayout title="Crear Reunión">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Convocar Nueva Reunión</h1>
          <p class="mt-2 text-sm text-gray-600">Complete el formulario para convocar una reunión de propietarios</p>
        </div>

        <Alert
          v-if="Object.keys(form.errors).length > 0"
          type="error"
          message="Por favor corrija los errores en el formulario"
        />

        <form @submit.prevent="submit">
          <Card title="Información General" class="mb-6">
            <div class="grid grid-cols-1 gap-4">
              <Input
                v-model="form.titulo"
                label="Título"
                required
                :error="form.errors.titulo"
                hint="Título descriptivo de la reunión"
              />

              <Textarea
                v-model="form.descripcion"
                label="Descripción"
                required
                :rows="4"
                :error="form.errors.descripcion"
                hint="Descripción general de la reunión"
              />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Input
                  v-model="form.fecha_convocatoria"
                  label="Fecha de Convocatoria"
                  type="date"
                  required
                  :error="form.errors.fecha_convocatoria"
                  hint="Fecha en que se convoca la reunión"
                />

                <Input
                  v-model="form.fecha_reunion"
                  label="Fecha de la Reunión"
                  type="datetime-local"
                  required
                  :error="form.errors.fecha_reunion"
                  hint="Fecha y hora de realización"
                />
              </div>

              <Input
                v-model="form.lugar"
                label="Lugar"
                required
                :error="form.errors.lugar"
                hint="Ubicación donde se realizará la reunión"
              />

              <Textarea
                v-model="form.orden_dia"
                label="Orden del Día"
                required
                :rows="8"
                :error="form.errors.orden_dia"
                hint="Temas a tratar en la reunión (uno por línea)"
              />
            </div>
          </Card>

          <Card title="Opciones de Convocatoria" class="mb-6">
            <div class="space-y-4">
              <label class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="form.enviar_convocatoria"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="text-sm font-medium text-gray-700">
                  Enviar convocatoria por correo electrónico a todos los propietarios
                </span>
              </label>
              <p class="text-xs text-gray-500 ml-6">
                Se enviará un correo con los detalles de la reunión a todos los propietarios activos
              </p>
            </div>
          </Card>

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('reuniones.index'))"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Crear Reunión
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
import Textarea from '@/Components/Form/Textarea.vue'
import Alert from '@/Components/Alert.vue'

const form = useForm({
  titulo: '',
  descripcion: '',
  fecha_convocatoria: '',
  fecha_reunion: '',
  lugar: '',
  orden_dia: '',
  enviar_convocatoria: true
})

const submit = () => {
  form.post(route('reuniones.store'))
}
</script>
