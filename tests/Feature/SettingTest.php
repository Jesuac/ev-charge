<?php

use App\Models\Setting;

test('the migration leaves exactly one settings row', function () {
    expect(Setting::query()->count())->toBe(1);
});

test('the settings page shows the stored values', function () {
    Setting::query()->update(['meter_start' => 14820.5, 'rate_per_kwh' => 0.169]);

    $this->get(route('settings.edit'))
        ->assertOk()
        ->assertSee('14820.500')
        ->assertSee('0.1690');
});

test('the settings can be updated', function () {
    $this->patch(route('settings.update'), [
        'meter_start' => '14820.5',
        'rate_per_kwh' => '0.169',
    ])
        ->assertRedirect(route('settings.edit'))
        ->assertSessionHas('status');

    expect(Setting::query()->first())
        ->meter_start->toBe('14820.500')
        ->rate_per_kwh->toBe('0.1690');

    expect(Setting::query()->count())->toBe(1);
});

test('the rate can be cleared while the meter start is kept', function () {
    Setting::query()->update(['meter_start' => 14820.5, 'rate_per_kwh' => 0.169]);

    $this->patch(route('settings.update'), [
        'meter_start' => '14820.5',
        'rate_per_kwh' => '',
    ])->assertRedirect(route('settings.edit'));

    expect(Setting::query()->first())
        ->rate_per_kwh->toBeNull()
        ->meter_start->toBe('14820.500');
});

test('the settings reject invalid values', function (array $payload, string $invalidField) {
    Setting::query()->update(['meter_start' => 100, 'rate_per_kwh' => 0.5]);

    $this->patch(route('settings.update'), $payload)->assertInvalid($invalidField);

    expect(Setting::query()->first())
        ->meter_start->toBe('100.000')
        ->rate_per_kwh->toBe('0.5000');
})->with([
    'missing meter start' => [['meter_start' => null], 'meter_start'],
    'negative meter start' => [['meter_start' => '-1'], 'meter_start'],
    'non numeric meter start' => [['meter_start' => 'lots'], 'meter_start'],
    'negative rate' => [['meter_start' => '100', 'rate_per_kwh' => '-0.1'], 'rate_per_kwh'],
    'non numeric rate' => [['meter_start' => '100', 'rate_per_kwh' => 'free'], 'rate_per_kwh'],
]);
