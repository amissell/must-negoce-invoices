<template>
  <div class="p-4 grid grid-cols-3 gap-4">
    <div>
      <p class="text-xs text-gray-500 uppercase tracking-wide">CA MTD</p>
      <p class="text-2xl font-bold text-gray-900 mt-2">{{ formattedMtd }}</p>
    </div>
    <div>
      <p class="text-xs text-gray-500 uppercase tracking-wide">Créances totales</p>
      <p class="text-2xl font-bold text-red-600 mt-2">{{ formattedReceivables }}</p>
    </div>
    <div>
      <p class="text-xs text-gray-500 uppercase tracking-wide">Factures en retard</p>
      <p class="text-2xl font-bold text-red-600 mt-2">{{ data.overdue_invoices ?? '—' }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ initial: { type: Object, default: null } });

const data = ref(props.initial ?? { mtd_revenue: 0, outstanding_receivables: 0, overdue_invoices: 0 });

const formattedMtd = computed(() => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.value.mtd_revenue || 0) + ' MAD');
const formattedReceivables = computed(() => new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(data.value.outstanding_receivables || 0) + ' MAD');

async function fetchData() {
  try {
    const r = await axios.get('/api/kpis/ceo-scorecard');
    data.value = r.data;
  } catch (e) { console.error('CEO score fetch', e); }
}

onMounted(() => {
  if (!props.initial) fetchData();
  setInterval(fetchData, 60000);
});
</script>

<style scoped></style>
