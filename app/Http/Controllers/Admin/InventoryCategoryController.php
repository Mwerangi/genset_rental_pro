<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\InventoryCategory;
use Illuminate\Http\Request;

class InventoryCategoryController extends Controller
{
    public function index()
    {
        $categories   = InventoryCategory::withCount('items')->with('account')->orderBy('name')->get();
        $coaAccounts  = Account::where('is_active', true)->orderBy('code')->get(['id', 'code', 'name', 'type']);
        return view('admin.inventory.categories.index', compact('categories', 'coaAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:inventory_categories,name',
            'description' => 'nullable|string|max:500',
            'account_id'  => 'nullable|exists:accounts,id',
        ]);

        InventoryCategory::create($data);

        return back()->with('success', 'Category "' . $data['name'] . '" created.');
    }

    public function update(Request $request, InventoryCategory $category)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:inventory_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
            'account_id'  => 'nullable|exists:accounts,id',
        ]);

        $category->update($data);

        return back()->with('success', 'Category updated.');
    }

    public function destroy(InventoryCategory $category)
    {
        if ($category->items()->exists()) {
            return back()->with('error', 'Cannot delete a category that has items. Reassign items first.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }
}
