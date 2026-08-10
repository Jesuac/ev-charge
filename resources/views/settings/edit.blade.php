@php
    $field = 'mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100';
    $label = 'block text-sm font-medium text-zinc-700 dark:text-zinc-300';
    $error = 'mt-1 text-sm text-red-600 dark:text-red-400';
    $hint = 'mt-1 text-sm text-zinc-500 dark:text-zinc-400';
@endphp

<x-layout title="Settings">
    <div>
        <h1 class="text-2xl font-semibold">Settings</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">The meter's starting point and, optionally, what a kWh costs.</p>
    </div>

    <form method="POST" action="{{ route('settings.update') }}" class="flex flex-col gap-5 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        @csrf
        @method('PATCH')

        <div>
            <label for="meter_start" class="{{ $label }}">Starting meter reading</label>
            <input id="meter_start" name="meter_start" type="number" step="0.001" min="0" required inputmode="decimal"
                   value="{{ old('meter_start', $setting->meter_start) }}"
                   class="{{ $field }}">
            @error('meter_start')
                <p class="{{ $error }}">{{ $message }}</p>
            @enderror
            <p class="{{ $hint }}">What the meter showed when you started logging charges here.</p>
        </div>

        <div>
            <label for="rate_per_kwh" class="{{ $label }}">
                Price per kWh <span class="font-normal text-zinc-400">(optional)</span>
            </label>
            <input id="rate_per_kwh" name="rate_per_kwh" type="number" step="0.0001" min="0" inputmode="decimal"
                   value="{{ old('rate_per_kwh', $setting->rate_per_kwh) }}"
                   class="{{ $field }}">
            @error('rate_per_kwh')
                <p class="{{ $error }}">{{ $message }}</p>
            @enderror
            <p class="{{ $hint }}">Leave empty to keep the report showing kWh only. Setting it re-prices every charge, past ones included.</p>
        </div>

        <div>
            <button type="submit"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Save settings
            </button>
        </div>
    </form>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h2 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">How the meter reading is worked out</h2>

        <dl class="mt-3 flex flex-col gap-2 text-sm">
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500 dark:text-zinc-400">Starting reading</dt>
                <dd class="tabular-nums">{{ number_format((float) $setting->meter_start, 2) }} kWh</dd>
            </div>
            <div class="flex items-center justify-between gap-4">
                <dt class="text-zinc-500 dark:text-zinc-400">Logged since then</dt>
                <dd class="tabular-nums">+{{ number_format($chargedKwh, 2) }} kWh</dd>
            </div>
            <div class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-2 font-semibold dark:border-zinc-800">
                <dt>Meter reading now</dt>
                <dd class="tabular-nums">{{ number_format($meterReading, 2) }} kWh</dd>
            </div>
        </dl>

        <p class="{{ $hint }}">It always recalculates from the charge log, so editing or deleting a charge corrects it automatically.</p>
    </div>
</x-layout>
