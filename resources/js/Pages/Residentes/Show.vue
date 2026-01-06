<template>
  <AppLayout title="Detalle Residente">
    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ residente.nombres }} {{ residente.apellido_paterno }} {{ residente.apellido_materno }}
            </h1>
            <p class="mt-2 text-sm text-gray-600">Información detallada del residente</p>
          </div>
          <div class="flex gap-2">
            <Button
              variant="primary"
              @click="router.visit(route('residentes.edit', residente.id))"
            >
              Editar
            </Button>
            <Button
              variant="ghost"
              @click="router.visit(route('residentes.index'))"
            >
              Volver
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-1">
            <Card>
              <div class="text-center">
                <img
                  v-if="residente.fotografia"
                  :src="`/storage/${residente.fotografia}`"
                  :alt="residente.nombres"
                  class="h-48 w-48 rounded-full object-cover mx-auto mb-4"
                />
                <div v-else class="h-48 w-48 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-4">
                  <span class="text-gray-500 font-bold text-6xl">
                    {{ residente.nombres.charAt(0) }}{{ residente.apellido_paterno.charAt(0) }}
                  </span>
                </div>

                <h2 class="text-xl font-semibold text-gray-900">
                  {{ residente.nombres }} {{ residente.apellido_paterno }}
                </h2>
                <p class="text-sm text-gray-600">{{ residente.user?.email }}</p>

                <div class="mt-4">
                  <span
                    class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full"
                    :class="{
                      'bg-blue-100 text-blue-800': residente.tipo_residente === 'PROPIETARIO',
                      'bg-green-100 text-green-800': residente.tipo_residente === 'INQUILINO',
                      'bg-purple-100 text-purple-800': residente.tipo_residente === 'FAMILIAR'
                    }"
                  >
                    {{ residente.tipo_residente }}
                  </span>
                </div>
              </div>
            </Card>

            <Card title="Vivienda" class="mt-6">
              <div class="space-y-3">
                <div>
                  <label class="text-sm font-medium text-gray-500">Número</label>
                  <p class="text-base text-gray-900">{{ residente.vivienda?.numero }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Dirección</label>
                  <p class="text-base text-gray-900">{{ residente.vivienda?.direccion }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Tipo</label>
                  <p class="text-base text-gray-900">{{ residente.vivienda?.tipo }}</p>
                </div>
                <Button
                  variant="ghost"
                  size="sm"
                  class="w-full mt-3"
                  @click="router.visit(route('viviendas.show', residente.vivienda?.id))"
                >
                  Ver Detalles de Vivienda
                </Button>
              </div>
            </Card>
          </div>

          <div class="lg:col-span-2 space-y-6">
            <Card title="Información Personal">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Nombres Completos</label>
                  <p class="text-base text-gray-900">
                    {{ residente.nombres }} {{ residente.apellido_paterno }} {{ residente.apellido_materno }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Carnet de Identidad</label>
                  <p class="text-base text-gray-900">{{ residente.ci }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Fecha de Nacimiento</label>
                  <p class="text-base text-gray-900">
                    {{ residente.fecha_nacimiento ? new Date(residente.fecha_nacimiento).toLocaleDateString('es-BO') : 'No especificada' }}
                  </p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Edad</label>
                  <p class="text-base text-gray-900">
                    {{ residente.fecha_nacimiento ? calcularEdad(residente.fecha_nacimiento) + ' años' : 'No especificada' }}
                  </p>
                </div>
              </div>
            </Card>

            <Card title="Información de Contacto">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Correo Electrónico</label>
                  <p class="text-base text-gray-900">{{ residente.user?.email }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Rol de Usuario</label>
                  <p class="text-base text-gray-900">{{ residente.user?.rol }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Celular</label>
                  <p class="text-base text-gray-900">{{ residente.celular || 'No especificado' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Teléfono</label>
                  <p class="text-base text-gray-900">{{ residente.telefono || 'No especificado' }}</p>
                </div>
              </div>
            </Card>

            <Card title="Participación en Reuniones">
              <div v-if="residente.participaciones_reuniones && residente.participaciones_reuniones.length > 0">
                <div class="space-y-3">
                  <div
                    v-for="participacion in residente.participaciones_reuniones"
                    :key="participacion.id"
                    class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                  >
                    <div class="flex justify-between items-start">
                      <div class="flex-1">
                        <h4 class="font-medium text-gray-900">{{ participacion.reunion.titulo }}</h4>
                        <p class="text-sm text-gray-600 mt-1">
                          Fecha: {{ new Date(participacion.reunion.fecha_reunion).toLocaleDateString('es-BO') }}
                        </p>
                        <p class="text-sm text-gray-600">
                          Lugar: {{ participacion.reunion.lugar }}
                        </p>
                        <p v-if="participacion.pivot.observaciones" class="text-sm text-gray-600 mt-2">
                          Observaciones: {{ participacion.pivot.observaciones }}
                        </p>
                      </div>
                      <span
                        class="px-2 py-1 text-xs font-semibold rounded-full"
                        :class="participacion.pivot.asistio ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      >
                        {{ participacion.pivot.asistio ? 'Asistió' : 'No asistió' }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay registro de participación en reuniones
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'

const props = defineProps({
  residente: Object
})

const calcularEdad = (fechaNacimiento) => {
  const hoy = new Date()
  const nacimiento = new Date(fechaNacimiento)
  let edad = hoy.getFullYear() - nacimiento.getFullYear()
  const mes = hoy.getMonth() - nacimiento.getMonth()
  if (mes < 0 || (mes === 0 && hoy.getDate() < nacimiento.getDate())) {
    edad--
  }
  return edad
}
</script>
