<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvoiceItem;
use App\Models\QuotationItem;
use App\Models\QuotationItemType;
use Illuminate\Http\Request;

class QuotationItemTypeController extends Controller
{
    public function index()
    {
        $itemTypes = QuotationItemType::orderBy('sort_order')->get();
        return view('admin.quotation-item-types.index', compact('itemTypes'));
    }

    /** @return \Illuminate\Http\RedirectResponse */
    private function toItemTypesTab(string $message, bool $isError = false)
    {
        $key = $isError ? 'item_type_error' : 'item_type_success';
        return redirect(route('admin.company-settings.edit') . '#item-types')->with($key, $message);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key'       => 'required|string|max:100|regex:/^[a-z0-9_]+$/|unique:quotation_item_types,key',
            'label'     => 'required|string|max:255',
            'is_rental' => 'boolean',
        ]);

        $maxOrder = QuotationItemType::max('sort_order') ?? 0;

        QuotationItemType::create([
            'key'        => $validated['key'],
            'label'      => $validated['label'],
            'is_rental'  => $request->boolean('is_rental'),
            'sort_order' => $maxOrder + 1,
            'is_active'  => true,
        ]);

        return $this->toItemTypesTab("Item type \"{$validated['label']}\" added.");
    }

    public function update(Request $request, QuotationItemType $itemType)
    {
        $validated = $request->validate([
            'label'      => 'required|string|max:255',
            'is_rental'  => 'boolean',
            'is_active'  => 'boolean',
            'sort_order' => 'required|integer|min:0',
        ]);

        $itemType->update([
            'label'      => $validated['label'],
            'is_rental'  => $request->boolean('is_rental'),
            'is_active'  => $request->boolean('is_active'),
            'sort_order' => $validated['sort_order'],
        ]);

        return $this->toItemTypesTab("Item type \"{$itemType->label}\" updated.");
    }

    public function destroy(QuotationItemType $itemType)
    {
        $inUse = QuotationItem::where('item_type', $itemType->key)->exists()
            || InvoiceItem::where('item_type', $itemType->key)->exists();

        if ($inUse) {
            return $this->toItemTypesTab(
                "Cannot delete \"{$itemType->label}\": it is used in existing quotations or invoices. Deactivate it instead.",
                true
            );
        }

        $label = $itemType->label;
        $itemType->delete();

        return $this->toItemTypesTab("Item type \"{$label}\" deleted.");
    }
}
