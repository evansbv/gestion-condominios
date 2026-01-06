<template>
  <div class="mb-4">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <input
      :id="id"
      type="file"
      :name="name"
      :required="required"
      :disabled="disabled"
      :accept="accept"
      @change="handleFileChange"
      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
      :class="{ 'border-red-500': error }"
    />
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-else-if="hint" class="mt-1 text-sm text-gray-500">{{ hint }}</p>
    <p v-if="currentFile" class="mt-1 text-sm text-gray-600">
      Archivo seleccionado: <span class="font-medium">{{ currentFile }}</span>
    </p>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  label: String,
  name: String,
  id: String,
  required: Boolean,
  disabled: Boolean,
  accept: String,
  error: String,
  hint: String
})

const emit = defineEmits(['update:modelValue'])

const currentFile = ref(null)

const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    currentFile.value = file.name
    emit('update:modelValue', file)
  } else {
    currentFile.value = null
    emit('update:modelValue', null)
  }
}
</script>
