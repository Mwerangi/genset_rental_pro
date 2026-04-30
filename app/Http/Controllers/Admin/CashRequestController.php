<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\CashRequest;
use App\Models\CashRequestItem;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\UserActivityLog;
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

        $query = CashRequest::with(['requestedBy', 'approvedBy', 'expenseCategory'])
                              ->withCount('items')
                              ->orderByDesc('id');

        if (!$seeAll) {
            $query->where('requested_by', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('expense_category_id', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('request_number', 'like', "%{$s}%")
                  ->orWhere('purpose', 'like', "%{$s}%")
                  ->orWhereHas('requestedBy', fn($q) => $q->where('name', 'like', "%{$s}%"));
            });
        }

        $perPage  = in_array((int) $request->get('per_page', 25), [10, 25, 50, 100]) ? (int) $request->get('per_page', 25) : 25;
        $requests = $query->paginate($perPage)->withQueryString();

        $base = $seeAll ? CashRequest::query() : CashRequest::where('requested_by', $user->id);
        $stats = [
            'draft'           => (clone $base)->where('status', 'draft')->count(),
            'pending'         => (clone $base)->where('status', 'pending')->count(),
            'approved'        => (clone $base)->where('status', 'approved')->count(),
            'paid_this_month' => (clone $base)->where('status', 'paid')
                                              ->whereMonth('paid_at', now()->month)
                                              ->whereYear('paid_at', now()->year)
                                              ->sum('total_amount'),
        ];

        $categories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        return view('admin.accounting.cash-requests.index', compact('requests', 'stats', 'seeAll', 'categories'));
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
            'purpose'                             => 'required|string|max:500',
            'expense_date'                        => 'required|date',
            'items'                               => 'required|array|min:1',
            'items.*.description'                 => 'required|string|max:500',
            'items.*.estimated_amount'            => 'required|numeric|min:0.01',
            'items.*.expense_category_id'         => 'required|exists:expense_categories,id',
            'items.*.zero_vat_override'           => 'nullable|boolean',
            'items.*.vat_justification'           => 'nullable|string|max:500',
            'attachment'                          => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic|max:5120',
            'notes'                               => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, &$cr) {
            // Handle attachment
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('cash-requests', 'private');
            }

            // Compute per-item VAT
            $categories = ExpenseCategory::whereIn('id', collect($request->items)->pluck('expense_category_id'))
                ->get()->keyBy('id');

            $netTotal = 0;
            $vatTotal = 0;
            $items    = [];
            foreach ($request->items as $item) {
                $cat             = $categories->get($item['expense_category_id']);
                $manualZero      = !empty($item['zero_vat_override']);
                $zeroRated       = $manualZero || ($cat ? (bool) $cat->is_zero_rated : false);
                $net             = round((float) $item['estimated_amount'], 2);
                $vat             = $zeroRated ? 0 : round($net * 0.18, 2);
                $total           = $net + $vat;
                $netTotal       += $net;
                $vatTotal       += $vat;
                $items[]         = [
                    'description'         => $item['description'],
                    'estimated_amount'    => $net,
                    'vat_amount'          => $vat,
                    'is_zero_rated'       => $zeroRated,
                    'vat_justification'   => $manualZero ? ($item['vat_justification'] ?? null) : null,
                    'total_amount'        => $total,
                    'expense_category_id' => $item['expense_category_id'],
                ];
            }

            $firstCatId = $items[0]['expense_category_id'] ?? null;
            $isZeroRated = $vatTotal == 0 && $netTotal > 0;

            $cr = CashRequest::create([
                'requested_by'        => auth()->id(),
                'expense_category_id' => $firstCatId,
                'purpose'             => $request->purpose,
                'expense_date'        => $request->expense_date,
                'amount'              => $netTotal,
                'vat_amount'          => $vatTotal,
                'total_amount'        => round($netTotal + $vatTotal, 2),
                'is_zero_rated'       => $isZeroRated,
                'attachment'          => $attachmentPath,
                'status'              => 'draft',
                'notes'               => $request->notes,
            ]);

            foreach ($items as $item) {
                $cr->items()->create($item);
            }
        });

        UserActivityLog::record(
            auth()->id(), 'created',
            'Created cash request' . ($cr ? ' ' . $cr->request_number : ''),
            CashRequest::class, $cr?->id
        );

        return redirect()->route('admin.accounting.cash-requests.show', $cr)
                         ->with('success', 'Cash request saved as draft.');
    }

    public function show(CashRequest $cashRequest)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_cash_requests');
        if (!$seeAll && $cashRequest->requested_by !== $user->id) {
            abort(403, 'You do not have permission to view this cash request.');
        }

        $cashRequest->load([
            'requestedBy', 'approvedBy', 'bankAccount', 'expenseCategory',
            'expense',
            'items.expenseCategory.account',
            'journalEntry.lines.account',
            'retireJournalEntry.lines.account',
        ]);

        $bankAccounts = BankAccount::where('is_active', true)->orderBy('name')->get();
        $categories = ExpenseCategory::with('account')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Unreconciled debit transactions for the payment bank account (for reconciliation modal)
        $unreconciledTxns = collect();
        if ($cashRequest->status === 'paid' && $cashRequest->expense && !$cashRequest->expense->bank_reconciled_at && $cashRequest->bank_account_id) {
            $unreconciledTxns = BankTransaction::whereHas('bankStatement', fn($q) =>
                    $q->where('bank_account_id', $cashRequest->bank_account_id))
                ->where('type', 'debit')
                ->whereNotIn('status', ['reconciled', 'ignored'])
                ->orderByDesc('transaction_date')
                ->limit(100)
                ->get();
        }

        // If already reconciled, find the linked bank transaction
        $reconciledTxn = null;
        if ($cashRequest->status === 'paid' && $cashRequest->expense?->bank_reconciled_at) {
            $reconciledTxn = BankTransaction::where('reconciled_payment_type', Expense::class)
                ->where('reconciled_payment_id', $cashRequest->expense_id)
                ->with('bankStatement')
                ->first();
        }

        return view('admin.accounting.cash-requests.show', compact(
            'cashRequest', 'bankAccounts', 'categories', 'unreconciledTxns', 'reconciledTxn'
        ));
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

        UserActivityLog::record(
            auth()->id(), 'submitted',
            'Submitted cash request ' . $cashRequest->request_number . ' for approval',
            CashRequest::class, $cashRequest->id
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

        UserActivityLog::record(
            auth()->id(), 'approved',
            'Approved cash request ' . $cashRequest->request_number,
            CashRequest::class, $cashRequest->id
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

        UserActivityLog::record(
            auth()->id(), 'rejected',
            'Rejected cash request ' . $cashRequest->request_number . ': ' . $request->reason,
            CashRequest::class, $cashRequest->id
        );

        return back()->with('success', 'Cash request rejected.');
    }

    /** Disburse cash (pay out) — creates JE + Expense record */
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
                BankAccount::where('id', $request->bank_account_id)
                           ->decrement('current_balance', (float) $cashRequest->total_amount);

                // Auto-create the linked Expense record
                $expense = Expense::create([
                    'expense_category_id' => $cashRequest->expense_category_id,
                    'bank_account_id'     => $request->bank_account_id,
                    'description'         => $cashRequest->purpose . ' — ' . $cashRequest->request_number,
                    'amount'              => (float) $cashRequest->amount ?: ((float) $cashRequest->total_amount - (float) $cashRequest->vat_amount),
                    'vat_amount'          => (float) $cashRequest->vat_amount,
                    'total_amount'        => (float) $cashRequest->total_amount,
                    'is_zero_rated'       => (bool) $cashRequest->is_zero_rated,
                    'expense_date'        => $cashRequest->expense_date?->toDateString() ?? now()->toDateString(),
                    'source_type'         => 'cash_request',
                    'source_id'           => $cashRequest->id,
                    'status'              => 'posted',
                    'created_by'          => $cashRequest->requested_by,
                    'attachment'          => $cashRequest->attachment,
                    'journal_entry_id'    => $je->id,
                ]);

                $cashRequest->update([
                    'journal_entry_id' => $je->id,
                    'expense_id'       => $expense->id,
                ]);
            }
        });

        UserActivityLog::record(
            auth()->id(), 'disbursed',
            'Disbursed cash request ' . $cashRequest->request_number . ' (TZS ' . number_format($cashRequest->total_amount, 0) . ')',
            CashRequest::class, $cashRequest->id
        );

        return back()->with('success', 'Cash disbursed and expense recorded successfully.');
    }

    /** Retire (reconcile) a paid cash request with actual receipts */
    /** Show the retirement form for a paid cash request */
    public function retireForm(CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'paid') {
            return redirect()->route('admin.accounting.cash-requests.show', $cashRequest)
                             ->with('error', 'Only paid requests can be retired.');
        }

        $cashRequest->load(['items.expenseCategory', 'requestedBy', 'bankAccount', 'expense']);

        return view('admin.accounting.cash-requests.retire', compact('cashRequest'));
    }

    public function retire(Request $request, CashRequest $cashRequest)
    {
        if ($cashRequest->status !== 'paid') {
            return back()->with('error', 'Only paid requests can be retired.');
        }

        $request->validate([
            'items'                 => 'required|array',
            'items.*.id'            => 'required|exists:cash_request_items,id',
            'items.*.actual_amount' => 'required|numeric|min:0',
            'items.*.receipt_ref'   => 'nullable|string|max:100',
            'items.*.receipt_file'  => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic|max:5120',
            'notes'                 => 'nullable|string|max:1000',
        ]);

        // 'posted' | 'no_variance' | 'failed'
        $jeResult = 'no_variance';

        DB::transaction(function () use ($request, $cashRequest, &$jeResult) {
            $totalActualNet = 0;
            $totalActualVat = 0;

            foreach ($request->items as $index => $itemData) {
                $item = $cashRequest->items()->find($itemData['id']);
                if (!$item) continue;

                $receiptPath = $item->receipt_path;
                if ($request->hasFile("items.{$index}.receipt_file")) {
                    $file = $request->file("items.{$index}.receipt_file");
                    $newPath = $file->store("receipts/{$cashRequest->id}", 'private');
                    if ($item->receipt_path && $item->receipt_path !== $newPath) {
                        Storage::disk('private')->delete($item->receipt_path);
                    }
                    $receiptPath = $newPath;
                }

                $actualNet = (float) $itemData['actual_amount'];
                $actualVat = $item->is_zero_rated ? 0 : round($actualNet * 0.18, 2);

                $item->update([
                    'actual_amount' => $actualNet,
                    'receipt_ref'   => $itemData['receipt_ref'] ?? null,
                    'receipt_path'  => $receiptPath,
                ]);

                $totalActualNet += $actualNet;
                $totalActualVat += $actualVat;
            }

            $totalActual = round($totalActualNet + $totalActualVat, 2);

            $cashRequest->update([
                'status'        => 'retired',
                'actual_amount' => $totalActual,
                'retired_at'    => now(),
                'notes'         => $request->filled('notes') ? $request->notes : $cashRequest->notes,
            ]);

            // Update linked expense with actual amounts
            if ($cashRequest->expense_id) {
                $cashRequest->expense()->update([
                    'amount'       => round($totalActualNet, 2),
                    'vat_amount'   => round($totalActualVat, 2),
                    'total_amount' => $totalActual,
                ]);
            }

            $freshCr = $cashRequest->fresh(['items.expenseCategory.account', 'bankAccount']);
            [$je, $hasVariance] = app(JournalEntryService::class)->onCashRequestRetired($freshCr);
            if ($hasVariance) {
                if ($je) {
                    $cashRequest->update(['retire_journal_entry_id' => $je->id]);
                    $jeResult = 'posted';
                } else {
                    $jeResult = 'failed';
                }
            }
        });

        UserActivityLog::record(
            auth()->id(), 'retired',
            'Retired cash request ' . $cashRequest->request_number,
            CashRequest::class, $cashRequest->id
        );

        $messages = [
            'posted'      => ['success', 'Cash request retired and variance journal entry posted to ledger.'],
            'no_variance' => ['success', 'Cash request retired. Actual amounts matched the estimate — no variance journal entry needed.'],
            'failed'      => ['warning', 'Cash request retired, but the variance journal entry could not be posted. Check that all expense categories have a linked Chart of Accounts entry.'],
        ];
        [$level, $msg] = $messages[$jeResult];

        return redirect()->route('admin.accounting.cash-requests.show', $cashRequest)->with($level, $msg);
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
            'expense_date'                => 'required|date',
            'items'                       => 'required|array|min:1',
            'items.*.description'         => 'required|string|max:500',
            'items.*.estimated_amount'    => 'required|numeric|min:0.01',
            'items.*.expense_category_id' => 'required|exists:expense_categories,id',
            'items.*.zero_vat_override'   => 'nullable|boolean',
            'items.*.vat_justification'   => 'nullable|string|max:500',
            'attachment'                  => 'nullable|file|mimes:jpg,jpeg,png,pdf,heic|max:5120',
            'notes'                       => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $cashRequest) {
            // Handle attachment
            $attachmentPath = $cashRequest->attachment;
            if ($request->hasFile('attachment')) {
                if ($attachmentPath) {
                    Storage::disk('private')->delete($attachmentPath);
                }
                $attachmentPath = $request->file('attachment')->store('cash-requests', 'private');
            }

            $categories = ExpenseCategory::whereIn('id', collect($request->items)->pluck('expense_category_id'))
                ->get()->keyBy('id');

            $netTotal = 0;
            $vatTotal = 0;
            $items    = [];
            foreach ($request->items as $item) {
                $cat             = $categories->get($item['expense_category_id']);
                $manualZero      = !empty($item['zero_vat_override']);
                $zeroRated       = $manualZero || ($cat ? (bool) $cat->is_zero_rated : false);
                $net             = round((float) $item['estimated_amount'], 2);
                $vat             = $zeroRated ? 0 : round($net * 0.18, 2);
                $total           = $net + $vat;
                $netTotal       += $net;
                $vatTotal       += $vat;
                $items[]         = [
                    'description'         => $item['description'],
                    'estimated_amount'    => $net,
                    'vat_amount'          => $vat,
                    'is_zero_rated'       => $zeroRated,
                    'vat_justification'   => $manualZero ? ($item['vat_justification'] ?? null) : null,
                    'total_amount'        => $total,
                    'expense_category_id' => $item['expense_category_id'],
                ];
            }

            $firstCatId  = $items[0]['expense_category_id'] ?? $cashRequest->expense_category_id;
            $isZeroRated = $vatTotal == 0 && $netTotal > 0;

            $cashRequest->update([
                'expense_category_id' => $firstCatId,
                'purpose'             => $request->purpose,
                'expense_date'        => $request->expense_date,
                'amount'              => $netTotal,
                'vat_amount'          => $vatTotal,
                'total_amount'        => round($netTotal + $vatTotal, 2),
                'is_zero_rated'       => $isZeroRated,
                'attachment'          => $attachmentPath,
                'notes'               => $request->notes,
            ]);

            $cashRequest->items()->delete();
            foreach ($items as $item) {
                $cashRequest->items()->create($item);
            }
        });

        UserActivityLog::record(
            auth()->id(), 'updated',
            'Updated cash request ' . $cashRequest->request_number,
            CashRequest::class, $cashRequest->id
        );

        return redirect()->route('admin.accounting.cash-requests.show', $cashRequest)
                         ->with('success', 'Cash request updated.');
    }

    /** Delete a draft or rejected cash request */
    public function destroy(CashRequest $cashRequest)
    {
        if (!in_array($cashRequest->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Only draft or rejected requests can be deleted.');
        }

        $number = $cashRequest->request_number;
        $cashRequestId = $cashRequest->id;
        $cashRequest->items()->delete();
        $cashRequest->delete();

        UserActivityLog::record(
            auth()->id(), 'deleted',
            'Deleted cash request ' . $number,
            CashRequest::class, $cashRequestId
        );

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

    public function downloadAttachment(CashRequest $cashRequest)
    {
        $user   = auth()->user();
        $seeAll = PermissionService::can($user, 'view_all_cash_requests');
        if (!$seeAll && $cashRequest->requested_by !== $user->id) {
            abort(403);
        }

        if (!$cashRequest->attachment) {
            abort(404);
        }

        return Storage::disk('private')->download($cashRequest->attachment);
    }

    public function reconcile(Request $request, CashRequest $cashRequest)
    {
        abort_if($cashRequest->status !== 'paid', 422, 'Only paid requests can be reconciled.');
        abort_unless($cashRequest->expense_id, 422, 'No linked expense found on this request.');

        $request->validate([
            'bank_transaction_id' => 'required|exists:bank_transactions,id',
        ]);

        $tx      = BankTransaction::with('bankStatement')->findOrFail($request->bank_transaction_id);
        $expense = $cashRequest->expense;

        abort_if($tx->bankStatement->bank_account_id !== $cashRequest->bank_account_id, 422,
            'The selected transaction is not from the same bank account used for disbursement.');
        abort_if($tx->status === 'reconciled', 409,
            'This bank transaction is already reconciled to another record.');
        abort_if($expense->bank_reconciled_at, 409,
            'This expense is already bank-reconciled.');

        $alreadyUsed = BankTransaction::where('reconciled_payment_type', Expense::class)
            ->where('reconciled_payment_id', $expense->id)
            ->exists();
        abort_if($alreadyUsed, 409, 'This expense is already linked to another bank transaction.');

        DB::transaction(function () use ($tx, $expense) {
            $tx->update([
                'status'                   => 'reconciled',
                'reconciled_payment_type'  => Expense::class,
                'reconciled_payment_id'    => $expense->id,
                'reconciled_at'            => now(),
                'reconciled_by'            => auth()->id(),
                'journal_entry_id'         => $expense->journal_entry_id ?? $tx->journal_entry_id,
            ]);
            $expense->update([
                'bank_reconciled_at' => now(),
                'bank_reconciled_by' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Reconciled — bank transaction matched to this cash request.');
    }

    public function unreconcile(CashRequest $cashRequest)
    {
        abort_if($cashRequest->status !== 'paid', 422);
        abort_unless($cashRequest->expense_id, 422);

        $expense = $cashRequest->expense;
        abort_unless($expense?->bank_reconciled_at, 422, 'This cash request is not yet reconciled.');

        $tx = BankTransaction::where('reconciled_payment_type', Expense::class)
            ->where('reconciled_payment_id', $expense->id)
            ->first();

        DB::transaction(function () use ($tx, $expense) {
            if ($tx) {
                $tx->update([
                    'status'                   => 'posted',
                    'reconciled_payment_type'  => null,
                    'reconciled_payment_id'    => null,
                    'reconciled_at'            => null,
                    'reconciled_by'            => null,
                ]);
            }
            $expense->update([
                'bank_reconciled_at' => null,
                'bank_reconciled_by' => null,
            ]);
        });

        return back()->with('success', 'Reconciliation removed.');
    }
}
