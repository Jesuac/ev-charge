@php
    $badge = 'inline-block rounded-md px-2 py-0.5 text-xs font-medium whitespace-nowrap';
@endphp

<x-layout title="Meter">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Meter</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Real readings from the meter, checked against what was logged between them.</p>
        </div>

        <a href="{{ route('meter-readings.create') }}"
           class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            Add reading
        </a>
    </div>

    @if ($latest !== null)
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
            <h2 class="text-sm font-medium text-zinc-700 dark:text-zinc-300">What the meter should show now</h2>

            <dl class="mt-3 flex flex-col gap-2 text-sm">
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-zinc-500 dark:text-zinc-400">Last reading, {{ $latest->read_at->format('d M Y') }}</dt>
                    <dd class="tabular-nums">{{ number_format((float) $latest->reading, 2) }} kWh</dd>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <dt class="text-zinc-500 dark:text-zinc-400">Logged since then</dt>
                    <dd class="tabular-nums">+{{ number_format($latest->chargedSince(), 2) }} kWh</dd>
                </div>
                <div class="flex items-center justify-between gap-4 border-t border-zinc-200 pt-2 font-semibold dark:border-zinc-800">
                    <dt>Expected reading now</dt>
                    <dd class="tabular-nums">{{ number_format($latest->expectedNow(), 2) }} kWh</dd>
                </div>
            </dl>

            <p class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">Compare this with the meter, then add what it really shows as a new reading to check the period.</p>
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Date</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Reading</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Metered</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Logged</th>
                        <th scope="col" class="px-4 py-3 font-medium">Difference</th>
                        <th scope="col" class="px-4 py-3 font-medium">Notes</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($periods as $period)
                        @php
                            $reading = $period['reading'];
                        @endphp
                        <tr>
                            <td class="px-4 py-3 whitespace-nowrap">{{ $reading->read_at->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-right font-medium tabular-nums">{{ number_format((float) $reading->reading, 2) }}</td>

                            @if ($period['previous'] === null)
                                <td class="px-4 py-3 text-right text-zinc-400 dark:text-zinc-500">—</td>
                                <td class="px-4 py-3 text-right text-zinc-400 dark:text-zinc-500">—</td>
                                <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">First reading</td>
                            @else
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($period['meteredKwh'], 2) }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ number_format($period['loggedKwh'], 2) }}</td>
                                <td class="px-4 py-3">
                                    @if (abs($period['differenceKwh']) < 0.01)
                                        <span class="{{ $badge }} bg-emerald-50 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Matches</span>
                                    @elseif ($period['differenceKwh'] > 0)
                                        <span class="{{ $badge }} bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300">+{{ number_format($period['differenceKwh'], 2) }} kWh not logged</span>
                                    @else
                                        <span class="{{ $badge }} bg-amber-50 text-amber-800 dark:bg-amber-950 dark:text-amber-300">−{{ number_format(abs($period['differenceKwh']), 2) }} kWh over-logged</span>
                                    @endif
                                </td>
                            @endif

                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $reading->notes }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('meter-readings.edit', $reading) }}" class="font-medium text-zinc-600 underline-offset-4 hover:underline dark:text-zinc-300">Edit</a>

                                    <form method="POST" action="{{ route('meter-readings.destroy', $reading) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="font-medium text-red-600 underline-offset-4 hover:underline dark:text-red-400">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                No readings yet.
                                <a href="{{ route('meter-readings.create') }}" class="font-medium text-zinc-900 underline underline-offset-4 dark:text-white">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($periods->isNotEmpty())
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            Metered is how far the meter moved since the previous reading; Logged is the charges recorded after that reading's day, up to and including this one's.
            @if ($uncheckedKwh > 0)
                {{ number_format($uncheckedKwh, 2) }} kWh logged on or before {{ $first->read_at->format('d M Y') }} comes before the first reading and isn't checked.
            @endif
        </p>
    @endif
</x-layout>
