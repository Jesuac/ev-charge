<x-layout title="Edit charge">
    <div>
        <h1 class="text-2xl font-semibold">Edit charge</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Recorded {{ $charge->created_at->diffForHumans() }}.</p>
    </div>

    @include('charges.form', [
        'action' => route('charges.update', $charge),
        'method' => 'PATCH',
        'submit' => 'Update charge',
    ])
</x-layout>
