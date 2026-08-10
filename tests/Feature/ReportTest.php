<?php

use App\Models\Apartment;
use App\Models\Charge;
use App\Models\Setting;
use Illuminate\Testing\TestResponse;

/**
 * Map the report's apartments onto "name => total kWh" for easier assertions.
 *
 * @return array<string, float>
 */
function totalsFrom(TestResponse $response): array
{
    return $response->viewData('apartments')
        ->mapWithKeys(fn (Apartment $apartment) => [$apartment->name => (float) $apartment->total_kwh])
        ->all();
}

test('it totals kwh per apartment within the selected range', function () {
    $first = Apartment::factory()->create(['name' => '1A']);
    $second = Apartment::factory()->create(['name' => '2B']);

    Charge::factory()->for($first)->on('2026-03-10')->create(['kwh' => 10.5]);
    Charge::factory()->for($first)->on('2026-03-20')->create(['kwh' => 4.5]);
    Charge::factory()->for($second)->on('2026-03-15')->create(['kwh' => 7.25]);

    $response = $this->get(route('report.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertOk();

    expect(totalsFrom($response))->toBe(['1A' => 15.0, '2B' => 7.25]);
    expect($response->viewData('totalKwh'))->toBe(22.25);
});

test('it excludes charges outside the selected range', function () {
    $apartment = Apartment::factory()->create(['name' => '1A']);

    Charge::factory()->for($apartment)->on('2026-02-28')->create(['kwh' => 99]);
    Charge::factory()->for($apartment)->on('2026-03-15')->create(['kwh' => 5]);
    Charge::factory()->for($apartment)->on('2026-04-01')->create(['kwh' => 99]);

    $response = $this->get(route('report.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertOk();

    expect(totalsFrom($response))->toBe(['1A' => 5.0]);
});

test('it includes charges recorded on the range boundaries', function () {
    $apartment = Apartment::factory()->create(['name' => '1A']);

    Charge::factory()->for($apartment)->on('2026-03-01')->create(['kwh' => 2]);
    Charge::factory()->for($apartment)->on('2026-03-31')->create(['kwh' => 3]);

    $response = $this->get(route('report.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertOk();

    expect(totalsFrom($response))->toBe(['1A' => 5.0]);
});

test('an apartment with no charges in the range shows zero', function () {
    Apartment::factory()->create(['name' => '1A']);
    Apartment::factory()->create(['name' => '2B']);

    $response = $this->get(route('report.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertOk();

    expect(totalsFrom($response))->toBe(['1A' => 0.0, '2B' => 0.0]);
});

test('it defaults to the current month', function () {
    $this->freezeTime();

    $apartment = Apartment::factory()->create(['name' => '1A']);

    Charge::factory()->for($apartment)->on(now()->startOfMonth()->toDateString())->create(['kwh' => 8]);
    Charge::factory()->for($apartment)->on(now()->subMonthNoOverflow()->startOfMonth()->toDateString())->create(['kwh' => 40]);

    $response = $this->get(route('report.index'))->assertOk();

    expect($response->viewData('from')->toDateString())->toBe(now()->startOfMonth()->toDateString());
    expect($response->viewData('to')->toDateString())->toBe(now()->endOfMonth()->toDateString());
    expect(totalsFrom($response))->toBe(['1A' => 8.0]);
});

test('it rejects an end date before the start date', function () {
    $this->get(route('report.index', ['from' => '2026-03-31', 'to' => '2026-03-01']))
        ->assertInvalid('to');
});

test('it shows a cost column when a rate is configured', function () {
    config(['ev.currency' => '$']);
    Setting::query()->update(['rate_per_kwh' => 0.2]);

    $apartment = Apartment::factory()->create(['name' => '1A']);
    Charge::factory()->for($apartment)->on('2026-03-10')->create(['kwh' => 10]);

    $this->get(route('report.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertOk()
        ->assertSee('$2.00');
});

test('it shows kwh only when no rate is configured', function () {
    Setting::query()->update(['rate_per_kwh' => null]);

    $apartment = Apartment::factory()->create(['name' => '1A']);
    Charge::factory()->for($apartment)->on('2026-03-10')->create(['kwh' => 10]);

    $this->get(route('report.index', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertOk()
        ->assertDontSee('Cost');
});
