<template>
  <div>
    <div :id="mapId" :style="{ height: height }" class="rounded-lg border border-gray-300"></div>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, watch, ref } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

const props = defineProps({
  markers: {
    type: Array,
    default: () => []
  },
  center: {
    type: Array,
    default: () => [-16.5000, -68.1500] // La Paz, Bolivia
  },
  zoom: {
    type: Number,
    default: 13
  },
  height: {
    type: String,
    default: '500px'
  },
  editable: {
    type: Boolean,
    default: false
  },
  modelValue: {
    type: Object,
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'marker-click'])

const mapId = `map-${Math.random().toString(36).substr(2, 9)}`
const map = ref(null)
const markerLayer = ref(null)
const editableMarker = ref(null)

// Fix Leaflet default marker icon issue with Vite
delete L.Icon.Default.prototype._getIconUrl
L.Icon.Default.mergeOptions({
  iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
  iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
  shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
})

onMounted(() => {
  // Initialize map
  map.value = L.map(mapId).setView(props.center, props.zoom)

  // Add OpenStreetMap tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
  }).addTo(map.value)

  // Create marker layer group
  markerLayer.value = L.layerGroup().addTo(map.value)

  // Add markers
  if (props.markers && props.markers.length > 0) {
    addMarkers(props.markers)
  }

  // If editable, add click handler to place marker
  if (props.editable) {
    if (props.modelValue) {
      addEditableMarker(props.modelValue.lat, props.modelValue.lng)
    }

    map.value.on('click', (e) => {
      const { lat, lng } = e.latlng
      addEditableMarker(lat, lng)
      emit('update:modelValue', { lat, lng })
    })
  }
})

onUnmounted(() => {
  if (map.value) {
    map.value.remove()
  }
})

watch(() => props.markers, (newMarkers) => {
  if (markerLayer.value && !props.editable) {
    markerLayer.value.clearLayers()
    addMarkers(newMarkers)
  }
}, { deep: true })

watch(() => props.modelValue, (newValue) => {
  if (props.editable && newValue) {
    addEditableMarker(newValue.lat, newValue.lng)
  }
}, { deep: true })

const addMarkers = (markers) => {
  markers.forEach(marker => {
    const leafletMarker = L.marker([marker.lat, marker.lng])
      .bindPopup(marker.popup || '')
      .addTo(markerLayer.value)

    if (marker.clickable !== false) {
      leafletMarker.on('click', () => {
        emit('marker-click', marker)
      })
    }
  })

  // Fit bounds if there are markers
  if (markers.length > 0) {
    const bounds = L.latLngBounds(markers.map(m => [m.lat, m.lng]))
    map.value.fitBounds(bounds, { padding: [50, 50] })
  }
}

const addEditableMarker = (lat, lng) => {
  if (editableMarker.value) {
    editableMarker.value.setLatLng([lat, lng])
  } else {
    editableMarker.value = L.marker([lat, lng], { draggable: true })
      .addTo(markerLayer.value)
      .bindPopup('Ubicación seleccionada')
      .openPopup()

    editableMarker.value.on('dragend', (e) => {
      const { lat, lng } = e.target.getLatLng()
      emit('update:modelValue', { lat, lng })
    })
  }

  map.value.setView([lat, lng], props.zoom)
}
</script>

<style>
/* Ensure Leaflet CSS is loaded */
@import 'leaflet/dist/leaflet.css';
</style>
