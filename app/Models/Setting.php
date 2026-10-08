<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['rate_per_kwh'])]
class Setting extends Model
{
    /**
     * The single settings row, created by the migration.
     */
    public static function current(): self
    {
        return once(fn () => static::query()->first() ?? new static);
    }

    /**
     * The price per kWh without trailing zeros, or null when no rate is set.
     */
    public function rateLabel(): ?string
    {
        if ($this->rate_per_kwh === null) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $this->rate_per_kwh, 4, '.', ''), '0'), '.');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate_per_kwh' => 'decimal:4',
        ];
    }
}
