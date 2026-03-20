<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = InventoryItem::with('category')->latest();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->stock === 'low') {
            $query->whereColumn('current_stock', '<=', 'min_stock_level')
                  ->where('min_stock_level', '>', 0);
        }

        $items      = $query->paginate(20)->withQueryString();
        $categories = InventoryCategory::orderBy('name')->get();
        $lowStockCount = InventoryItem::whereColumn('current_stock', '<=', 'min_stock_level')
                            ->where('min_stock_level', '>', 0)->count();

        return view('admin.inventory.items.index', compact('items', 'categories', 'lowStockCount'));
    }

    public function show(InventoryItem $item)
    {
        $item->load('category');
        $movements = $item->stockMovements()
            ->with('maintenanceRecord', 'purchaseOrder', 'createdBy')
            ->latest()
            ->paginate(20);

        return view('admin.inventory.items.show', compact('item', 'movements'));
    }

    public function create()
    {
        $categories = InventoryCategory::orderBy('name')->get();
        return view('admin.inventory.items.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'     => 'nullable|exists:inventory_categories,id',
            'sku'             => 'required|string|max:100|unique:inventory_items,sku',
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'unit'            => 'required|in:pieces,litres,kg,metres,sets,pairs,boxes',
            'min_stock_level' => 'required|numeric|min:0',
            'unit_cost'       => 'required|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        $item = InventoryItem::create($data);

        return redirect()->route('admin.inventory.items.show', $item)
            ->with('success', $item->name . ' added to inventory.');
    }

    public function edit(InventoryItem $item)
    {
        $categories = InventoryCategory::orderBy('name')->get();
        return view('admin.inventory.items.create', compact('item', 'categories'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'category_id'     => 'nullable|exists:inventory_categories,id',
            'sku'             => 'required|string|max:100|unique:inventory_items,sku,' . $item->id,
            'name'            => 'required|string|max:255',
            'description'     => 'nullable|string',
            'unit'            => 'required|in:pieces,litres,kg,metres,sets,pairs,boxes',
            'min_stock_level' => 'required|numeric|min:0',
            'unit_cost'       => 'required|numeric|min:0',
            'notes'           => 'nullable|string|max:500',
        ]);

        $item->update($data);

        return redirect()->route('admin.inventory.items.show', $item)
            ->with('success', 'Item updated.');
    }

    public function adjust(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'type'      => 'required|in:in,out,adjustment',
            'quantity'  => 'required|numeric|min:0.001',
            'unit_cost' => 'nullable|numeric|min:0',
            'notes'     => 'nullable|string|max:500',
        ]);

        if ($data['type'] === 'out' && $item->current_stock < $data['quantity']) {
            return back()->with('error', 'Insufficient stock. Available: ' . $item->current_stock . ' ' . $item->unit_label);
        }

        $item->adjustStock($data['type'], $data['quantity'], $data['unit_cost'] ?? 0, [
            'notes'      => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'Stock adjusted successfully.');
    }
}
