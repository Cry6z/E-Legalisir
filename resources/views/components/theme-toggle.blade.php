@props([
    'variant' => 'ghost',
    'class' => null,
])

<button
    type="button"
    data-theme-toggle
    onclick="window.toggleTheme && window.toggleTheme()"
    {{ $attributes->merge([
        'class' => 'flex items-center justify-center rounded-full border border-zinc-200 p-2 text-zinc-600 transition hover:bg-zinc-100 dark:border-zinc-700 dark:text-zinc-100 dark:hover:bg-zinc-800',
    ]) }}
    aria-pressed="false"
    aria-label="{{ __('Toggle theme') }}"
>
    <span class="relative flex h-9 w-9 items-center justify-center rounded-full bg-zinc-100 text-zinc-600 transition dark:bg-zinc-700 dark:text-yellow-300">
        <flux:icon name="moon" class="h-4 w-4 inline dark:hidden" />
        <flux:icon name="sun" class="hidden h-4 w-4 dark:inline" />
    </span>
</button>
