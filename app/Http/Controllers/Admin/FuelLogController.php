<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\FuelLog;
use App\Models\Genset;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;

class FuelLogController extends Controller
{
    public function index(Request $request)
    {
        $query = FuelLog::with('genset', 'booking.client')->latest('fuelled_at');

        if ($request->genset_id) {
            $query->where('genset_id', $request->genset_id);
        }
        if ($request->booking_id) {
            $query->where('booking_id', $request->booking_id);
        }
        if ($request->from) {
            $query->where('fuelled_at', '>=', $request->from);
        }
        if ($request->to) {
            $query->where('fuelled_at', '<=', $request->to . ' 23:59:59');
        }

        $logs         = $query->paginate(25)->withQueryString();
        $gensets      = Genset::orderBy('asset_number')->get(['id', 'asset_number', 'name']);
        $bankAccounts = \App\Models\BankAccount::where('is_active', true)->orderBy('name')->get();

        $stats = [
            'total_litres' => FuelLog::sum('litres'),
            'total_cost'   => FuelLog::sum('total_cost'),
            'this_month'   => FuelLog::whereMonth('fuelled_at', now()->month)
                                ->whereYear('fuelled_at', now()->year)->sum('litres'),
        ];

        return view('admin.inventory.fuel-logs.index', compact('logs', 'gensets', 'stats', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'genset_id'        => 'required|exists:gensets,id',
            'booking_id'       => 'nullable|exists:bookings,id',
            'litres'           => 'required|numeric|min:0.1',
            'cost_per_litre'   => 'required|numeric|min:0',
            'run_hours_before' => 'nullable|numeric|min:0',
            'run_hours_after'  => 'nullable|numeric|min:0|gte:run_hours_before',
            'fuelled_at'       => 'required|date',
            'fuelled_by'       => 'nullable|string|max:255',
            'location'         => 'nullable|string|max:255',
            'notes'            => 'nullable|string',
            'bank_account_id'  => 'required|exists:bank_accounts,id',
        ]);

        $bankAccountId = (int) $data['bank_account_id'];
        unset($data['bank_account_id']);

        $data['created_by'] = auth()->id();

        $fuelLog = FuelLog::create($data);

        // Post fuel expense to ledger
        app(JournalEntryService::class)->onFuelLogged($fuelLog, $bankAccountId);

        return back()->with('success', 'Fuel log recorded — ' . $data['litres'] . ' L @ Tsh ' . number_format($data['cost_per_litre'], 2) . '/L.');
    }

    public function gensetLogs(Genset $genset)
    {
        $logs = $genset->fuelLogs()
            ->with('booking.client', 'createdBy')
            ->latest('fuelled_at')
            ->paginate(20);

        $stats = [
            'total_litres'   => $genset->fuelLogs()->sum('litres'),
            'total_cost'     => $genset->fuelLogs()->sum('total_cost'),
            'avg_rate'       => null,
        ];

        // Avg consumption: only entries with both run hours
        $withHours = $genset->fuelLogs()
            ->whereNotNull('run_hours_before')
            ->whereNotNull('run_hours_after')
            ->get();

        if ($withHours->count() > 0) {
            $totalHours  = $withHours->sum(fn($l) => $l->run_hours_after - $l->run_hours_before);
            $totalLitres = $withHours->sum('litres');
            $stats['avg_rate'] = $totalHours > 0 ? round($totalLitres / $totalHours, 2) : null;
        }

        return view('admin.inventory.fuel-logs.genset', compact('genset', 'logs', 'stats'));
    }
}
