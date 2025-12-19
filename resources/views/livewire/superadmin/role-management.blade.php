<section class="w-full">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Manajemen Role Pengguna') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Kelola akun alumni/staf/dekan dari panel ini') }}</flux:subheading>
        <flux:separator variant="subtle" />
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        <flux:input
            class="w-full max-w-md"
            placeholder="{{ __('Cari nama atau email...') }}"
            icon="magnifying-glass"
            wire:model.live.debounce.500ms="search"
        />

        <flux:button icon="arrow-left" variant="ghost" :href="route('superadmin.dashboard')" wire:navigate>
            {{ __('Kembali ke Dashboard') }}
        </flux:button>
    </div>

    @if (session('roleStatus'))
        <div class="mt-6">
            <flux:callout variant="success" icon="check-circle" heading="{{ session('roleStatus') }}" />
        </div>
    @endif

    @if (session('roleError'))
        <div class="mt-6">
            <flux:callout variant="danger" icon="exclamation-triangle" heading="{{ session('roleError') }}" />
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <form wire:submit.prevent="createUser" class="space-y-4 rounded-2xl border border-zinc-200 bg-zinc-50/70 p-5 shadow-xs dark:border-zinc-800 dark:bg-zinc-900/60">
            <div>
                <flux:heading size="md">{{ __('Tambah Pengguna Baru') }}</flux:heading>
                <flux:text class="text-sm text-zinc-500 dark:text-zinc-300">{{ __('Isi data dasar akun dan pilih role awal.') }}</flux:text>
            </div>

            <flux:input wire:model.defer="createName" :label="__('Nama Lengkap')" required />
            <flux:input wire:model.defer="createEmail" :label="__('Email')" type="email" required />

            <flux:select wire:model.defer="createRole" :label="__('Role')" required>
                @foreach ($manageableRoles as $role)
                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model.defer="createPassword"
                :label="__('Password (opsional)')"
                type="password"
                placeholder="{{ __('Kosongkan untuk password otomatis') }}"
            />

            <div class="flex items-center justify-between">
                <flux:button type="submit" icon="user-plus" variant="primary">
                    {{ __('Simpan Pengguna') }}
                </flux:button>

                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                    {{ __('Password random 12 karakter akan dibuat bila dikosongkan') }}
                </flux:text>
            </div>
        </form>

        @if ($editingUserId)
            <form wire:submit.prevent="updateUser" class="space-y-4 rounded-2xl border border-amber-200 bg-amber-50/80 p-5 shadow-xs dark:border-amber-500/40 dark:bg-amber-500/10">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="md">{{ __('Edit Pengguna') }}</flux:heading>
                        <flux:text class="text-sm text-amber-700 dark:text-amber-200">{{ __('Perbarui data akun yang dipilih.') }}</flux:text>
                    </div>
                    <flux:button type="button" variant="ghost" icon="x-mark" wire:click="cancelEdit">
                        {{ __('Batal') }}
                    </flux:button>
                </div>

                <flux:input wire:model.defer="editName" :label="__('Nama Lengkap')" required />
                <flux:input wire:model.defer="editEmail" :label="__('Email')" type="email" required />

                <flux:select wire:model.defer="editRole" :label="__('Role')" required>
                    @foreach ($manageableRoles as $role)
                        <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model.defer="editPassword"
                    :label="__('Password Baru (opsional)')"
                    type="password"
                    placeholder="{{ __('Kosongkan bila tidak ingin mengubah password') }}"
                />

                <div class="flex items-center gap-3">
                    <flux:button type="submit" icon="check" variant="primary">
                        {{ __('Perbarui Pengguna') }}
                    </flux:button>
                    <flux:text class="text-xs text-amber-700 dark:text-amber-200">
                        {{ __('Simpan untuk menerapkan perubahan.') }}
                    </flux:text>
                </div>
            </form>
        @else
            <div class="rounded-2xl border border-dashed border-zinc-200 bg-white/70 p-5 text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900/50">
                {{ __('Klik tombol edit pada tabel pengguna untuk menampilkan formulir pengeditan.') }}
            </div>
        @endif
    </div>

    @if ($pendingDeleteId)
        <div class="mt-6 rounded-2xl border border-red-200 bg-red-50/80 p-5 text-sm text-red-900 dark:border-red-500/40 dark:bg-red-500/10 dark:text-red-100">
            <div class="flex items-center justify-between">
                <div>
                    <flux:heading size="md">{{ __('Konfirmasi Hapus Pengguna') }}</flux:heading>
                    <flux:text class="text-sm text-red-700 dark:text-red-100">
                        {{ __('Kamu akan menghapus pengguna') }} <strong>{{ $pendingDeleteName }}</strong>.
                    </flux:text>
                </div>
                <div class="flex gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cancelDelete">{{ __('Batal') }}</flux:button>
                    <flux:button type="button" variant="danger" icon="trash" wire:click="deleteUser">
                        {{ __('Hapus Sekarang') }}
                    </flux:button>
                </div>
            </div>
        </div>
    @endif

    <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800">
        <table class="min-w-full divide-y divide-zinc-100 dark:divide-zinc-800">
            <thead class="bg-zinc-50/80 text-left text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:bg-zinc-900/40 dark:text-zinc-400">
                <tr>
                    <th class="px-4 py-3">{{ __('Nama') }}</th>
                    <th class="px-4 py-3">{{ __('Email') }}</th>
                    <th class="px-4 py-3">{{ __('Role saat ini') }}</th>
                    <th class="px-4 py-3 text-right">{{ __('Ubah role') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 bg-white text-sm dark:divide-zinc-800 dark:bg-zinc-950">
                @forelse ($this->manageableUsers as $user)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-zinc-900 dark:text-white">{{ $user->name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <flux:text class="text-sm text-zinc-500 dark:text-zinc-300">{{ $user->email }}</flux:text>
                        </td>
                        <td class="px-4 py-3">
                            <flux:badge color="zinc">{{ $user->role }}</flux:badge>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <flux:button
                                    size="xs"
                                    variant="outline"
                                    icon="pencil"
                                    wire:click="editUser({{ $user->id }})"
                                >
                                    {{ __('Edit') }}
                                </flux:button>

                                <flux:button
                                    size="xs"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $user->id }})"
                                >
                                    {{ __('Hapus') }}
                                </flux:button>

                                @foreach ($manageableRoles as $role)
                                    <flux:button
                                        size="xs"
                                        variant="{{ $user->role === $role ? 'primary' : 'outline' }}"
                                        wire:click="updateRole({{ $user->id }}, '{{ $role }}')"
                                    >
                                        {{ ucfirst($role) }}
                                    </flux:button>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6">
                            <flux:callout
                                variant="secondary"
                                icon="users"
                                heading="{{ __('Tidak ada pengguna yang bisa diatur') }}"
                                text="{{ __('Coba ubah kata kunci pencarian.') }}"
                            />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
