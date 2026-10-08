<x-layout title="Add meter reading">
    <div>
        <h1 class="text-2xl font-semibold">Add meter reading</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
            @if ($latest === null)
                The number on the meter and the day you read it. Charges logged after that day count from here.
            @else
                The log expects the meter to show {{ number_format($latest->expectedNow(), 2) }} kWh today.
            @endif
        </p>
    </div>

    @include('meter-readings.form', [
        'action' => route('meter-readings.store'),
        'method' => 'POST',
        'submit' => 'Save reading',
    ])
</x-layout>
