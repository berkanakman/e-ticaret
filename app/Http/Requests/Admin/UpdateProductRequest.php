<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize() { return $this->user('admin') != null; }
    public function rules(): array {
        return [
            'name'=>'required|string|max:191',
            'slug'=>'nullable|string|max:191|unique:products,slug',
            'description'=>'nullable|string',
            'price'=>'nullable|numeric',
            'stock'=>'nullable|integer',
            'sku'=>'nullable|string|max:191',
            'category_id'=>'nullable|integer|exists:categories,id',
            'is_active'=>'nullable|boolean',
            'attributes'=>'nullable|array',
            'attributes.*'=>'nullable',
            'attributes_new'=>'nullable|array',
            'variants'=>'nullable|array',
        ];
    }
}
