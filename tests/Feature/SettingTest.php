<?php

use App\Models\Setting;

test('the migration leaves exactly one settings row', function () {
    expect(Setting::query()->count())->toBe(1);
});

test('the settings page shows the stored price', function () {
    Setting::query()->update(['rate_per_kwh' => 0.169]);

    $this->get(route('settings.edit'))
        ->assertOk()
        ->assertSee('0.1690');
});

test('the settings can be updated', function () {
    $this->patch(route('settings.update'), ['rate_per_kwh' => '0.169'])
        ->assertRedirect(route('settings.edit'))
        ->assertSessionHas('status');

    expect(Setting::query()->first())->rate_per_kwh->toBe('0.1690');

    expect(Setting::query()->count())->toBe(1);
});

test('the rate can be cleared', function () {
    Setting::query()->update(['rate_per_kwh' => 0.169]);

    $this->patch(route('settings.update'), ['rate_per_kwh' => ''])
        ->assertRedirect(route('settings.edit'));

    expect(Setting::query()->first())->rate_per_kwh->toBeNull();
});

test('the settings reject invalid values', function (array $payload, string $invalidField) {
    Setting::query()->update(['rate_per_kwh' => 0.5]);

    $this->patch(route('settings.update'), $payload)->assertInvalid($invalidField);

    expect(Setting::query()->first())->rate_per_kwh->toBe('0.5000');
})->with([
    'negative rate' => [['rate_per_kwh' => '-0.1'], 'rate_per_kwh'],
    'non numeric rate' => [['rate_per_kwh' => 'free'], 'rate_per_kwh'],
]);
