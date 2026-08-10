<?php

use App\Models\Apartment;
use App\Models\Charge;

test('the index lists recorded charges', function () {
    $apartment = Apartment::factory()->create(['name' => '4B']);
    Charge::factory()->for($apartment)->create(['kwh' => 12.5]);

    $this->get(route('charges.index'))
        ->assertOk()
        ->assertSee('4B')
        ->assertSee('12.50');
});

test('a charge can be recorded', function () {
    $apartment = Apartment::factory()->create();

    $this->post(route('charges.store'), [
        'apartment_id' => $apartment->id,
        'charged_at' => '2026-08-01',
        'kwh' => '18.25',
        'notes' => 'Overnight',
    ])->assertRedirect(route('charges.index'));

    $this->assertDatabaseHas('charges', [
        'apartment_id' => $apartment->id,
        'charged_at' => '2026-08-01',
        'kwh' => 18.25,
        'notes' => 'Overnight',
    ]);
});

test('a charge requires a valid apartment, date and kwh', function (array $overrides, string $invalidField) {
    $apartment = Apartment::factory()->create();

    $payload = array_merge([
        'apartment_id' => $apartment->id,
        'charged_at' => now()->toDateString(),
        'kwh' => '10',
    ], $overrides);

    $this->post(route('charges.store'), $payload)->assertInvalid($invalidField);

    expect(Charge::query()->count())->toBe(0);
})->with([
    'missing kwh' => [['kwh' => null], 'kwh'],
    'zero kwh' => [['kwh' => '0'], 'kwh'],
    'negative kwh' => [['kwh' => '-5'], 'kwh'],
    'non numeric kwh' => [['kwh' => 'twelve'], 'kwh'],
    'unknown apartment' => [['apartment_id' => 999], 'apartment_id'],
    'missing apartment' => [['apartment_id' => null], 'apartment_id'],
    'missing date' => [['charged_at' => null], 'charged_at'],
    'future date' => [['charged_at' => '2099-01-01'], 'charged_at'],
]);

test('a charge can be updated', function () {
    $charge = Charge::factory()->create(['kwh' => 10]);
    $otherApartment = Apartment::factory()->create();

    $this->patch(route('charges.update', $charge), [
        'apartment_id' => $otherApartment->id,
        'charged_at' => '2026-07-15',
        'kwh' => '22.125',
        'notes' => null,
    ])->assertRedirect(route('charges.index'));

    expect($charge->fresh())
        ->apartment_id->toBe($otherApartment->id)
        ->kwh->toBe('22.125')
        ->charged_at->toDateString()->toBe('2026-07-15');
});

test('a charge can be deleted', function () {
    $charge = Charge::factory()->create();

    $this->delete(route('charges.destroy', $charge))
        ->assertRedirect(route('charges.index'));

    $this->assertDatabaseEmpty('charges');
});

test('the create form lists the apartments', function () {
    Apartment::factory()->create(['name' => '1A']);
    Apartment::factory()->create(['name' => '2C']);

    $this->get(route('charges.create'))
        ->assertOk()
        ->assertSee('1A')
        ->assertSee('2C');
});
