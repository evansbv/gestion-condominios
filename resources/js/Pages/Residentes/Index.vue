<template>
  <AppLayout title="Residentes">
    <div class="py-6">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-3xl font-bold text-gray-900">Residentes</h1>
          <Button
             v-if="isAdmin"
            @click="router.visit(route('residentes.create'))"
            variant="primary"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo Residente
          </Button>
        </div>

        <Alert
          v-if="$page.props.flash.success"
          type="success"
          :message="$page.props.flash.success"
          :auto-dismiss="5000"
        />

        <Alert
          v-if="$page.props.flash.error"
          type="error"
          :message="$page.props.flash.error"
        />

        <Card class="mb-6">
          <form @submit.prevent="handleSearch" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <Input
              v-model="searchForm.search"
              label="Buscar"
              placeholder="Nombre, CI..."
              type="text"
            />

            <Select
              v-model="searchForm.tipo"
              label="Tipo de Residente"
            >
              <option value="TODOS">Todos</option>
              <option value="PROPIETARIO">Propietario</option>
              <option value="INQUILINO">Inquilino</option>
              <option value="FAMILIAR">Familiar</option>
            </Select>

            <div class="flex items-end gap-2">
              <Button type="submit" variant="primary" class="flex-1">
                Buscar
              </Button>
              <Button type="button" variant="ghost" @click="clearFilters">
                Limpiar
              </Button>
            </div>
          </form>
        </Card>

        <Card>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Foto
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Nombre Completo
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    CI
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Vivienda
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Tipo
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contacto
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Acciones
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="residente in residentes.data" :key="residente.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <img
                      v-if="residente.fotografia"
                      :src="`/urbalemana/gestion-condominios/public/storage/${residente.fotografia}`"
                      :alt="residente.nombres"
                      class="h-10 w-10 rounded-full object-cover"
                    />
                    <div v-else class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                      <span class="text-gray-500 font-medium">
                        {{ residente.nombres.charAt(0) }}{{ residente.apellido_paterno.charAt(0) }}
                      </span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ residente.nombres }} {{ residente.apellido_paterno }} {{ residente.apellido_materno }}
                    </div>
                    <div class="text-sm text-gray-500">{{ residente.user?.email }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ residente.ci }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ residente.vivienda?.numero }}</div>
                    <div class="text-sm text-gray-500">{{ residente.vivienda?.direccion }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full"
                      :class="{
                        'bg-blue-100 text-blue-800': residente.tipo_residente === 'PROPIETARIO',
                        'bg-green-100 text-green-800': residente.tipo_residente === 'INQUILINO',
                        'bg-purple-100 text-purple-800': residente.tipo_residente === 'FAMILIAR'
                      }"
                    >
                      {{ residente.tipo_residente }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div v-if="residente.celular">{{ residente.celular }}</div>
                    <div v-if="residente.telefono">{{ residente.telefono }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end gap-2">
                      <Button
                        size="sm"
                        variant="ghost"
                        @click="router.visit(route('residentes.show', residente.id))"
                      >
                        Ver
                      </Button>
                      <Button
                        v-if="canEdit"
                        size="sm"
                        variant="primary"
                        @click="router.visit(route('residentes.edit', residente.id))"
                      >
                        Editar
                      </Button>
                      <Button
                        v-if="canDelete"
                        size="sm"
                        variant="danger"
                        @click="confirmDelete(residente)"
                      >
                        Desactivar
                      </Button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <template #footer>
            <Pagination
              :links="residentes.links"
              :from="residentes.from"
              :to="residentes.to"
              :total="residentes.total"
            />
          </template>
        </Card>
      </div>
    </div>

    <Modal :show="showDeleteModal" @close="showDeleteModal = false" title="Confirmar Desactivación">
      <p class="text-sm text-gray-600">
        ¿Está seguro que desea desactivar al residente
        <strong>{{ selectedResidente?.nombres }} {{ selectedResidente?.apellido_paterno }}</strong>?
      </p>
      <p class="text-sm text-gray-600 mt-2">
        Esta acción desactivará también su usuario asociado.
      </p>

      <template #footer>
        <div class="flex justify-end gap-2">
          <Button variant="ghost" @click="showDeleteModal = false">
            Cancelar
          </Button>
          <Button variant="danger" @click="deleteResidente" :loading="deleteForm.processing">
            Desactivar
          </Button>
        </div>
      </template>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Button from '@/Components/Button.vue'
import Card from '@/Components/Card.vue'
import Input from '@/Components/Form/Input.vue'
import Select from '@/Components/Form/Select.vue'
import Alert from '@/Components/Alert.vue'
import Pagination from '@/Components/Pagination.vue'
import Modal from '@/Components/Modal.vue'

const props = defineProps({
  residentes: Object,
  filters: Object
})

const canCreate = ref(true)
const canEdit = ref(true)
const canDelete = ref(true)

const searchForm = reactive({
  search: props.filters.search || '',
  tipo: props.filters.tipo || 'TODOS'
})

const showDeleteModal = ref(false)
const selectedResidente = ref(null)

const deleteForm = useForm({})

const handleSearch = () => {
  router.get(route('residentes.index'), searchForm, {
    preserveState: true,
    preserveScroll: true
  })
}

const clearFilters = () => {
  searchForm.search = ''
  searchForm.tipo = 'TODOS'
  handleSearch()
}

const confirmDelete = (residente) => {
  selectedResidente.value = residente
  showDeleteModal.value = true
}

const deleteResidente = () => {
  deleteForm.delete(route('residentes.destroy', selectedResidente.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false
      selectedResidente.value = null
    }
  })
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
