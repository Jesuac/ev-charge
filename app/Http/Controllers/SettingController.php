<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SettingController extends Controller
{
    /**
     * Show the form for editing the price per kWh.
     */
    public function edit(): View
    {
        return view('settings.edit', ['setting' => Setting::current()]);
    }

    /**
     * Update the settings.
     */
    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        Setting::current()->fill($request->validated())->save();

        return to_route('settings.edit')->with('status', 'Settings saved.');
    }
}
