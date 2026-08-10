<?php

use App\Models\Apartment;
use App\Models\Charge;
use App\Models\Setting;

test('the meter shows the starting reading when nothing has been logged', function () {
    Setting::query()->update(['meter_start' => 14820.5]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('14,820.50 kWh')
        ->assertSee('+0.00 since start');
});

test('the meter adds every logged charge to the starting reading', function () {
    Setting::query()->update(['meter_start' => 14820.5]);

    $apartment = Apartment::factory()->create();
    Charge::factory()->for($apartment)->create(['kwh' => 24.35]);
    Charge::factory()->for($apartment)->create(['kwh' => 11.5]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('14,856.35 kWh')
        ->assertSee('+35.85 since start');
});

test('the meter is recalculated when a charge is deleted', function () {
    Setting::query()->update(['meter_start' => 1000]);

    $charge = Charge::factory()->create(['kwh' => 20]);
    Charge::factory()->create(['kwh' => 5]);

    $this->get(route('charges.index'))->assertSee('1,025.00 kWh');

    $this->delete(route('charges.destroy', $charge));

    $this->get(route('charges.index'))
        ->assertSee('1,005.00 kWh')
        ->assertSee('+5.00 since start');
});

test('the meter and price are visible on every page', function (string $route) {
    config(['ev.currency' => '$']);
    Setting::query()->update(['meter_start' => 14820.5, 'rate_per_kwh' => 0.169]);
    Charge::factory()->create(['kwh' => 24.35]);

    $this->get(route($route))
        ->assertOk()
        ->assertSee('14,844.85 kWh')
        ->assertSee('$0.169')
        ->assertSee('per kWh');
})->with([
    'charges' => 'charges.index',
    'report' => 'report.index',
    'apartments' => 'apartments.index',
    'settings' => 'settings.edit',
]);

test('the price is shown without trailing zeros', function (string $stored, string $shown) {
    config(['ev.currency' => '$']);
    Setting::query()->update(['rate_per_kwh' => $stored]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee($shown)
        ->assertDontSee('Not set');
})->with([
    'three decimals' => ['0.169', '$0.169'],
    'four decimals' => ['0.1695', '$0.1695'],
    'whole number' => ['2', '$2'],
    'one decimal' => ['0.5', '$0.5'],
]);

test('the price shows a prompt when no rate is set', function () {
    Setting::query()->update(['rate_per_kwh' => null]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('Not set')
        ->assertSee('per kWh');
});
