@php
    $field = 'mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:ring-1 focus:ring-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100';
    $label = 'block text-sm font-medium text-zinc-700 dark:text-zinc-300';

    $ranges = [
        'This month' => [now()->startOfMonth(), now()->endOfMonth()],
        'Last month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
        'This year' => [now()->startOfYear(), now()->endOfYear()],
    ];

    $rate = $ratePerKwh === null ? null : (float) $ratePerKwh;
    $currency = config('ev.currency');
@endphp

<x-layout title="Report">
    <div>
        <h1 class="text-2xl font-semibold">Consumption by apartment</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
        </p>
    </div>

    <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <form method="GET" action="{{ route('report.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-40 flex-1">
                <label for="from" class="{{ $label }}">From</label>
                <input id="from" name="from" type="date" value="{{ $from->toDateString() }}" class="{{ $field }}">
            </div>

            <div class="min-w-40 flex-1">
                <label for="to" class="{{ $label }}">To</label>
                <input id="to" name="to" type="date" value="{{ $to->toDateString() }}" class="{{ $field }}">
            </div>

            <button type="submit"
                    class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                Apply
            </button>
        </form>

        @error('to')
            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror

        <div class="flex flex-wrap items-center gap-2 text-sm">
            <span class="text-zinc-500 dark:text-zinc-400">Quick ranges:</span>
            @foreach ($ranges as $name => [$start, $end])
                <a href="{{ route('report.index', ['from' => $start->toDateString(), 'to' => $end->toDateString()]) }}"
                   class="rounded-full border border-zinc-200 px-3 py-1 font-medium text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800">
                    {{ $name }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Apartment</th>
                        <th scope="col" class="px-4 py-3 font-medium">Resident</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">kWh</th>
                        @if ($rate !== null)
                            <th scope="col" class="px-4 py-3 text-right font-medium">Cost</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($apartments as $apartment)
                        @php $kwh = (float) $apartment->total_kwh; @endphp
                        <tr @class(['text-zinc-400 dark:text-zinc-500' => $kwh === 0.0])>
                            <td class="px-4 py-3 font-medium">{{ $apartment->name }}</td>
                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $apartment->resident_name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($kwh, 2) }}</td>
                            @if ($rate !== null)
                                <td class="px-4 py-3 text-right tabular-nums">{{ $currency }}{{ number_format($kwh * $rate, 2) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $rate !== null ? 4 : 3 }}" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                No apartments yet.
                                <a href="{{ route('apartments.create') }}" class="font-medium text-zinc-900 underline underline-offset-4 dark:text-white">Add one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($apartments->isNotEmpty())
                    <tfoot class="border-t border-zinc-200 font-semibold dark:border-zinc-800">
                        <tr>
                            <td class="px-4 py-3" colspan="2">Total</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ number_format($totalKwh, 2) }}</td>
                            @if ($rate !== null)
                                <td class="px-4 py-3 text-right tabular-nums">{{ $currency }}{{ number_format($totalKwh * $rate, 2) }}</td>
                            @endif
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-layout>
