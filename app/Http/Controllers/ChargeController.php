<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChargeRequest;
use App\Http\Requests\UpdateChargeRequest;
use App\Models\Apartment;
use App\Models\Charge;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChargeController extends Controller
{
    /**
     * Display the most recently recorded charges, optionally for a single apartment.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'apartment_id' => ['nullable', 'integer', Rule::exists('apartments', 'id')],
        ]);

        $selectedApartmentId = isset($filters['apartment_id']) ? (int) $filters['apartment_id'] : null;

        $charges = Charge::query()
            ->with('apartment')
            ->when($selectedApartmentId, fn (Builder $query, int $apartmentId) => $query->where('apartment_id', $apartmentId))
            ->latest('charged_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('charges.index', [
            'charges' => $charges,
            'apartments' => $this->apartments(),
            'selectedApartmentId' => $selectedApartmentId,
        ]);
    }

    /**
     * Show the form for recording a new charge.
     */
    public function create(): View
    {
        return view('charges.create', ['apartments' => $this->apartments()]);
    }

    /**
     * Store a newly recorded charge.
     */
    public function store(StoreChargeRequest $request): RedirectResponse
    {
        Charge::query()->create($request->validated());

        return to_route('charges.index')->with('status', 'Charge recorded.');
    }

    /**
     * Show the form for editing the given charge.
     */
    public function edit(Charge $charge): View
    {
        return view('charges.edit', [
            'charge' => $charge,
            'apartments' => $this->apartments(),
        ]);
    }

    /**
     * Update the given charge.
     */
    public function update(UpdateChargeRequest $request, Charge $charge): RedirectResponse
    {
        $charge->update($request->validated());

        return to_route('charges.index')->with('status', 'Charge updated.');
    }

    /**
     * Delete the given charge.
     */
    public function destroy(Charge $charge): RedirectResponse
    {
        $charge->delete();

        return to_route('charges.index')->with('status', 'Charge deleted.');
    }

    /**
     * The apartments available in the charge form and filter dropdowns.
     *
     * @return Collection<int, Apartment>
     */
    private function apartments(): Collection
    {
        return Apartment::query()->orderBy('name')->get();
    }
}
