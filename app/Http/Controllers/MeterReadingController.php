<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeterReadingRequest;
use App\Http\Requests\UpdateMeterReadingRequest;
use App\Models\Charge;
use App\Models\MeterReading;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class MeterReadingController extends Controller
{
    /**
     * Display every meter reading next to what was logged between it and the one before.
     */
    public function index(): View
    {
        $periods = MeterReading::periods();
        $first = $periods->first()['reading'] ?? null;

        return view('meter-readings.index', [
            'periods' => $periods->reverse(),
            'latest' => $periods->last()['reading'] ?? null,
            'first' => $first,
            'uncheckedKwh' => $first === null
                ? 0.0
                : (float) Charge::query()->where('charged_at', '<=', $first->read_at->toDateString())->sum('kwh'),
        ]);
    }

    /**
     * Show the form for adding a meter reading.
     */
    public function create(): View
    {
        return view('meter-readings.create', ['latest' => MeterReading::mostRecent()]);
    }

    /**
     * Store a newly taken meter reading.
     */
    public function store(StoreMeterReadingRequest $request): RedirectResponse
    {
        MeterReading::query()->create($request->validated());

        return to_route('meter-readings.index')->with('status', 'Meter reading added.');
    }

    /**
     * Show the form for editing the given meter reading.
     */
    public function edit(MeterReading $meterReading): View
    {
        return view('meter-readings.edit', ['meterReading' => $meterReading]);
    }

    /**
     * Update the given meter reading.
     */
    public function update(UpdateMeterReadingRequest $request, MeterReading $meterReading): RedirectResponse
    {
        $meterReading->update($request->validated());

        return to_route('meter-readings.index')->with('status', 'Meter reading updated.');
    }

    /**
     * Delete the given meter reading.
     */
    public function destroy(MeterReading $meterReading): RedirectResponse
    {
        $meterReading->delete();

        return to_route('meter-readings.index')->with('status', 'Meter reading deleted.');
    }
}
