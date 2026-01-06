<template>
  <AppLayout title="Crear Residente">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Crear Nuevo Residente</h1>
          <p class="mt-2 text-sm text-gray-600">Complete el formulario para registrar un nuevo residente</p>
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
                hint="Se usará para iniciar sesión"
              />

              <Input
                v-model="form.password"
                label="Contraseña"
                type="password"
                required
                :error="form.errors.password"
                hint="Mínimo 6 caracteres"
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
                hint="Cómo aparecerá en el sistema"
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
              <FileInput
                @update:modelValue="form.fotografia = $event"
                label="Fotografía"
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
              Crear Residente
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
  viviendas: Array
})

const form = useForm({
  email: '',
  password: '',
  nombre_completo: '',
  vivienda_id: '',
  nombres: '',
  apellido_paterno: '',
  apellido_materno: '',
  ci: '',
  fecha_nacimiento: '',
  telefono: '',
  celular: '',
  fotografia: null,
  tipo_residente: ''
})

const submit = () => {
  form.post(route('residentes.store'))
}
</script>
