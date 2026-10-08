<x-layout title="Edit meter reading">
    <div>
        <h1 class="text-2xl font-semibold">Edit meter reading</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $meterReading->read_at->format('d M Y') }}</p>
    </div>

    @include('meter-readings.form', [
        'action' => route('meter-readings.update', $meterReading),
        'method' => 'PATCH',
        'submit' => 'Update reading',
    ])
</x-layout>
