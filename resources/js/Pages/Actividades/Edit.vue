<template>
  <AppLayout title="Editar Actividad">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Editar Actividad</h1>
          <p class="mt-2 text-sm text-gray-600">Actualice la información de la actividad</p>
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
              />

              <Textarea
                v-model="form.descripcion"
                label="Descripción"
                required
                rows="4"
                :error="form.errors.descripcion"
              />

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <Select
                  v-model="form.tipo"
                  label="Tipo de Actividad"
                  required
                  :error="form.errors.tipo"
                >
                  <option value="">Seleccione un tipo</option>
                  <option value="MANTENIMIENTO_CHURRASQUERA">Mantenimiento Churrasquera</option>
                  <option value="LIMPIEZA_ACERAS">Limpieza Aceras</option>
                  <option value="MANTENIMIENTO_CALLES">Mantenimiento Calles</option>
                  <option value="JARDINERIA">Jardinería</option>
                  <option value="SEGURIDAD">Seguridad</option>
                  <option value="OTRO">Otro</option>
                </Select>

                <Select
                  v-model="form.reunion_id"
                  label="Reunión Asociada"
                  :error="form.errors.reunion_id"
                >
                  <option value="">Sin reunión asociada</option>
                  <option v-for="reunion in reuniones" :key="reunion.id" :value="reunion.id">
                    {{ reunion.titulo }} - {{ new Date(reunion.fecha_reunion).toLocaleDateString('es-BO') }}
                  </option>
                </Select>
              </div>
            </div>
          </Card>

          <Card title="Fechas y Presupuesto" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                v-model="form.fecha_inicio"
                label="Fecha de Inicio"
                type="date"
                required
                :error="form.errors.fecha_inicio"
              />

              <Input
                v-model="form.fecha_fin"
                label="Fecha de Finalización"
                type="date"
                required
                :error="form.errors.fecha_fin"
              />

              <Input
                v-model="form.presupuesto_aprobado"
                label="Presupuesto Aprobado (Bs.)"
                type="number"
                step="0.01"
                min="0"
                required
                :error="form.errors.presupuesto_aprobado"
              />

              <Input
                v-model="form.presupuesto_ejecutado"
                label="Presupuesto Ejecutado (Bs.)"
                type="number"
                step="0.01"
                min="0"
                :error="form.errors.presupuesto_ejecutado"
              />

              <Select
                v-model="form.estado"
                label="Estado"
                required
                :error="form.errors.estado"
              >
                <option value="PLANIFICADA">Planificada</option>
                <option value="EN_PROGRESO">En Progreso</option>
                <option value="COMPLETADA">Completada</option>
                <option value="CANCELADA">Cancelada</option>
              </Select>

              <Input
                v-model="form.porcentaje_avance"
                label="Porcentaje de Avance (%)"
                type="number"
                min="0"
                max="100"
                :error="form.errors.porcentaje_avance"
              />

              <Select
                v-model="form.responsable_id"
                label="Responsable"
                :error="form.errors.responsable_id"
              >
                <option value="">Sin responsable asignado</option>
                <option v-for="usuario in usuarios" :key="usuario.id" :value="usuario.id">
                  {{ usuario.name }} ({{ usuario.rol }})
                </option>
              </Select>
            </div>

            <div class="mt-4">
              <Textarea
                v-model="form.observaciones"
                label="Observaciones"
                rows="3"
                :error="form.errors.observaciones"
              />
            </div>
          </Card>

          <div class="flex justify-end gap-3">
            <Button
              type="button"
              variant="ghost"
              @click="router.visit(route('actividades.index'))"
            >
              Cancelar
            </Button>
            <Button
              type="submit"
              variant="primary"
              :loading="form.processing"
            >
              Actualizar Actividad
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
  actividad: Object,
  reuniones: Array,
  usuarios: Array
})

const form = useForm({
  reunion_id: props.actividad?.reunion_id || '',
  titulo: props.actividad?.titulo || '',
  descripcion: props.actividad?.descripcion || '',
  tipo: props.actividad?.tipo || '',
  fecha_inicio: props.actividad?.fecha_inicio || '',
  fecha_fin: props.actividad?.fecha_fin || '',
  presupuesto_aprobado: props.actividad?.presupuesto_aprobado || '',
  presupuesto_ejecutado: props.actividad?.presupuesto_ejecutado || '',
  estado: props.actividad?.estado || 'PLANIFICADA',
  porcentaje_avance: props.actividad?.porcentaje_avance || 0,
  responsable_id: props.actividad?.responsable_id || '',
  observaciones: props.actividad?.observaciones || ''
})

const submit = () => {
  if (props.actividad?.id) {
    form.put(route('actividades.update', props.actividad.id))
  }
}
</script>
