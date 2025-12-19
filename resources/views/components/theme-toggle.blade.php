@props([
    'variant' => 'ghost',
    'class' => null,
])

<button
    type="button"
    data-theme-toggle
    onclick="window.toggleTheme && window.toggleTheme()"
    {{ $attributes->merge([
        'class' => 'flex items-center gap-2 rounded-xl border border-zinc-200 px-3 py-2 text-sm text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800',
    ]) }}
    aria-pressed="false"
>
    <span class="relative flex h-7 w-7 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 transition dark:bg-zinc-700 dark:text-yellow-300">
        <flux:icon name="moon" class="h-4 w-4 inline dark:hidden" />
        <flux:icon name="sun" class="hidden h-4 w-4 dark:inline" />
    </span>
    <div class="flex flex-col text-left leading-tight">
        <span class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">{{ __('Mode Tampilan') }}</span>
        <span class="text-sm font-semibold text-zinc-800 dark:text-zinc-50">
            <span class="inline dark:hidden">{{ __('Night') }}</span>
            <span class="hidden dark:inline">{{ __('Day') }}</span>
        </span>
    </div>
</button>
