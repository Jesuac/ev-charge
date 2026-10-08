<?php

use App\Models\Apartment;
use App\Models\Charge;
use App\Models\MeterReading;
use App\Models\Setting;

test('the meter shows the latest reading when nothing has been logged since', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 14820.5]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('14,820.50 kWh')
        ->assertSee('+0.00 since 01 Aug');
});

test('the meter adds every charge logged after the latest reading', function () {
    MeterReading::factory()->on('2026-07-01')->create(['reading' => 14000]);
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 14820.5]);

    $apartment = Apartment::factory()->create();
    Charge::factory()->for($apartment)->on('2026-07-15')->create(['kwh' => 800]);
    Charge::factory()->for($apartment)->on('2026-08-01')->create(['kwh' => 9]);
    Charge::factory()->for($apartment)->on('2026-08-02')->create(['kwh' => 24.35]);
    Charge::factory()->for($apartment)->on('2026-08-05')->create(['kwh' => 11.5]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('14,856.35 kWh')
        ->assertSee('+35.85 since 01 Aug');
});

test('the meter is recalculated when a charge is deleted', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);

    $charge = Charge::factory()->on('2026-08-02')->create(['kwh' => 20]);
    Charge::factory()->on('2026-08-03')->create(['kwh' => 5]);

    $this->get(route('charges.index'))->assertSee('1,025.00 kWh');

    $this->delete(route('charges.destroy', $charge));

    $this->get(route('charges.index'))
        ->assertSee('1,005.00 kWh')
        ->assertSee('+5.00 since 01 Aug');
});

test('the meter shows a prompt when no reading has been taken', function () {
    Charge::factory()->create(['kwh' => 20]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('No reading');
});

test('the meter and price are visible on every page', function (string $route) {
    config(['ev.currency' => '$']);
    Setting::query()->update(['rate_per_kwh' => 0.169]);
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 14820.5]);
    Charge::factory()->on('2026-08-02')->create(['kwh' => 24.35]);

    $this->get(route($route))
        ->assertOk()
        ->assertSee('14,844.85 kWh')
        ->assertSee('$0.169')
        ->assertSee('per kWh');
})->with([
    'charges' => 'charges.index',
    'report' => 'report.index',
    'meter' => 'meter-readings.index',
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
