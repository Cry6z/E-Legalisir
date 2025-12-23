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

            @if ($pengajuan->payment_proof_path)
                <div class="mt-4 flex flex-wrap items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-emerald-900 dark:border-emerald-500/40 dark:bg-emerald-500/10 dark:text-emerald-50">
                    <div class="flex items-center gap-2">
                        <flux:icon name="photo" class="h-4 w-4" />
                        <div class="text-sm">
                            <p class="font-semibold">{{ __('Bukti pembayaran alumni tersedia') }}</p>
                            @if ($pengajuan->payment_notes)
                                <p class="text-xs text-emerald-800 dark:text-emerald-100">
                                    {{ \Illuminate\Support\Str::limit($pengajuan->payment_notes, 120) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <flux:button
                        variant="outline"
                        icon="arrow-top-right-on-square"
                        :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->payment_proof_path)"
                        target="_blank"
                    >
                        {{ __('Lihat Bukti') }}
                    </flux:button>
                </div>
            @endif

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
                        <flux:modal.trigger name="confirm-payment-{{ $pengajuan->id }}">
                            <flux:button variant="primary" icon="check-badge">
                                {{ __('Konfirmasi Pembayaran') }}
                            </flux:button>
                        </flux:modal.trigger>
                    @endif
                </div>
            @endif
        </div>

        @if (in_array(auth()->user()->role, ['staf', 'superadmin'], true))
            @php($this->ensureShippingState($pengajuan))
            <div class="rounded-2xl border border-dashed border-zinc-200 bg-white/50 p-4 text-sm dark:border-zinc-700 dark:bg-zinc-900/40">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <flux:text class="text-xs uppercase tracking-wide text-zinc-500">{{ __('Pengiriman Dokumen') }}</flux:text>
                        @if ($pengajuan->shipping_receipt_number)
                            <p class="text-sm font-semibold">
                                {{ __('Nomor Resi: :resi', ['resi' => $pengajuan->shipping_receipt_number]) }}
                            </p>
                            @if ($pengajuan->shipping_sent_at)
                                <p class="text-xs text-zinc-500">
                                    {{ __('Dikirim pada :date', ['date' => $pengajuan->shipping_sent_at->format('d M Y, H:i')]) }}
                                </p>
                            @endif
                        @else
                            <p class="text-sm text-zinc-500">
                                {{ __('Kirimkan nomor resi agar alumni dapat melacak pengiriman.') }}
                            </p>
                        @endif
                    </div>

                    @if ($pengajuan->shipping_receipt_path)
                        <flux:button
                            variant="outline"
                            icon="arrow-top-right-on-square"
                            :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->shipping_receipt_path)"
                            target="_blank"
                        >
                            {{ __('Lihat Lampiran Resi') }}
                        </flux:button>
                    @endif
                </div>

                @php($canSendResi = in_array($pengajuan->status?->code, ['DISETUJUI', 'SELESAI'], true))
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <flux:input
                        label="{{ __('Nomor Resi') }}"
                        wire:model.defer="shippingReceipts.{{ $pengajuan->id }}.number"
                        placeholder="Contoh: JNE123456789"
                    />
                    <flux:input
                        type="file"
                        label="{{ __('Lampiran Resi (JPG/PNG/PDF, maks 5MB)') }}"
                        wire:model="shippingReceiptUploads.{{ $pengajuan->id }}"
                        accept=".jpg,.jpeg,.png,.pdf"
                    />
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <flux:button
                        variant="primary"
                        icon="paper-airplane"
                        wire:click="uploadShippingReceipt({{ $pengajuan->id }})"
                        wire:target="shippingReceiptUploads.{{ $pengajuan->id }}, uploadShippingReceipt"
                        wire:loading.attr="disabled"
                        :disabled="! $canSendResi"
                    >
                        {{ __('Kirim Resi ke Alumni') }}
                    </flux:button>

                    @unless ($canSendResi)
                        <flux:button variant="outline" disabled>
                            {{ __('Menunggu persetujuan dekan') }}
                        </flux:button>
                    @endunless
                </div>
            </div>
        @endif

        <div class="flex flex-wrap items-center gap-2">
            <flux:button
                variant="outline"
                icon="document-arrow-down"
                :href="\Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->file_path)"
                target="_blank"
            >
                {{ __('Lihat Dokumen') }}
            </flux:button>

            @if ($pengajuan->shipping_receipt_number)
                <flux:button
                    variant="ghost"
                    icon="truck"
                    :href="$pengajuan->shipping_receipt_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($pengajuan->shipping_receipt_path) : null"
                    target="_blank"
                    :disabled="! $pengajuan->shipping_receipt_path"
                >
                    {{ __('Resi: ') }}{{ $pengajuan->shipping_receipt_number }}
                </flux:button>
            @endif

            @if (auth()->user()->role === 'dekan')
                <flux:button variant="primary" icon="check" wire:click="setStatus({{ $pengajuan->id }}, 'DIVERIFIKASI')">
                    {{ __('Verifikasi') }}
                </flux:button>
                <flux:button variant="outline" icon="check-badge" wire:click="setStatus({{ $pengajuan->id }}, 'DISETUJUI')">
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

<flux:modal name="confirm-payment-{{ $pengajuan->id }}" separator>
    <div class="space-y-4">
        <div>
            <flux:heading size="lg">{{ __('Konfirmasi Pembayaran') }}</flux:heading>
            <flux:text class="text-sm text-zinc-500 dark:text-zinc-300">
                {{ __('Pastikan bukti pembayaran telah diperiksa dan jumlah transfer sesuai sebelum melanjutkan.') }}
            </flux:text>
        </div>

        @if (! $pengajuan->payment_proof_path)
            <flux:callout
                variant="danger"
                icon="exclamation-triangle"
                heading="{{ __('Bukti pembayaran belum tersedia') }}"
                text="{{ __('Alumni harus mengunggah bukti sebelum pembayaran dapat dikonfirmasi.') }}"
            />
        @else
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 text-sm dark:border-zinc-800 dark:bg-zinc-900/50">
                <p class="font-medium">{{ __('Nominal yang akan dikonfirmasi') }}</p>
                <p class="text-lg font-semibold">Rp {{ number_format($pengajuan->total_fee ?? 0, 0, ',', '.') }}</p>
            </div>
        @endif

        <div class="flex justify-end gap-2">
            <flux:modal.close>
                <flux:button variant="ghost">
                    {{ __('Batal') }}
                </flux:button>
            </flux:modal.close>

            <flux:button
                variant="primary"
                icon="shield-check"
                wire:click="confirmPayment({{ $pengajuan->id }})"
                :disabled="! $pengajuan->payment_proof_path"
            >
                {{ __('Saya sudah memverifikasi') }}
            </flux:button>
        </div>
    </div>
</flux:modal>
