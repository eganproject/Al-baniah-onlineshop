<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCollection;
use App\Support\AdminList;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** Admin › Katalog › Koleksi: aktivitas seperti Running/Yoga yang bisa berisi produk dari kategori mana pun. */
class CollectionController extends Controller
{
    /** Batas produk yang bisa diatur sekaligus dari form koleksi. */
    public const MAX_PRODUCTS = 500;

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->value();

        $query = ProductCollection::withCount('products')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->ordered();

        return view('admin.collections.index', [
            'collections' => AdminList::paginate($query, $request),
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return $this->form(new ProductCollection(['is_active' => true, 'show_on_home' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $collection = DB::transaction(function () use ($request) {
            $collection = ProductCollection::create($this->validated($request));
            $collection->products()->sync($this->productIds($request));

            return $collection;
        });

        return redirect()->route('admin.collections.index')->with('success', "Koleksi {$collection->name} ditambahkan.");
    }

    public function edit(ProductCollection $collection): View
    {
        return $this->form($collection);
    }

    public function update(Request $request, ProductCollection $collection): RedirectResponse
    {
        DB::transaction(function () use ($request, $collection) {
            $collection->update($this->validated($request, $collection));
            // Koleksi yang terlalu besar untuk form diatur dari form produk saja.
            if ($request->boolean('products_managed')) {
                $collection->products()->sync($this->productIds($request));
            }
        });

        return redirect()->route('admin.collections.index')->with('success', "Koleksi {$collection->name} diperbarui.");
    }

    public function destroy(ProductCollection $collection): RedirectResponse
    {
        if ($collection->image_path) {
            Storage::disk('public')->delete($collection->image_path);
        }

        $collection->delete();

        return back()->with('success', 'Koleksi dihapus. Produknya tetap ada.');
    }

    public function toggle(ProductCollection $collection): RedirectResponse
    {
        $collection->update(['is_active' => ! $collection->is_active]);

        return back()->with('success', "Koleksi {$collection->name} ".($collection->is_active ? 'ditampilkan.' : 'disembunyikan.'));
    }

    /** Pencarian produk untuk pemilih isi koleksi (JSON). */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = Str::limit(trim((string) $request->query('q')), 100, '');

        $products = Product::query()->search($term)
            ->orderByDesc('is_active')->orderBy('name')
            ->take(20)->get();

        return response()->json($products->map(fn (Product $p) => $p->pickerPayload())->all());
    }

    private function form(ProductCollection $collection): View
    {
        $count = $collection->exists ? $collection->products()->count() : 0;

        return view('admin.collections.form', [
            'collection' => $collection,
            'manageable' => $count <= self::MAX_PRODUCTS,
            'products' => $collection->exists
                ? $collection->products()->orderBy('name')->take(self::MAX_PRODUCTS)->get()->map(fn (Product $p) => $p->pickerPayload())->all()
                : [],
        ]);
    }

    /** @return list<int> */
    private function productIds(Request $request): array
    {
        $data = $request->validate([
            'products' => ['nullable', 'array', 'max:'.self::MAX_PRODUCTS],
            'products.*' => ['integer', 'distinct', Rule::exists('products', 'id')],
        ], ['products.max' => 'Maksimal '.self::MAX_PRODUCTS.' produk per koleksi dari form ini.'], ['products' => 'produk']);

        return array_map('intval', $data['products'] ?? []);
    }

    private function validated(Request $request, ?ProductCollection $collection = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', Rule::unique('collections')->ignore($collection)],
            'tagline' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'icon' => ['nullable', Rule::in(array_keys(ProductCollection::ICONS))],
            'tone' => ['nullable', Rule::in(array_keys(ProductCollection::TONES))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['sometimes', 'boolean'],
        ], [], ['name' => 'nama koleksi', 'image' => 'foto', 'tone' => 'warna', 'icon' => 'ikon']);

        $data['slug'] = ($data['slug'] ?? null) ?: $this->uniqueSlug($data['name'], $collection);
        $data['is_active'] = $request->boolean('is_active');
        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($collection?->image_path) {
                Storage::disk('public')->delete($collection->image_path);
            }
            $data['image_path'] = $request->hasFile('image') ? $request->file('image')->store('collections', 'public') : null;
        }

        unset($data['image'], $data['remove_image']);

        return $data;
    }

    private function uniqueSlug(string $name, ?ProductCollection $ignore): string
    {
        $base = Str::slug($name) ?: 'koleksi';
        $slug = $base;
        $i = 2;

        while (ProductCollection::where('slug', $slug)->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
