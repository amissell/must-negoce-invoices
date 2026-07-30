import './bootstrap';
import { createApp } from 'vue';
import SalesPulseCard from './components/executive/SalesPulseCard.vue';
import CEOScorecard from './components/executive/CEOScorecard.vue';
import MarginDetailsTable from './components/executive/MarginDetailsTable.vue';
import TvaDetailsTable from './components/executive/TvaDetailsTable.vue';
import ScheduledReportsManager from './components/executive/ScheduledReportsManager.vue';

// Mount components on existing Blade mount points (if present). Blade provides initial data via data-initial attribute.
document.addEventListener('DOMContentLoaded', () => {
    const sp = document.getElementById('vue-sales-pulse');
    if (sp) {
        const initial = sp.dataset.initial ? JSON.parse(sp.dataset.initial) : null;
        createApp(SalesPulseCard, { initial }).mount(sp);
    }

    const ceo = document.getElementById('vue-ceo-scorecard');
    if (ceo) {
        const initial = ceo.dataset.initial ? JSON.parse(ceo.dataset.initial) : null;
        createApp(CEOScorecard, { initial }).mount(ceo);
    }

    const margin = document.getElementById('vue-margin-details');
    if (margin) {
        createApp(MarginDetailsTable).mount(margin);
    }

    const tva = document.getElementById('vue-tva-details');
    if (tva) {
        createApp(TvaDetailsTable).mount(tva);
    }

    const mgr = document.getElementById('vue-scheduled-reports');
    if (mgr) {
        createApp(ScheduledReportsManager).mount(mgr);
    }
});
