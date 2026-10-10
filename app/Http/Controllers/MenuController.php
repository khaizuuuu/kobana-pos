<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display a listing of the menus.
     */
    public function index(Request $request): View
    {
        $query = Menu::with('category')->withCount('transactionItems');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === '1');
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'code');
        $sortOrder = $request->input('sort_order', 'asc');

        if (in_array($sortBy, ['code', 'name', 'price', 'created_at', 'transaction_items_count'])) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('code', 'asc');
        }

        $menus = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('menus.index', compact('menus', 'categories'));
    }

    /**
     * Show the form for creating a new menu.
     */
    public function create(): View
    {
        $categories = Category::orderBy('name')->get();

        // Calculate next code suggestion (e.g. M01 -> M20)
        $lastMenu = Menu::withTrashed()->orderBy('id', 'desc')->first();
        $suggestedCode = 'M01';
        if ($lastMenu && preg_match('/M(\d+)/i', $lastMenu->code, $matches)) {
            $nextNum = (int) $matches[1] + 1;
            $suggestedCode = sprintf('M%02d', $nextNum);
        }

        return view('menus.create', compact('categories', 'suggestedCode'));
    }

    /**
     * Store a newly created menu in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Strip non-digits from price input if user entered Rp or commas
        $price = (int) preg_replace('/[^\d]/', '', (string) $request->input('price', 0));
        $request->merge(['price' => $price]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:menus,code'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Kode menu wajib diisi.',
            'code.unique' => 'Kode menu sudah digunakan oleh menu lain.',
            'name.required' => 'Nama menu wajib diisi.',
            'category_id.required' => 'Kategori menu wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.min' => 'Harga menu tidak boleh bernilai negatif.',
        ]);

        Menu::create([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('menus.index')->with('success', 'Menu baru berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified menu.
     */
    public function edit(Menu $menu): View
    {
        $categories = Category::orderBy('name')->get();
        return view('menus.edit', compact('menu', 'categories'));
    }

    /**
     * Update the specified menu in storage.
     */
    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $price = (int) preg_replace('/[^\d]/', '', (string) $request->input('price', 0));
        $request->merge(['price' => $price]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('menus', 'code')->ignore($menu->id)],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'price' => ['required', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'code.required' => 'Kode menu wajib diisi.',
            'code.unique' => 'Kode menu sudah digunakan oleh menu lain.',
            'name.required' => 'Nama menu wajib diisi.',
            'category_id.required' => 'Kategori menu wajib dipilih.',
            'price.required' => 'Harga menu wajib diisi.',
            'price.min' => 'Harga menu tidak boleh bernilai negatif.',
        ]);

        $menu->update([
            'code' => strtoupper(trim($validated['code'])),
            'name' => trim($validated['name']),
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('menus.index')->with('success', 'Data menu berhasil diperbarui!');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(Menu $menu): RedirectResponse
    {
        $menu->update([
            'is_active' => !$menu->is_active,
        ]);

        $statusText = $menu->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('menus.index')->with('success', "Menu {$menu->name} berhasil {$statusText}!");
    }

    /**
     * Remove the specified menu from storage.
     */
    public function destroy(Menu $menu): RedirectResponse
    {
        $name = $menu->name;

        if ($menu->transactionItems()->exists()) {
            // Soft delete
            $menu->update(['is_active' => false]);
            $menu->delete();

            return redirect()->route('menus.index')->with(
                'warning',
                "Menu {$name} telah memiliki riwayat transaksi, sehingga diarsipkan (soft delete) dan dinonaktifkan untuk menjaga integritas data histori."
            );
        }

        $menu->forceDelete();

        return redirect()->route('menus.index')->with('success', "Menu {$name} berhasil dihapus permanen!");
    }
}
