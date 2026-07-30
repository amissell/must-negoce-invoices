<template>
  <div class="p-4">
    <p class="text-xs text-gray-500 uppercase tracking-wide">Pouls des ventes — aujourd'hui</p>
    <p class="text-2xl font-bold text-gray-900 mt-2">{{ formattedCurrent }}</p>
    <p class="text-xs mt-1" :class="deltaClass">{{ arrow }} {{ weekPctDisplay }} vs même jour S-1</p>
    <div style="width:160px;height:48px" class="mt-2"><SparklineChart :data="sparkline" /></div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import SparklineChart from './SparklineChart.vue';

const props = defineProps({ initial: { type: Object, default: null } });

const current = ref(props.initial?.current ?? 0);
const sparkline = ref(props.initial?.sparkline ?? []);
const vsWeek = ref(props.initial?.vs_last_week ?? { percent: null, delta: 0 });

const formattedCurrent = computed(() => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(current.value) + ' MAD');
const weekPctDisplay = computed(() => vsWeek.value.percent === null ? 'n/a' : Math.abs(vsWeek.value.percent).toFixed(1) + '%');
const deltaClass = computed(() => vsWeek.value.delta >= 0 ? 'text-green-600' : 'text-red-600');
const arrow = computed(() => vsWeek.value.delta >= 0 ? '▲' : '▼');

async function fetchData() {
  try {
    const res = await axios.get('/api/kpis/sales-pulse');
    current.value = res.data.current;
    sparkline.value = res.data.sparkline || [];
    vsWeek.value = res.data.vs_last_week || vsWeek.value;
  } catch (e) {
    console.error('SalesPulse fetch error', e);
  }
}

onMounted(() => {
  if (!props.initial) fetchData();
  setInterval(fetchData, 60000);
});
</script>

<style scoped></style>
