<?php

namespace App\View\Components;

use App\Models\Setting;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public ?string $title = null) {}

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $setting = Setting::current();

        return view('components.layout', [
            'meterReading' => $setting->meterReading(),
            'chargedKwh' => $setting->chargedKwh(),
            'rateLabel' => $setting->rateLabel(),
        ]);
    }
}
