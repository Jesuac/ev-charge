<?php

use App\Models\Charge;
use App\Models\MeterReading;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('the index lists meter readings', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 14820.5, 'notes' => 'Photo taken']);

    $this->get(route('meter-readings.index'))
        ->assertOk()
        ->assertSee('01 Aug 2026')
        ->assertSee('14,820.50')
        ->assertSee('Photo taken')
        ->assertSee('First reading');
});

test('the index prompts for a first reading when there are none', function () {
    $this->get(route('meter-readings.index'))
        ->assertOk()
        ->assertSee('No readings yet.');
});

test('a meter reading can be added', function () {
    $this->post(route('meter-readings.store'), [
        'read_at' => '2026-08-01',
        'reading' => '14820.5',
        'notes' => 'Start',
    ])
        ->assertRedirect(route('meter-readings.index'))
        ->assertSessionHas('status');

    $this->assertDatabaseHas('meter_readings', [
        'read_at' => '2026-08-01',
        'reading' => 14820.5,
        'notes' => 'Start',
    ]);
});

test('a meter reading requires a valid date and reading', function (array $overrides, string $invalidField) {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);

    $payload = array_merge(['read_at' => '2026-08-15', 'reading' => '1100'], $overrides);

    $this->post(route('meter-readings.store'), $payload)->assertInvalid($invalidField);

    expect(MeterReading::query()->count())->toBe(1);
})->with([
    'missing date' => [['read_at' => null], 'read_at'],
    'malformed date' => [['read_at' => 'yesterday'], 'read_at'],
    'future date' => [['read_at' => '2999-01-01'], 'read_at'],
    'date already read' => [['read_at' => '2026-08-01'], 'read_at'],
    'missing reading' => [['reading' => null], 'reading'],
    'negative reading' => [['reading' => '-1'], 'reading'],
    'non numeric reading' => [['reading' => 'lots'], 'reading'],
    'notes too long' => [['notes' => str_repeat('a', 256)], 'notes'],
]);

test('a meter reading cannot go backwards between its neighbours', function (string $date, string $reading, bool $valid) {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1200]);

    $response = $this->post(route('meter-readings.store'), ['read_at' => $date, 'reading' => $reading]);

    $valid ? $response->assertValid() : $response->assertInvalid('reading');
})->with([
    'lower than the earlier reading' => ['2026-08-15', '999.999', false],
    'higher than the later reading' => ['2026-08-15', '1200.001', false],
    'between both' => ['2026-08-15', '1100', true],
    'equal to the earlier reading' => ['2026-08-15', '1000', true],
    'lower than the last reading, after it' => ['2026-09-05', '1199', false],
    'higher than the first reading, before it' => ['2026-07-20', '1001', false],
]);

test('a meter reading keeps its own date and place when updated', function () {
    $reading = MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1200]);

    $this->patch(route('meter-readings.update', $reading), [
        'read_at' => '2026-08-01',
        'reading' => '1050',
    ])->assertRedirect(route('meter-readings.index'));

    expect($reading->fresh()->reading)->toBe('1050.000');

    $this->patch(route('meter-readings.update', $reading), [
        'read_at' => '2026-08-01',
        'reading' => '1300',
    ])->assertInvalid('reading');
});

test('a meter reading can be deleted', function () {
    $reading = MeterReading::factory()->create();

    $this->delete(route('meter-readings.destroy', $reading))
        ->assertRedirect(route('meter-readings.index'));

    $this->assertDatabaseEmpty('meter_readings');
});

test('a period compares the meter movement with the charges logged in it', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1050]);

    Charge::factory()->on('2026-08-10')->create(['kwh' => 20]);
    Charge::factory()->on('2026-08-20')->create(['kwh' => 30]);

    expect(MeterReading::periods()->last())
        ->meteredKwh->toBe(50.0)
        ->loggedKwh->toBe(50.0)
        ->differenceKwh->toBe(0.0);

    $this->get(route('meter-readings.index'))
        ->assertOk()
        ->assertSee('Matches')
        ->assertDontSee('not logged')
        ->assertDontSee('over-logged');
});

test('charges on the day of a reading belong to the period that reading closes', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1050]);
    MeterReading::factory()->on('2026-09-30')->create(['reading' => 1060]);

    Charge::factory()->on('2026-08-01')->create(['kwh' => 7]);
    Charge::factory()->on('2026-08-02')->create(['kwh' => 20]);
    Charge::factory()->on('2026-08-31')->create(['kwh' => 30]);
    Charge::factory()->on('2026-09-01')->create(['kwh' => 10]);

    $periods = MeterReading::periods();

    expect($periods[0])->previous->toBeNull()->loggedKwh->toBeNull();
    expect($periods[1])->loggedKwh->toBe(50.0)->differenceKwh->toBe(0.0);
    expect($periods[2])->loggedKwh->toBe(10.0)->differenceKwh->toBe(0.0);

    $this->get(route('meter-readings.index'))
        ->assertSee('7.00 kWh logged on or before 01 Aug 2026');
});

test('a period flags usage the log does not explain', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1053.2]);

    Charge::factory()->on('2026-08-10')->create(['kwh' => 50]);

    $this->get(route('meter-readings.index'))
        ->assertOk()
        ->assertSee('+3.20 kWh not logged')
        ->assertDontSee('Matches');
});

test('a period flags a log that exceeds what the meter moved', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1048.5]);

    Charge::factory()->on('2026-08-10')->create(['kwh' => 50]);

    $this->get(route('meter-readings.index'))
        ->assertOk()
        ->assertSee('−1.50 kWh over-logged')
        ->assertDontSee('Matches');
});

test('the index shows what the meter should read now', function () {
    MeterReading::factory()->on('2026-08-01')->create(['reading' => 1000]);
    MeterReading::factory()->on('2026-08-31')->create(['reading' => 1050]);

    Charge::factory()->on('2026-08-10')->create(['kwh' => 50]);
    Charge::factory()->on('2026-09-02')->create(['kwh' => 12.5]);

    $this->get(route('meter-readings.index'))
        ->assertOk()
        ->assertSee('Last reading, 31 Aug 2026')
        ->assertSee('+12.50 kWh')
        ->assertSee('1,062.50 kWh');
});

test('the starting reading from settings becomes the first meter reading', function () {
    $migration = require database_path('migrations/2026_10_08_192228_create_meter_readings_table.php');

    $migration->down();

    DB::table('settings')->update(['meter_start' => 14820.5]);
    Charge::factory()->on('2026-08-10')->create();
    Charge::factory()->on('2026-08-03')->create();

    $migration->up();

    expect(MeterReading::query()->sole())
        ->read_at->toDateString()->toBe('2026-08-02')
        ->reading->toBe('14820.500');

    expect(Schema::hasColumn('settings', 'meter_start'))->toBeFalse();
});
