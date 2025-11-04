// public/js/admin/panel.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin panel JS aktif');

    // === QUILL ===
    if (document.getElementById('editor')) {
        const quill = new Quill('#editor', { theme: 'snow' });
        const descInput = document.getElementById('description');
        document.querySelector('form')?.addEventListener('submit', function() {
            descInput.value = quill.root.innerHTML;
        });
    }

    // === SELECT2 ===
    if (window.jQuery && $('#attributeSelect').length) {
        $('#attributeSelect').select2();
    }

    // === ÖZELLİK SEÇİMİ ===
    const attributeSelect = document.getElementById('attributeSelect');
    const optionsContainer = document.getElementById('optionsContainer');
    const allOptions = window.ATTRIBUTE_OPTIONS || [];

    if (attributeSelect && optionsContainer) {
        attributeSelect.addEventListener('change', () => {
            const id = attributeSelect.value;
            const options = allOptions.filter(o => o.attribute_id == id);
            if (!options.length) {
                optionsContainer.innerHTML = '<small class="text-muted">Bu özelliğin seçeneği yok.</small>';
                return;
            }
            optionsContainer.innerHTML = options.map(o => `
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" value="${o.id}" id="opt_${o.id}">
                    <label class="form-check-label" for="opt_${o.id}">${o.name}</label>
                </div>
            `).join('');
        });
    }

    // === VARYANT ===
    const btnGenerate = document.getElementById('btnGenerateVariants');
    const variantsContainer = document.getElementById('variantsContainer');

    if (btnGenerate && variantsContainer) {
        btnGenerate.addEventListener('click', () => {
            const selected = Array.from(document.querySelectorAll('#optionsContainer input:checked'))
                .map(ch => ch.nextElementSibling.textContent.trim());
            if (!selected.length) {
                variantsContainer.innerHTML = '<small class="text-danger">Hiç seçenek seçilmedi.</small>';
                return;
            }
            variantsContainer.innerHTML = selected.map(v => `
                <div class="border p-2 mb-1 rounded">${v}</div>
            `).join('');
        });
    }
});
