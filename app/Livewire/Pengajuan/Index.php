<?php

namespace App\Livewire\Pengajuan;

use App\Models\Pengajuan;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Pengajuan Saya')]
class Index extends Component
{
    public function getPengajuansProperty()
    {
        return Pengajuan::query()
            ->with('status')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.pengajuan.index');
    }
}
