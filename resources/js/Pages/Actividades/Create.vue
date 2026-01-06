<template>
  <AppLayout title="Crear Actividad">
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Crear Nueva Actividad</h1>
          <p class="mt-2 text-sm text-gray-600">Complete el formulario para registrar una nueva actividad</p>
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
                hint="Nombre descriptivo de la actividad"
              />

              <Textarea
                v-model="form.descripcion"
                label="Descripción"
                required
                rows="4"
                :error="form.errors.descripcion"
                hint="Detalles de la actividad a realizar"
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
                hint="Información adicional relevante"
              />
            </div>
          </Card>

          <Card title="Generar Aportes Automáticamente" class="mb-6">
            <div class="flex items-start">
              <input
                type="checkbox"
                v-model="form.generar_aportes"
                class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <div class="ml-3">
                <label class="text-sm font-medium text-gray-700">
                  Generar aportes para todas las viviendas activas
                </label>
                <p class="text-sm text-gray-500">
                  Al marcar esta opción, se creará automáticamente un aporte para cada vivienda activa del condominio
                </p>
              </div>
            </div>

            <div v-if="form.generar_aportes" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <Input
                v-model="form.monto_por_vivienda"
                label="Monto por Vivienda (Bs.)"
                type="number"
                step="0.01"
                min="0"
                :required="form.generar_aportes"
                :error="form.errors.monto_por_vivienda"
                hint="Monto que aportará cada vivienda"
              />

              <Input
                v-model="form.dias_vencimiento"
                label="Días para Vencimiento"
                type="number"
                min="1"
                :error="form.errors.dias_vencimiento"
                hint="Días desde hoy hasta el vencimiento"
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
              Crear Actividad
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
  reuniones: Array,
  usuarios: Array
})

const form = useForm({
  reunion_id: '',
  titulo: '',
  descripcion: '',
  tipo: '',
  fecha_inicio: '',
  fecha_fin: '',
  presupuesto_aprobado: '',
  responsable_id: '',
  observaciones: '',
  generar_aportes: false,
  monto_por_vivienda: '',
  dias_vencimiento: 30
})

const submit = () => {
  form.post(route('actividades.store'))
}
</script>
