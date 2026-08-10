<?php

use App\Models\Apartment;
use App\Models\Charge;

test('the index lists apartments with their charge counts', function () {
    $apartment = Apartment::factory()->create(['name' => '3A', 'resident_name' => 'Ana']);
    Charge::factory()->count(2)->for($apartment)->create();

    $this->get(route('apartments.index'))
        ->assertOk()
        ->assertSee('3A')
        ->assertSee('Ana');
});

test('an apartment can be added', function () {
    $this->post(route('apartments.store'), [
        'name' => '4B',
        'resident_name' => 'Marta',
    ])->assertRedirect(route('apartments.index'));

    $this->assertDatabaseHas('apartments', ['name' => '4B', 'resident_name' => 'Marta']);
});

test('an apartment name is required and must be unique', function () {
    Apartment::factory()->create(['name' => '4B']);

    $this->post(route('apartments.store'), ['name' => ''])->assertInvalid('name');
    $this->post(route('apartments.store'), ['name' => '4B'])->assertInvalid('name');

    expect(Apartment::query()->count())->toBe(1);
});

test('an apartment keeps its own name when updated', function () {
    $apartment = Apartment::factory()->create(['name' => '4B']);

    $this->patch(route('apartments.update', $apartment), [
        'name' => '4B',
        'resident_name' => 'Marta',
    ])->assertRedirect(route('apartments.index'));

    expect($apartment->fresh()->resident_name)->toBe('Marta');
});

test('an apartment without charges can be deleted', function () {
    $apartment = Apartment::factory()->create();

    $this->delete(route('apartments.destroy', $apartment))
        ->assertRedirect(route('apartments.index'));

    $this->assertDatabaseEmpty('apartments');
});

test('an apartment with charges cannot be deleted', function () {
    $apartment = Apartment::factory()->create();
    Charge::factory()->for($apartment)->create();

    $this->delete(route('apartments.destroy', $apartment))
        ->assertRedirect(route('apartments.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('apartments', ['id' => $apartment->id]);
});
