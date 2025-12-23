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

    <div class="space-y-4">
        @forelse ($this->pengajuans as $pengajuan)
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="space-y-2">
                        @php
                            $statusColor = match ($pengajuan->status?->code) {
                                'DISETUJUI' => 'emerald',
                                'DITOLAK' => 'rose',
                                default => 'zinc',
                            };
                        @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            <flux:heading size="lg">{{ $pengajuan->kode }}</flux:heading>
                            <flux:badge color="{{ $statusColor }}">{{ $pengajuan->status?->name }}</flux:badge>
                        </div>
                        <flux:text class="text-sm">{{ $pengajuan->jenis_dokumen }} • {{ __('Jumlah') }}: {{ $pengajuan->jumlah }}</flux:text>
                        <flux:text class="text-xs text-zinc-500">{{ $pengajuan->created_at?->format('d/m/Y H:i') }}</flux:text>

                        @if ($pengajuan->catatan)
                            <flux:text class="text-sm">{{ $pengajuan->catatan }}</flux:text>
                        @endif

                        @if (($pengajuan->total_fee ?? 0) > 0)
                            <div
                                @class([
                                    'mt-2 rounded-lg p-3 text-sm',
                                    'border border-dashed border-amber-300 bg-amber-50 text-amber-900 dark:border-amber-500/60 dark:bg-amber-500/10 dark:text-amber-100' => $pengajuan->payment_status === 'pending',
                                    'border border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-500/60 dark:bg-emerald-500/10 dark:text-emerald-100' => $pengajuan->payment_status !== 'pending',
                                ])
                            >
                                <div class="flex items-center gap-2">
                                    <flux:icon
                                        name="{{ $pengajuan->payment_status === 'pending' ? 'banknotes' : 'check-circle' }}"
                                        class="h-4 w-4"
                                    />
                                    <span class="font-medium">
                                        {{ $pengajuan->payment_status === 'pending' ? __('Menunggu Pembayaran') : __('Bukti Pembayaran Diterima') }}
                                    </span>
                                </div>
                                <div class="mt-1 font-semibold">
                                    Rp {{ number_format($pengajuan->total_fee ?? 0, 0, ',', '.') }}
                                </div>
                                <p class="mt-1 text-xs leading-relaxed">
                                    {{ $pengajuan->payment_status === 'pending'
                                        ? config('app.payment_account_note')
                                        : __('Tim sedang memverifikasi. Kamu akan mendapat notifikasi setelah pembayaran disetujui.') }}
                                </p>
                            </div>
                        @endif

                        @if ($pengajuan->shipping_receipt_number)
                            <div class="mt-3 rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-800 dark:bg-zinc-900/40">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <p class="font-semibold">{{ __('Resi Pengiriman') }}</p>
                                        <p>{{ $pengajuan->shipping_receipt_number }}</p>
                                        @if ($pengajuan->shipping_sent_at)
                                            <p class="text-xs text-zinc-500">
                                                {{ __('Dikirim pada :date', ['date' => $pengajuan->shipping_sent_at->format('d M Y, H:i')]) }}
                                            </p>
                                        @endif
                                    </div>

                                    @if ($pengajuan->shipping_receipt_path)
                                        <flux:button
                                            variant="outline"
                                            icon="truck"
                                            :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->shipping_receipt_path)"
                                            target="_blank"
                                        >
                                            {{ __('Lihat Lampiran Resi') }}
                                        </flux:button>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                        <flux:button
                            variant="outline"
                            icon="document-arrow-down"
                            :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_path)"
                            target="_blank"
                        >
                            {{ __('Lihat File') }}
                        </flux:button>

                        @if (($pengajuan->total_fee ?? 0) > 0 && $pengajuan->payment_status === 'pending')
                            <flux:modal.trigger name="upload-payment-proof-{{ $pengajuan->id }}">
                                <flux:button variant="primary" icon="photo" wire:click="showPaymentProofForm({{ $pengajuan->id }})">
                                    {{ __('Sudah Bayar') }}
                                </flux:button>
                            </flux:modal.trigger>
                        @endif
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
                text="{{ __('Silahkan buat pengajuan legalisir pertama kamu.') }}"
            />
        @endforelse
    </div>
</section>
