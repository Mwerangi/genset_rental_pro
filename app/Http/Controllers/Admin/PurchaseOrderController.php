<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier')->latest();

        if ($request->search) {
            $query->where('po_number', 'like', "%{$request->search}%")
                  ->orWhereHas('supplier', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $orders = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => PurchaseOrder::count(),
            'draft'    => PurchaseOrder::where('status', 'draft')->count(),
            'pending'  => PurchaseOrder::whereIn('status', ['sent', 'partial'])->count(),
            'received' => PurchaseOrder::where('status', 'received')->count(),
        ];

        return view('admin.inventory.purchase-orders.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items     = InventoryItem::where('is_active', true)->with('category')->orderBy('name')->get();
        return view('admin.inventory.purchase-orders.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id'   => 'nullable|exists:suppliers,id',
            'expected_at'   => 'nullable|date',
            'notes'         => 'nullable|string',
            'items'         => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity_ordered'  => 'required|numeric|min:0.001',
            'items.*.unit_cost'         => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'supplier_id' => $request->supplier_id,
            'expected_at' => $request->expected_at,
            'notes'       => $request->notes,
            'status'      => 'draft',
            'created_by'  => auth()->id(),
        ]);

        foreach ($request->items as $line) {
            $po->items()->create([
                'inventory_item_id' => $line['inventory_item_id'],
                'quantity_ordered'  => $line['quantity_ordered'],
                'unit_cost'         => $line['unit_cost'],
                'notes'             => $line['notes'] ?? null,
            ]);
        }

        return redirect()->route('admin.purchase-orders.show', $po)
            ->with('success', $po->po_number . ' created.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load('supplier', 'items.inventoryItem.category', 'createdBy');
        return view('admin.inventory.purchase-orders.show', compact('purchaseOrder'));
    }

    public function send(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return back()->with('error', 'Only draft orders can be sent.');
        }

        $purchaseOrder->update([
            'status'     => 'sent',
            'ordered_at' => now(),
        ]);

        return back()->with('success', $purchaseOrder->po_number . ' marked as sent to supplier.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (!in_array($purchaseOrder->status, ['sent', 'partial'])) {
            return back()->with('error', 'Only sent or partially received orders can be received.');
        }

        $request->validate([
            'items'                       => 'required|array',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received'   => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $purchaseOrder) {
            $batchValue    = 0.0;
            $receivedItems = [];

            foreach ($request->items as $line) {
                $poItem = PurchaseOrderItem::with('inventoryItem.category.account')
                                           ->find($line['purchase_order_item_id']);
                $qty    = (float) $line['quantity_received'];

                if ($qty <= 0) continue;

                $lineValue    = $qty * (float) $poItem->unit_cost;
                $batchValue  += $lineValue;

                // Route each line to the category's COA account (falls back to 1150 Inventory)
                $accountCode    = $poItem->inventoryItem?->category?->account?->code ?? '1150';
                $receivedItems[] = ['account_code' => $accountCode, 'value' => $lineValue];

                $poItem->increment('quantity_received', $qty);

                // Update inventory stock
                $poItem->inventoryItem->adjustStock('in', $qty, $poItem->unit_cost, [
                    'purchase_order_id' => $purchaseOrder->id,
                    'notes'             => 'Received via ' . $purchaseOrder->po_number,
                    'created_by'        => auth()->id(),
                ]);

                // Update item unit cost to latest purchase price
                $poItem->inventoryItem->update(['unit_cost' => $poItem->unit_cost]);
            }

            // Determine new status
            $purchaseOrder->load('items');
            $fullyReceived = $purchaseOrder->items->every(
                fn($i) => $i->quantity_received >= $i->quantity_ordered
            );

            $purchaseOrder->update([
                'status'      => $fullyReceived ? 'received' : 'partial',
                'received_at' => $fullyReceived ? now() : null,
            ]);

            // Post Inventory / AP journal entry for every received batch (partial or full)
            if ($batchValue > 0) {
                app(JournalEntryService::class)->onPurchaseOrderReceived($purchaseOrder, $batchValue, $receivedItems);
            }
        });

        return back()->with('success', 'Stock received and inventory updated.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel a ' . $purchaseOrder->status . ' order.');
        }

        $purchaseOrder->update(['status' => 'cancelled']);

        return back()->with('success', $purchaseOrder->po_number . ' cancelled.');
    }
}
