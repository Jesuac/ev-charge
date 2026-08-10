@php
    $apartment ??= null;
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
        <label for="name" class="{{ $label }}">Apartment</label>
        <input id="name" name="name" type="text" required maxlength="50" placeholder="4B"
               value="{{ old('name', $apartment?->name) }}"
               class="{{ $field }}">
        @error('name')
            <p class="{{ $error }}">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="resident_name" class="{{ $label }}">Resident <span class="font-normal text-zinc-400">(optional)</span></label>
        <input id="resident_name" name="resident_name" type="text" maxlength="100"
               value="{{ old('resident_name', $apartment?->resident_name) }}"
               class="{{ $field }}">
        @error('resident_name')
            <p class="{{ $error }}">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex items-center gap-3">
        <button type="submit"
                class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            {{ $submit }}
        </button>
        <a href="{{ route('apartments.index') }}" class="text-sm font-medium text-zinc-500 underline-offset-4 hover:underline dark:text-zinc-400">Cancel</a>
    </div>
</form>
