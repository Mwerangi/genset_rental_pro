<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\BankAccount;
use App\Models\CashRequest;
use App\Models\CashRequestItem;
use App\Models\ExpenseCategory;
use App\Services\JournalEntryService;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CashRequestController extends Controller
{
    public function index(Request $request)
    {
        $user   = auth()->user();
        // Users with approve or full accounting access see ALL requests; others see only their own
        $seeAll = PermissionService::can($user, 'view_all_cash_requests');

        $query = CashRequest::with(['requestedBy', 'approvedBy'])->latest();

        if (!$seeAll) {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('request_number', 'like', "%{$s}%")
                  ->orWhere('purpose', 'like', "%{$s}%")
                  ->orWhereHas('requestedBy', fn($q) => $q->where('name', 'like', "%{$s}%"));
            });
        }

        $requests = $query->paginate(25)->withQueryString();

        $base = $seeAll ? CashRequest::query() : CashRequest::where('requested_by', $user->id);
        $stats = [
            'pending'            => (clone $base)->where('status', 'pending')->count(),
            'paid_this_month'    => (clone $base)->where('status', 'paid')
                                                  ->whereMonth('paid_at', now()->month)
                                                  ->sum('total_amount'),
            'pending_retirement' => (clone $base)->where('status', 'paid')->count(),
        ];

        return view('admin.accounting.cash-requests.index', compact('requests', 'stats', 'seeAll'));
    }

    public function create()
    {
        $categories = ExpenseCategory::with('account')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        return view('admin.accounting.cash-requests.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'purpose'               => 'required|string|max:500',
            'items'                 => 'required|array|min:1',
            'items.*.description'   => 'required|string|max:500',
            'items.*.estimated_amount' => 'required|numeric|min:0.01',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
            'notes'                 => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = collect($request->items)->sum('estimated_amount');

            $cr = CashRequest::create([
                'requested_by' => auth()->id(),
                'purpose'      => $request->purpose,
                'total_amount' => $totalAmount,
                'status'       => 'draft',
                'notes'        => $request->notes,
            ]);

            foreach ($request->items as $item) {
                $cr->items()->create([
                    'description'         => $item['description'],
                    'estimated_amount'    => $item['estimated_amount'],
                    'expense_category_id' => $item['expense_category_id'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.accounting.cash-requests.index')
                         ->with('success', 'Cash request submitted as draft.');
    }

    public function show(CashRequest $cashRequest)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_cash_requests');
        if (!$seeAll && $cashRequest->requested_by !== $user->id) {
            abort(403, 'You do not have permission to view this cash request.');
        }

        $cashRequest->load([
            'requestedBy', 'approvedBy', 'bankAccount',
            'items.expenseCategory.account',
            'journalEntry.lines.account',
            'retireJournalEntry.lines.account',
        ]);

        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        $categories = ExpenseCategory::with('account')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.accounting.cash-requests.show', compact('cashRequest', 'bankAccounts', 'categories'));
    }

    /** Submit draft for approval */
    public function submit(CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'draft') {
            return back()->with('error', 'Only draft requests can be submitted.');
        }

        $cashRequest->update(['status' => 'pending']);

        AppNotification::notify(
            null,
            'cash_request',
            'Cash Request Pending Approval',
            ($cashRequest->requestedBy?->name ?? 'A user') . ' submitted a cash request for TZS ' . number_format($cashRequest->total_amount, 0) . '.',
            route('admin.accounting.cash-requests.show', $cashRequest),
            'cash'
        );

        return back()->with('success', 'Cash request submitted for approval.');
    }

    /** Approve the request */
    public function approve(CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        if ($cashRequest->requested_by === auth()->id()) {
            return back()->with('error', 'You cannot approve your own cash request.');
        }

        $cashRequest->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        AppNotification::notify(
            $cashRequest->requested_by,
            'cash_request',
            'Your Cash Request Was Approved',
            'TZS ' . number_format($cashRequest->total_amount, 0) . ' is approved for disbursement.',
            route('admin.accounting.cash-requests.show', $cashRequest),
            'cash'
        );

        return back()->with('success', 'Cash request approved.');
    }

    /** Reject the request */
    public function reject(Request $request, CashRequest $cashRequest)
    {
        if (!in_array($cashRequest->status, ['pending', 'draft'])) {
            return back()->with('error', 'Cannot reject this request.');
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $cashRequest->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        AppNotification::notify(
            $cashRequest->requested_by,
            'cash_request',
            'Your Cash Request Was Rejected',
            'Reason: ' . $request->reason,
            route('admin.accounting.cash-requests.show', $cashRequest),
            'cash'
        );

        return back()->with('success', 'Cash request rejected.');
    }

    /** Disburse cash (pay out) */
    public function pay(Request $request, CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'approved') {
            return back()->with('error', 'Only approved requests can be disbursed.');
        }

        $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request, $cashRequest) {
            $cashRequest->update([
                'status'          => 'paid',
                'bank_account_id' => $request->bank_account_id,
                'paid_at'         => now(),
            ]);

            $je = app(JournalEntryService::class)->onCashRequestDisbursed($cashRequest);
            if ($je) {
                $cashRequest->update(['journal_entry_id' => $je->id]);
                BankAccount::where('id', $request->bank_account_id)
                           ->decrement('current_balance', (float) $cashRequest->total_amount);
            }
        });

        return back()->with('success', 'Cash disbursed successfully.');
    }

    /** Retire (reconcile) a paid cash request with actual receipts */
    public function retire(Request $request, CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'paid') {
            return back()->with('error', 'Only paid requests can be retired.');
        }

        $request->validate([
            'items'                       => 'required|array',
            'items.*.id'                  => 'required|exists:cash_request_items,id',
            'items.*.actual_amount'       => 'required|numeric|min:0',
            'items.*.receipt_ref'         => 'nullable|string|max:100',
            'items.*.receipt_file'        => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic|max:5120',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
        ]);

        $jePosted = false;

        DB::transaction(function () use ($request, $cashRequest, &$jePosted) {
            $totalActual = 0;
            foreach ($request->items as $index => $itemData) {
                $item = $cashRequest->items()->find($itemData['id']);
                if (!$item) continue;

                $receiptPath = $item->receipt_path; // keep existing if no new upload
                if ($request->hasFile("items.{$index}.receipt_file")) {
                    $file = $request->file("items.{$index}.receipt_file");
                    $receiptPath = $file->store(
                        "receipts/{$cashRequest->id}",
                        'private'
                    );
                    // Delete old file if replacing
                    if ($item->receipt_path && $item->receipt_path !== $receiptPath) {
                        Storage::disk('private')->delete($item->receipt_path);
                    }
                }

                $item->update([
                    'actual_amount'       => $itemData['actual_amount'],
                    'receipt_ref'         => $itemData['receipt_ref'] ?? null,
                    'receipt_path'        => $receiptPath,
                    'expense_category_id' => $itemData['expense_category_id'] ?? $item->expense_category_id,
                ]);
                $totalActual += (float) $itemData['actual_amount'];
            }

            $cashRequest->update([
                'status'        => 'retired',
                'actual_amount' => $totalActual,
                'retired_at'    => now(),
            ]);

            $je = app(JournalEntryService::class)->onCashRequestRetired($cashRequest);
            if ($je) {
                $cashRequest->update(['retire_journal_entry_id' => $je->id]);
                $jePosted = true;
            }
        });

        return back()->with(
            $jePosted ? 'success' : 'warning',
            $jePosted
                ? 'Cash request retired and posted to ledger.'
                : 'Cash request retired, but journal entry could not be posted — ensure all expense categories have a linked COA account.'
        );
    }

    /** Edit a draft cash request */
    public function edit(CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'draft') {
            return redirect()->route('admin.accounting.cash-requests.show', $cashRequest)
                             ->with('error', 'Only draft requests can be edited.');
        }

        $categories = ExpenseCategory::with('account')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $cashRequest->load('items.expenseCategory');

        return view('admin.accounting.cash-requests.edit', compact('cashRequest', 'categories'));
    }

    /** Update a draft cash request */
    public function update(Request $request, CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'draft') {
            return redirect()->route('admin.accounting.cash-requests.show', $cashRequest)
                             ->with('error', 'Only draft requests can be edited.');
        }

        $request->validate([
            'purpose'                     => 'required|string|max:500',
            'items'                       => 'required|array|min:1',
            'items.*.description'         => 'required|string|max:500',
            'items.*.estimated_amount'    => 'required|numeric|min:0.01',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
            'notes'                       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $cashRequest) {
            $totalAmount = collect($request->items)->sum('estimated_amount');

            $cashRequest->update([
                'purpose'      => $request->purpose,
                'total_amount' => $totalAmount,
                'notes'        => $request->notes,
            ]);

            // Replace all items
            $cashRequest->items()->delete();
            foreach ($request->items as $item) {
                $cashRequest->items()->create([
                    'description'         => $item['description'],
                    'estimated_amount'    => $item['estimated_amount'],
                    'expense_category_id' => $item['expense_category_id'] ?? null,
                ]);
            }
        });

        return redirect()->route('admin.accounting.cash-requests.show', $cashRequest)
                         ->with('success', 'Cash request updated.');
    }

    /** Delete a draft or rejected cash request */
    public function destroy(CashRequest $cashRequest)
    {
        if (!in_array($cashRequest->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected requests can be deleted.');
        }

        $cashRequest->items()->delete();
        $cashRequest->delete();

        return redirect()->route('admin.accounting.cash-requests.index')
                         ->with('success', 'Cash request deleted.');
    }

    /** Serve a privately-stored receipt file */
    public function downloadReceipt(CashRequest $cashRequest, CashRequestItem $item)
    {
        if ($item->cash_request_id !== $cashRequest->id || !$item->receipt_path) {
            abort(404);
        }

        return Storage::disk('private')->download($item->receipt_path);
    }
}
