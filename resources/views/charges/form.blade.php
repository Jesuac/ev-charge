@php
    $charge ??= null;
    $field = 'mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100';
    $label = 'block text-sm font-medium text-zinc-700 dark:text-zinc-300';
    $error = 'mt-1 text-sm text-red-600 dark:text-red-400';
@endphp

<form method="POST" action="{{ $action }}" class="flex flex-col gap-5 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div>
        <label for="apartment_id" class="{{ $label }}">Apartment</label>
        <select id="apartment_id" name="apartment_id" required class="{{ $field }}">
            <option value="">Select an apartment…</option>
            @foreach ($apartments as $apartment)
                <option value="{{ $apartment->id }}" @selected((int) old('apartment_id', $charge?->apartment_id) === $apartment->id)>
                    {{ $apartment->name }}@if ($apartment->resident_name) — {{ $apartment->resident_name }}@endif
                </option>
            @endforeach
        </select>
        @error('apartment_id')
            <p class="{{ $error }}">{{ $message }}</p>
        @enderror
        @if ($apartments->isEmpty())
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                No apartments yet —
                <a href="{{ route('apartments.create') }}" class="font-medium underline underline-offset-4">add one first</a>.
            </p>
        @endif
    </div>

    <div>
        <label for="charged_at" class="{{ $label }}">Date</label>
        <input id="charged_at" name="charged_at" type="date" required
               max="{{ today()->toDateString() }}"
               value="{{ old('charged_at', $charge?->charged_at?->toDateString() ?? today()->toDateString()) }}"
               class="{{ $field }}">
        @error('charged_at')
            <p class="{{ $error }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="kwh" class="{{ $label }}">kWh charged</label>
        <input id="kwh" name="kwh" type="number" step="0.001" min="0.001" required inputmode="decimal"
               value="{{ old('kwh', $charge?->kwh) }}"
               class="{{ $field }}">
        @error('kwh')
            <p class="{{ $error }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="notes" class="{{ $label }}">Notes <span class="font-normal text-zinc-400">(optional)</span></label>
        <input id="notes" name="notes" type="text" maxlength="255"
               value="{{ old('notes', $charge?->notes) }}"
               class="{{ $field }}">
        @error('notes')
            <p class="{{ $error }}">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            {{ $submit }}
        </button>
        <a href="{{ route('charges.index') }}" class="text-sm font-medium text-zinc-500 underline-offset-4 hover:underline dark:text-zinc-400">Cancel</a>
    </div>
</form>
