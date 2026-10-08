<?php

namespace App\Http\Requests;

use App\Models\MeterReading;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreMeterReadingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'read_at' => [
                'required',
                'date_format:Y-m-d',
                'before_or_equal:today',
                Rule::unique('meter_readings', 'read_at')->ignore($this->route('meter_reading')),
            ],
            'reading' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * A meter only counts up, so a reading has to sit between its neighbours by date.
     *
     * @return array<int, Closure(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $date = $this->string('read_at')->toString();
                $reading = $this->float('reading');

                $others = MeterReading::query()
                    ->when($this->route('meter_reading'), fn ($query, MeterReading $current) => $query->whereKeyNot($current->getKey()));

                $earlier = (clone $others)->where('read_at', '<', $date)->orderByDesc('read_at')->first();
                $later = (clone $others)->where('read_at', '>', $date)->orderBy('read_at')->first();

                if ($earlier !== null && $reading < (float) $earlier->reading) {
                    $validator->errors()->add('reading', sprintf(
                        'The meter reading cannot be lower than the %s kWh read on %s.',
                        number_format((float) $earlier->reading, 2),
                        $earlier->read_at->format('d M Y'),
                    ));
                }

                if ($later !== null && $reading > (float) $later->reading) {
                    $validator->errors()->add('reading', sprintf(
                        'The meter reading cannot be higher than the %s kWh read on %s.',
                        number_format((float) $later->reading, 2),
                        $later->read_at->format('d M Y'),
                    ));
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'read_at' => 'date',
            'reading' => 'meter reading',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'read_at.unique' => 'There is already a meter reading for that date.',
        ];
    }
}
