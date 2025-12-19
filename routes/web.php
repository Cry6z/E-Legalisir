<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use App\Livewire\Pengajuan\Create as PengajuanCreate;
use App\Livewire\Pengajuan\Index as PengajuanIndex;
use App\Livewire\Admin\Pengajuan\Index as AdminPengajuanIndex;
use App\Livewire\Superadmin\Dashboard as SuperadminDashboard;
use App\Livewire\Superadmin\RoleManagement as SuperadminRoleManagement;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('landing');
})->name('home');

Route::get('dashboard', function () {
    $user = auth()->user();

    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->role === 'superadmin') {
        return redirect()->route('superadmin.dashboard');
    }

    if ($user->role === 'alumni') {
        $hasPengajuan = $user->pengajuans()->exists();

        if ($hasPengajuan) {
            return redirect()->route('pengajuan.index');
        }

        return redirect()->route('pengajuan.create');
    }

    if (in_array($user->role, ['staf', 'dekan'], true)) {
        return redirect()->route('admin.pengajuan.index');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('settings/appearance', Appearance::class)->name('appearance.edit');

    if (Features::enabled(Features::updateProfileInformation())) {
        Route::get('user/profile-information', ProfileInformation::class)->name('user-profile-information.edit');
    }

    if (Features::enabled(Features::updatePasswords())) {
        Route::get('user/password', UserPassword::class)->name('user-password.edit');
    }

    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');

    Route::middleware(['role:alumni'])->group(function () {
        Route::get('pengajuan', PengajuanIndex::class)->name('pengajuan.index');
        Route::get('pengajuan/create', PengajuanCreate::class)->name('pengajuan.create');
    });

    Route::middleware(['role:staf,dekan,superadmin'])->group(function () {
        Route::get('admin/pengajuan', AdminPengajuanIndex::class)->name('admin.pengajuan.index');
    });

    Route::middleware(['role:superadmin'])->group(function () {
        Route::get('superadmin/dashboard', SuperadminDashboard::class)->name('superadmin.dashboard');
        Route::get('superadmin/roles', SuperadminRoleManagement::class)->name('superadmin.roles');
    });
});
