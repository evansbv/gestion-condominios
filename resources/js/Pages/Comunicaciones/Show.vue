<template>
  <AppLayout title="Detalle Comunicación">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ comunicacion.asunto }}</h1>
            <p class="mt-2 text-sm text-gray-600">
              {{ comunicacion.tipo ? comunicacion.tipo.replace(/_/g, ' ') : 'Sin tipo' }}
            </p>
          </div>
          <div class="flex gap-2">
            <Button
              v-if="canEdit"
              variant="primary"
              @click="router.visit(route('comunicaciones.edit', comunicacion.id))"
            >
              Editar
            </Button>
            <Button
              variant="ghost"
              @click="router.visit(route('comunicaciones.index'))"
            >
              Volver
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <Card>
              <div class="flex items-center justify-between mb-4">
                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-blue-100 text-blue-800': comunicacion.estado === 'BORRADOR',
                    'bg-green-100 text-green-800': comunicacion.estado === 'ENVIADO',
                    'bg-gray-100 text-gray-800': comunicacion.estado === 'ARCHIVADO'
                  }"
                >
                  {{ comunicacion.estado }}
                </span>
                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-gray-100 text-gray-800': comunicacion.prioridad === 'BAJA',
                    'bg-blue-100 text-blue-800': comunicacion.prioridad === 'MEDIA',
                    'bg-yellow-100 text-yellow-800': comunicacion.prioridad === 'ALTA',
                    'bg-red-100 text-red-800': comunicacion.prioridad === 'URGENTE'
                  }"
                >
                  {{ comunicacion.prioridad }}
                </span>
              </div>

              <div class="prose max-w-none">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Contenido</h3>
                <div class="text-gray-700 whitespace-pre-wrap">{{ comunicacion.contenido }}</div>
              </div>

              <div v-if="comunicacion.archivo_adjunto" class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Archivo Adjunto</h3>
                <a
                  :href="`/urbalemana/storage/${comunicacion.archivo_adjunto}`"
                  target="_blank"
                  class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors"
                >
                  <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  Descargar Archivo
                </a>
              </div>
            </Card>

            <Card title="Destinatarios">
              <div class="space-y-3">
                <div
                  v-for="destinatario in comunicacion.destinatarios"
                  :key="destinatario.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <div>
                    <p class="font-medium text-gray-900">{{ destinatario.name }}</p>
                    <p class="text-sm text-gray-600">{{ destinatario.email }}</p>
                    <p class="text-xs text-gray-500">{{ destinatario.rol }}</p>
                  </div>
                  <div class="text-right">
                    <span
                      v-if="destinatario.pivot?.leido"
                      class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800"
                    >
                      <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      Leído
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800"
                    >
                      Sin leer
                    </span>
                    <p v-if="destinatario.pivot?.fecha_lectura" class="text-xs text-gray-500 mt-1">
                      {{ new Date(destinatario.pivot.fecha_lectura).toLocaleDateString('es-BO') }}
                    </p>
                  </div>
                </div>
              </div>
            </Card>
          </div>

          <div class="space-y-6">
            <Card title="Información">
              <div class="space-y-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Remitente</label>
                  <p class="text-base text-gray-900">{{ comunicacion.remitente?.name || 'Sin remitente' }}</p>
                  <p v-if="comunicacion.remitente" class="text-sm text-gray-500">{{ comunicacion.remitente.email }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Fecha de Envío</label>
                  <p class="text-base text-gray-900">
                    {{ formatDateTime(comunicacion.fecha_envio)}}
                  </p>
                </div>
              </div>
            </Card>

            <Card title="Estadísticas de Lectura">
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Total Destinatarios</span>
                  <span class="font-semibold text-gray-900">{{ comunicacion.total_destinatarios || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Leídos</span>
                  <span class="font-semibold text-green-600">{{ comunicacion.total_leidas || 0 }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Pendientes</span>
                  <span class="font-semibold text-red-600">
                    {{ (comunicacion.total_destinatarios || 0) - (comunicacion.total_leidas || 0) }}
                  </span>
                </div>
                <div class="pt-4 border-t border-gray-200">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Tasa de Lectura</span>
                    <span class="font-semibold text-gray-900">
                      {{ comunicacion.total_destinatarios > 0 ? Math.round((comunicacion.total_leidas / comunicacion.total_destinatarios) * 100) : 0 }}%
                    </span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-green-600 h-2 rounded-full transition-all"
                      :style="{ width: (comunicacion.total_destinatarios > 0 ? (comunicacion.total_leidas / comunicacion.total_destinatarios) * 100 : 0) + '%' }"
                    ></div>
                  </div>
                </div>
              </div>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'

const props = defineProps({
  comunicacion: Object
})

const page = usePage()
const canEdit = computed(() => {
  const user = page.props.auth.user
  return user && ['ADMINISTRADOR', 'MIEMBRO_DIRECTORIO'].includes(user.rol)
})

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
</script>
