<section class="w-full" wire:poll.10s>
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Pengajuan Saya') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Daftar pengajuan legalisir yang pernah kamu buat') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    @php
        $needsPaymentReminder = $this->pengajuans->contains(function ($pengajuan) {
            return ($pengajuan->total_fee ?? 0) > 0 && $pengajuan->payment_status === 'pending';
        });
    @endphp

    @if ($needsPaymentReminder)
        <div class="mb-6">
            <flux:callout
                variant="secondary"
                icon="banknotes"
                heading="{{ __('Pembayaran Diperlukan') }}"
                text="{{ config('app.payment_account_note') }}"
            />
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <flux:button variant="primary" icon="document-plus" :href="route('pengajuan.create')" wire:navigate>
            {{ __('Ajukan Legalisir') }}
        </flux:button>

        <flux:button
            variant="ghost"
            icon="arrow-top-right-on-square"
            :href="route('home')"
        >
            {{ __('Kunjungi Landing Page') }}
        </flux:button>
    </div>

    @if (session('status'))
        <div class="mb-6">
            <flux:callout variant="success" icon="check-circle" heading="{{ session('status') }}" />
        </div>
    @endif

    <div class="space-y-3">
        @forelse ($this->pengajuans as $pengajuan)
            <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <flux:heading size="lg">{{ $pengajuan->kode }}</flux:heading>
                            <flux:badge color="zinc">{{ $pengajuan->status?->name }}</flux:badge>
                        </div>
                        <flux:text class="text-sm">{{ $pengajuan->jenis_dokumen }} • {{ __('Jumlah') }}: {{ $pengajuan->jumlah }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">{{ $pengajuan->created_at?->format('d/m/Y H:i') }}</flux:text>

                        @if ($pengajuan->catatan)
                            <flux:text class="text-sm">{{ $pengajuan->catatan }}</flux:text>
                        @endif

                        @if (($pengajuan->total_fee ?? 0) > 0 && $pengajuan->payment_status === 'pending')
                            <div class="mt-2 rounded-lg border border-dashed border-amber-300 bg-amber-50 p-3 text-sm text-amber-900 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-100">
                                <div class="flex items-center gap-2">
                                    <flux:icon name="banknotes" class="h-4 w-4" />
                                    <span class="font-medium">{{ __('Menunggu Pembayaran') }}</span>
                                </div>
                                <div class="mt-1 font-semibold">
                                    Rp {{ number_format($pengajuan->total_fee ?? 0, 0, ',', '.') }}
                                </div>
                                <p class="mt-1 text-xs leading-relaxed">
                                    {{ config('app.payment_account_note') }}
                                </p>
                            </div>
                        @endif
                    </div>

                        @if (($pengajuan->total_fee ?? 0) > 0 && $pengajuan->payment_status === 'pending')
                            <div class="mt-3 flex flex-wrap gap-2">
                                <flux:modal.trigger name="upload-payment-proof-{{ $pengajuan->id }}">
                                    <flux:button variant="primary" icon="photo" wire:click="showPaymentProofForm({{ $pengajuan->id }})">
                                        {{('Sudah Bayar') }}
                                    </flux:button>
                                </flux:modal.trigger>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <flux:button
                            variant="outline"
                            icon="document-arrow-down"
                            :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_path)"
                            target="_blank"
                        >
                            {{ __('Lihat File') }}
                        </flux:button>
                    </div>
                </div>

                <flux:modal name="upload-payment-proof-{{ $pengajuan->id }}" separator>
                    <form wire:submit.prevent="submitPaymentProof" class="space-y-4">
                        <flux:heading size="lg">{{ __('Unggah Bukti Pembayaran') }}</flux:heading>
                        <flux:text class="text-sm text-zinc-500 dark:text-zinc-300">
                            {{ __('Unggah foto bukti transfer yang jelas. Format yang diterima: JPG, PNG (maks 5 MB).') }}
                        </flux:text>

                        <div class="space-y-3">
                            <flux:input
                                wire:model="paymentProofFile"
                                label="{{ __('Foto Bukti Pembayaran') }}"
                                type="file"
                                accept="image/*"
                                required
                            />

                            <flux:textarea
                                wire:model.defer="paymentProofNote"
                                label="{{ __('Catatan Tambahan (opsional)') }}"
                                rows="3"
                                placeholder="{{ __('Contoh: Sudah transfer via mobile banking') }}"
                            />
                        </div>

                        <div class="flex flex-wrap justify-end gap-2">
                            <flux:modal.close>
                                <flux:button variant="ghost" type="button" wire:click="cancelPaymentProof">
                                    {{ __('Batal') }}
                                </flux:button>
                            </flux:modal.close>

                            <flux:button
                                variant="primary"
                                type="submit"
                                icon="paper-airplane"
                                wire:loading.attr="disabled"
                                wire:target="paymentProofFile, submitPaymentProof"
                            >
                                {{ __('Kirim Bukti Pembayaran') }}
                            </flux:button>
                        </div>
                    </form>
                </flux:modal>
            </div>
        @empty
            <flux:callout
                variant="secondary"
                icon="document"
                heading="{{ __('Belum ada pengajuan') }}"
                text="{{ __('Silakan buat pengajuan legalisir pertama kamu.') }}"
            />
        @endforelse
    </div>
</section>
