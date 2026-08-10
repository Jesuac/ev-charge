<x-layout title="Record a charge">
    <div>
        <h1 class="text-2xl font-semibold">Record a charge</h1>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Pick your apartment and enter how much you charged.</p>
    </div>

    @include('charges.form', [
        'action' => route('charges.store'),
        'method' => 'POST',
        'submit' => 'Save charge',
    ])
</x-layout>
