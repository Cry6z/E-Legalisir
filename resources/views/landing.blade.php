<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white antialiased dark:bg-linear-to-b dark:from-neutral-950 dark:to-neutral-900">
        @php
            $isAlumni = auth()->check() && auth()->user()->role === 'alumni';
            $myPengajuans = collect();
            if ($isAlumni) {
                $myPengajuans = auth()
                    ->user()
                    ->pengajuans()
                    ->with('status')
                    ->latest()
                    ->limit(4)
                    ->get();
            }
        @endphp
        <div class="mx-auto w-full max-w-6xl px-6 py-10">
            <header class="flex flex-wrap items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-3 font-medium" wire:navigate>
                    <span class="flex h-10 w-10 items-center justify-center rounded-md">
                        <x-app-logo-icon class="h-7 fill-current text-black dark:text-white" />
                    </span>
                    <span class="text-sm text-zinc-900 dark:text-white">{{ config('app.name') }}</span>
                </a>

                <div class="flex flex-1 flex-wrap items-center justify-end gap-4">
                <nav class="flex items-center gap-2">
                    @auth
                        <flux:button variant="outline" :href="route('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:button>

                        @if (auth()->user()->role === 'alumni')
                            <flux:button variant="primary" :href="route('pengajuan.create')" wire:navigate>{{ __('Ajukan Legalisir') }}</flux:button>
                        @endif

                        @if (in_array(auth()->user()->role, ['staf', 'dekan'], true))
                            <flux:button variant="primary" :href="route('admin.pengajuan.index')" wire:navigate>{{ __('Manajemen Pengajuan') }}</flux:button>
                        @endif

                        @if (auth()->user()->role === 'superadmin')
                            <flux:button variant="primary" :href="route('superadmin.dashboard')" wire:navigate>{{ __('Dashboard Superadmin') }}</flux:button>
                        @endif
                    @else
                        <flux:button variant="outline" :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:button>
                        @if (Route::has('register'))
                            <flux:button variant="primary" :href="route('register')" wire:navigate>{{ __('Register') }}</flux:button>
                        @endif
                    @endauth
                </nav>
                </div>
            </header>

            <main class="mt-12 space-y-16">
                <section id="hero" class="grid gap-10 rounded-[32px] border border-zinc-200/80 bg-white/80 p-8 shadow-[0_25px_45px_rgba(15,23,42,0.15)] backdrop-blur-md dark:border-zinc-800/60 dark:bg-zinc-900/80 lg:grid-cols-2">
                    <div class="space-y-6">
                        <flux:badge color="zinc">{{ __('E-Legalisir Universitas Muhammadiyah Bengkulu') }}</flux:badge>
                        <flux:heading size="2xl" level="1">{{ __('Legalisir Dokumen Alumni Secara Online') }}</flux:heading>
                        <flux:text class="text-base text-zinc-600 dark:text-zinc-300">
                            {{ __('Ajukan legalisir ijazah / transkrip, unggah berkas, pantau status secara real-time, dan terima hasil sesuai alur layanan.') }}
                        </flux:text>

                        <div class="flex flex-wrap gap-3">
                            @auth
                                @if (auth()->user()->role === 'alumni')
                                    <flux:button variant="primary" icon="document-plus" :href="route('pengajuan.create')" wire:navigate>{{ __('Mulai Ajukan') }}</flux:button>
                                    <flux:button variant="outline" icon="clipboard-document-list" :href="route('pengajuan.index')" wire:navigate>{{ __('Lihat Pengajuan Saya') }}</flux:button>
                                @endif

                                @if (in_array(auth()->user()->role, ['staf', 'dekan'], true))
                                    <flux:button variant="primary" icon="clipboard-document" :href="route('admin.pengajuan.index')" wire:navigate>{{ __('Buka Antrian Pengajuan') }}</flux:button>
                                @endif

                                @if (auth()->user()->role === 'superadmin')
                                    <flux:button variant="primary" icon="shield-check" :href="route('superadmin.dashboard')" wire:navigate>{{ __('Buka Dashboard Superadmin') }}</flux:button>
                                @endif
                            @else
                                <flux:button variant="primary" icon="arrow-right" :href="route('login')" wire:navigate>{{ __('Masuk untuk Mengajukan') }}</flux:button>
                            @endauth
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-zinc-200 bg-gradient-to-br from-zinc-50 via-white to-zinc-100 p-6 shadow-lg dark:border-zinc-800 dark:from-zinc-900 dark:via-zinc-900 dark:to-zinc-950">
                        <flux:heading size="lg">{{ __('Alur Singkat') }}</flux:heading>
                        <flux:separator variant="subtle" class="my-4" />

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <flux:heading size="sm">1. {{ __('Buat Pengajuan') }}</flux:heading>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Isi jenis dokumen, jumlah, catatan, lalu unggah file.') }}</flux:text>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <flux:heading size="sm">2. {{ __('Verifikasi Staf') }}</flux:heading>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Staf memeriksa kelengkapan dan memvalidasi berkas.') }}</flux:text>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <flux:heading size="sm">3. {{ __('Persetujuan Dekan') }}</flux:heading>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Dekan memberi persetujuan untuk diproses.') }}</flux:text>
                            </div>
                            <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                <flux:heading size="sm">4. {{ __('Selesai') }}</flux:heading>
                                <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Status berubah dan alumni dapat memantau progresnya.') }}</flux:text>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="keunggulan" class="grid gap-6 rounded-[28px] border border-zinc-200 bg-white/80 p-8 shadow-sm dark:border-zinc-800 dark:bg-zinc-950/70 lg:grid-cols-3">
                    <div>
                        <flux:heading size="lg">{{ __('Keunggulan Sistem') }}</flux:heading>
                        <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-300">{{ __('Transparansi status, verifikasi cepat, dan notifikasi otomatis untuk setiap perubahan proses.') }}</flux:text>
                    </div>
                    <div>
                        <flux:heading size="md">{{ __('Layanan Terintegrasi') }}</flux:heading>
                        <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-300">{{ __('Staf & Dekan bekerja melalui dashboard khusus sehingga pengajuan kamu tidak tersendat.') }}</flux:text>
                    </div>
                    <div>
                        <flux:heading size="md">{{ __('Aman & Tertelusur') }}</flux:heading>
                        <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-300">{{ __('Setiap pengajuan tercatat lengkap, siap dikembangkan menuju e-signature dan QR legalisir.') }}</flux:text>
                    </div>
                </section>

                @if ($isAlumni)
                    <section id="alumni-section" class="grid gap-6 lg:grid-cols-2">
                        <div class="rounded-[24px] border border-zinc-200 bg-gradient-to-br from-white to-zinc-100/70 p-6 shadow-sm dark:border-zinc-800 dark:from-zinc-900 dark:to-zinc-950">
                            <flux:heading size="lg">{{ __('Halo, ') }}{{ auth()->user()->name }}</flux:heading>
                            <flux:text class="mt-2 text-sm text-zinc-500 dark:text-zinc-300">{{ __('Ayo lanjutkan proses legalisir kamu dari sini.') }}</flux:text>
                            <div class="mt-6 flex flex-wrap gap-3">
                                <flux:button variant="primary" icon="document-plus" :href="route('pengajuan.create')" wire:navigate>
                                    {{ __('Buat Pengajuan Baru') }}
                                </flux:button>
                                <flux:button variant="outline" icon="clipboard-document-list" :href="route('pengajuan.index')" wire:navigate>
                                    {{ __('Lihat Semua Pengajuan') }}
                                </flux:button>
                            </div>
                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800">
                                    <flux:heading size="md">{{ $myPengajuans->count() }}</flux:heading>
                                    <flux:text class="text-zinc-500 dark:text-zinc-300">{{ __('Pengajuan terakhir') }}</flux:text>
                                </div>
                                <div class="rounded-xl border border-zinc-200 p-4 text-sm dark:border-zinc-800">
                                    <flux:heading size="md">{{ __('Realtime Status') }}</flux:heading>
                                    <flux:text class="text-zinc-500 dark:text-zinc-300">{{ __('Pantau perubahan status secara otomatis.') }}</flux:text>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[24px] border border-zinc-200 bg-white/90 p-6 shadow-sm dark:border-zinc-800 dark:bg-zinc-950">
                            <div class="flex items-center justify-between gap-4">
                                <flux:heading size="lg">{{ __('Status Pengajuan Saya') }}</flux:heading>
                                <flux:badge color="zinc">{{ __('Terbaru') }}</flux:badge>
                            </div>
                            <flux:separator variant="subtle" class="my-4" />
                            <div class="space-y-3">
                                @forelse ($myPengajuans as $pengajuan)
                                    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-800">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <flux:heading size="sm" class="truncate">{{ $pengajuan->kode }}</flux:heading>
                                                <flux:text class="text-xs text-zinc-500 truncate">
                                                    {{ $pengajuan->jenis_dokumen }} • {{ $pengajuan->created_at?->format('d M Y, H:i') }}
                                                </flux:text>
                                            </div>
                                            <flux:badge color="zinc">{{ $pengajuan->status?->name ?? __('Belum diketahui') }}</flux:badge>
                                        </div>
                                    </div>
                                @empty
                                    <flux:callout
                                        variant="secondary"
                                        icon="clipboard-document"
                                        heading="{{ __('Belum ada pengajuan') }}"
                                        text="{{ __('Pengajuan yang kamu buat akan terlihat di sini dengan status Terproses / Diterima / Ditolak.') }}"
                                    />
                                @endforelse
                            </div>
                        </div>
                    </section>
                @endif

                <footer id="footer" class="rounded-[28px] border border-zinc-200 bg-zinc-50/80 p-8 text-sm text-zinc-600 shadow-sm dark:border-zinc-800 dark:bg-zinc-950/80 dark:text-zinc-300">
                    <div class="grid gap-6 md:grid-cols-4">
                        <div>
                            <flux:heading size="md">{{ config('app.name') }}</flux:heading>
                            <flux:text class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Platform legalisir resmi Universitas Muhammadiyah Bengkulu.') }}
                            </flux:text>
                        </div>

                        <div>
                            <flux:heading size="sm">{{ __('Kontak') }}</flux:heading>
                            <ul class="mt-3 space-y-1 text-xs">
                                <li>Email: <a class="hover:underline" href="mailto:legalisir@umb.ac.id">legalisir@umb.ac.id</a></li>
                                <li>Telepon: (0736) 123456</li>
                                <li>Alamat: Jl. Bali, Kota Bengkulu</li>
                            </ul>
                        </div>

                        <div>
                            <flux:heading size="sm">{{ __('Sosial Media') }}</flux:heading>
                            <ul class="mt-3 space-y-1 text-xs">
                                <li><a class="hover:underline" href="https://www.instagram.com" target="_blank">Instagram</a></li>
                                <li><a class="hover:underline" href="https://www.facebook.com" target="_blank">Facebook</a></li>
                                <li><a class="hover:underline" href="https://www.youtube.com" target="_blank">YouTube</a></li>
                            </ul>
                        </div>

                        <div>
                            <flux:heading size="sm">{{ __('Informasi') }}</flux:heading>
                            <ul class="mt-3 space-y-1 text-xs">
                                <li><a class="hover:underline" href="#">FAQ</a></li>
                                <li><a class="hover:underline" href="#">Kebijakan Privasi</a></li>
                                <li><a class="hover:underline" href="#">Panduan Pengajuan</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <span>© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</span>
                        <span>{{ __('E-Legalisir Universitas Muhammadiyah Bengkulu') }}</span>
                    </div>
                </footer>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
