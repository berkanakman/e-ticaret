// resources/js/admin/product_features_variants.js
export default function initProductFeaturesVariants() {
    // ensure ATTRIBUTES_MAP is available
    const attributesMap = window.ATTRIBUTES_MAP || {};
    const genBtn = document.getElementById('generate-variants-btn');
    if (!genBtn) return;

    function collectAttributeOptions() {
        const map = {};
        document.querySelectorAll('.attr-block').forEach(block => {
            const aid = block.dataset.attrId;
            if (!aid) return;
            const values = Array.from(block.querySelectorAll('.attr-option-checkbox:checked')).map(c => Number(c.value)).filter(Boolean);
            if (values.length) map[aid] = values;
        });
        return map;
    }

    genBtn.addEventListener('click', async function (e) {
        e.preventDefault();
        e.stopPropagation();
        const attributeOptions = collectAttributeOptions();
        if (!Object.keys(attributeOptions).length) {
            alert('Önce en az bir özellik için en az bir seçenek seçin.');
            return;
        }
        // send to backend - route must be present in window.VARIANTS_BOOTSTRAP.routes.generate or update accordingly
        const route = window.VARIANTS_BOOTSTRAP?.routes?.generate;
        if (!route) {
            alert('Generate route not configured');
            return;
        }
        genBtn.disabled = true;
        const orig = genBtn.textContent;
        genBtn.textContent = 'Oluşturuluyor...';
        try {
            const res = await fetch(route, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ attribute_options: attributeOptions })
            });
            if (res.ok) {
                const json = await res.json();
                // ideally render returned variants; here reload to simplify
                location.reload();
            } else {
                const err = await res.json().catch(()=>null);
                alert(err?.message || 'Varyant oluşturulamadı');
            }
        } catch (err) {
            console.error(err);
            alert('Sunucu hatası');
        } finally {
            genBtn.disabled = false;
            genBtn.textContent = orig;
        }
    });
}
