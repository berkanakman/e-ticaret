// public/js/admin/product_features_variants.js
(function () {
    function initProductFeaturesVariants() {
        const boot = window.VARIANTS_BOOTSTRAP || {};
        const attributesMap = boot.allForJs || window.ATTRIBUTES_MAP || {};
        const existingVariants = boot.variantsForJs || [];
        const container = document.getElementById('selected-attributes-list');
        const variantsList = document.getElementById('variants-list');
        const sel = document.getElementById('attributes-select');

        // Helper: Generate Cartesian Product
        function cartesian(args) {
            var r = [], max = args.length - 1;
            function helper(arr, i) {
                for (var j = 0, l = args[i].length; j < l; j++) {
                    var a = arr.slice(0); // clone arr
                    a.push(args[i][j]);
                    if (i == max) r.push(a);
                    else helper(a, i + 1);
                }
            }
            helper([], 0);
            return r;
        }

        // Helper: Escape HTML
        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.toString().replace(/[&<>"']/g, function (m) { return map[m]; });
        }

        function renderVariantToDOM(v, index) {
            if (!variantsList) return;

            // Check if variant with this ID already exists to prevent duplicates (if needed)
            // But here we might want to re-render or append. 
            // For generation, we usually clear or append. 
            // Let's assume append for now, but we need a way to identify them.

            const div = document.createElement('div');
            div.className = 'card mb-2 variant-row';
            // Use existing ID or generate a temp one for new variants
            const variantId = v.id || ('new_' + Math.random().toString(36).substr(2, 9));
            div.dataset.variantId = variantId;

            // Generate a unique key for the form array to avoid collisions on delete/add
            // If it's an existing variant, we can use 'id_{id}', otherwise random
            const rowKey = v.id ? 'id_' + v.id : 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5);

            let optionsHtml = '';
            let combinationKey = [];

            (v.options || []).forEach(function (o) {
                const attrId = o.attribute_id;
                const optId = o.option_id;

                // Find names if not provided
                let attrName = o.attribute_name;
                let optName = o.option_name;

                if (!attrName && attributesMap[attrId]) {
                    attrName = attributesMap[attrId].name;
                }
                if (!optName && attributesMap[attrId] && attributesMap[attrId].options) {
                    const foundOpt = attributesMap[attrId].options.find(x => x.id == optId);
                    if (foundOpt) optName = foundOpt.name;
                }

                optionsHtml += `<span class="badge bg-secondary me-1">${escapeHtml(attrName)}: ${escapeHtml(optName)}</span>`;

                // Hidden inputs for reconstruction
                optionsHtml += `<input type="hidden" name="variants[${rowKey}][values][${attrId}]" value="${optId}">`;

                combinationKey.push(optId);
            });

            // Sort combination key for consistency
            combinationKey.sort((a, b) => a - b);
            const combinationKeyStr = combinationKey.join('_');

            // Check if this combination already exists in the DOM to avoid duplicates during generation
            // If it exists, we might want to skip or update. For now, let's skip if exact match found.
            // But only for new generations. Existing variants from DB should be rendered.
            // We can use a data attribute for combination key.

            // Note: We need to handle the case where we are rendering existing variants which might not have 'values' formatted yet in the same way.
            // But existingVariants passed from server should be normalized.

            div.dataset.combination = combinationKeyStr;

            const sku = v.sku || '';
            const price = v.price || '';
            const stock = v.stock || '';

            div.innerHTML = `
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>${optionsHtml}</div>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-variant-btn">&times;</button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="small text-muted">SKU</label>
                            <input type="text" name="variants[${rowKey}][sku]" class="form-control form-control-sm" value="${escapeHtml(sku)}" placeholder="SKU">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Fiyat</label>
                            <input type="number" name="variants[${rowKey}][price]" class="form-control form-control-sm" value="${price}" step="0.01" placeholder="Fiyat">
                        </div>
                        <div class="col-md-4">
                            <label class="small text-muted">Stok</label>
                            <input type="number" name="variants[${rowKey}][stock]" class="form-control form-control-sm" value="${stock}" placeholder="Stok">
                        </div>
                    </div>
                    <input type="hidden" name="variants[${rowKey}][id]" value="${v.id || ''}">
                </div>
            `;

            // Attach event listener for remove button
            div.querySelector('.remove-variant-btn').addEventListener('click', function () {
                div.remove();
            });

            variantsList.appendChild(div);
        }

        // Render existing variants on load
        if (existingVariants && existingVariants.length > 0) {
            existingVariants.forEach(function (v, i) {
                renderVariantToDOM(v, i);
            });
        }

        function ensureBlocksFromSelect() {
            if (!container || !sel) return;

            const selectedIds = Array.from(sel.selectedOptions).map(o => o.value);

            // Remove blocks that are no longer selected
            const existingBlocks = container.querySelectorAll('.attr-block');
            existingBlocks.forEach(block => {
                if (!selectedIds.includes(block.dataset.attrId)) {
                    block.remove();
                }
            });

            // Add new blocks
            selectedIds.forEach(id => {
                if (container.querySelector(`.attr-block[data-attr-id="${id}"]`)) return;

                const attr = attributesMap[String(id)];
                if (!attr) return;

                const block = document.createElement('div');
                block.className = 'mb-3 attr-block';
                block.dataset.attrId = attr.id;
                block.dataset.attrType = attr.type;

                const header = document.createElement('div');
                header.className = 'd-flex align-items-center justify-content-between mb-1';
                header.innerHTML = `<label class="form-label mb-0">${escapeHtml(attr.name)}</label>`;

                const optionsDiv = document.createElement('div');
                optionsDiv.className = 'options-list';

                (attr.options || []).forEach(opt => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'form-check form-check-inline';

                    const input = document.createElement('input');
                    input.className = 'form-check-input attr-option-checkbox';
                    input.type = 'checkbox';
                    input.name = `attributes[${attr.id}][]`;
                    input.id = `attr_${attr.id}_opt_${opt.id}`;
                    input.value = opt.id;

                    // Check if this option was previously selected (if we have saved data)
                    // This part is tricky because we might be re-creating the block.
                    // Ideally we should check if there's a global saved state or check the DOM before removing.
                    // But for now, let's assume standard behavior.

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.htmlFor = input.id;

                    if (attr.type === 'color') {
                        const sp = document.createElement('span');
                        sp.style.cssText = `display:inline-block;width:18px;height:18px;border-radius:3px;background:${opt.value || '#000'};border:1px solid #ddd;margin-right:6px;vertical-align:middle`;
                        label.appendChild(sp);
                    }

                    label.appendChild(document.createTextNode(opt.name));
                    wrapper.appendChild(input);
                    wrapper.appendChild(label);
                    optionsDiv.appendChild(wrapper);
                });

                block.appendChild(header);
                block.appendChild(optionsDiv);
                container.appendChild(block);
            });
        }

        function collectAttributeOptions() {
            var data = [];
            document.querySelectorAll('.attr-block').forEach(function (block) {
                var aid = block.dataset.attrId;
                if (!aid) return;

                var checked = Array.from(block.querySelectorAll('.attr-option-checkbox:checked'));
                if (checked.length === 0) return;

                var options = checked.map(function (i) {
                    return {
                        attribute_id: aid,
                        option_id: i.value
                    };
                });

                data.push(options);
            });
            return data;
        }

        function generateVariants() {
            const sets = collectAttributeOptions();
            if (sets.length === 0) {
                alert('Lütfen en az bir özellik ve seçenek seçiniz.');
                return;
            }

            const combinations = cartesian(sets);

            combinations.forEach(combo => {
                // combo is an array of {attribute_id, option_id} objects

                // Check if this combination already exists
                const comboKey = combo.map(c => c.option_id).sort((a, b) => a - b).join('_');
                const exists = document.querySelector(`.variant-row[data-combination="${comboKey}"]`);

                if (exists) return; // Skip existing

                const variantData = {
                    id: null,
                    sku: '',
                    price: '',
                    stock: '',
                    options: combo
                };

                renderVariantToDOM(variantData);
            });
        }

        function attachGenerate() {
            const btn = document.getElementById('generate-variants-btn');
            if (!btn) return;
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                generateVariants();
            });
        }

        function initSelect2() {
            var attempts = 0;
            var maxAttempts = 20; // 2 seconds max

            function tryInit() {
                var $ = window.jQuery;
                if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') {
                    attempts++;
                    if (attempts < maxAttempts) {
                        console.warn('jQuery/Select2 not ready, retrying... (' + attempts + ')');
                        setTimeout(tryInit, 100);
                        return;
                    } else {
                        console.error('jQuery or Select2 failed to load after multiple attempts.');
                        return;
                    }
                }

                console.log('jQuery and Select2 loaded. Initializing...');
                var $sel = $(sel);
                if ($sel.length && !$sel.hasClass('select2-hidden-accessible')) {
                    $sel.select2({
                        placeholder: 'Özellik seçin',
                        width: '100%',
                        closeOnSelect: false,
                        allowClear: true
                    });

                    // Trigger change event for our logic when Select2 changes
                    $sel.on('change.select2', function () {
                        ensureBlocksFromSelect();
                    });
                    console.log('Select2 initialized successfully on #attributes-select');
                }
            }

            tryInit();
        }

        // Initialize
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                initSelect2();
                ensureBlocksFromSelect();
                attachGenerate();
                if (sel) sel.addEventListener('change', ensureBlocksFromSelect);
            });
        } else {
            initSelect2();
            ensureBlocksFromSelect();
            attachGenerate();
            if (sel) sel.addEventListener('change', ensureBlocksFromSelect);
        }
    }

    // Expose globally
    window.initProductFeaturesVariants = initProductFeaturesVariants;

    // Auto-init only if not already initialized
    if (!window.productFeaturesVariantsInitialized) {
        window.productFeaturesVariantsInitialized = true;
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProductFeaturesVariants);
        } else {
            initProductFeaturesVariants();
        }
    }

})();
