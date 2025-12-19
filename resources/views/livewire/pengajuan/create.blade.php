<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Ajukan Legalisir') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Lengkapi data pengajuan dan unggah dokumen') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="mb-6">
        <flux:button variant="outline" icon="arrow-left" :href="route('pengajuan.index')" wire:navigate>
            {{ __('Kembali') }}
        </flux:button>
    </div>

    <form wire:submit="submit" class="w-full max-w-2xl space-y-6">
        <flux:input
            wire:model="jenis_dokumen"
            :label="__('Jenis Dokumen')"
            type="text"
            required
            autocomplete="off"
            placeholder="{{ __('Contoh: Ijazah, Transkrip Nilai') }}"
        />

        <flux:input
            wire:model="jumlah"
            :label="__('Jumlah')"
            type="number"
            min="1"
            max="100"
            required
        />

        <flux:textarea wire:model="catatan" :label="__('Catatan (opsional)')" rows="4" />

        <flux:input
            wire:model="file"
            :label="__('File Dokumen')"
            type="file"
            required
        />

        <div class="flex items-center gap-4">
            <flux:button variant="primary" type="submit" icon="paper-airplane">
                {{ __('Kirim Pengajuan') }}
            </flux:button>

            <flux:text class="text-sm text-zinc-500" wire:loading wire:target="submit,file">
                {{ __('Mengunggah...') }}
            </flux:text>
        </div>
    </form>
</section>
