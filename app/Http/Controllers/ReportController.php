<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReportController extends Controller
{
    /**
     * Show the kWh consumed per apartment over the selected date range.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = isset($filters['from'])
            ? Carbon::parse($filters['from'])->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = isset($filters['to'])
            ? Carbon::parse($filters['to'])->startOfDay()
            : Carbon::now()->endOfMonth()->startOfDay();

        $apartments = Apartment::query()
            ->withSum(
                ['charges as total_kwh' => fn (Builder $query) => $query->whereBetween('charged_at', [$from->toDateString(), $to->toDateString()])],
                'kwh'
            )
            ->orderBy('name')
            ->get();

        return view('report.index', [
            'apartments' => $apartments,
            'from' => $from,
            'to' => $to,
            'totalKwh' => (float) $apartments->sum('total_kwh'),
            'ratePerKwh' => Setting::current()->rate_per_kwh,
        ]);
    }
}
