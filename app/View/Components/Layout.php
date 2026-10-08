<?php

namespace App\View\Components;

use App\Models\MeterReading;
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
        $latestReading = MeterReading::mostRecent();
        $chargedSince = $latestReading?->chargedSince();

        return view('components.layout', [
            'latestReading' => $latestReading,
            'chargedSince' => $chargedSince,
            'meterReading' => $latestReading === null ? null : (float) $latestReading->reading + $chargedSince,
            'rateLabel' => Setting::current()->rateLabel(),
        ]);
    }
}
