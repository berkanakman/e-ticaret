<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    /**
     * Liste: yalnızca ana kategoriler (parent_id IS NULL)
     */
    public function index(Request $request)
    {
        $query = Category::whereNull('parent_id')->orderBy('sort_order', 'asc');

        if ($q = $request->query('q')) {
            $query->where('name', 'like', "%{$q}%");
        }

        $categories = $query->paginate(25);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Create form for top-level category (mevcut create kullanılabilir)
     */
    public function create()
    {
        $parents = Category::whereNull('parent_id')->orderBy('sort_order')->get();
        return view('admin.categories.create', compact('parents'));
    }

    /**
     * Store top-level category
     */
    public function store(Request $request)
    {
        $request->validate([
            'parent_id'   => 'nullable|exists:categories,id',
            'name'        => 'required|string|max:191',
            'slug'        => 'nullable|string|max:191|unique:categories,slug',
            'description' => 'nullable|string|max:2000',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'sometimes|boolean',
        ]);

        $data = $request->only(['parent_id','name','slug','description','sort_order']);
        $data['is_active'] = $request->boolean('is_active');

        $category = Category::create($data);

        if ($request->filled('parent_id')) {
            return redirect()->route('admin.categories.show', $request->input('parent_id'))
                ->with('success', 'Alt kategori oluşturuldu.');
        }

        return redirect()->route('admin.categories.index')->with('success', 'Kategori oluşturuldu.');
    }

    /**
     * Show parent category details and its children; allow adding child categories here.
     */
    public function show(Category $category)
    {
        abort_if($category->parent_id !== null, 404);

        // Yalnızca doğrudan çocuklar
        $children = $category->children()->orderBy('sort_order')->get();

        // form için ana kategorinin kendisini parent olarak gönder
        return view('admin.categories.show', compact('category', 'children'));
    }

    /**
     * Edit top-level or child category
     */
    public function edit(Category $category)
    {
        // parents listesinde, düzenlenen kategori kendisi veya kendi altını parent olarak seçilemeyecek
        $parents = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('sort_order')->get();

        return view('admin.categories.edit', compact('category','parents'));
    }

    /**
     * Update category (top-level veya child)
     */
    public function update(Request $request, Category $category)
    {
        $request->validate([
            'parent_id'   => 'nullable|exists:categories,id|not_in:'.$category->id,
            'name'        => 'required|string|max:191',
            'slug'        => 'nullable|string|max:191|unique:categories,slug,'.$category->id,
            'description' => 'nullable|string|max:2000',
            'sort_order'  => 'nullable|integer',
            'is_active'   => 'sometimes|boolean',
        ]);

        $data = $request->only(['parent_id','name','slug','description','sort_order']);
        $data['is_active'] = $request->boolean('is_active');

        $category->update($data);

        // Eğer güncelleme sonrası kategori artık child ise show yerine index'e yönlendirme mantığı
        if ($category->parent_id) {
            return redirect()->route('admin.categories.show', $category->parent_id)->with('success','Kategori güncellendi.');
        }

        return redirect()->route('admin.categories.index')->with('success','Kategori güncellendi.');
    }

    /**
     * Destroy category (korumalar)
     */
    public function destroy(Category $category)
    {
        // Alt kategori varsa silme
        if ($category->children()->exists()) {
            return redirect()->back()->with('error', 'Alt kategorisi olan kategori silinemez. Önce alt kategorileri taşı veya sil.');
        }

        if (method_exists($category, 'products') && $category->products()->exists()) {
            return redirect()->back()->with('error', 'Bu kategoride ürünler var. Önce ürünleri taşı veya sil.');
        }

        $parentId = $category->parent_id;
        $category->delete();

        if ($parentId) {
            return redirect()->route('admin.categories.show', $parentId)->with('success','Alt kategori silindi.');
        }

        return redirect()->route('admin.categories.index')->with('success','Kategori silindi.');
    }

    public function reorderTopLevel(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        $order = $request->input('order', []);

        // Güvenlik: gönderilen id'lerin hepsi top-level (parent_id IS NULL) olmalı
        $topLevelIds = Category::whereNull('parent_id')->pluck('id')->toArray();
        if (array_diff($order, $topLevelIds)) {
            throw ValidationException::withMessages(['order' => 'Geçersiz kategori listesi.']);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                Category::where('id', $id)->update(['sort_order' => $index]);
            }
        });

        return response()->json(['status' => 'ok']);
    }

    public function reorderChildren(Request $request, Category $category)
    {
        // ensure this is a top-level category
        abort_if($category->parent_id !== null, 404);

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:categories,id',
        ]);

        $order = $request->input('order', []);

        // Güvenlik: gönderilen id'lerin hepsi bu ana kategorinin çocukları olmalı
        $childrenIds = $category->children()->pluck('id')->toArray();
        if (array_diff($order, $childrenIds)) {
            throw ValidationException::withMessages(['order' => 'Geçersiz alt kategori listesi.']);
        }

        DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                // index bazlı sort_order (0 tabanlı) -> istersen 1 tabanlı yap
                \App\Models\Category::where('id', $id)->update(['sort_order' => $index]);
            }
        });

        return response()->json(['status' => 'ok']);
    }
}
