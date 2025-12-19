<?php

namespace Database\Seeders;

use App\Models\Status;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Status::query()->updateOrCreate(
            ['code' => 'DIAJUKAN'],
            ['name' => 'Diajukan', 'sort_order' => 1, 'is_final' => false],
        );

        Status::query()->updateOrCreate(
            ['code' => 'DIVERIFIKASI'],
            ['name' => 'Diverifikasi Staf', 'sort_order' => 2, 'is_final' => false],
        );

        Status::query()->updateOrCreate(
            ['code' => 'DISETUJUI'],
            ['name' => 'Disetujui Dekan', 'sort_order' => 3, 'is_final' => false],
        );

        Status::query()->updateOrCreate(
            ['code' => 'DITOLAK'],
            ['name' => 'Ditolak', 'sort_order' => 4, 'is_final' => true],
        );

        Status::query()->updateOrCreate(
            ['code' => 'SELESAI'],
            ['name' => 'Selesai', 'sort_order' => 5, 'is_final' => true],
        );

        User::query()->updateOrCreate(
            ['email' => 'alumni@gmail.com'],
            ['name' => 'Tester Alumni', 'password' => Hash::make('password'), 'role' => 'alumni', 'email_verified_at' => now()],
        );

        User::query()->updateOrCreate(
            ['email' => 'staff@gmail.com'],
            ['name' => 'Tester Staf', 'password' => Hash::make('password'), 'role' => 'staf', 'email_verified_at' => now()],
        );

        User::query()->updateOrCreate(
            ['email' => 'dekan@gmail.com'],
            ['name' => 'Tester Dekan', 'password' => Hash::make('password'), 'role' => 'dekan', 'email_verified_at' => now()],
        );

        User::query()->updateOrCreate(
            ['email' => 'superadmin@gmail.com'],
            ['name' => 'Tester Superadmin', 'password' => Hash::make('password'), 'role' => 'superadmin', 'email_verified_at' => now()],
        );
    }
}
