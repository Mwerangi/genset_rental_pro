<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::withCount('purchaseOrders');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('contact_person', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        $suppliers = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.inventory.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.inventory.suppliers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'category'             => 'nullable|in:fuel,parts,services,equipment,other',
            'contact_person'       => 'nullable|string|max:255',
            'phone'                => 'nullable|string|max:50',
            'phone_alt'            => 'nullable|string|max:50',
            'email'                => 'nullable|email|max:255',
            'website'              => 'nullable|url|max:255',
            'address'              => 'nullable|string|max:500',
            'city'                 => 'nullable|string|max:100',
            'country'              => 'nullable|string|max:100',
            'tin_number'           => 'nullable|string|max:100',
            'vrn_number'           => 'nullable|string|max:100',
            'payment_terms'        => 'nullable|string|max:100',
            'currency'             => 'nullable|in:TZS,USD',
            'bank_name'            => 'nullable|string|max:255',
            'bank_account_name'    => 'nullable|string|max:255',
            'bank_account_number'  => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
        ]);

        $supplier = Supplier::create($data);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', $supplier->name . ' added as supplier.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'purchaseOrders' => fn($q) => $q->latest()->limit(10),
            'purchaseOrders.items',
            'payments' => fn($q) => $q->latest()->limit(8),
        ]);

        $stats = [
            'total_orders'    => $supplier->purchaseOrders()->count(),
            'pending_orders'  => $supplier->purchaseOrders()->whereIn('status', ['draft', 'sent'])->count(),
            'received_orders' => $supplier->purchaseOrders()->where('status', 'received')->count(),
            'total_paid'      => $supplier->payments()->sum('amount'),
        ];

        return view('admin.inventory.suppliers.show', compact('supplier', 'stats'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.inventory.suppliers.create', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'category'             => 'nullable|in:fuel,parts,services,equipment,other',
            'contact_person'       => 'nullable|string|max:255',
            'phone'                => 'nullable|string|max:50',
            'phone_alt'            => 'nullable|string|max:50',
            'email'                => 'nullable|email|max:255',
            'website'              => 'nullable|url|max:255',
            'address'              => 'nullable|string|max:500',
            'city'                 => 'nullable|string|max:100',
            'country'              => 'nullable|string|max:100',
            'tin_number'           => 'nullable|string|max:100',
            'vrn_number'           => 'nullable|string|max:100',
            'payment_terms'        => 'nullable|string|max:100',
            'currency'             => 'nullable|in:TZS,USD',
            'bank_name'            => 'nullable|string|max:255',
            'bank_account_name'    => 'nullable|string|max:255',
            'bank_account_number'  => 'nullable|string|max:100',
            'notes'                => 'nullable|string',
            'is_active'            => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $supplier->update($data);

        return redirect()->route('admin.suppliers.show', $supplier)
            ->with('success', 'Supplier updated.');
    }
}
