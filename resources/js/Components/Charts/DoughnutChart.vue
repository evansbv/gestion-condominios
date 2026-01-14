<template>
  <div class="chart-container" :style="{ height: height }">
    <canvas :id="chartId"></canvas>
  </div>
</template>

<script setup>
import { onMounted, onBeforeUnmount, watch, ref } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  chartId: {
    type: String,
    required: true
  },
  labels: {
    type: Array,
    required: true
  },
  data: {
    type: Array,
    required: true
  },
  backgroundColor: {
    type: Array,
    default: () => [
      '#3b82f6',
      '#10b981',
      '#f59e0b',
      '#ef4444',
      '#8b5cf6',
      '#06b6d4'
    ]
  },
  height: {
    type: String,
    default: '300px'
  },
  options: {
    type: Object,
    default: () => ({})
  }
})

const chartInstance = ref(null)

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'right'
    },
    tooltip: {
      enabled: true
    }
  }
}

const createChart = () => {
  const ctx = document.getElementById(props.chartId)

  if (ctx && !chartInstance.value) {
    chartInstance.value = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: props.labels,
        datasets: [{
          data: props.data,
          backgroundColor: props.backgroundColor,
          borderWidth: 2,
          borderColor: '#ffffff'
        }]
      },
      options: { ...defaultOptions, ...props.options }
    })
  }
}

const updateChart = () => {
  if (chartInstance.value) {
    chartInstance.value.data.labels = props.labels
    chartInstance.value.data.datasets[0].data = props.data
    chartInstance.value.update()
  }
}

onMounted(() => {
  createChart()
})

onBeforeUnmount(() => {
  if (chartInstance.value) {
    chartInstance.value.destroy()
  }
})

watch(() => [props.labels, props.data], () => {
  updateChart()
}, { deep: true })
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}
</style>
