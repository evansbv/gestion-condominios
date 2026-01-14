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
  datasets: {
    type: Array,
    required: true
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
      position: 'top'
    },
    tooltip: {
      enabled: true,
      mode: 'index',
      intersect: false
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

const createChart = () => {
  const ctx = document.getElementById(props.chartId)

  if (ctx && !chartInstance.value) {
    chartInstance.value = new Chart(ctx, {
      type: 'line',
      data: {
        labels: props.labels,
        datasets: props.datasets
      },
      options: { ...defaultOptions, ...props.options }
    })
  }
}

const updateChart = () => {
  if (chartInstance.value) {
    chartInstance.value.data.labels = props.labels
    chartInstance.value.data.datasets = props.datasets
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

watch(() => [props.labels, props.datasets], () => {
  updateChart()
}, { deep: true })
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}
</style>
