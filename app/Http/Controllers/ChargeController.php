<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChargeRequest;
use App\Http\Requests\UpdateChargeRequest;
use App\Models\Apartment;
use App\Models\Charge;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

class ChargeController extends Controller
{
    /**
     * Display the most recently recorded charges.
     */
    public function index(): View
    {
        $charges = Charge::query()
            ->with('apartment')
            ->latest('charged_at')
            ->latest('id')
            ->paginate(25);

        return view('charges.index', ['charges' => $charges]);
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
     * The apartments available in the charge form's dropdown.
     *
     * @return Collection<int, Apartment>
     */
    private function apartments(): Collection
    {
        return Apartment::query()->orderBy('name')->get();
    }
}
