<template>
  <div class="chart-container" :style="{ height: height }">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch, onBeforeUnmount } from 'vue';
import { Chart, registerables } from 'chart.js';

// Register all Chart.js components
Chart.register(...registerables);

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (value: string) => ['line', 'bar', 'pie', 'doughnut'].includes(value)
  },
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  height: {
    type: String,
    default: '300px'
  }
});

const chartCanvas = ref<HTMLCanvasElement | null>(null);
let chartInstance: Chart | null = null;

const createChart = () => {
  if (!chartCanvas.value) return;
  
  // Destroy existing chart if it exists
  if (chartInstance) {
    chartInstance.destroy();
  }
  
  // Create new chart
  chartInstance = new Chart(chartCanvas.value, {
    type: props.type as any,
    data: props.data,
    options: {
      responsive: true,
      maintainAspectRatio: false,
      ...props.options
    }
  });
};

// Watch for data changes to update the chart
watch(() => props.data, () => {
  createChart();
}, { deep: true });

// Create chart on component mount
onMounted(() => {
  createChart();
});

// Clean up chart instance on component unmount
onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}
</style>
