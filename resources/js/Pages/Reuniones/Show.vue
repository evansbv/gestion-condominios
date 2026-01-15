<template>
  <AppLayout title="Detalle de Reunión">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-start">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ reunion.titulo }}</h1>
            <p class="mt-2 text-sm text-gray-600">
              Convocada por {{ reunion.convocante?.name || 'N/A' }} el {{ formatDate(reunion.fecha_convocatoria) }}
            </p>
          </div>
          <div class="flex gap-2">
            <!-- Export Buttons - Conditional by status -->
            <Button
              v-if="reunion.estado === 'CONVOCADA'"
              variant="secondary"
              @click="exportarConvocatoria"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              Exportar Convocatoria
            </Button>

            <Button
              v-if="reunion.estado === 'REALIZADA'"
              variant="success"
              @click="exportarActa"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Exportar Acta
            </Button>

            <!-- Admin Edit Button -->
            <Button
              v-if="isAdmin && reunion.estado !== 'REALIZADA'"
              variant="primary"
              @click="router.visit(route('reuniones.edit', reunion.id))"
            >
              Editar
            </Button>

            <!-- Back Button -->
            <Button
              variant="ghost"
              @click="router.visit(route('reuniones.index'))"
            >
              Volver
            </Button>
          </div>
        </div>

        <!-- Estado -->
        <div class="mb-6">
          <span
            :class="{
              'bg-yellow-100 text-yellow-800': reunion.estado === 'CONVOCADA',
              'bg-green-100 text-green-800': reunion.estado === 'REALIZADA',
              'bg-red-100 text-red-800': reunion.estado === 'CANCELADA'
            }"
            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
          >
            {{ reunion.estado }}
          </span>
        </div>

        <!-- Información General -->
        <Card title="Información General" class="mb-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de la Reunión</label>
              <p class="text-gray-900">{{ formatDateTime(reunion.fecha_reunion) }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Lugar</label>
              <p class="text-gray-900">{{ reunion.lugar }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
              <p class="text-gray-900">{{ reunion.descripcion }}</p>
            </div>
            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Orden del Día</label>
              <div class="text-gray-900 whitespace-pre-wrap">{{ reunion.orden_dia }}</div>
            </div>
          </div>
        </Card>

        <!-- Estadísticas de Participación -->
        <Card title="Participación" class="mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
              <p class="text-sm text-blue-600 font-medium">Convocados</p>
              <p class="text-3xl font-bold text-blue-900">{{ reunion.total_convocados || 0 }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
              <p class="text-sm text-green-600 font-medium">Asistieron</p>
              <p class="text-3xl font-bold text-green-900">{{ reunion.total_asistieron || 0 }}</p>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
              <p class="text-sm text-gray-600 font-medium">% Asistencia</p>
              <p class="text-3xl font-bold text-gray-900">
                {{ reunion.total_convocados > 0 ? Math.round((reunion.total_asistieron / reunion.total_convocados) * 100) : 0 }}%
              </p>
            </div>
          </div>
        </Card>

        <!-- Participantes -->
        <Card title="Participantes" class="mb-6">
          <div v-if="reunion.participantes && reunion.participantes.length > 0" class="space-y-2">
            <div
              v-for="participante in reunion.participantes"
              :key="participante.id"
              class="flex items-center justify-between p-4 bg-gray-50 rounded-lg"
            >
              <div class="flex-1">
                <p class="font-medium text-gray-900">
                  {{ participante.nombres }} {{ participante.apellido_paterno }} {{ participante.apellido_materno }}
                </p>
                <p class="text-sm text-gray-600">
                  Vivienda: {{ participante.vivienda?.numero }} {{ participante.vivienda?.direccion || 'N/A' }}
                </p>
                
                <p v-if="participante.pivot?.observaciones" class="text-sm text-gray-500 mt-1">
                  {{ participante.pivot.observaciones }}
                </p>
              </div>
              <div>
                <span
                  v-if="participante.pivot?.asistio"
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800"
                >
                  Asistió
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800"
                >
                  No asistió
                </span>
              </div>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No hay participantes registrados
          </div>

          <div v-if="reunion.estado !== 'CANCELADA'" class="mt-4 pt-4 border-t">
            <Button v-if="isAdmin"
              variant="primary"
              @click="router.visit(route('reuniones.participantes', reunion.id))"
            >
              Registrar Participantes
            </Button>
          </div>
        </Card>


        <!-- Registrar Acta -->
        <Card v-if="reunion.estado === 'CONVOCADA'" title="Acta de Reunion" class="mb-6">
          <div v-if="reunion.participantes && reunion.participantes.length > 0" class="space-y-2">
            
            <div v-if="reunion.estado === 'CONVOCADA' && !reunion.acta" class="mt-4">
              <Button v-if="isAdmin"
                variant="primary"
                @click="router.visit(route('reuniones.acta', reunion.id))"
              >
                Registrar Acta
              </Button>
              <div v-else class="text-center py-8 text-gray-500">
                No se ha redactado el acta de la reunión.
              </div>
            </div>          

          </div>
        </Card>

        <!-- Acta -->
        <Card v-if="reunion.estado === 'REALIZADA' && reunion.acta" title="Acta de la Reunión" class="mb-6">
          <div class="prose max-w-none">
            <div class="whitespace-pre-wrap text-gray-900">{{ reunion.acta }}</div>
          </div>
        </Card>

        <!-- Acuerdos -->
        <Card v-if="reunion.acuerdos && reunion.acuerdos.length > 0" title="Acuerdos" class="mb-6">
          <div class="space-y-3">
            <div
              v-for="(acuerdo, index) in reunion.acuerdos"
              :key="index"
              class="p-4 bg-gray-50 rounded-lg"
            >
              <p class="font-medium text-gray-900">{{ acuerdo.descripcion }}</p>
              <div class="mt-2 flex gap-4 text-sm text-gray-600">
                <span v-if="acuerdo.responsable_id">
                  Responsable: {{ getResponsableName(acuerdo.responsable_id) }}
                </span>
                <span v-if="acuerdo.fecha_limite">
                  Fecha límite: {{ formatDate(acuerdo.fecha_limite) }}
                </span>
              </div>
            </div>
          </div>
        </Card>

        <!-- Actividades Relacionadas -->
        <Card v-if="reunion.actividades && reunion.actividades.length > 0" title="Actividades Relacionadas" class="mb-6">
          <div class="space-y-2">
            <div
              v-for="actividad in reunion.actividades"
              :key="actividad.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors cursor-pointer"
              @click="router.visit(route('actividades.show', actividad.id))"
            >
              <div>
                <p class="font-medium text-gray-900">{{ actividad.titulo }}</p>
                <p class="text-sm text-gray-600">{{ actividad.tipo }}</p>
              </div>
              <span
                :class="{
                  'bg-yellow-100 text-yellow-800': actividad.estado === 'PLANIFICADA',
                  'bg-blue-100 text-blue-800': actividad.estado === 'EN_PROGRESO',
                  'bg-green-100 text-green-800': actividad.estado === 'COMPLETADA',
                  'bg-red-100 text-red-800': actividad.estado === 'CANCELADA'
                }"
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              >
                {{ actividad.estado }}
              </span>
            </div>
          </div>
        </Card>
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
  reunion: Object
})

const formatDate = (fecha) => {
  if (!fecha) return 'N/A'
  const date = new Date(fecha)
  return date.toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatDateTime = (fecha) => {
  if (!fecha) return 'N/A'
  const date = new Date(fecha)
  return date.toLocaleDateString('es-BO', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: false,            // 24 horas
    timeZone: 'UTC'
  })
}

const getResponsableName = (responsableId) => {
  // This would need to be passed from the controller
  // For now, return the ID
  return `Usuario #${responsableId}`
}

const exportarConvocatoria = () => {
  window.location.href = route('reuniones.exportConvocatoria', props.reunion.id)
}

const exportarActa = () => {
  window.location.href = route('reuniones.exportActa', props.reunion.id)
}

//validar si es admin
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
// Usuario autenticado desde Inertia
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Computed correcto
const isAdmin = computed(() => {
    return user.value?.rol === 'ADMINISTRADOR'
})
</script>
