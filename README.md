# E-Legalisir Universitas Muhammadiyah Bengkulu

Platform internal untuk pengajuan legalisir ijazah & transkrip alumni Universitas Muhammadiyah Bengkulu. Sistem ini memanfaatkan Laravel 12, Livewire, dan Fortify untuk menyediakan alur self-service bagi alumni serta dashboard pemeriksaan untuk staf, dekan, dan superadmin.@app/Models/User.php#3-86 @routes/web.php#3-86

> **Catatan pribadi:** Ini merupakan project magang saya, sehingga dokumentasi ini juga menjadi bagian dari laporan dan bukti capaian pembelajaran selama program berlangsung.

## Fitur Utama

- **Pengajuan online oleh alumni**: unggah dokumen, pilih jenis legalisir, dan pantau status secara real-time.@resources/views/landing.blade.php#59-193 @app/Models/Pengajuan.php#8-69
- **Alur verifikasi bertahap**: staf memvalidasi, dekan menyetujui, hingga status "Selesai" dengan jejak audit lengkap.@database/migrations/2025_12_17_000030_create_pengajuan_table.php#7-35 @database/seeders/DatabaseSeeder.php#18-42
- **Dashboard per-role**: alumni, staf, dekan, dan superadmin memiliki rute serta komponen Livewire berbeda.@routes/web.php#19-85
- **Manajemen pengaturan pengguna**: profil, sandi, tampilan, dan keamanan dua faktor via Fortify.@routes/web.php#47-86 @app/Providers/FortifyServiceProvider.php#27-72
- **Notifikasi & tracking pembayaran/pengiriman**: field khusus untuk bukti bayar, ongkos kirim, nomor resi, dan dokumen PDF bertanda tangan.@app/Models/Pengajuan.php#12-68

## Teknologi

| Lapisan      | Teknologi                                                                 |
|--------------|----------------------------------------------------------------------------|
| Backend      | PHP ^8.2, Laravel Framework ^12.0, Laravel Fortify, Livewire Flux, Queue database driver.@composer.json#1-92 @.env.example#23-48|
| Frontend     | Vite, Tailwind CSS 4, Flux UI components, Axios.@package.json#1-24|
| Tooling      | Composer scripts, npm scripts, PestPHP untuk testing, Laravel Pint & Pail.@composer.json#18-75|

## Kebutuhan Sistem

- PHP 8.2 + ekstensi standar Laravel.
- Composer 2.
- Node.js 20+ & npm.
- Database (default SQLite, dapat diganti MySQL/PostgreSQL melalui `.env`).@.env.example#23-34
- Redis optional untuk cache / queue (default database queue).@.env.example#40-48

## Setup Cepat

```bash
# 1. Clone repo
# git clone <repo-url> && cd e-legalisir

# 2. Instal dependensi PHP & buat file .env
composer install
cp .env.example .env
php artisan key:generate

# 3. Setel koneksi database di .env
#    Sesuaikan DB_CONNECTION, DB_HOST, dst.

# 4. Migrasi & seed
php artisan migrate --seed

# 5. Instal dependensi JS
npm install

# 6. Build aset produksi
npm run build
```

> Alternatif: jalankan `composer run setup` untuk otomatisasi instalasi, migrasi, dan build.@composer.json#40-75

## Menjalankan Aplikasi

### Mode pengembangan

```bash
# Jalankan server Laravel, listener queue, dan Vite secara paralel
composer run dev
```
@composer.json#40-75

### Manual

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

### Cron / Worker

- Pastikan queue worker berjalan karena status & notifikasi mengandalkan antrian database.
- Gunakan Supervisor / Laravel Horizon (opsional) di produksi.

### Storage link

Jika menyimpan berkas di `storage/app`, jalankan `php artisan storage:link` agar dapat diakses publik.

## Akun Seeders

Seeder menambahkan status dasar dan pengguna uji berikut:@database/seeders/DatabaseSeeder.php#18-62

| Peran        | Email              | Sandi     |
|--------------|--------------------|-----------|
| Alumni       | `alumni@gmail.com` | `password`|
| Staf         | `staff@gmail.com`  | `password`|
| Dekan        | `dekan@gmail.com`  | `password`|
| Superadmin   | `superadmin@gmail.com` | `password` |

## Alur Pengajuan

1. Alumni membuat pengajuan (kode unik + dokumen) dan mengunggah file.@app/Models/Pengajuan.php#12-37
2. Staf memvalidasi, memberi catatan, dan memperbarui status ke **Diverifikasi**.@routes/web.php#73-80 @database/seeders/DatabaseSeeder.php#23-26
3. Dekan menyetujui atau menolak (status **Disetujui** / **Ditolak**).@database/seeders/DatabaseSeeder.php#28-36
4. Ketika selesai, sistem dapat menyimpan PDF bertanda tangan, bukti bayar, hingga nomor resi pengiriman.@app/Models/Pengajuan.php#19-35

## Struktur Direktori Singkat

```
app/
  Livewire/        # Komponen UI dinamis untuk setiap peran
  Models/          # User, Pengajuan, Status, Notifikasi
config/
  app.php          # Nama aplikasi, timezone, catatan rekening pembayaran
resources/views/   # Landing page & komponen Blade
routes/web.php     # Rute publik, dashboard role-based
```
@routes/web.php#1-86 @app/Models/User.php#3-86 @config/app.php#1-130 @resources/views/landing.blade.php#1-234

## Variabel Lingkungan Esensial

| Kunci                 | Keterangan                                  |
|-----------------------|----------------------------------------------|
| `APP_NAME`            | Nama aplikasi/branding di UI.                |
| `APP_URL`             | Digunakan untuk URL yang dihasilkan Artisan. |
| `DB_*`                | Pengaturan basis data (default SQLite).      |
| `QUEUE_CONNECTION`    | Default `database`, ubah jika pakai Redis.   |
| `MAIL_*`              | Mailer (set ke SMTP produksi).               |
| `PAYMENT_ACCOUNT_NOTE`| Pesan rekening biaya legalisir.@config/app.php#121-129|
| `VITE_APP_NAME`       | Dipakai di build frontend Vite.              |
@.env.example#1-66

## Testing & QA

- Jalankan unit/feature test dengan `php artisan test` atau `composer run test` (menggunakan Pest).@composer.json#40-75
- Gunakan `php artisan config:clear --ansi` sebelum test agar memaksa pembacaan ulang konfigurasi (sudah otomatis di script).@composer.json#40-75

## Kontribusi

1. Fork & buat branch fitur.
2. Ikuti standar kode Laravel + Pint (`./vendor/bin/pint`).
3. Tambahkan test jika memperkenalkan logika baru.
4. Buat PR dengan deskripsi alur fitur & cara uji.

---

Selamat membangun layanan legalisir digital kampus! Jika menemukan kendala, cek log (`storage/logs`), antrean, atau konfigurasi `.env` terlebih dahulu.
