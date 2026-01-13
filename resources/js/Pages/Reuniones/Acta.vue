<template>
  <AppLayout title="Registrar Acta">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Registrar Acta de Reunión</h1>
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
          <!-- Información de la Reunión -->
          <Card title="Resumen de la Reunión" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div class="bg-blue-50 p-4 rounded-lg">
                <p class="text-sm text-blue-600 font-medium">Convocados</p>
                <p class="text-2xl font-bold text-blue-900">{{ reunion.participantes?.length || 0 }}</p>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <p class="text-sm text-green-600 font-medium">Asistieron</p>
                <p class="text-2xl font-bold text-green-900">{{ contarAsistentes }}</p>
              </div>
              <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-600 font-medium">% Asistencia</p>
                <p class="text-2xl font-bold text-gray-900">{{ porcentajeAsistencia }}%</p>
              </div>
            </div>

            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">Orden del Día</label>
              <div class="bg-gray-50 p-4 rounded-lg whitespace-pre-wrap text-sm text-gray-700">
                {{ reunion.orden_dia }}
              </div>
            </div>
          </Card>

          <!-- Acta -->
          <Card title="Contenido del Acta" class="mb-6">
            <div class="space-y-4">
              <Textarea
                v-model="form.acta"
                label="Acta de la Reunión"
                required
                :rows="12"
                :error="form.errors.acta"
                hint="Describa detalladamente lo acontecido en la reunión, los puntos discutidos, decisiones tomadas, etc."
                placeholder="ACTA DE REUNIÓN

En la ciudad de Santa Cruz de la Sierra,  siendo las 20:00 horas, se llevó a cabo la reunión Reunión Ordinaria - Enero 2026 en la Casa del Vecino Luis Enrique Justiniano.

Actuó como presidente de la reunión el Vecino Benjamin Galindo y como secretario(a) el Vecino Luis Enrique Justiniano, quien suscribe la presente acta.

El Orden del dia fue aprobado sin modificacion:

1. Verificación de quórum
2. Lectura de acta anterior(Diciembre)
3. Lectura de Correspondencia
4. Aprobación de actividades(Enero)
5. Varios

DESARROLLO DE LA REUNIÓN

1. La reunión dio inicio con la verificación del quórum correspondiente, procediéndose posteriormente al tratamiento de los puntos establecidos en el orden del día.

2. Lectura del Acta Anterior (Diciembre) de fecha  12-12-2025
se aprobo sin observaciones

3. se dio lectura a las correspondencia
....
Se expuso y analizó ______________________________________
y se tomaron en cuenta las que beneficia a la urbanizacion.

4. Se procedio con la Aprobación de actividades(Enero)
....
Se expuso y analizó y se consideraron las siguientes:
a) Limpiza de Aceras ______________________________________
b) Contactarse con los propietarios de Terrenos ___________.
cada actividad tendra un vecino encargado.

5. se procedio con los puntos varios
....
Se expuso y analizó ______________________________________.
En este punto se decidio considerar en la siguiene reunion.

No habiendo más asuntos que tratar, se dio por concluida la reunión a horas 23:00, del mismo día, en señal de conformidad se firma la presente acta para constancia y efectos legales que correspondan.


Benjamin Galindo
Presidente


Luis Enrique Justiniano
Secretario
"
              />
            </div>
          </Card>

          <!-- Acuerdos -->
          <Card title="Acuerdos y Compromisos" class="mb-6">
            <div class="mb-4">
              <p class="text-sm text-gray-600 mb-4">
                Registre los acuerdos, compromisos y tareas que surgieron de la reunión
              </p>

              <div class="space-y-4">
                <div
                  v-for="(acuerdo, index) in form.acuerdos"
                  :key="index"
                  class="border border-gray-200 rounded-lg p-4 bg-gray-50"
                >
                  <div class="flex justify-between items-start mb-3">
                    <h4 class="font-medium text-gray-900">Acuerdo #{{ index + 1 }}</h4>
                    <Button
                      v-if="form.acuerdos.length > 1"
                      type="button"
                      variant="ghost"
                      size="sm"
                      @click="eliminarAcuerdo(index)"
                    >
                      Eliminar
                    </Button>
                  </div>

                  <div class="space-y-3">
                    <Textarea
                      v-model="acuerdo.descripcion"
                      label="Descripción del Acuerdo"
                      required
                      :rows="3"
                      :error="form.errors[`acuerdos.${index}.descripcion`]"
                      placeholder="Describa el acuerdo o compromiso alcanzado"
                    />

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <Select
                        v-model="acuerdo.responsable_id"
                        label="Responsable (Opcional)"
                        :error="form.errors[`acuerdos.${index}.responsable_id`]"
                      >
                        <option value="">Sin responsable asignado</option>
                        <option
                          v-for="responsable in responsables"
                          :key="responsable.id"
                          :value="responsable.id"
                        >
                          {{ responsable.name }} ({{ responsable.rol }})
                        </option>
                      </Select>

                      <Input
                        v-model="acuerdo.fecha_limite"
                        label="Fecha Límite (Opcional)"
                        type="date"
                        :error="form.errors[`acuerdos.${index}.fecha_limite`]"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <Button
                type="button"
                variant="secondary"
                class="mt-4"
                @click="agregarAcuerdo"
              >
                + Agregar Otro Acuerdo
              </Button>
            </div>
          </Card>

          <!-- Advertencia -->
          <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Importante</h3>
                <div class="mt-2 text-sm text-yellow-700">
                  <p>Al registrar el acta, la reunión cambiará automáticamente al estado "REALIZADA" y no podrá ser editada posteriormente.</p>
                </div>
              </div>
            </div>
          </div>

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
              Registrar Acta y Finalizar Reunión
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
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  reunion: Object,
  responsables: Array
})

const form = useForm({
  acta: '',
  acuerdos: [
    {
      descripcion: '',
      responsable_id: '',
      fecha_limite: ''
    }
  ]
})

const contarAsistentes = computed(() => {
  if (!props.reunion.participantes) return 0
  return props.reunion.participantes.filter(p => p.pivot?.asistio).length
})

const porcentajeAsistencia = computed(() => {
  const total = props.reunion.participantes?.length || 0
  if (total === 0) return 0
  return Math.round((contarAsistentes.value / total) * 100)
})

const agregarAcuerdo = () => {
  form.acuerdos.push({
    descripcion: '',
    responsable_id: '',
    fecha_limite: ''
  })
}

const eliminarAcuerdo = (index) => {
  form.acuerdos.splice(index, 1)
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
  form.post(route('reuniones.registrarActa', props.reunion.id))
}
</script>
