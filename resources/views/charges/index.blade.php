@php
    $field = 'mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100';
    $label = 'block text-sm font-medium text-zinc-700 dark:text-zinc-300';
@endphp

<x-layout title="Charges">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Charges</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Every charging session recorded on the shared charger.</p>
        </div>

        <a href="{{ route('charges.create') }}"
           class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            Add charge
        </a>
    </div>

    <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <form method="GET" action="{{ route('charges.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-40 flex-1">
                <label for="apartment_id" class="{{ $label }}">Apartment</label>
                <select id="apartment_id" name="apartment_id" class="{{ $field }}">
                    <option value="">All apartments</option>
                    @foreach ($apartments as $apartment)
                        <option value="{{ $apartment->id }}" @selected($selectedApartmentId === $apartment->id)>
                            {{ $apartment->name }}@if ($apartment->resident_name) — {{ $apartment->resident_name }}@endif
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Apply
            </button>

            @if ($selectedApartmentId !== null)
                <a href="{{ route('charges.index') }}" class="py-2 text-sm font-medium text-zinc-500 underline-offset-4 hover:underline dark:text-zinc-400">Clear</a>
            @endif
        </form>

        @error('apartment_id')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Date</th>
                        <th scope="col" class="px-4 py-3 font-medium">Apartment</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">kWh</th>
                        <th scope="col" class="px-4 py-3 font-medium">Notes</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($charges as $charge)
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $charge->charged_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-medium">{{ $charge->apartment->name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format((float) $charge->kwh, 2) }}</td>
                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $charge->notes }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('charges.edit', $charge) }}" class="font-medium text-zinc-600 underline-offset-4 hover:underline dark:text-zinc-300">Edit</a>

                                    <form method="POST" action="{{ route('charges.destroy', $charge) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 underline-offset-4 hover:underline dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                @if ($selectedApartmentId !== null)
                                    No charges recorded for this apartment.
                                @else
                                    No charges recorded yet.
                                    <a href="{{ route('charges.create') }}" class="font-medium text-zinc-900 underline underline-offset-4 dark:text-white">Record the first one</a>.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $charges->links() }}
</x-layout>
