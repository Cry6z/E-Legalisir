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

    <div class="space-y-3">
        @forelse ($this->pengajuans as $pengajuan)
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-md dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2">
                                <flux:badge color="zinc">{{ $pengajuan->kode }}</flux:badge>
                                <flux:badge>{{ $pengajuan->status?->name }}</flux:badge>
                            </div>
                            <flux:heading size="md" class="mt-2">{{ $pengajuan->jenis_dokumen }} • {{ __('Jumlah') }}: {{ $pengajuan->jumlah }}</flux:heading>
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-300">
                                {{ $pengajuan->user?->name }} — {{ $pengajuan->user?->email }}
                            </flux:text>
                        </div>
                        <div class="text-right">
                            <flux:text class="text-xs text-zinc-500">{{ __('Diajukan pada') }}</flux:text>
                            <flux:heading size="sm">{{ $pengajuan->created_at?->format('d M Y, H:i') }}</flux:heading>
                        </div>
                    </div>

                    @if ($pengajuan->catatan)
                        <div class="rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-700 dark:bg-zinc-800/60">
                            {{ $pengajuan->catatan }}
                        </div>
                    @endif

                    <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-4 text-sm dark:border-zinc-800 dark:bg-zinc-800/60">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Status pembayaran') }}</flux:text>
                                <flux:badge color="{{ $pengajuan->payment_status === 'paid' ? 'success' : 'warning' }}">
                                    {{ $pengajuan->payment_status === 'paid' ? __('Lunas') : __('Belum Bayar') }}
                                </flux:badge>
                            </div>
                            <flux:heading size="lg">Rp {{ number_format($pengajuan->total_fee ?? 0, 0, ',', '.') }}</flux:heading>
                        </div>

                        @if (in_array(auth()->user()->role, ['staf', 'superadmin'], true))
                            @php($this->ensureFeeState($pengajuan))
                            <div class="mt-4 grid gap-3 md:grid-cols-3">
                                <flux:input
                                    label="{{ __('Biaya Legalisir') }}"
                                    type="number"
                                    min="0"
                                    wire:model.defer="fees.{{ $pengajuan->id }}.legalisir"
                                />
                                <flux:input
                                    label="{{ __('Biaya Fotokopi') }}"
                                    type="number"
                                    min="0"
                                    wire:model.defer="fees.{{ $pengajuan->id }}.photocopy"
                                />
                                <flux:input
                                    label="{{ __('Biaya Pengiriman') }}"
                                    type="number"
                                    min="0"
                                    wire:model.defer="fees.{{ $pengajuan->id }}.shipping"
                                />
                            </div>
                            <div class="mt-3">
                                <flux:textarea
                                    label="{{ __('Catatan Pembayaran (opsional)') }}"
                                    rows="2"
                                    wire:model.defer="fees.{{ $pengajuan->id }}.notes"
                                />
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <flux:button variant="outline" icon="banknotes" wire:click="saveFees({{ $pengajuan->id }})">
                                    {{ __('Simpan Biaya') }}
                                </flux:button>

                                @if ($pengajuan->payment_status !== 'paid')
                                    <flux:button variant="primary" icon="check-badge" wire:click="confirmPayment({{ $pengajuan->id }})">
                                        {{ __('Konfirmasi Pembayaran') }}
                                    </flux:button>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <flux:button
                            variant="outline"
                            icon="document-arrow-down"
                            :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_path)"
                            target="_blank"
                        >
                            {{ __('Lihat Dokumen') }}
                        </flux:button>

                        @if (auth()->user()->role === 'staf')
                            <flux:button variant="primary" icon="check" wire:click="setStatus({{ $pengajuan->id }}, 'DIVERIFIKASI')">
                                {{ __('Verifikasi') }}
                            </flux:button>
                            <flux:button variant="outline" icon="check-badge" wire:click="setStatus({{ $pengajuan->id }}, 'SELESAI')">
                                {{ __('Selesai') }}
                            </flux:button>
                            <flux:button variant="danger" icon="x-mark" wire:click="setStatus({{ $pengajuan->id }}, 'DITOLAK')">
                                {{ __('Tolak') }}
                            </flux:button>
                        @endif

                        @if (auth()->user()->role === 'dekan')
                            <flux:button variant="primary" icon="check-badge" wire:click="setStatus({{ $pengajuan->id }}, 'DISETUJUI')">
                                {{ __('Setujui') }}
                            </flux:button>
                            <flux:button variant="danger" icon="x-mark" wire:click="setStatus({{ $pengajuan->id }}, 'DITOLAK')">
                                {{ __('Tolak') }}
                            </flux:button>
                        @endif

                        @if (auth()->user()->role === 'superadmin')
                            <flux:button variant="outline" icon="arrow-path" wire:click="setStatus({{ $pengajuan->id }}, 'DIAJUKAN')">
                                {{ __('Diajukan') }}
                            </flux:button>
                            <flux:button variant="primary" icon="check" wire:click="setStatus({{ $pengajuan->id }}, 'DIVERIFIKASI')">
                                {{ __('Verifikasi') }}
                            </flux:button>
                            <flux:button variant="primary" icon="check-badge" wire:click="setStatus({{ $pengajuan->id }}, 'DISETUJUI')">
                                {{ __('Setujui') }}
                            </flux:button>
                            <flux:button variant="outline" icon="check-circle" wire:click="setStatus({{ $pengajuan->id }}, 'SELESAI')">
                                {{ __('Selesai') }}
                            </flux:button>
                            <flux:button variant="danger" icon="x-mark" wire:click="setStatus({{ $pengajuan->id }}, 'DITOLAK')">
                                {{ __('Tolak') }}
                            </flux:button>
                        @endif
                    </div>
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
</section>
