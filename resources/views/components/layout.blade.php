@php
    $navLink = fn (bool $active) => $active
        ? 'rounded-md px-3 py-2 text-sm font-medium bg-zinc-900 text-white dark:bg-white dark:text-zinc-900'
        : 'rounded-md px-3 py-2 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-white';

    $chip = 'flex-1 rounded-lg border border-zinc-200 px-3 py-2 text-center hover:border-zinc-300 hover:bg-zinc-50 sm:flex-none sm:text-left dark:border-zinc-800 dark:hover:border-zinc-700 dark:hover:bg-zinc-800';
    $chipLabel = 'block text-[10px] font-medium uppercase tracking-wider text-zinc-400 dark:text-zinc-500';
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="bg-zinc-50 dark:bg-zinc-950">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name', 'Laravel') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-zinc-950 dark:text-zinc-100">
        <header class="border-b border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mx-auto flex max-w-4xl flex-wrap items-center justify-between gap-4 px-4 py-4">
                <a href="{{ route('charges.index') }}" class="flex items-center gap-2 text-base font-semibold">
                    <span aria-hidden="true">⚡</span>
                    <span>Charger log</span>
                </a>

                <div class="order-last flex w-full flex-wrap items-stretch gap-2 sm:order-none sm:w-auto">
                    <a href="{{ route('settings.edit') }}"
                       title="Meter reading — set the starting number in Settings"
                       class="{{ $chip }}">
                        <span class="{{ $chipLabel }}">Meter</span>
                        <span class="block text-base font-semibold tabular-nums">{{ number_format($meterReading, 2) }} kWh</span>
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">+{{ number_format($chargedKwh, 2) }} since start</span>
                    </a>

                    <a href="{{ route('settings.edit') }}"
                       title="Price per kWh — set it in Settings"
                       class="{{ $chip }}">
                        <span class="{{ $chipLabel }}">Price</span>
                        @if ($rateLabel === null)
                            <span class="block text-base font-semibold text-zinc-400 dark:text-zinc-500">Not set</span>
                        @else
                            <span class="block text-base font-semibold tabular-nums">{{ config('ev.currency') }}{{ $rateLabel }}</span>
                        @endif
                        <span class="block text-xs text-zinc-500 dark:text-zinc-400">per kWh</span>
                    </a>
                </div>

                <nav class="flex items-center gap-1">
                    <a href="{{ route('charges.index') }}" class="{{ $navLink(request()->routeIs('charges.*')) }}">Charges</a>
                    <a href="{{ route('report.index') }}" class="{{ $navLink(request()->routeIs('report.*')) }}">Report</a>
                    <a href="{{ route('apartments.index') }}" class="{{ $navLink(request()->routeIs('apartments.*')) }}">Apartments</a>
                    <a href="{{ route('settings.edit') }}" class="{{ $navLink(request()->routeIs('settings.*')) }}">Settings</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto flex max-w-4xl flex-col gap-6 px-4 py-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </body>
</html>
