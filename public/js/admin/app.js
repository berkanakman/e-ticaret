// resources/js/admin/app.js
// Central admin entry for JS. Ensures bootstrap is exposed globally and
// initializes common UI pieces (accordions, modals, tooltips) safely.

import '../bootstrap'; // Laravel default bootstrap helpers (axios, lodash, etc.)
import * as bootstrap from 'bootstrap';
import initVariants from './variants';
import './product_features_variants.js';
document.addEventListener('DOMContentLoaded', () => initVariants());

// expose bootstrap to global so legacy inline scripts can use `bootstrap`
window.bootstrap = bootstrap;

// provide a ready promise so inline scripts can await bootstrap availability
if (!window.bootstrapReady) {
    window.bootstrapReady = new Promise((resolve) => {
        if (window.bootstrap) return resolve(window.bootstrap);
        const iv = setInterval(() => {
            if (window.bootstrap) {
                clearInterval(iv);
                resolve(window.bootstrap);
            }
        }, 25);
        // safety timeout: resolve after 5s to avoid hanging completely
        setTimeout(() => {
            clearInterval(iv);
            resolve(window.bootstrap || null);
        }, 5000);
    });
}

console.log('APP.JS RUN AT', new Date().toISOString());

// --- UI init helpers ---
function initAccordions() {
    document.querySelectorAll('.accordion').forEach(acc => {
        const collapse = acc.querySelector('.accordion-collapse');
        if (!collapse) return;
        // ensure collapse instance exists if server rendered as open
        if (collapse.classList.contains('show')) {
            try { new bootstrap.Collapse(collapse, { toggle: false }); } catch (e) {}
        }
        // sync aria-expanded for toggles inside this accordion
        acc.querySelectorAll('[data-bs-toggle="collapse"]').forEach(btn => {
            const sel = btn.getAttribute('data-bs-target') || btn.getAttribute('href');
            const target = document.querySelector(sel);
            if (target) btn.setAttribute('aria-expanded', target.classList.contains('show') ? 'true' : 'false');
        });
    });
}

function initModals() {
    try {
        // Example modal binding: attributeOptionModal
        const optionModalEl = document.getElementById('attributeOptionModal');
        if (optionModalEl) {
            const optionModal = new bootstrap.Modal(optionModalEl);
            const saveBtn = document.getElementById('attributeOptionSaveBtn');
            if (saveBtn) {
                saveBtn.addEventListener('click', () => {
                    // placeholder: implement actual save logic where needed
                    optionModal.hide();
                });
            }
        }
    } catch (e) {
        console.warn('Modal init failed', e);
    }
}

function initTooltipsAndPopovers() {
    try {
        if (bootstrap.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
                try { new bootstrap.Tooltip(el); } catch (e) {}
            });
        }
        if (bootstrap.Popover) {
            document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
                try { new bootstrap.Popover(el); } catch (e) {}
            });
        }
    } catch (e) {
        console.warn('Tooltip/Popover init failed', e);
    }
}

// Optional: generic handler to ensure any inline scripts can safely use window.bootstrap
// Inline scripts can do: window.bootstrapReady.then(() => { /* use bootstrap */ });
document.addEventListener('DOMContentLoaded', () => {
    window.bootstrapReady.then(() => {
        try {
            initAccordions();
            initModals();
            initTooltipsAndPopovers();
        } catch (e) {
            console.warn('Admin init error', e);
        }
        console.log('Admin panel JS yüklendi', new Date().toISOString());
    });
});

// Export nothing; file runs for side effects.
