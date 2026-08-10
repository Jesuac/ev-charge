<x-layout title="Apartments">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">Apartments</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">The neighbors sharing the charger.</p>
        </div>

        <a href="{{ route('apartments.create') }}"
           class="rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            Add apartment
        </a>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-zinc-200 text-xs uppercase tracking-wide text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th scope="col" class="px-4 py-3 font-medium">Apartment</th>
                        <th scope="col" class="px-4 py-3 font-medium">Resident</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium">Charges</th>
                        <th scope="col" class="px-4 py-3 text-right font-medium"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($apartments as $apartment)
                        <tr>
                            <td class="px-4 py-3 font-medium">{{ $apartment->name }}</td>
                            <td class="px-4 py-3 text-zinc-500 dark:text-zinc-400">{{ $apartment->resident_name }}</td>
                            <td class="px-4 py-3 text-right tabular-nums">{{ $apartment->charges_count }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('apartments.edit', $apartment) }}" class="font-medium text-zinc-600 underline-offset-4 hover:underline dark:text-zinc-300">Edit</a>

                                    @if ($apartment->charges_count === 0)
                                        <form method="POST" action="{{ route('apartments.destroy', $apartment) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="font-medium text-red-600 underline-offset-4 hover:underline dark:text-red-400">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-10 text-center text-zinc-500 dark:text-zinc-400">
                                No apartments yet.
                                <a href="{{ route('apartments.create') }}" class="font-medium text-zinc-900 underline underline-offset-4 dark:text-white">Add the first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
