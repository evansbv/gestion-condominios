<template>
  <AppLayout title="Editar Comunicación">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Editar Comunicación</h1>
          <p class="mt-2 text-sm text-gray-600">Actualizar la información del borrador</p>
        </div>

        <Alert
          v-if="Object.keys(form.errors).length > 0"
          type="error"
          message="Por favor corrija los errores en el formulario"
        />

        <form @submit.prevent="submit">
          <Card title="Información General" class="mb-6">
            <div class="grid grid-cols-1 gap-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Select
                  v-model="form.tipo"
                  label="Tipo"
                  required
                  :error="form.errors.tipo"
                >
                  <option value="">Seleccione un tipo</option>
                  <option value="COMUNICADO">Comunicado</option>
                  <option value="CONVOCATORIA">Convocatoria</option>
                  <option value="QUEJA">Queja</option>
                  <option value="RECOMENDACION">Recomendación</option>
                </Select>

                <Select
                  v-model="form.prioridad"
                  label="Prioridad"
                  required
                  :error="form.errors.prioridad"
                >
                  <option value="">Seleccione prioridad</option>
                  <option value="BAJA">Baja</option>
                  <option value="MEDIA">Media</option>
                  <option value="ALTA">Alta</option>
                  <option value="URGENTE">Urgente</option>
                </Select>
              </div>

              <Input
                v-model="form.asunto"
                label="Asunto"
                required
                :error="form.errors.asunto"
                hint="Título breve de la comunicación"
              />

              <Textarea
                v-model="form.contenido"
                label="Contenido"
                required
                :rows="8"
                :error="form.errors.contenido"
                hint="Mensaje de la comunicación"
              />

              <Input
                v-model="form.fecha_envio"
                label="Fecha de Envío (Opcional)"
                type="datetime-local"
                :error="form.errors.fecha_envio"
                hint="Si no se especifica, se usará la fecha y hora actual al enviar"
              />

              <div>
                <FileInput
                  v-model="form.archivo_adjunto"
                  label="Archivo Adjunto (Opcional)"
                  accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                  :error="form.errors.archivo_adjunto"
                  hint="PDF, DOC, DOCX, JPG, PNG (Máx. 10MB)"
                />
                <div v-if="comunicacion.archivo_adjunto && !form.archivo_adjunto" class="mt-2">
                  <p class="text-sm text-gray-600">
                    Archivo actual:
                    <a
                      :href="`/storage/${comunicacion.archivo_adjunto}`"
                      target="_blank"
                      class="text-blue-600 hover:text-blue-800"
                    >
                      Ver archivo adjunto
                    </a>
                  </p>
                  <p class="text-xs text-gray-500 mt-1">Seleccione un nuevo archivo solo si desea reemplazarlo</p>
                </div>
              </div>
            </div>
          </Card>

          <Card title="Destinatarios" class="mb-6">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Seleccionar Destinatarios <span class="text-red-500">*</span>
              </label>
              <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-300 rounded-md p-3">
                <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded">
                  <input
                    type="checkbox"
                    @change="toggleTodos"
                    :checked="todosSeleccionados"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <span class="font-medium text-gray-900">Seleccionar Todos</span>
                </label>
                <div class="border-t border-gray-200 my-2"></div>
                <label
                  v-for="usuario in usuarios"
                  :key="usuario.id"
                  class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
                >
                  <input
                    type="checkbox"
                    :value="usuario.id"
                    v-model="form.destinatarios"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <div class="flex-1">
                    <span class="text-sm font-medium text-gray-900">{{ usuario.name }}</span>
                    <span class="text-xs text-gray-500 ml-2">({{ usuario.rol }})</span>
                    <p class="text-xs text-gray-500">{{ usuario.email }}</p>
                  </div>
                </label>
              </div>
              <p v-if="form.errors.destinatarios" class="mt-1 text-sm text-red-600">
                {{ form.errors.destinatarios }}
              </p>
              <p class="mt-1 text-sm text-gray-500">
                {{ form.destinatarios.length }} destinatario(s) seleccionado(s)
              </p>
            </div>

            <div class="space-y-4">
              <label class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="form.enviar_email"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                />
                <span class="text-sm font-medium text-gray-700">
                  Enviar notificación por correo electrónico
                </span>
              </label>
            </div>
          </Card>

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('comunicaciones.index'))"
            >
              Cancelar
            </Button>
            <Button
              type="button"
              variant="secondary"
              @click="guardarBorrador"
              :loading="form.processing"
            >
              Guardar Cambios
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Enviar Comunicación
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
import FileInput from '@/Components/Form/FileInput.vue'
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  comunicacion: Object,
  usuarios: Array
})

// Formatear fecha para input datetime-local
const formatFechaParaInput = (fecha) => {
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
  tipo: props.comunicacion?.tipo || '',
  asunto: props.comunicacion?.asunto || '',
  contenido: props.comunicacion?.contenido || '',
  fecha_envio: formatFechaParaInput(props.comunicacion?.fecha_envio) || '',
  prioridad: props.comunicacion?.prioridad || 'MEDIA',
  archivo_adjunto: null,
  destinatarios: props.comunicacion?.destinatarios?.map(d => d.id) || [],
  enviar_email: true,
  guardar_como_borrador: false
})

const todosSeleccionados = computed(() => {
  return form.destinatarios.length === props.usuarios.length && props.usuarios.length > 0
})

const toggleTodos = (event) => {
  if (event.target.checked) {
    form.destinatarios = props.usuarios.map(u => u.id)
  } else {
    form.destinatarios = []
  }
}

const submit = () => {
  form.guardar_como_borrador = false
  form.put(route('comunicaciones.update', props.comunicacion.id), {
    forceFormData: true
  })
}

const guardarBorrador = () => {
  form.guardar_como_borrador = true
  form.put(route('comunicaciones.update', props.comunicacion.id), {
    forceFormData: true
  })
}
</script>
