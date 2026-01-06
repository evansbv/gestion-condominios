<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden" :class="customClass">
    <div v-if="$slots.header || title" class="px-6 py-4 border-b border-gray-200 bg-gray-50">
      <slot name="header">
        <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      </slot>
    </div>

    <div class="px-6 py-4" :class="noPadding ? '!p-0' : ''">
      <slot />
    </div>

    <div v-if="$slots.footer" class="px-6 py-4 border-t border-gray-200 bg-gray-50">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: String,
  noPadding: Boolean,
  customClass: String
})
</script>
