<?php

namespace App\Models;

use App\Casts\DateOnly;
use Database\Factories\MeterReadingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

#[Fillable(['read_at', 'reading', 'notes'])]
class MeterReading extends Model
{
    /** @use HasFactory<MeterReadingFactory> */
    use HasFactory;

    /**
     * The newest reading taken from the physical meter, if any.
     */
    public static function mostRecent(): ?self
    {
        return static::query()->orderByDesc('read_at')->first();
    }

    /**
     * Every reading, oldest first, compared against the charges logged since the reading before it.
     *
     * A reading already includes the charges dated on its own day, so a period covers
     * the charges after the previous reading's date up to and including this one's.
     *
     * @return Collection<int, array{reading: self, previous: ?self, meteredKwh: ?float, loggedKwh: ?float, differenceKwh: ?float}>
     */
    public static function periods(): Collection
    {
        $loggedByDate = Charge::query()
            ->toBase()
            ->selectRaw('charged_at, sum(kwh) as kwh')
            ->groupBy('charged_at')
            ->pluck('kwh', 'charged_at');

        $previous = null;

        return static::query()->orderBy('read_at')->get()->toBase()->map(
            function (self $reading) use (&$previous, $loggedByDate): array {
                $period = [
                    'reading' => $reading,
                    'previous' => $previous,
                    'meteredKwh' => null,
                    'loggedKwh' => null,
                    'differenceKwh' => null,
                ];

                if ($previous !== null) {
                    $after = $previous->read_at->toDateString();
                    $until = $reading->read_at->toDateString();

                    $period['meteredKwh'] = round((float) $reading->reading - (float) $previous->reading, 3);
                    $period['loggedKwh'] = round((float) $loggedByDate
                        ->filter(fn (mixed $kwh, string $date): bool => $date > $after && $date <= $until)
                        ->sum(), 3);
                    $period['differenceKwh'] = round($period['meteredKwh'] - $period['loggedKwh'], 3);
                }

                $previous = $reading;

                return $period;
            }
        );
    }

    /**
     * The kWh logged after the day this reading was taken.
     */
    public function chargedSince(): float
    {
        return (float) Charge::query()
            ->where('charged_at', '>', $this->read_at->toDateString())
            ->sum('kwh');
    }

    /**
     * What the meter should show right now if every charge since this reading was logged.
     */
    public function expectedNow(): float
    {
        return (float) $this->reading + $this->chargedSince();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'read_at' => DateOnly::class,
            'reading' => 'decimal:3',
        ];
    }
}
