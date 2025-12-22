<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('superadmin users are redirected to the superadmin dashboard', function () {
    $this->actingAs($user = User::factory()->create([
        'role' => 'superadmin',
    ]));

    $this->get('/dashboard')->assertRedirect(route('superadmin.dashboard'));
});

test('alumni users without submissions are redirected to pengajuan create page', function () {
    $this->actingAs($user = User::factory()->create([
        'role' => 'alumni',
    ]));

    $this->get('/dashboard')->assertRedirect(route('pengajuan.create'));
});