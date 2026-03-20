<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuoteRequest;
use Illuminate\Http\Request;

class QuoteRequestController extends Controller
{
    /**
     * Display a listing of quote requests
     */
    public function index(Request $request)
    {
        $query = QuoteRequest::with('reviewedBy')
            ->whereNotIn('status', ['converted', 'rejected'])
            ->latest();

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                  ->orWhere('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by generator type
        if ($request->has('genset_type') && $request->genset_type !== 'all') {
            $query->where('genset_type', $request->genset_type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $quoteRequests = $query->paginate($request->get('per_page', 25));

        // Stats
        $stats = [
            'total_new'      => QuoteRequest::where('status', 'new')->count(),
            'this_week'      => QuoteRequest::whereNotIn('status', ['converted', 'rejected'])
                                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                                    ->count(),
            'pending_quotes' => QuoteRequest::whereIn('status', ['new', 'reviewed'])->count(),
            'conversion_rate' => $this->calculateConversionRate(),
        ];

        return view('admin.quote-requests.index', compact('quoteRequests', 'stats'));
    }

    /**
     * Display the specified quote request
     */
    public function show(QuoteRequest $quoteRequest)
    {
        $quoteRequest->load(['reviewedBy', 'quotations.items', 'quotations.booking']);
        $quotation = $quoteRequest->quotations->first();

        return view('admin.quote-requests.show', compact('quoteRequest', 'quotation'));
    }

    /**
     * Mark quote request as reviewed
     */
    public function markAsReviewed(QuoteRequest $quoteRequest)
    {
        $quoteRequest->markAsReviewed(auth()->id());

        return back()->with('success', 'Quote request marked as reviewed.');
    }

    /**
     * Reject quote request
     */
    public function reject(Request $request, QuoteRequest $quoteRequest)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $quoteRequest->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', 'Quote request rejected.');
    }

    /**
     * Export quote requests to CSV
     */
    public function export(Request $request)
    {
        $quoteRequests = QuoteRequest::latest()->get();

        $filename = 'quote-requests-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($quoteRequests) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Request #', 'Name', 'Email', 'Phone', 'Company', 'Generator Type', 'Start Date', 'Duration (Days)', 'Status', 'Created At']);

            foreach ($quoteRequests as $request) {
                fputcsv($file, [
                    $request->request_number,
                    $request->full_name,
                    $request->email,
                    $request->phone,
                    $request->company_name,
                    $request->genset_type_formatted,
                    $request->rental_start_date->format('Y-m-d'),
                    $request->rental_duration_days,
                    ucfirst($request->status),
                    $request->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate conversion rate
     */
    private function calculateConversionRate(): float
    {
        $total = QuoteRequest::count();
        $converted = QuoteRequest::where('status', 'converted')->count();

        return $total > 0 ? round(($converted / $total) * 100, 1) : 0;
    }
}
