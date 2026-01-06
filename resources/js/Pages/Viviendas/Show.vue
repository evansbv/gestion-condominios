<template>
  <AppLayout title="Detalle Vivienda">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Vivienda {{ vivienda.numero }}</h1>
            <p class="mt-2 text-sm text-gray-600">{{ vivienda.direccion }}</p>
          </div>
          <div class="flex gap-2">
            <Button
              variant="primary"
              @click="router.visit(route('viviendas.edit', vivienda.id))"
            >
              Editar
            </Button>
            <Button
              variant="ghost"
              @click="router.visit(route('viviendas.index'))"
            >
              Volver
            </Button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 space-y-6">
            <Card title="Información General">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Número</label>
                  <p class="text-base text-gray-900">{{ vivienda.numero }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Tipo</label>
                  <span
                    class="px-2 py-1 text-xs font-semibold rounded-full mt-1 inline-block"
                    :class="{
                      'bg-blue-100 text-blue-800': vivienda.tipo === 'CASA',
                      'bg-green-100 text-green-800': vivienda.tipo === 'DEPARTAMENTO',
                      'bg-purple-100 text-purple-800': vivienda.tipo === 'DUPLEX',
                      'bg-gray-100 text-gray-800': vivienda.tipo === 'OTRO'
                    }"
                  >
                    {{ vivienda.tipo }}
                  </span>
                </div>
                <div class="md:col-span-2">
                  <label class="text-sm font-medium text-gray-500">Dirección</label>
                  <p class="text-base text-gray-900">{{ vivienda.direccion }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Área</label>
                  <p class="text-base text-gray-900">{{ vivienda.area_m2 ? vivienda.area_m2 + ' m²' : 'No especificada' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Número de Habitantes</label>
                  <p class="text-base text-gray-900">{{ vivienda.numero_habitantes || 'No especificado' }}</p>
                </div>
                <div v-if="vivienda.observaciones" class="md:col-span-2">
                  <label class="text-sm font-medium text-gray-500">Observaciones</label>
                  <p class="text-base text-gray-900">{{ vivienda.observaciones }}</p>
                </div>
              </div>
            </Card>

            <Card title="Ubicación GPS">
              <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                  <label class="text-sm font-medium text-gray-500">Latitud</label>
                  <p class="text-base text-gray-900">{{ vivienda.latitud }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500">Longitud</label>
                  <p class="text-base text-gray-900">{{ vivienda.longitud }}</p>
                </div>
              </div>
              <Map
                :markers="[{
                  lat: parseFloat(vivienda.latitud),
                  lng: parseFloat(vivienda.longitud),
                  popup: `<strong>${vivienda.numero}</strong><br>${vivienda.direccion}`
                }]"
                :center="[parseFloat(vivienda.latitud), parseFloat(vivienda.longitud)]"
                :zoom="17"
                height="350px"
              />
            </Card>

            <Card title="Residentes Activos">
              <div v-if="vivienda.residentes && vivienda.residentes.length > 0">
                <div class="space-y-3">
                  <div
                    v-for="residente in vivienda.residentes"
                    :key="residente.id"
                    class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors cursor-pointer"
                    @click="router.visit(route('residentes.show', residente.id))"
                  >
                    <div class="flex items-center">
                      <img
                        v-if="residente.fotografia"
                        :src="`/storage/${residente.fotografia}`"
                        :alt="residente.nombres"
                        class="h-12 w-12 rounded-full object-cover mr-3"
                      />
                      <div v-else class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center mr-3">
                        <span class="text-gray-500 font-medium text-sm">
                          {{ residente.nombres.charAt(0) }}{{ residente.apellido_paterno.charAt(0) }}
                        </span>
                      </div>
                      <div>
                        <p class="text-sm font-medium text-gray-900">
                          {{ residente.nombres }} {{ residente.apellido_paterno }} {{ residente.apellido_materno }}
                        </p>
                        <p class="text-xs text-gray-600">CI: {{ residente.ci }}</p>
                      </div>
                    </div>
                    <span
                      class="px-2 py-1 text-xs font-semibold rounded-full"
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
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay residentes activos en esta vivienda
              </div>
            </Card>
          </div>

          <div class="space-y-6">
            <Card title="Resumen de Deuda">
              <div v-if="resumenDeuda" class="space-y-4">
                <div class="bg-blue-50 rounded-lg p-4">
                  <p class="text-xs text-blue-600 font-medium uppercase">Total Aportes</p>
                  <p class="text-2xl font-bold text-blue-900">{{ resumenDeuda.total_aportes }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                  <p class="text-xs text-green-600 font-medium uppercase">Pagados</p>
                  <p class="text-2xl font-bold text-green-900">{{ resumenDeuda.aportes_pagados }}</p>
                  <p class="text-sm text-green-700 mt-1">Bs. {{ resumenDeuda.monto_pagado?.toFixed(2) }}</p>
                </div>
                <div class="bg-red-50 rounded-lg p-4">
                  <p class="text-xs text-red-600 font-medium uppercase">Pendientes</p>
                  <p class="text-2xl font-bold text-red-900">{{ resumenDeuda.aportes_pendientes }}</p>
                  <p class="text-sm text-red-700 mt-1">Bs. {{ resumenDeuda.monto_pendiente?.toFixed(2) }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                  <p class="text-xs text-yellow-600 font-medium uppercase">Mora Acumulada</p>
                  <p class="text-2xl font-bold text-yellow-900">Bs. {{ resumenDeuda.total_mora?.toFixed(2) }}</p>
                </div>
                <div class="border-t-2 border-gray-200 pt-4">
                  <p class="text-xs text-gray-600 font-medium uppercase">Saldo Total Pendiente</p>
                  <p class="text-3xl font-bold text-gray-900">
                    Bs. {{ (resumenDeuda.monto_pendiente + resumenDeuda.total_mora)?.toFixed(2) }}
                  </p>
                </div>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay información de deuda disponible
              </div>
            </Card>

            <Card title="Aportes Recientes">
              <div v-if="vivienda.aportes && vivienda.aportes.length > 0">
                <div class="space-y-2">
                  <div
                    v-for="aporte in vivienda.aportes.slice(0, 5)"
                    :key="aporte.id"
                    class="text-sm border-b border-gray-100 pb-2 last:border-0"
                  >
                    <p class="font-medium text-gray-900">{{ aporte.actividad?.titulo }}</p>
                    <p class="text-xs text-gray-600">Bs. {{ aporte.monto }}</p>
                    <span
                      class="text-xs px-2 py-0.5 rounded-full mt-1 inline-block"
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
                <Button
                  variant="ghost"
                  size="sm"
                  class="w-full mt-3"
                  @click="router.visit(route('aportes.index', { vivienda_id: vivienda.id }))"
                >
                  Ver Todos los Aportes
                </Button>
              </div>
              <div v-else class="text-center py-8 text-gray-500">
                No hay aportes registrados
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
import Map from '@/Components/Map.vue'

const props = defineProps({
  vivienda: Object,
  resumenDeuda: Object
})
</script>
