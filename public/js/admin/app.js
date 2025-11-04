// resources/js/admin/app.js
import '../bootstrap';
import * as bootstrap from 'bootstrap';
import initProductFeaturesVariants from './product_features_variants';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    try { initProductFeaturesVariants(); } catch (e) { console.error('initProductFeaturesVariants error', e); }
});
