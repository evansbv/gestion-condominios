<template>
  <AppLayout title="Editar Residente">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Editar Residente</h1>
          <p class="mt-2 text-sm text-gray-600">Actualice la información del residente</p>
        </div>

        <Alert
          v-if="Object.keys(form.errors).length > 0"
          type="error"
          message="Por favor corrija los errores en el formulario"
        />

        <form @submit.prevent="submit">
          <Card title="Información de Acceso" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                v-model="form.email"
                label="Correo Electrónico"
                type="email"
                required
                :error="form.errors.email"
              />

              <Input
                v-model="form.password"
                label="Nueva Contraseña"
                type="password"
                :error="form.errors.password"
                hint="Dejar en blanco para mantener la actual"
              />
            </div>
          </Card>

          <Card title="Datos Personales" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Input
                v-model="form.nombres"
                label="Nombres"
                required
                :error="form.errors.nombres"
              />

              <Input
                v-model="form.apellido_paterno"
                label="Apellido Paterno"
                required
                :error="form.errors.apellido_paterno"
              />

              <Input
                v-model="form.apellido_materno"
                label="Apellido Materno"
                :error="form.errors.apellido_materno"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
              <Input
                v-model="form.nombre_completo"
                label="Nombre Completo (para usuario)"
                required
                :error="form.errors.nombre_completo"
              />

              <Input
                v-model="form.ci"
                label="Carnet de Identidad"
                required
                :error="form.errors.ci"
              />

              <Input
                v-model="form.fecha_nacimiento"
                label="Fecha de Nacimiento"
                type="date"
                :error="form.errors.fecha_nacimiento"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <Input
                v-model="form.celular"
                label="Celular"
                type="tel"
                :error="form.errors.celular"
              />

              <Input
                v-model="form.telefono"
                label="Teléfono"
                type="tel"
                :error="form.errors.telefono"
              />
            </div>

            <div class="mt-4">
              <div v-if="residente?.fotografia" class="mb-3">
                <label class="block text-sm font-medium text-gray-700 mb-2">Fotografía Actual</label>
                <img
                  :src="`/urbalemana/storage/${residente.fotografia}`"
                  :alt="residente?.nombres || 'Fotografía'"
                  class="h-32 w-32 rounded-lg object-cover"
                />
              </div>
              <FileInput
                @update:modelValue="form.fotografia = $event"
                label="Nueva Fotografía"
                accept="image/*"
                :error="form.errors.fotografia"
                hint="Formato: JPG, PNG. Tamaño máximo: 2MB"
              />
            </div>
          </Card>

          <Card title="Información de Vivienda" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

              <Select
                v-model="form.tipo_residente"
                label="Tipo de Residente"
                required
                :error="form.errors.tipo_residente"
              >
                <option value="">Seleccione un tipo</option>
                <option value="PROPIETARIO">Propietario</option>
                <option value="INQUILINO">Inquilino</option>
                <option value="FAMILIAR">Familiar</option>
              </Select>
            </div>
          </Card>

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('residentes.index'))"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Actualizar Residente
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
import FileInput from '@/Components/Form/FileInput.vue'
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  residente: Object,
  viviendas: Array
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

const form = useForm({
  _method: 'PUT',
  email: props.residente?.user?.email || '',
  password: '',
  nombre_completo: props.residente?.user?.name || '',
  vivienda_id: props.residente?.vivienda_id || '',
  nombres: props.residente?.nombres || '',
  apellido_paterno: props.residente?.apellido_paterno || '',
  apellido_materno: props.residente?.apellido_materno || '',
  ci: props.residente?.ci || '',
  fecha_nacimiento: formatFechaParaDate(props.residente?.fecha_nacimiento) || '',
  telefono: props.residente?.telefono || '',
  celular: props.residente?.celular || '',
  fotografia: null,
  tipo_residente: props.residente?.tipo_residente || ''
})

const submit = () => {
  form.post(route('residentes.update', props.residente.id), {
    forceFormData: true
  })
}
</script>
