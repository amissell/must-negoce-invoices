<template>
  <canvas ref="canvas"></canvas>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
  data: { type: Array, default: () => [] },
  color: { type: String, default: '#14B8A6' },
});
const canvas = ref(null);
let chart = null;

onMounted(() => {
  chart = new Chart(canvas.value, {
    type: 'line',
    data: {
      labels: props.data.map((_, i) => i + 1),
      datasets: [{ data: props.data, borderColor: props.color, borderWidth: 2, pointRadius: 0, tension: 0.35, fill: false }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false }, tooltip: { enabled: false } },
      scales: { x: { display: false }, y: { display: false } },
    },
  });
});

watch(() => props.data, (v) => {
  if (chart) {
    chart.data.datasets[0].data = v || [];
    chart.update();
  }
});

onBeforeUnmount(() => {
  if (chart) {
    chart.destroy();
    chart = null;
  }
});
</script>

<style scoped>
canvas { width: 100%; height: 100%; }
</style>
