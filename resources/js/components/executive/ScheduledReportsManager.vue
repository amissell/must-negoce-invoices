<template>
  <div class="scheduled-reports">
    <h3>Scheduled Reports</h3>
    <div v-if="loading">Loading…</div>
    <div v-else>
      <div v-if="reports.length === 0">No scheduled reports.</div>
      <ul>
        <li v-for="r in reports" :key="r.id">
          <strong>{{ r.name }}</strong> — {{ r.report_type }} — {{ r.status }}
          <button @click="runNow(r.id)">Run now</button>
          <button @click="remove(r.id)">Delete</button>
        </li>
      </ul>
      <hr/>
      <h4>Create</h4>
      <form @submit.prevent="create">
        <input v-model="form.name" placeholder="Name" required />
        <select v-model="form.report_type">
          <option value="margins">Margins</option>
          <option value="tva">TVA</option>
          <option value="kpis">KPIs</option>
        </select>
        <select v-model="form.format">
          <option value="csv">CSV</option>
          <option value="xlsx">XLSX</option>
        </select>
        <select v-model="form.recurrence">
          <option value="none">None</option>
          <option value="daily">Daily</option>
          <option value="weekly">Weekly</option>
          <option value="monthly">Monthly</option>
        </select>
        <input v-model="form.recipients" placeholder="Recipients (comma)" />
        <button type="submit">Create</button>
      </form>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
export default {
  data() {
    return {
      loading: true,
      reports: [],
      form: { name: '', report_type: 'margins', format: 'csv', recurrence: 'none', recipients: '' },
    };
  },
  mounted() {
    this.fetch();
  },
  methods: {
    async fetch() {
      this.loading = true;
      const res = await axios.get('/admin/scheduled-reports');
      this.reports = res.data;
      this.loading = false;
    },
    async create() {
      const recipients = this.form.recipients ? this.form.recipients.split(',').map(s => s.trim()) : [];
      const payload = { ...this.form, recipients };
      await axios.post('/admin/scheduled-reports', payload);
      this.form = { name: '', report_type: 'margins', format: 'csv', recurrence: 'none', recipients: '' };
      await this.fetch();
    },
    async remove(id) {
      await axios.delete(`/admin/scheduled-reports/${id}`);
      await this.fetch();
    },
    async runNow(id) {
      await axios.post(`/admin/scheduled-reports/${id}/run`);
      alert('Report dispatched');
    }
  }
}
</script>

<style scoped>
.scheduled-reports { background: white; padding: 1rem; border-radius: 6px; }
</style>
