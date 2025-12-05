// resources/js/admin/variants.js
export default function initVariants() {
    const boot = window.VARIANTS_BOOTSTRAP || {};
    const allAttrs = boot.allForJs || window.ATTRIBUTES_MAP || {};
    const existingVariants = boot.variantsForJs || [];
    const routes = boot.routes || {};

    function renderVariantToDOM(v) {
        const list = document.getElementById('variants-list');
        if (!list) return;

        const variantId = v.id ?? ('new-' + Math.random().toString(36).slice(2,8));

        const card = document.createElement('div');
        card.className = 'card mb-2 variant-row';
        card.dataset.variantId = variantId;

        const cardBody = document.createElement('div');
        cardBody.className = 'card-body p-2';

        const row = document.createElement('div');
        row.className = 'row align-items-center';

        // Variant options
        const optionsCol = document.createElement('div');
        optionsCol.className = 'col-md-5';
        (v.options || []).forEach(o => {
            const span = document.createElement('span');
            span.className = 'badge bg-secondary me-1';
            const attrName = o.attribute_name ?? (allAttrs[String(o.attribute_id)]?.name ?? '');
            const optName = o.option_name ?? (allAttrs[String(o.attribute_id)]?.options?.find(x=>x.id===o.option_id)?.name ?? '');
            span.textContent = `${attrName}: ${optName}`;
            optionsCol.appendChild(span);
        });

        // SKU
        const skuCol = document.createElement('div');
        skuCol.className = 'col-md-2';
        const skuInput = document.createElement('input');
        skuInput.type = 'text';
        skuInput.name = `variants[${variantId}][sku]`;
        skuInput.className = 'form-control form-control-sm';
        skuInput.value = v.sku ?? '';
        skuInput.placeholder = 'SKU';
        skuCol.appendChild(skuInput);

        // Price
        const priceCol = document.createElement('div');
        priceCol.className = 'col-md-2';
        const priceInput = document.createElement('input');
        priceInput.type = 'number';
        priceInput.name = `variants[${variantId}][price]`;
        priceInput.className = 'form-control form-control-sm';
        priceInput.value = v.price ?? '';
        priceInput.placeholder = 'Fiyat';
        priceInput.step = '0.01';
        priceCol.appendChild(priceInput);

        // Stock
        const stockCol = document.createElement('div');
        stockCol.className = 'col-md-2';
        const stockInput = document.createElement('input');
        stockInput.type = 'number';
        stockInput.name = `variants[${variantId}][stock]`;
        stockInput.className = 'form-control form-control-sm';
        stockInput.value = v.stock ?? '';
        stockInput.placeholder = 'Stok';
        stockCol.appendChild(stockInput);

        // Delete button
        const deleteCol = document.createElement('div');
        deleteCol.className = 'col-md-1 text-end';
        const deleteButton = document.createElement('button');
        deleteButton.type = 'button';
        deleteButton.className = 'btn btn-danger btn-sm';
        deleteButton.innerHTML = '&times;';
        deleteButton.addEventListener('click', () => {
            card.remove();
        });
        deleteCol.appendChild(deleteButton);

        row.appendChild(optionsCol);
        row.appendChild(skuCol);
        row.appendChild(priceCol);
        row.appendChild(stockCol);
        row.appendChild(deleteCol);
        cardBody.appendChild(row);
        card.appendChild(cardBody);
        list.appendChild(card);
    }

    (existingVariants || []).forEach(v => renderVariantToDOM(v));

    function getAttributeBlocks() {
        return Array.from(document.querySelectorAll('.attr-block'));
    }

    function collectAttributeOptionsFromPage() {
        const map = {};
        getAttributeBlocks().forEach(block => {
            const aid = block.dataset.attrId;
            if (!aid) return;
            const checked = Array.from(block.querySelectorAll('.attr-option-checkbox:checked')).map(c => Number(c.value)).filter(Boolean);
            if (checked.length) map[aid] = checked;
        });
        return map;
    }

    const genBtn = document.getElementById('generate-variants-btn');
    genBtn?.addEventListener('click', async function (e) {
        if (e && typeof e.preventDefault === 'function') { e.preventDefault(); e.stopPropagation(); }

        const attributeOptions = collectAttributeOptionsFromPage();
        if (Object.keys(attributeOptions).length === 0) {
            alert('Bir veya daha fazla seçenek işaretleyin.');
            return;
        }

        genBtn.disabled = true;
        genBtn.textContent = 'Oluşturuluyor...';

        try {
            const res = await fetch(routes.generate, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ attribute_options: attributeOptions })
            });
            if (res.ok) {
                const json = await res.json();
                (json.created || []).forEach(v => renderVariantToDOM(v));
                alert((json.count || 0) + ' yeni varyant oluşturuldu.');
            } else {
                const j = await res.json().catch(()=>null);
                alert(j?.message ?? 'Oluşturulamadı');
            }
        } catch (err) {
            console.error(err);
            alert('Sunucu hatası. Konsolu kontrol edin.');
        } finally {
            genBtn.disabled = false;
            genBtn.textContent = 'Otomatik Oluştur';
        }
    });

    // render selected attr-blocks client-side if none exist but attributes-select exists
    document.addEventListener('DOMContentLoaded', function () {
        if (!document.querySelectorAll('.attr-block').length && document.getElementById('attributes-select')) {
            const sel = document.getElementById('attributes-select');
            Array.from(sel.selectedOptions).forEach(opt => {
                const aid = opt.value;
                const attr = allAttrs[String(aid)];
                if (!attr) return;
                const container = document.getElementById('selected-attributes-list');
                if (!container) return;
                const block = document.createElement('div');
                block.className = 'mb-3 attr-block';
                block.dataset.attrId = attr.id;
                block.dataset.attrType = attr.type;
                const header = document.createElement('div');
                header.className = 'd-flex align-items-center justify-content-between mb-1';
                header.innerHTML = `<label class="form-label mb-0">${attr.name}</label>`;
                const optionsDiv = document.createElement('div');
                optionsDiv.className = 'options-list';
                attr.options.forEach(optItem => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'form-check form-check-inline';
                    const input = document.createElement('input');
                    input.className = 'form-check-input attr-option-checkbox';
                    input.type = 'checkbox';
                    input.name = `attributes[${attr.id}][]`;
                    input.id = `attr_${attr.id}_opt_${optItem.id}`;
                    input.value = optItem.id;
                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.htmlFor = input.id;
                    label.appendChild(document.createTextNode(optItem.name));
                    wrapper.appendChild(input);
                    wrapper.appendChild(label);
                    optionsDiv.appendChild(wrapper);
                });
                block.appendChild(header);
                block.appendChild(optionsDiv);
                container.appendChild(block);
            });
        }
    });
}
