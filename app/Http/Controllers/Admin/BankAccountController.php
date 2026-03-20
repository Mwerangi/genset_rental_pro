<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\BankAccount;
use App\Models\InvoicePayment;
use App\Models\SupplierPayment;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::with('account')->orderBy('name')->get();

        return view('admin.accounting.bank-accounts.index', compact('accounts'));
    }

    public function create()
    {
        $coaAccounts = Account::where('type', 'asset')
                              ->where('is_active', true)
                              ->orderBy('code')
                              ->get(['id', 'code', 'name']);

        return view('admin.accounting.bank-accounts.create', compact('coaAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:200',
            'account_type'    => 'required|in:bank,cash,mobile_money',
            'bank_name'       => 'nullable|string|max:200',
            'account_number'  => 'nullable|string|max:100',
            'account_id'      => 'required|exists:accounts,id',
            'currency'        => 'required|string|max:10',
            'current_balance' => 'required|numeric',
            'notes'           => 'nullable|string',
        ]);

        BankAccount::create($data + ['is_active' => true]);

        return redirect()->route('admin.accounting.bank-accounts.index')
                         ->with('success', 'Bank account created.');
    }

    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load('account');

        // Collect all transactions referencing this bank account
        $receipts = InvoicePayment::with('invoice.client')
                                  ->where('bank_account_id', $bankAccount->id)
                                  ->latest('payment_date')
                                  ->take(50)->get();

        $payments = SupplierPayment::with('supplier', 'purchaseOrder')
                                   ->where('bank_account_id', $bankAccount->id)
                                   ->latest('payment_date')
                                   ->take(50)->get();

        return view('admin.accounting.bank-accounts.show', compact('bankAccount', 'receipts', 'payments'));
    }

    public function edit(BankAccount $bankAccount)
    {
        $coaAccounts = Account::where('type', 'asset')
                              ->where('is_active', true)
                              ->orderBy('code')
                              ->get(['id', 'code', 'name']);

        return view('admin.accounting.bank-accounts.edit', compact('bankAccount', 'coaAccounts'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:200',
            'account_type'    => 'required|in:bank,cash,mobile_money',
            'bank_name'       => 'nullable|string|max:200',
            'account_number'  => 'nullable|string|max:100',
            'account_id'      => 'required|exists:accounts,id',
            'currency'        => 'required|string|max:10',
            'current_balance' => 'required|numeric',
            'notes'           => 'nullable|string',
            'is_active'       => 'boolean',
        ]);

        $bankAccount->update($data);

        return redirect()->route('admin.accounting.bank-accounts.index')
                         ->with('success', 'Bank account updated.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        if ($bankAccount->invoicePayments()->exists() || $bankAccount->supplierPayments()->exists()) {
            return back()->with('error', 'Cannot delete bank account with existing transactions.');
        }

        $bankAccount->delete();

        return redirect()->route('admin.accounting.bank-accounts.index')
                         ->with('success', 'Bank account deleted.');
    }
}
