import './bootstrap';
import { createApp } from 'vue';
import SalesPulseCard from './components/executive/SalesPulseCard.vue';
import CEOScorecard from './components/executive/CEOScorecard.vue';

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
});
