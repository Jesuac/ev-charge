<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApartmentRequest;
use App\Http\Requests\UpdateApartmentRequest;
use App\Models\Apartment;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ApartmentController extends Controller
{
    /**
     * Display all apartments sharing the charger.
     */
    public function index(): View
    {
        $apartments = Apartment::query()
            ->withCount('charges')
            ->orderBy('name')
            ->get();

        return view('apartments.index', ['apartments' => $apartments]);
    }

    /**
     * Show the form for adding an apartment.
     */
    public function create(): View
    {
        return view('apartments.create');
    }

    /**
     * Store a newly added apartment.
     */
    public function store(StoreApartmentRequest $request): RedirectResponse
    {
        Apartment::query()->create($request->validated());

        return to_route('apartments.index')->with('status', 'Apartment added.');
    }

    /**
     * Show the form for editing the given apartment.
     */
    public function edit(Apartment $apartment): View
    {
        return view('apartments.edit', ['apartment' => $apartment]);
    }

    /**
     * Update the given apartment.
     */
    public function update(UpdateApartmentRequest $request, Apartment $apartment): RedirectResponse
    {
        $apartment->update($request->validated());

        return to_route('apartments.index')->with('status', 'Apartment updated.');
    }

    /**
     * Delete the given apartment, keeping any apartment that still has charges.
     */
    public function destroy(Apartment $apartment): RedirectResponse
    {
        if ($apartment->charges()->exists()) {
            return to_route('apartments.index')
                ->with('error', "Apartment {$apartment->name} still has recorded charges and cannot be deleted.");
        }

        $apartment->delete();

        return to_route('apartments.index')->with('status', 'Apartment deleted.');
    }
}
