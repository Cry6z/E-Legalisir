<?php

namespace App\Livewire\Superadmin;

use App\Models\Pengajuan;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Dashboard Superadmin')]
class Dashboard extends Component
{
    public function getUsersByRoleProperty(): array
    {
        $roles = ['alumni', 'staf', 'dekan', 'superadmin'];

        $result = array_fill_keys($roles, 0);

        $rows = User::query()
            ->select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->all();

        foreach ($rows as $role => $total) {
            if (array_key_exists($role, $result)) {
                $result[$role] = (int) $total;
            }
        }

        return $result;
    }

    public function getStatusCountsProperty()
    {
        return Status::query()
            ->select(['id', 'code', 'name', 'sort_order'])
            ->withCount('pengajuans')
            ->orderBy('sort_order')
            ->get();
    }

    public function getLatestPengajuansProperty()
    {
        return Pengajuan::query()
            ->with(['user', 'status'])
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.superadmin.dashboard');
    }
}
