@php
    use Illuminate\Support\Str;
@endphp

<section class="w-full" wire:poll.10s>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Manajemen Pengajuan') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Verifikasi & persetujuan pengajuan legalisir') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @if (session('status'))
        <div class="mb-6">
            <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
        </div>
    @endif

    <div class="mb-8 grid gap-4 md:grid-cols-3">
        @foreach ($this->statusSummary as $status)
            <div class="rounded-2xl border border-zinc-200 bg-gradient-to-br from-white to-zinc-50 p-4 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                <div class="flex items-center justify-between">
                    <flux:heading size="md">{{ $status->name }}</flux:heading>
                    <flux:badge color="zinc">{{ $status->code }}</flux:badge>
                </div>
                <flux:heading size="xl" class="mt-4">{{ $status->pengajuans_count }}</flux:heading>
                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Total pengajuan pada status ini') }}</flux:text>
            </div>
        @endforeach
    </div>

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <flux:heading size="lg">{{ __('Manajemen Status') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500">{{ __('Pilih daftar pengajuan yang ingin ditinjau') }}</flux:text>
            </div>

            <div class="flex gap-2">
                <flux:button
                    variant="{{ $this->viewTab === 'active' ? 'primary' : 'ghost' }}"
                    icon="arrow-path"
                    wire:click="setViewTab('active')"
                >
                    {{ __('Sedang Diproses') }}
                </flux:button>
                <flux:button
                    variant="{{ $this->viewTab === 'history' ? 'primary' : 'ghost' }}"
                    icon="archive-box"
                    wire:click="setViewTab('history')"
                >
                    {{ __('Riwayat Pengajuan') }}
                </flux:button>
            </div>
        </div>

        @if ($this->viewTab === 'active')
            <div class="space-y-3">
                @forelse ($this->activePengajuans as $pengajuan)
                    <div wire:key="active-{{ $pengajuan->id }}">
                        @include('livewire.admin.pengajuan.partials.card', ['pengajuan' => $pengajuan])
                    </div>
                @empty
                    <flux:callout
                        variant="secondary"
                        icon="clipboard-document-check"
                        heading="{{ __('Tidak ada pengajuan aktif') }}"
                        text="{{ __('Semua pengajuan terbaru telah selesai diproses.') }}"
                    />
                @endforelse
            </div>
        @else
            <div class="space-y-3">
                @forelse ($this->historyPengajuans as $pengajuan)
                    <div wire:key="history-{{ $pengajuan->id }}">
                        @include('livewire.admin.pengajuan.partials.card', ['pengajuan' => $pengajuan])
                    </div>
                @empty
                    <flux:callout
                        variant="secondary"
                        icon="archive-box"
                        heading="{{ __('Belum ada riwayat') }}"
                        text="{{ __('Pengajuan yang selesai atau ditolak akan tampil di sini.') }}"
                    />
                @endforelse
            </div>
        @endif
    </div>
</section>
