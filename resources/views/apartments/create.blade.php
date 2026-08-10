<x-layout title="Add apartment">
    <div>
        <h1 class="text-2xl font-semibold">Add apartment</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Apartments appear in the dropdown when recording a charge.</p>
    </div>

    @include('apartments.form', [
        'action' => route('apartments.store'),
        'method' => 'POST',
        'submit' => 'Save apartment',
    ])
</x-layout>
