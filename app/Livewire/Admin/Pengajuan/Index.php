<?php

namespace App\Livewire\Admin\Pengajuan;

use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Manajemen Pengajuan')]
class Index extends Component
{
    public array $fees = [];

    public function getPengajuansProperty()
    {
        return Pengajuan::query()
            ->with(['status', 'user'])
            ->latest()
            ->get();
    }

    public function getStatusSummaryProperty()
    {
        return Status::query()
            ->select(['id', 'name', 'code'])
            ->withCount('pengajuans')
            ->orderBy('sort_order')
            ->get();
    }

    public function ensureFeeState(Pengajuan $pengajuan): void
    {
        if (isset($this->fees[$pengajuan->id])) {
            return;
        }

        $this->fees[$pengajuan->id] = [
            'legalisir' => $pengajuan->legalisir_fee ?? 0,
            'photocopy' => $pengajuan->photocopy_fee ?? 0,
            'shipping' => $pengajuan->shipping_fee ?? 0,
            'notes' => $pengajuan->payment_notes,
        ];
    }

    public function setStatus(int $pengajuanId, string $statusCode): void
    {
        $user = auth()->user();

        $allowedByRole = match ($user->role) {
            'staf' => in_array($statusCode, ['DIVERIFIKASI', 'DITOLAK', 'SELESAI'], true),
            'dekan' => in_array($statusCode, ['DISETUJUI', 'DITOLAK'], true),
            'superadmin' => in_array($statusCode, ['DIAJUKAN', 'DIVERIFIKASI', 'DISETUJUI', 'DITOLAK', 'SELESAI'], true),
            default => false,
        };

        abort_unless($allowedByRole, 403);

        $status = Status::query()->where('code', $statusCode)->firstOrFail();
        $pengajuanSnapshot = Pengajuan::query()->findOrFail($pengajuanId);

        if ($statusCode === 'SELESAI' && $pengajuanSnapshot->payment_status !== 'paid') {
            session()->flash('status', 'Tidak bisa menyelesaikan pengajuan sebelum pembayaran dinyatakan lunas.');

            return;
        }

        DB::transaction(function () use ($pengajuanId, $status, $statusCode, $user) {
            $pengajuan = Pengajuan::query()->lockForUpdate()->findOrFail($pengajuanId);

            $pengajuan->status_id = $status->id;

            if ($statusCode === 'DIVERIFIKASI') {
                $pengajuan->validated_by = $user->id;
                $pengajuan->validated_at = now();
            }

            if ($statusCode === 'DISETUJUI') {
                $pengajuan->approved_by = $user->id;
                $pengajuan->approved_at = now();
            }

            $pengajuan->save();

            Notifikasi::query()->create([
                'user_id' => $pengajuan->user_id,
                'pengajuan_id' => $pengajuan->id,
                'title' => 'Status pengajuan berubah',
                'body' => 'Pengajuan '.$pengajuan->kode.' sekarang berstatus: '.$status->name.'.',
                'type' => 'PENGAJUAN_STATUS',
                'data' => ['kode' => $pengajuan->kode, 'status' => $status->code],
            ]);
        });

        session()->flash('status', 'Status berhasil diperbarui.');
    }

    public function saveFees(int $pengajuanId): void
    {
        abort_unless(in_array(auth()->user()->role, ['staf', 'superadmin'], true), 403);

        $input = $this->fees[$pengajuanId] ?? [];

        $data = Validator::make($input, [
            'legalisir' => ['required', 'integer', 'min:0'],
            'photocopy' => ['required', 'integer', 'min:0'],
            'shipping' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ])->validate();

        $pengajuan = Pengajuan::query()->findOrFail($pengajuanId);

        $total = ($data['legalisir'] ?? 0) + ($data['photocopy'] ?? 0) + ($data['shipping'] ?? 0);

        $updates = [
            'legalisir_fee' => $data['legalisir'],
            'photocopy_fee' => $data['photocopy'],
            'shipping_fee' => $data['shipping'],
            'total_fee' => $total,
            'payment_notes' => $data['notes'] ?? null,
        ];

        if ($pengajuan->payment_status !== 'paid') {
            $updates['payment_status'] = $total > 0 ? 'pending' : 'paid';
            $updates['payment_confirmed_at'] = $total > 0 ? null : now();
        }

        $pengajuan->update($updates);

        session()->flash('status', 'Biaya pengajuan diperbarui.');
    }

    public function confirmPayment(int $pengajuanId): void
    {
        abort_unless(in_array(auth()->user()->role, ['staf', 'superadmin'], true), 403);

        $pengajuan = Pengajuan::query()->findOrFail($pengajuanId);

        $pengajuan->update([
            'payment_status' => 'paid',
            'payment_confirmed_at' => now(),
        ]);

        session()->flash('status', 'Pembayaran telah dikonfirmasi.');
    }

    public function render()
    {
        return view('livewire.admin.pengajuan.index');
    }
}
