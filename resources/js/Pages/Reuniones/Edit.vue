<template>
  <AppLayout title="Editar Reunión">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Editar Reunión</h1>
          <p class="mt-2 text-sm text-gray-600">Actualizar la información de la reunión</p>
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

              <Select
                v-model="form.estado"
                label="Estado"
                required
                :error="form.errors.estado"
              >
                <option value="CONVOCADA">Convocada</option>
                <option value="REALIZADA">Realizada</option>
                <option value="CANCELADA">Cancelada</option>
              </Select>
            </div>
          </Card>

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('reuniones.show', reunion.id))"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Actualizar Reunión
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
  reunion: Object
})

// Formatear fecha para input date
const formatFechaParaDate = (fecha) => {
  if (!fecha) return ''
  const date = new Date(fecha)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Formatear fecha para input datetime-local
const formatFechaParaDateTime = (fecha) => {
  if (!fecha) return ''
  const date = new Date(fecha)
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')
  return `${year}-${month}-${day}T${hours}:${minutes}`
}

const form = useForm({
  titulo: props.reunion?.titulo || '',
  descripcion: props.reunion?.descripcion || '',
  fecha_convocatoria: formatFechaParaDate(props.reunion?.fecha_convocatoria) || '',
  fecha_reunion: formatFechaParaDateTime(props.reunion?.fecha_reunion) || '',
  lugar: props.reunion?.lugar || '',
  orden_dia: props.reunion?.orden_dia || '',
  estado: props.reunion?.estado || 'CONVOCADA'
})

const submit = () => {
  form.put(route('reuniones.update', props.reunion.id))
}
</script>
