<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttributeRequest;
use App\Http\Requests\Admin\UpdateAttributeRequest;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAttributeController extends Controller
{
    public function index()
    {
        $attributes = ProductAttribute::withCount('options')->orderBy('sort_order')->paginate(25);
        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.create', ['attribute' => null]);
    }

    public function store(StoreAttributeRequest $request)
    {
        $data = $request->only(['name','slug','type','is_filterable','sort_order','is_active']);
        if (empty($data['slug'])) $data['slug'] = Str::slug($data['name']);
        $attr = ProductAttribute::create($data);

        $options = $request->input('options', []);
        foreach ($options as $opt) {
            if (empty($opt['name'])) continue;
            $attr->options()->create([
                'name' => $opt['name'],
                'value' => $opt['value'] ?? null,
                'meta' => $opt['meta'] ?? null,
                'sort_order' => $opt['sort_order'] ?? 0,
                'is_active' => !empty($opt['is_active']) ? 1 : 0,
            ]);
        }

        return redirect()->route('admin.attributes.index')->with('success', 'Özellik oluşturuldu');
    }

    public function edit(ProductAttribute $attribute)
    {
        $attribute->load('options');
        return view('admin.attributes.edit', compact('attribute'));
    }

    public function update(UpdateAttributeRequest $request, ProductAttribute $attribute)
    {
        $data = $request->only(['name','slug','type','is_filterable','sort_order','is_active']);
        $attribute->update($data);

        $incoming = $request->input('options', []);
        $existing = $attribute->options()->pluck('id')->toArray();

        $incomingIds = [];
        foreach ($incoming as $i => $opt) {
            if (!empty($opt['id'])) {
                $incomingIds[] = (int)$opt['id'];
                ProductAttributeOption::where('id', $opt['id'])->update([
                    'name' => $opt['name'] ?? '',
                    'value' => $opt['value'] ?? null,
                    'meta' => $opt['meta'] ?? null,
                    'sort_order' => $opt['sort_order'] ?? $i,
                    'is_active' => !empty($opt['is_active']) ? 1 : 0,
                ]);
            } else {
                $attribute->options()->create([
                    'name' => $opt['name'] ?? '',
                    'value' => $opt['value'] ?? null,
                    'meta' => $opt['meta'] ?? null,
                    'sort_order' => $opt['sort_order'] ?? $i,
                    'is_active' => !empty($opt['is_active']) ? 1 : 0,
                ]);
            }
        }

        $toDelete = array_diff($existing, $incomingIds);
        if (!empty($toDelete)) {
            ProductAttributeOption::whereIn('id', $toDelete)->delete();
        }

        return redirect()->route('admin.attributes.edit', $attribute)->with('success', 'Özellik güncellendi');
    }

    public function destroy(ProductAttribute $attribute)
    {
        $attribute->delete();
        return redirect()->route('admin.attributes.index')->with('success', 'Özellik silindi');
    }

    public function ajaxAddOption(Request $request, ProductAttribute $attribute)
    {
        // optional authorize: $this->authorize('update',$attribute);
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'value' => 'nullable|string|max:255',
        ]);

        $opt = $attribute->options()->create([
            'name' => $data['name'],
            'value' => $data['value'] ?? null,
            'sort_order' => ($attribute->options()->max('sort_order') ?? 0) + 1,
            'is_active' => true,
        ]);

        return response()->json(['ok' => true, 'option' => $opt]);
    }

    public function reorderOptions(Request $request, ProductAttribute $attribute)
    {
        // optional authorize
        $order = $request->input('order', []);
        foreach ($order as $i => $id) {
            ProductAttributeOption::where('id', $id)->update(['sort_order' => $i]);
        }
        return response()->json(['ok' => true]);
    }
}
