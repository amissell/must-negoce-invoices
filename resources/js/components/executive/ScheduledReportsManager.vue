<template>
  <div class="scheduled-reports">
    <h3>Scheduled Reports</h3>

    <div v-if="loading">Loading…</div>

    <div v-else>
      <div v-if="reports.length === 0">
        No scheduled reports.
      </div>

      <ul>
        <li v-for="r in reports" :key="r.id">
          <strong>{{ r.name }}</strong>
          — {{ r.report_type }}
          — {{ r.status }}

          <label>
            <input
              type="checkbox"
              :checked="r.status === 'active'"
              @change="toggleStatus(r, $event.target.checked ? 'active' : 'inactive')"
            />
            Active
          </label>

          <div v-if="getLatestRun(r)">
            <small>Latest run: {{ getLatestRun(r).status }}</small>
            <small v-if="getLatestRun(r).finished_at">Finished: {{ getLatestRun(r).finished_at }}</small>
            <small v-if="getLatestRun(r).duration_ms">Duration: {{ getLatestRun(r).duration_ms }} ms</small>
            <small v-if="getLatestRun(r).file_size">File size: {{ getLatestRun(r).file_size }}</small>
            <small v-if="getLatestRun(r).status === 'failed' && getLatestRun(r).error_message">Error: {{ getLatestRun(r).error_message }}</small>
          </div>

          <button @click="edit(r)">
            Edit
          </button>

          <button @click="runNow(r.id)">
            Run now
          </button>

          <button
            v-if="canDownloadLatest(r)"
            type="button"
            @click="downloadLatest(r)"
          >
            Download Latest
          </button>

          <button @click="remove(r.id)">
            Delete
          </button>
        </li>
      </ul>

      <hr />

      <h4>
        {{ editingReportId ? 'Edit' : 'Create' }}
      </h4>

      <form @submit.prevent="editingReportId ? update() : create()">
        <input
          v-model="form.name"
          placeholder="Name"
          required
        />

        <select v-model="form.report_type">
          <option value="margins">Margins</option>
          <option value="tva">TVA</option>
          <option value="kpis">KPIs</option>
          <option value="profitability">Profitability</option>
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

        <input
          v-model="form.from"
          type="date"
          placeholder="From"
        />

        <input
          v-model="form.to"
          type="date"
          placeholder="To"
        />

        <select
          v-if="['margins', 'profitability'].includes(form.report_type)"
          v-model="form.dimension"
        >
          <option value="">Default</option>
          <option v-if="form.report_type === 'margins'" value="product">Product</option>
          <option v-if="form.report_type === 'margins'" value="client">Client</option>
          <option v-if="form.report_type === 'margins'" value="seller">Seller</option>
          <option v-if="form.report_type === 'profitability'" value="product">Product</option>
          <option v-if="form.report_type === 'profitability'" value="customer">Customer</option>
          <option v-if="form.report_type === 'profitability'" value="salesperson">Salesperson</option>
        </select>

        <input
          v-model="form.recipients"
          placeholder="Recipients (comma)"
        />

        <button type="submit">
          {{ editingReportId ? 'Update' : 'Create' }}
        </button>

        <button
          v-if="editingReportId"
          type="button"
          @click="cancelEdit"
        >
          Cancel
        </button>
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
      editingReportId: null,
      form: {
        name: '',
        report_type: 'margins',
        format: 'csv',
        recurrence: 'none',
        from: '',
        to: '',
        dimension: '',
        recipients: '',
      },
    };
  },

  mounted() {
    this.fetch();
  },

  methods: {
    async fetch() {
      this.loading = true;

      try {
        const res = await axios.get('/admin/scheduled-reports');
        this.reports = res.data;
      } finally {
        this.loading = false;
      }
    },

    resetForm() {
      this.form = {
        name: '',
        report_type: 'margins',
        format: 'csv',
        recurrence: 'none',
        from: '',
        to: '',
        dimension: '',
        recipients: '',
      };
    },

    edit(report) {
      this.editingReportId = report.id;

      this.form = {
        name: report.name || '',
        report_type: report.report_type || 'margins',
        format: report.format || 'csv',
        recurrence: report.recurrence || 'none',
        from: report.params && report.params.from ? report.params.from : '',
        to: report.params && report.params.to ? report.params.to : '',
        dimension: report.params && report.params.dimension ? report.params.dimension : '',
        recipients: Array.isArray(report.recipients)
          ? report.recipients.join(', ')
          : (report.recipients || ''),
      };
    },

    cancelEdit() {
      this.editingReportId = null;
      this.resetForm();
    },

    async create() {
      const recipients = this.form.recipients
        ? this.form.recipients
            .split(',')
            .map(s => s.trim())
            .filter(Boolean)
        : [];

      const params = {};
      if (this.form.from) params.from = this.form.from;
      if (this.form.to) params.to = this.form.to;
      if (this.form.dimension) params.dimension = this.form.dimension;

      const payload = {
        name: this.form.name,
        report_type: this.form.report_type,
        format: this.form.format,
        recurrence: this.form.recurrence,
        params: Object.keys(params).length ? params : null,
        recipients,
      };

      await axios.post('/admin/scheduled-reports', payload);

      this.resetForm();
      await this.fetch();
    },

    async update() {
      if (!this.editingReportId) {
        return;
      }

      const recipients = this.form.recipients
        ? this.form.recipients
            .split(',')
            .map(s => s.trim())
            .filter(Boolean)
        : [];

      const params = {};
      if (this.form.from) params.from = this.form.from;
      if (this.form.to) params.to = this.form.to;
      if (this.form.dimension) params.dimension = this.form.dimension;

      const payload = {
        name: this.form.name,
        report_type: this.form.report_type,
        format: this.form.format,
        recurrence: this.form.recurrence,
        params: Object.keys(params).length ? params : null,
        recipients,
      };

      await axios.put(
        `/admin/scheduled-reports/${this.editingReportId}`,
        payload
      );

      this.editingReportId = null;
      this.resetForm();
      await this.fetch();
    },

    getLatestRun(report) {
      if (!Array.isArray(report.runs) || report.runs.length === 0) {
        return null;
      }

      const sortedRuns = [...report.runs].sort((a, b) => {
        const aDate = a.finished_at ? new Date(a.finished_at).getTime() : 0;
        const bDate = b.finished_at ? new Date(b.finished_at).getTime() : 0;
        return bDate - aDate;
      });

      return sortedRuns[0];
    },

    canDownloadLatest(report) {
      const run = this.getLatestRun(report);
      return !!(run && run.status === 'success' && run.file_path);
    },

    async toggleStatus(report, status) {
      await axios.put(`/admin/scheduled-reports/${report.id}`, { status });
      await this.fetch();
    },

    async downloadLatest(report) {
      if (!report) {
        return;
      }

      const response = await axios.get(`/admin/scheduled-reports/${report.id}/download-latest`);
      const url = response && response.data ? response.data.download_url : null;

      if (url) {
        window.location.href = url;
      }
    },

    async remove(id) {
      await axios.delete(`/admin/scheduled-reports/${id}`);
      await this.fetch();
    },

    async runNow(id) {
      await axios.post(`/admin/scheduled-reports/${id}/run`);
      alert('Report dispatched');
    },
  },
};
</script>

<style scoped>
.scheduled-reports {
  background: white;
  padding: 1rem;
  border-radius: 6px;
}
</style>
