<x-layout title="Edit apartment">
    <div>
        <h1 class="text-2xl font-semibold">Edit apartment</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ $apartment->name }}</p>
    </div>

    @include('apartments.form', [
        'action' => route('apartments.update', $apartment),
        'method' => 'PATCH',
        'submit' => 'Update apartment',
    ])
</x-layout>
