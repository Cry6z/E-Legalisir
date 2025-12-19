<section class="w-full" wire:poll.15s>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Dashboard Superadmin') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Ringkasan sistem e-Legalisir') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">{{ __('Alumni') }}</flux:text>
                <flux:badge color="zinc">alumni</flux:badge>
            </div>
            <flux:heading size="xl" class="mt-2">{{ $this->usersByRole['alumni'] ?? 0 }}</flux:heading>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">{{ __('Staf') }}</flux:text>
                <flux:badge color="zinc">staf</flux:badge>
            </div>
            <flux:heading size="xl" class="mt-2">{{ $this->usersByRole['staf'] ?? 0 }}</flux:heading>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">{{ __('Dekan') }}</flux:text>
                <flux:badge color="zinc">dekan</flux:badge>
            </div>
            <flux:heading size="xl" class="mt-2">{{ $this->usersByRole['dekan'] ?? 0 }}</flux:heading>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between">
                <flux:text class="text-sm text-zinc-500">{{ __('Superadmin') }}</flux:text>
                <flux:badge color="zinc">superadmin</flux:badge>
            </div>
            <flux:heading size="xl" class="mt-2">{{ $this->usersByRole['superadmin'] ?? 0 }}</flux:heading>
        </div>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-950">
            <div class="flex items-center justify-between gap-4">
                <flux:heading size="lg">{{ __('Pengajuan per Status') }}</flux:heading>
                <flux:badge color="zinc">{{ __('Live') }}</flux:badge>
            </div>
            <flux:separator variant="subtle" class="my-4" />

            <div class="space-y-3">
                @foreach ($this->statusCounts as $status)
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <flux:text class="text-sm font-medium">{{ $status->name }}</flux:text>
                            <flux:text class="text-xs text-zinc-500">{{ $status->code }}</flux:text>
                        </div>
                        <flux:badge color="zinc">{{ $status->pengajuans_count }}</flux:badge>
                    </div>
                @endforeach
            </div>

            <div class="mt-5">
                <flux:button variant="outline" icon="clipboard-document" :href="route('admin.pengajuan.index')" wire:navigate>
                    {{ __('Buka Manajemen Pengajuan') }}
                </flux:button>
            </div>
        </div>

        <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-950">
            <flux:heading size="lg">{{ __('Pengajuan Terbaru') }}</flux:heading>
            <flux:separator variant="subtle" class="my-4" />

            <div class="space-y-3">
                @forelse ($this->latestPengajuans as $pengajuan)
                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <flux:heading size="sm" class="truncate">{{ $pengajuan->kode }}</flux:heading>
                                <flux:text class="text-xs text-zinc-500 truncate">
                                    {{ $pengajuan->user?->name }} • {{ $pengajuan->jenis_dokumen }}
                                </flux:text>
                            </div>
                            <flux:badge color="zinc">{{ $pengajuan->status?->name }}</flux:badge>
                        </div>
                    </div>
                @empty
                    <flux:callout
                        variant="secondary"
                        icon="clipboard-document"
                        heading="{{ __('Belum ada pengajuan') }}"
                        text="{{ __('Data akan muncul ketika alumni membuat pengajuan.') }}"
                    />
                @endforelse
            </div>
        </div>
    </div>

</section>
