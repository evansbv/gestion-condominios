<template>
  <div
    class="bg-white rounded-lg shadow-sm border-2 p-3 cursor-pointer hover:shadow-md transition-shadow"
    :class="borderColor"
    @click="verDetalle"
  >
    <div class="flex items-start justify-between mb-2">
      <h4 class="font-semibold text-sm text-gray-900 flex-1 pr-2">
        {{ actividad.titulo }}
      </h4>
      <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
      </svg>
    </div>

    <p class="text-xs text-gray-600 line-clamp-2 mb-2">
      {{ actividad.descripcion }}
    </p>

    <div class="space-y-1 mb-2">
      <div v-if="actividad.tipo" class="flex items-center text-xs text-gray-600">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
        </svg>
        {{ actividad.tipo }}
      </div>

      <div v-if="actividad.fecha_inicio" class="flex items-center text-xs text-gray-600">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        {{ formatDate(actividad.fecha_inicio) }}
        <span v-if="actividad.fecha_fin" class="mx-1">→</span>
        <span v-if="actividad.fecha_fin">{{ formatDate(actividad.fecha_fin) }}</span>
      </div>

      <div v-if="actividad.responsable" class="flex items-center text-xs text-gray-600">
        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
        </svg>
        {{ actividad.responsable.name }}
      </div>
    </div>

    <div v-if="actividad.presupuesto_aprobado" class="pt-2 border-t border-gray-200">
      <div class="flex items-center justify-between text-xs">
        <span class="text-gray-600">Presupuesto:</span>
        <span class="font-semibold text-gray-900">
          Bs. {{ formatNumber(actividad.presupuesto_aprobado) }}
        </span>
      </div>
      <div v-if="actividad.porcentaje_avance !== null" class="mt-2">
        <div class="flex items-center justify-between text-xs mb-1">
          <span class="text-gray-600">Avance:</span>
          <span class="font-semibold text-gray-900">{{ actividad.porcentaje_avance }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5">
          <div
            class="h-1.5 rounded-full transition-all"
            :class="progressColor"
            :style="{ width: `${actividad.porcentaje_avance}%` }"
          ></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  actividad: {
    type: Object,
    required: true
  },
  estadoColor: {
    type: String,
    default: 'gray'
  }
})

const borderColor = computed(() => {
  const colors = {
    yellow: 'border-yellow-300',
    blue: 'border-blue-300',
    green: 'border-green-300',
    red: 'border-red-300'
  }
  return colors[props.estadoColor] || 'border-gray-300'
})

const progressColor = computed(() => {
  if (props.actividad.porcentaje_avance >= 80) return 'bg-green-500'
  if (props.actividad.porcentaje_avance >= 50) return 'bg-yellow-500'
  return 'bg-red-500'
})

const formatNumber = (value) => {
  return new Intl.NumberFormat('es-BO', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('es-BO', {
    day: '2-digit',
    month: 'short'
  })
}

const verDetalle = () => {
  router.visit(route('actividades.show', props.actividad.id))
}
</script>
