<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttributeRequest extends FormRequest
{
    public function authorize() { return $this->user('admin') != null; }
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191|unique:product_attributes,slug',
            'type' => ['required', Rule::in(['select','multiselect','text','color','number','boolean'])],
            'is_filterable' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer',
            'is_active' => 'sometimes|boolean',
            'options' => 'nullable|array',
            'options.*.id' => 'nullable|integer|exists:product_attribute_options,id',
            'options.*.name' => 'required_with:options|string|max:191',
            'options.*.value' => 'nullable|string|max:255',
            'options.*.meta' => 'nullable|array',
            'options.*.sort_order' => 'nullable|integer',
            'options.*.is_active' => 'nullable|boolean',
            'reorder' => 'nullable|array' // for AJAX reorder
        ];
    }
}
