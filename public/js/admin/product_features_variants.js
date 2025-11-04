$(document).ready(function(){

    // Quill
    const quill = new Quill('#quill-editor', { theme: 'snow' });
    $('#product-edit-form').submit(function(){
        $('#description').val(quill.root.innerHTML);
    });

    // Select2
    $('#attributes').select2({ placeholder: "Özellik seçin", width: '100%' });

    // Backend'den gelen seçenekler ve seçili değerler
    const allOptions = window.attributeOptions || {};
    const productValues = window.productValues || {};
    const selectedAttributes = window.selectedAttributes || [];
    const attributesMap = window.attributesMap || {};

    // Önceden kaydedilmiş varyantlar
    const variantsWithValues = window.variantsWithValues || [];

    // Sayfa yüklendiğinde
    $(document).ready(function() {
        // Önceden seçilmiş özellikleri seçili hale getir
        if (selectedAttributes && selectedAttributes.length > 0) {
            $('#attributes').val(selectedAttributes).trigger('change');
        }

        // Özellik seçimi değiştiğinde
        $('#attributes').on('change', function() {
            renderOptions();
        });

        // Sayfa yüklendiğinde seçenekleri ve varyantları göster
        renderOptions();
        renderExistingVariants();
    });

    function renderOptions(){
        const container = $('#attribute-options-container');
        container.empty();

        // Eğer attributes seçili değilse, işlemi durdur
        const selectedAttrs = $('#attributes').val();
        if (!selectedAttrs || selectedAttrs.length === 0) return;

        selectedAttrs.forEach(attrId=>{
            const options = allOptions[attrId] || [];
            if(options.length){
                let html = `<div class="mb-2"><strong>${$('#attributes option[value="'+attrId+'"]').text()}</strong><br>`;
                options.forEach(opt => {
                    const label = opt?.name ?? opt?.value ?? '';
                    const isColor = /^#([0-9A-F]{3}){1,2}$/i.test(opt.value);
                    const checked = (productValues[attrId] && productValues[attrId].includes(parseInt(opt.id))) ? 'checked' : '';

                    html += `<div class="form-check form-check-inline" style="align-items:center;">
                        <input class="form-check-input" type="checkbox" name="attribute_values[${attrId}][]" value="${opt.id}" ${checked}>
                        <label class="form-check-label" style="display:flex; align-items:center;">`;

                    if(isColor){
                        html += `<span style="display:inline-block;width:14px;height:14px;background-color:${opt.value};margin-right:5px;border:1px solid #ccc;"></span>`;
                    }

                    html += `${label}</label>
                    </div>`;
                });

                html += '</div>';
                container.append(html);
            }
        });
    }

    // Özellik seçimi değiştiğinde
    $('#attributes').on('change', function() {
        renderOptions();
    });

    // Sayfa yüklendiğinde mevcut varyantları göster
    setTimeout(function() {
        renderExistingVariants();
    }, 500);

    // OTOMATİK VARYANT OLUŞTURMA
    function combine(arrays) {
        if(arrays.length === 0) return [];
        if(arrays.length === 1) return arrays[0].map(v => [v]);

        const result = [];
        const allCasesOfRest = combine(arrays.slice(1));
        for(const v of arrays[0]) {
            for(const combination of allCasesOfRest) {
                result.push([v, ...combination]);
            }
        }
        return result;
    }

    // Sayfa yüklendiğinde mevcut varyantları göster
    function renderExistingVariants() {
        if (variantsWithValues && variantsWithValues.length > 0) {
            const tbody = $('#variant-list tbody');
            tbody.empty();

            variantsWithValues.forEach((variant, index) => {
                let row = '<tr>';
                row += `<td><input type="text" name="variants[${index}][sku]" class="form-control" value="${variant.sku || ''}"></td>`;
                row += `<td><input type="number" name="variants[${index}][price]" class="form-control" step="0.01" value="${variant.price || ''}"></td>`;
                row += `<td><input type="number" name="variants[${index}][stock]" class="form-control" value="${variant.stock || ''}"></td>`;
                row += '<td>';

                // Varyant özelliklerini göster
                if (variant.values) {
                    Object.entries(variant.values).forEach(([attrId, optionId]) => {
                        const attrName = $('#attributes option[value="'+attrId+'"]').text();
                        let optionLabel = '';

                        // Özellik seçeneğinin adını bul
                        if (allOptions[attrId]) {
                            const option = allOptions[attrId].find(opt => opt.id == optionId);
                            if (option) {
                                optionLabel = option.name || option.value || '';
                            }
                        }

                        row += `<span class="badge bg-secondary me-1">${attrName}: ${optionLabel}</span>`;
                        row += `<input type="hidden" name="variants[${index}][values][${attrId}]" value="${optionId}">`;
                    });
                }

                row += '</td>';
                row += `<td><button type="button" class="btn btn-sm btn-danger remove-variant">Sil</button></td>`;
                row += '</tr>';
                tbody.append(row);
            });
        }
    }

    // Sayfa yüklendiğinde mevcut varyantları göster
    renderExistingVariants();

    function renderExistingVariants() {
        if (!variantsWithValues || variantsWithValues.length === 0) return;

        const tbody = $('#variant-list tbody');

        variantsWithValues.forEach((variant, index) => {
            let row = '<tr>';
            row += `<td><input type="text" name="variants[${index}][sku]" class="form-control" value="${variant.sku || ''}"></td>`;
            row += `<td><input type="number" name="variants[${index}][price]" class="form-control" step="0.01" value="${variant.price || 0}"></td>`;
            row += `<td><input type="number" name="variants[${index}][stock]" class="form-control" value="${variant.stock || 0}"></td>`;
            row += '<td>';

            if (variant.values) {
                try {
                    const values = JSON.parse(variant.values);
                    Object.entries(values).forEach(([attrId, optionId]) => {
                        const attrName = window.attributesMap?.[attrId] || 'Özellik';
                        let optionLabel = optionId;

                        if (allOptions[attrId]) {
                            const option = allOptions[attrId].find(opt => opt.id == optionId);
                            if (option) {
                                optionLabel = option.name || option.value || '';
                            }
                        }

                        row += `<span class="badge bg-secondary me-1">${attrName}: ${optionLabel}</span>`;
                        row += `<input type="hidden" name="variants[${index}][values][${attrId}]" value="${optionId}">`;
                    });
                } catch (e) {
                    console.error('Varyant değerleri ayrıştırılamadı:', e);
                }
            }

            row += '</td>';
            row += `<td><button type="button" class="btn btn-sm btn-danger remove-variant">Sil</button></td>`;
            row += '</tr>';
            tbody.append(row);
        });
    }
        $('#generate-variants').click(function(){
        const selectedOptions = [];
        const featureIds = [];

        $('#attribute-options-container > div').each(function(){
            const attrId = $(this).find('input[type="checkbox"]').first().attr('name').match(/\d+/)[0];
            featureIds.push(attrId);

            const checkboxes = $(this).find('input[type="checkbox"]:checked');
            const vals = [];
            checkboxes.each(function(){
                vals.push({id: $(this).val(), label: $(this).next('label').text()});
            });

            selectedOptions.push(vals.length ? vals : [{}]);
        });

        const combinations = combine(selectedOptions);

        const tbody = $('#variant-list tbody');
        tbody.empty();

        combinations.forEach((combo,index)=>{
            let row = '<tr>';
            row += `<td><input type="text" name="variants[${index}][sku]" class="form-control"></td>`;
            row += `<td><input type="number" name="variants[${index}][price]" class="form-control" step="0.01"></td>`;
            row += `<td><input type="number" name="variants[${index}][stock]" class="form-control"></td>`;
            row += '<td>';

            combo.forEach((v,i)=>{
                if(v && v.id){
                    const attrId = featureIds[i];
                    row += `<span class="badge bg-secondary me-1">${$('#attributes option[value="'+attrId+'"]').text()}: ${v.label}</span>`;
                    row += `<input type="hidden" name="variants[${index}][values][${attrId}]" value="${v.id}">`;
                }
            });

            row += '</td>';
            row += `<td><button type="button" class="btn btn-sm btn-danger remove-variant">Sil</button></td>`;
            row += '</tr>';
            tbody.append(row);
        });
    });

    // MANUEL VARYANT EKLEME
    $('#manual-attribute').select2({
        placeholder: "Özellik seçin",
        width: '100%',
        dropdownParent: $('#manualVariantModal')
    });

    $('#manual-attribute').on('change', function(){
        const attrId = $(this).val();
        const container = $('#manual-attribute-options-container');
        container.empty();
        if(!attrId) return;

        const options = allOptions[attrId] || [];
        if(options.length){
            let html = `<div class="mb-2"><strong>${$('#manual-attribute option:selected').text()}</strong><br>`;
            options.forEach(opt=>{
                const label = opt.name ?? opt.value;
                const isColor = /^#([0-9A-F]{3}){1,2}$/i.test(opt.value);
                html += `<div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="manual_attribute_value" value="${opt.id}" data-option-id="${opt.id}">
                        <label class="form-check-label" style="display:flex;align-items:center;">`;
                if(isColor){
                    html += `<span style="display:inline-block;width:14px;height:14px;background-color:${opt.value};margin-right:5px;border:1px solid #ccc;"></span>`;
                }
                html += `${label}</label></div>`;
            });
            html += '</div>';
            container.append(html);
        }
    });

    $('#add-manual-variant').on('click', function(){
        const sku = $('#manual-sku').val();
        const price = $('#manual-price').val();
        const stock = $('#manual-stock').val();
        const attrId = $('#manual-attribute').val();
        const attrName = $('#manual-attribute option:selected').text();
        const selectedOptionId = $('input[name="manual_attribute_value"]:checked').data('option-id');
        const selectedOptionLabel = $('input[name="manual_attribute_value"]:checked').next('label').text();

        if(!sku || !attrId || !selectedOptionId){
            alert('Özellik ve seçenek ile SKU girilmelidir.');
            return;
        }

        const index = $('#variant-list tbody tr').length;
        let row = '<tr>';
        row += `<td><input type="text" name="variants[${index}][sku]" class="form-control" value="${sku}"></td>`;
        row += `<td><input type="number" name="variants[${index}][price]" class="form-control" step="0.01" value="${price}"></td>`;
        row += `<td><input type="number" name="variants[${index}][stock]" class="form-control" value="${stock}"></td>`;
        row += '<td>';
        row += `<span class="badge bg-secondary">${attrName}: ${selectedOptionLabel}</span>`;
        row += `<input type="hidden" name="variants[${index}][values][${attrId}]" value="${selectedOptionId}">`;
        row += '</td>';
        row += '<td><button type="button" class="btn btn-sm btn-danger remove-variant">Sil</button></td></tr>';

        $('#variant-list tbody').append(row);

        // Modal temizle
        $('#manual-attribute-options-container').empty();
        $('#manual-attribute').val('').trigger('change');
        $('#manual-sku').val('');
        $('#manual-price').val('');
        $('#manual-stock').val('');
        $('#manualVariantModal').modal('hide');
    });

    // Sil butonu
    $(document).on('click','.remove-variant',function(){
        $(this).closest('tr').remove();
    });

});
