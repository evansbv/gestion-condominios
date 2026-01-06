<template>
  <AppLayout title="Registrar Participación">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Registrar Participación</h1>
          <p class="mt-2 text-sm text-gray-600">{{ reunion.titulo }}</p>
          <p class="mt-1 text-sm text-gray-500">
            Fecha: {{ formatDateTime(reunion.fecha_reunion) }} - {{ reunion.lugar }}
          </p>
        </div>

        <Alert
          v-if="Object.keys(form.errors).length > 0"
          type="error"
          message="Por favor corrija los errores en el formulario"
        />

        <form @submit.prevent="submit">
          <Card title="Lista de Asistencia" class="mb-6">
            <div class="mb-4">
              <div class="flex justify-between items-center mb-4">
                <p class="text-sm text-gray-600">
                  Marque la asistencia de cada residente
                </p>
                <div class="flex gap-2">
                  <Button
                    type="button"
                    variant="secondary"
                    size="sm"
                    @click="marcarTodos(true)"
                  >
                    Marcar Todos Presentes
                  </Button>
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    @click="marcarTodos(false)"
                  >
                    Marcar Todos Ausentes
                  </Button>
                </div>
              </div>

              <div class="space-y-3">
                <div
                  v-for="(participante, index) in form.participantes"
                  :key="participante.residente_id"
                  class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors"
                >
                  <div class="flex items-start gap-4">
                    <!-- Checkbox de asistencia -->
                    <div class="flex items-center pt-1">
                      <input
                        type="checkbox"
                        v-model="participante.asistio"
                        class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                      />
                    </div>

                    <!-- Información del residente -->
                    <div class="flex-1">
                      <div class="flex items-center gap-2">
                        <p class="font-medium text-gray-900">
                          {{ getResidenteInfo(participante.residente_id).nombre }}
                        </p>
                        <span
                          :class="{
                            'bg-green-100 text-green-800': participante.asistio,
                            'bg-red-100 text-red-800': !participante.asistio
                          }"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                        >
                          {{ participante.asistio ? 'Presente' : 'Ausente' }}
                        </span>
                      </div>
                      <p class="text-sm text-gray-600 mt-1">
                        Vivienda: {{ getResidenteInfo(participante.residente_id).vivienda }}
                      </p>

                      <!-- Observaciones -->
                      <div class="mt-3">
                        <Textarea
                          v-model="participante.observaciones"
                          :placeholder="participante.asistio ? 'Observaciones (opcional)' : 'Motivo de ausencia (opcional)'"
                          :rows="2"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <div class="flex justify-between text-sm">
                  <span class="text-blue-900 font-medium">Total convocados:</span>
                  <span class="text-blue-900">{{ form.participantes.length }}</span>
                </div>
                <div class="flex justify-between text-sm mt-2">
                  <span class="text-green-900 font-medium">Presentes:</span>
                  <span class="text-green-900">{{ contarPresentes }}</span>
                </div>
                <div class="flex justify-between text-sm mt-2">
                  <span class="text-red-900 font-medium">Ausentes:</span>
                  <span class="text-red-900">{{ form.participantes.length - contarPresentes }}</span>
                </div>
                <div class="flex justify-between text-sm mt-2 pt-2 border-t border-blue-200">
                  <span class="text-blue-900 font-bold">% Asistencia:</span>
                  <span class="text-blue-900 font-bold">
                    {{ form.participantes.length > 0 ? Math.round((contarPresentes / form.participantes.length) * 100) : 0 }}%
                  </span>
                </div>
              </div>
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
              Guardar Participación
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
import Textarea from '@/Components/Form/Textarea.vue'
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  reunion: Object,
  residentes: Array
})

// Crear array de participantes con todos los residentes
const form = useForm({
  participantes: props.residentes.map(residente => ({
    residente_id: residente.id,
    asistio: residente.ya_registrado ? residente.asistio : false,
    observaciones: residente.observaciones || ''
  }))
})

const contarPresentes = computed(() => {
  return form.participantes.filter(p => p.asistio).length
})

const getResidenteInfo = (residenteId) => {
  const residente = props.residentes.find(r => r.id === residenteId)
  return {
    nombre: residente ? `${residente.nombres} ${residente.apellidos}` : 'N/A',
    vivienda: residente?.vivienda?.numero_vivienda || 'N/A'
  }
}

const marcarTodos = (asistio) => {
  form.participantes.forEach(p => {
    p.asistio = asistio
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
    minute: '2-digit'
  })
}

const submit = () => {
  form.post(route('reuniones.registrarParticipacion', props.reunion.id))
}
</script>
