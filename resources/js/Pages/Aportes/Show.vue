<template>
  <AppLayout title="Detalle Aporte">
    <div class="py-6">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Detalle de Aporte</h1>
            <p class="mt-2 text-sm text-gray-600">{{ aporte.actividad?.titulo }}</p>
          </div>
          <div class="flex gap-2">
            <Button
              v-if="canEdit && aporte.estado !== 'PAGADO'"
              variant="primary"
              @click="router.visit(route('aportes.edit', aporte.id))"
            >
              Editar
            </Button>
            <Button
              variant="ghost"
              @click="router.visit(route('aportes.index'))"
            >
              Volver
            </Button>
          </div>
        </div>

        <Alert
          v-if="$page.props.flash.success"
          type="success"
          :message="$page.props.flash.success"
          :auto-dismiss="5000"
        />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <Card title="Información del Aporte">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="text-sm font-medium text-gray-500">Vivienda</label>
                  <p class="text-base font-semibold text-gray-900">{{ aporte.vivienda?.numero }}</p>
                  <p class="text-sm text-gray-600">{{ aporte.vivienda?.direccion }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Estado</label>
                  <div class="mt-1">
                    <span
                      class="px-3 py-1 text-sm font-semibold rounded-full"
                      :class="{
                        'bg-green-100 text-green-800': aporte.estado === 'PAGADO',
                        'bg-yellow-100 text-yellow-800': aporte.estado === 'PENDIENTE',
                        'bg-red-100 text-red-800': aporte.estado === 'VENCIDO',
                        'bg-blue-100 text-blue-800': aporte.estado === 'PARCIAL'
                      }"
                    >
                      {{ aporte.estado }}
                    </span>
                  </div>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Monto Original</label>
                  <p class="text-2xl font-bold text-gray-900">Bs. {{ aporte.monto }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Monto Pagado</label>
                  <p class="text-2xl font-bold text-green-600">Bs. {{ aporte.monto_pagado }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Mora Acumulada</label>
                  <p class="text-2xl font-bold text-red-600">Bs. {{ aporte.mora_actualizada?.toFixed(2) || '0.00' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Fecha de Vencimiento</label>
                  <p class="text-base text-gray-900">{{ new Date(aporte.fecha_vencimiento).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
                <div v-if="aporte.fecha_pago" class="md:col-span-2">
                  <label class="text-sm font-medium text-gray-500">Fecha de Pago</label>
                  <p class="text-base text-gray-900">{{ new Date(aporte.fecha_pago).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>
                </div>
                <div v-if="aporte.observaciones" class="md:col-span-2">
                  <label class="text-sm font-medium text-gray-500">Observaciones</label>
                  <p class="text-base text-gray-900">{{ aporte.observaciones }}</p>
                </div>
              </div>
            </Card>

            <Card title="Actividad Relacionada">
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <h4 class="font-medium text-gray-900">{{ aporte.actividad?.titulo }}</h4>
                  <p class="text-sm text-gray-600 mt-1">{{ aporte.actividad?.tipo?.replace(/_/g, ' ') }}</p>
                  <p class="text-sm text-gray-600 mt-2">{{ aporte.actividad?.descripcion }}</p>
                  <div class="mt-3 flex gap-4 text-sm">
                    <span class="text-gray-600">
                      Presupuesto: <strong class="text-gray-900">Bs. {{ aporte.actividad?.presupuesto_aprobado }}</strong>
                    </span>
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': aporte.actividad?.estado === 'PLANIFICADA',
                        'bg-yellow-100 text-yellow-800': aporte.actividad?.estado === 'EN_PROGRESO',
                        'bg-green-100 text-green-800': aporte.actividad?.estado === 'COMPLETADA',
                        'bg-red-100 text-red-800': aporte.actividad?.estado === 'CANCELADA'
                      }"
                    >
                      {{ aporte.actividad?.estado?.replace('_', ' ') }}
                    </span>
                  </div>
                </div>
                <Button
                  size="sm"
                  variant="ghost"
                  @click="router.visit(route('actividades.show', aporte.actividad?.id))"
                >
                  Ver Actividad
                </Button>
              </div>
            </Card>

            <Card title="Historial de Pagos">
              <div v-if="aporte.pagos && aporte.pagos.length > 0">
                <div class="space-y-4">
                  <div
                    v-for="pago in aporte.pagos"
                    :key="pago.id"
                    class="border border-gray-200 rounded-lg p-4"
                  >
                    <div class="flex justify-between items-start mb-3">
                      <div>
                        <p class="text-lg font-semibold text-gray-900">Bs. {{ pago.monto }}</p>
                        <p class="text-sm text-gray-600">
                          {{ new Date(pago.fecha_pago).toLocaleDateString('es-BO', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </p>
                      </div>
                      <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ pago.metodo_pago }}
                      </span>
                    </div>
                    <div v-if="pago.observaciones" class="text-sm text-gray-600 mb-2">
                      {{ pago.observaciones }}
                    </div>
                    <div v-if="pago.comprobante" class="mt-2">
                      <a
                        :href="`/storage/${pago.comprobante}`"
                        target="_blank"
                        class="text-sm text-blue-600 hover:text-blue-800 inline-flex items-center"
                      >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Ver Comprobante
                      </a>
                    </div>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay pagos registrados para este aporte
              </div>
            </Card>
          </div>

          <div class="space-y-6">
            <Card title="Resumen">
              <div class="space-y-4">
                <div class="bg-gray-50 rounded-lg p-4">
                  <p class="text-xs text-gray-600 font-medium uppercase">Monto del Aporte</p>
                  <p class="text-2xl font-bold text-gray-900">Bs. {{ aporte.monto }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                  <p class="text-xs text-green-600 font-medium uppercase">Total Pagado</p>
                  <p class="text-2xl font-bold text-green-900">Bs. {{ aporte.monto_pagado }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                  <p class="text-xs text-red-600 font-medium uppercase">Mora</p>
                  <p class="text-2xl font-bold text-red-900">Bs. {{ aporte.mora_actualizada?.toFixed(2) || '0.00' }}</p>
                </div>
                <div class="border-t-2 border-gray-200 pt-4">
                  <p class="text-xs text-gray-600 font-medium uppercase">Total Adeudado</p>
                  <p class="text-3xl font-bold text-gray-900">
                    Bs. {{ aporte.total_adeudado?.toFixed(2) || '0.00' }}
                  </p>
                </div>
              </div>
            </Card>

            <Card v-if="aporte.estado !== 'PAGADO'" title="Registrar Pago">
              <form @submit.prevent="submitPago">
                <div class="space-y-4">
                  <Input
                    v-model="pagoForm.monto_pago"
                    label="Monto del Pago (Bs.)"
                    type="number"
                    step="0.01"
                    min="0.01"
                    :max="aporte.total_adeudado"
                    required
                    :error="pagoForm.errors.monto_pago"
                    hint="Monto máximo: Bs. {{ aporte.total_adeudado?.toFixed(2) }}"
                  />

                  <Input
                    v-model="pagoForm.fecha_pago"
                    label="Fecha de Pago"
                    type="date"
                    required
                    :error="pagoForm.errors.fecha_pago"
                  />

                  <Select
                    v-model="pagoForm.metodo_pago"
                    label="Método de Pago"
                    required
                    :error="pagoForm.errors.metodo_pago"
                  >
                    <option value="">Seleccione un método</option>
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="TRANSFERENCIA">Transferencia</option>
                    <option value="DEPOSITO">Depósito</option>
                    <option value="TARJETA">Tarjeta</option>
                    <option value="OTRO">Otro</option>
                  </Select>

                  <FileInput
                    @update:modelValue="pagoForm.comprobante = $event"
                    label="Comprobante de Pago"
                    accept="image/*,.pdf"
                    :error="pagoForm.errors.comprobante"
                    hint="JPG, PNG o PDF - Máximo 5MB"
                  />

                  <Textarea
                    v-model="pagoForm.observaciones"
                    label="Observaciones"
                    rows="3"
                    :error="pagoForm.errors.observaciones"
                  />

                  <Button
                    type="submit"
                    variant="success"
                    class="w-full"
                    :loading="pagoForm.processing"
                  >
                    Registrar Pago
                  </Button>
                </div>
              </form>
            </Card>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Input from '@/Components/Form/Input.vue'
import Select from '@/Components/Form/Select.vue'
import Textarea from '@/Components/Form/Textarea.vue'
import FileInput from '@/Components/Form/FileInput.vue'
import Alert from '@/Components/Alert.vue'

const props = defineProps({
  aporte: Object
})

const canEdit = ref(true)

const pagoForm = useForm({
  monto_pago: props.aporte.total_adeudado,
  fecha_pago: new Date().toISOString().split('T')[0],
  metodo_pago: '',
  comprobante: null,
  observaciones: ''
})

const submitPago = () => {
  pagoForm.post(route('aportes.registrarPago', props.aporte.id))
}
</script>
