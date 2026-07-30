<template>
  <div class="mt-4 bg-white rounded-2xl p-4">
    <div class="flex items-center justify-between mb-3">
      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Période</label>
        <input type="date" v-model="from" class="border rounded px-2 py-1" />
        <input type="date" v-model="to" class="border rounded px-2 py-1" />
        <button @click="reload" class="ml-2 px-3 py-1 bg-blue-600 text-white rounded">Appliquer</button>
      </div>
      <div>
        <button @click="exportCsv" :disabled="exporting" class="px-3 py-1 bg-gray-100 border rounded">{{ exporting ? 'Export...' : 'Exporter CSV' }}</button>
      </div>
    </div>

    <div v-if="loading" class="py-6 text-center text-gray-500">Chargement…</div>

    <div v-else>
      <table v-if="rows.length" class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Facture</th><th class="py-2">Date</th><th class="py-2">Client</th><th class="py-2 text-right">TVA</th></tr></thead>
        <tbody>
          <tr v-for="r in rows" :key="r.invoice_id" class="border-b last:border-0 hover:bg-gray-50">
            <td class="py-2 font-medium">{{ r.invoice_number }}</td>
            <td class="py-2">{{ r.invoice_date }}</td>
            <td class="py-2">{{ r.client_label }}</td>
            <td class="py-2 text-right">{{ fmt(r.tva_amount) }}</td>
          </tr>
        </tbody>
      </table>

      <div v-else class="py-6 text-center text-gray-400">Aucune TVA pour cette période.</div>

      <div class="flex items-center justify-between mt-3">
        <div class="text-sm text-gray-600">Page {{ meta.current_page }} / {{ meta.last_page }}</div>
        <div class="flex items-center gap-2">
          <button @click="prev" :disabled="!meta.prev_page_url" class="px-3 py-1 border rounded">Préc</button>
          <button @click="next" :disabled="!meta.next_page_url" class="px-3 py-1 border rounded">Suiv</button>
        </div>
      </div>
    </div>
    <div v-if="error" class="mt-3 text-red-600">{{ error }}</div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() { return { from: '', to: '', page:1, per_page:25, rows: [], meta: { current_page:1, last_page:1, prev_page_url:null, next_page_url:null }, loading:false, exporting:false, error:null }; },
  mounted() {
    const today = new Date();
    const first = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0,10);
    const last = new Date(today.getFullYear(), today.getMonth()+1, 0).toISOString().slice(0,10);
    this.from = first; this.to = last; this.reload();
  },
  methods: {
    fmt(v) { return new Intl.NumberFormat('fr-FR', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(v) + ' MAD'; },
    async loadPage() {
      this.loading = true; this.error = null;
      try {
        const params = { from: this.from, to: this.to, per_page: this.per_page, page: this.page };
        const res = await axios.get('/api/kpis/tva/details', { params });
        const data = res.data;
        this.rows = data.data || [];
        this.meta = { current_page: data.current_page, last_page: data.last_page, prev_page_url: data.prev_page_url, next_page_url: data.next_page_url };
      } catch (e) { this.error = e.response?.data?.message || 'Erreur réseau'; }
      finally { this.loading = false; }
    },
    reload() { this.page = 1; this.loadPage(); },
    next() { if (this.meta.next_page_url) { this.page++; this.loadPage(); } },
    prev() { if (this.meta.prev_page_url) { this.page--; this.loadPage(); } },
    exportCsv() { this.exporting = true; try { const params = new URLSearchParams({ from: this.from, to: this.to }).toString(); window.location.href = '/api/kpis/tva/export?' + params; } catch (e) { this.error = 'Impossible d\'exporter'; } finally { this.exporting = false; } }
  }
}
</script>

<style scoped>
.card-shadow { box-shadow: 0 6px 18px rgba(15,23,42,0.06); }
</style>
