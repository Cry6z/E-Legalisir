<?php

namespace App\Livewire\Pengajuan;

use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\Status;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Ajukan Legalisir')]
class Create extends Component
{
    use WithFileUploads;

    public string $jenis_dokumen = '';

    public int $jumlah = 1;

    public string $catatan = '';

    public $file;

    public function submit(): void
    {
        $validated = $this->validate([
            'jenis_dokumen' => ['required', 'string', 'max:255'],
            'jumlah' => ['required', 'integer', 'min:1', 'max:100'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $status = Status::query()->where('code', 'DIAJUKAN')->firstOrFail();

        $kode = 'LEG-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));

        $path = $this->file->store('pengajuan/'.auth()->id(), 'public');
        $pengajuan = Pengajuan::query()->create([
            'user_id' => auth()->id(),
            'status_id' => $status->id,
            'kode' => $kode,
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'jumlah' => $validated['jumlah'],
            'catatan' => $validated['catatan'] !== '' ? $validated['catatan'] : null,
            'file_path' => $path,
            'verification_token' => Str::uuid()->toString(),
        ]);

        Notifikasi::query()->create([
            'user_id' => auth()->id(),
            'pengajuan_id' => $pengajuan->id,
            'title' => 'Pengajuan dibuat',
            'body' => 'Pengajuan legalisir berhasil dibuat dengan kode '.$kode.'.',
            'type' => 'PENGAJUAN_DIAJUKAN',
            'data' => ['kode' => $kode],
        ]);

        session()->flash('status', 'Pengajuan berhasil dibuat.');

        $this->redirectRoute('pengajuan.index');
    }
}
