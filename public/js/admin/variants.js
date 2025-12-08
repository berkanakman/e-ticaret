(function($) {
    "use strict";

    // Debounce function to limit the rate at which a function can fire.
    function debounce(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    // --- STATE ---
    let selectedAttributes = {}; // { attribute_id: [option_id1, option_id2], ... }
    let variants = window.VARIANTS_BOOTSTRAP.variantsForJs || [];
    const attributesMap = window.VARIANTS_BOOTSTRAP.allForJs || {};

    // --- DOM ELEMENTS ---
    const $attributesSelect = $('#attributes-select');
    const $selectedAttributesList = $('#selected-attributes-list');
    const $variantsList = $('#variants-list');
    const $generateBtn = $('#generate-variants-btn');

    // --- FUNCTIONS ---

    /**
     * Renders the list of variants based on the current `variants` state array.
     */
    function renderVariants() {
        $variantsList.empty();
        if (variants.length === 0) {
            $variantsList.html('<div class="alert alert-info">Gösterilecek varyant yok. Lütfen yukarıdan özellik seçip "Otomatik Oluştur" butonuna tıklayın.</div>');
            return;
        }

        variants.forEach((variant, index) => {
            const variantId = variant.id || `new_${index}`;
            const optionsHtml = variant.options.map(opt => {
                // Hidden input to store the selected option for this variant
                const inputName = `variants[${variantId}][options][${opt.attribute_id}]`;
                return `<input type="hidden" name="${inputName}" value="${opt.option_id}">
                        <span class="badge bg-primary me-1">${opt.attribute_name}: ${opt.option_name}</span>`;
            }).join('');

            const variantHtml = `
                <div class="variant-item row align-items-center mb-3 p-2 border rounded">
                    <div class="col-md-5">
                        <div class="fw-bold">Seçenekler:</div>
                        <div>${optionsHtml}</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">SKU</label>
                        <input type="text" name="variants[${variantId}][sku]" class="form-control form-control-sm" value="${variant.sku || ''}" placeholder="Varyant SKU">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Fiyat</label>
                        <input type="number" step="0.01" name="variants[${variantId}][price]" class="form-control form-control-sm" value="${variant.price || ''}" placeholder="Fiyat">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Stok</label>
                        <input type="number" name="variants[${variantId}][stock]" class="form-control form-control-sm" value="${variant.stock || ''}" placeholder="Stok Adedi">
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-danger btn-sm delete-variant-btn" data-index="${index}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            $variantsList.append(variantHtml);
        });
    }


    /**
     * Updates the checkbox list based on the selected attributes in the main select dropdown.
     */
    function updateSelectedAttributes() {
        const selectedIds = $attributesSelect.val() || [];
        $selectedAttributesList.empty();
        selectedAttributes = {}; // Reset state

        if (selectedIds.length === 0) {
            $selectedAttributesList.html('<div class="text-muted">Varyant oluşturmak için lütfen "Kullanılacak Özellikler" listesinden seçim yapın.</div>');
        }

        selectedIds.forEach(attributeId => {
            const attribute = attributesMap[attributeId];
            if (!attribute) return;

            // Initialize the attribute in our state object
            selectedAttributes[attributeId] = [];

            let optionsHtml = attribute.options.map(option => {
                // Determine if this option should be checked
                const isChecked = variants.some(v => v.options.some(o => o.option_id === option.id));
                return `
                    <div class="form-check form-check-inline">
                        <input class="form-check-input attribute-option-check"
                               type="checkbox"
                               id="option-${option.id}"
                               data-attribute-id="${attribute.id}"
                               value="${option.id}">
                        <label class="form-check-label" for="option-${option.id}">${option.name}</label>
                    </div>
                `;
            }).join('');

            const attributeHtml = `
                <div class="mb-3 p-2 border rounded">
                    <h6 class="mb-2">${attribute.name}</h6>
                    <div>${optionsHtml}</div>
                </div>
            `;
            $selectedAttributesList.append(attributeHtml);
        });
    }


    /**
     * Handles the generation of variants via AJAX.
     */
    function handleGenerateVariants() {
        // Collect all checked options, grouped by attribute
        const selectedOptions = {};
        $('.attribute-option-check:checked').each(function() {
            const attrId = $(this).data('attribute-id');
            const optionId = $(this).val();
            if (!selectedOptions[attrId]) {
                selectedOptions[attrId] = [];
            }
            selectedOptions[attrId].push(optionId);
        });

        if (Object.keys(selectedOptions).length === 0) {
            alert('Lütfen kombinasyon oluşturmak için en az bir seçenek işaretleyin.');
            return;
        }

        // Show loading indicator
        $generateBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Oluşturuluyor...');

        $.ajax({
            url: window.VARIANTS_BOOTSTRAP.routes.generate,
            type: 'POST',
            data: {
                options: selectedOptions,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(generatedVariants) {
                // Basic check if the response is what we expect
                if (Array.isArray(generatedVariants)) {
                    // Simple merge: add new variants to the existing list, avoiding exact duplicates
                    generatedVariants.forEach(newVariant => {
                        const isDuplicate = variants.some(existingVariant => {
                            if (existingVariant.options.length !== newVariant.options.length) return false;
                            // Check if all options in the new variant exist in the current variant
                            return newVariant.options.every(newOpt =>
                                existingVariant.options.some(existOpt =>
                                    existOpt.attribute_id == newOpt.attribute_id && existOpt.option_id == newOpt.option_id
                                )
                            );
                        });

                        if (!isDuplicate) {
                            variants.push(newVariant);
                        }
                    });
                    renderVariants();
                } else {
                    console.error("Unexpected response format:", generatedVariants);
                    alert("Varyantlar oluşturulurken bir hata oluştu. Lütfen konsolu kontrol edin.");
                }
            },
            error: function(xhr) {
                console.error("AJAX Error:", xhr.responseText);
                alert("Sunucuyla iletişim kurulamadı. Lütfen tekrar deneyin.");
            },
            complete: function () {
                // Hide loading indicator
                $generateBtn.prop('disabled', false).text('Otomatik Oluştur');
            }
        });
    }

    // --- EVENT LISTENERS ---

    // When attribute selection changes, update the checkbox UI
    $attributesSelect.on('change', debounce(updateSelectedAttributes, 200));

    // Generate variants button click
    $generateBtn.on('click', handleGenerateVariants);

    // Delete variant button click (uses event delegation)
    $variantsList.on('click', '.delete-variant-btn', function() {
        if (confirm('Bu varyantı silmek istediğinizden emin misiniz?')) {
            const indexToRemove = $(this).data('index');
            variants.splice(indexToRemove, 1); // Remove the variant from the state array
            renderVariants(); // Re-render the list
        }
    });


    // --- INITIALIZATION ---
    $(document).ready(function() {
        // Initial setup on page load
        if ($attributesSelect.length > 0) {
            updateSelectedAttributes(); // Display checkboxes for pre-selected attributes
            renderVariants(); // Display existing variants
        }
    });

})(jQuery);
