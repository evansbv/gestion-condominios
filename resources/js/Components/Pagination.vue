<template>
  <div v-if="links.length > 3" class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
    <div class="flex flex-1 justify-between sm:hidden">
      <Link
        v-if="prevUrl"
        :href="prevUrl"
        class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Anterior
      </Link>
      <Link
        v-if="nextUrl"
        :href="nextUrl"
        class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
      >
        Siguiente
      </Link>
    </div>

    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Mostrando
          <span class="font-medium">{{ from }}</span>
          a
          <span class="font-medium">{{ to }}</span>
          de
          <span class="font-medium">{{ total }}</span>
          resultados
        </p>
      </div>

      <div>
        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
          <component
            v-for="(link, index) in links"
            :key="index"
            :is="link.url ? Link : 'span'"
            :href="link.url"
            v-html="link.label"
            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold"
            :class="{
              'bg-blue-600 text-white z-10 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600': link.active,
              'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0': !link.active && link.url,
              'text-gray-400 ring-1 ring-inset ring-gray-300': !link.active && !link.url,
              'rounded-l-md': index === 0,
              'rounded-r-md': index === links.length - 1
            }"
          />
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
  links: {
    type: Array,
    required: true
  },
  from: Number,
  to: Number,
  total: Number
})

const prevUrl = computed(() => {
  const prevLink = props.links.find(link => link.label.includes('Previous') || link.label.includes('Anterior'))
  return prevLink?.url || null
})

const nextUrl = computed(() => {
  const nextLink = props.links.find(link => link.label.includes('Next') || link.label.includes('Siguiente'))
  return nextLink?.url || null
})
</script>
