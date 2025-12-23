<?php

namespace App\Livewire\Admin\Pengajuan;

use App\Models\Notifikasi;
use App\Models\Pengajuan;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Manajemen Pengajuan')]
class Index extends Component
{
    use WithFileUploads;

    public array $fees = [];

    public array $shippingReceipts = [];

    public array $shippingReceiptUploads = [];

    public string $viewTab = 'active';

    public string $historySearch = '';

    public function getPengajuansProperty()
    {
        return Pengajuan::query()
            ->with(['status', 'user'])
            ->latest()
            ->get();
    }

    public function getActivePengajuansProperty()
    {
        return $this->pengajuans->filter(function (Pengajuan $pengajuan) {
            return ! in_array($pengajuan->status?->code, ['DISETUJUI', 'DITOLAK', 'SELESAI'], true);
        });
    }

    public function getHistoryPengajuansProperty()
    {
        $history = $this->pengajuans->filter(function (Pengajuan $pengajuan) {
            return in_array($pengajuan->status?->code, ['DISETUJUI', 'DITOLAK', 'SELESAI'], true);
        });

        $term = trim($this->historySearch);

        if ($term === '') {
            return $history;
        }

        $needle = Str::lower($term);

        return $history->filter(function (Pengajuan $pengajuan) use ($needle) {
            $haystacks = [
                Str::lower($pengajuan->kode ?? ''),
                Str::lower($pengajuan->user?->name ?? ''),
                Str::lower($pengajuan->jenis_dokumen ?? ''),
            ];

            foreach ($haystacks as $text) {
                if ($text !== '' && str_contains($text, $needle)) {
                    return true;
                }
            }

            return false;
        });
    }

    public function getStatusSummaryProperty()
    {
        return Status::query()
            ->select(['id', 'name', 'code'])
            ->withCount('pengajuans')
            ->orderBy('sort_order')
            ->get();
    }

    public function setViewTab(string $tab): void
    {
        if (! in_array($tab, ['active', 'history'], true)) {
            return;
        }

        $this->viewTab = $tab;
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

    public function ensureShippingState(Pengajuan $pengajuan): void
    {
        if (isset($this->shippingReceipts[$pengajuan->id]['number'])) {
            return;
        }

        $this->shippingReceipts[$pengajuan->id] = [
            'number' => $pengajuan->shipping_receipt_number ?? '',
        ];
    }

    public function setStatus(int $pengajuanId, string $statusCode): void
    {
        $user = auth()->user();

        $allowedByRole = match ($user->role) {
            'staf' => false,
            'dekan' => in_array($statusCode, ['DIVERIFIKASI', 'DISETUJUI', 'DITOLAK'], true),
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

        if (! $pengajuan->payment_proof_path) {
            session()->flash('status', 'Tidak dapat mengonfirmasi pembayaran karena bukti belum diunggah oleh alumni.');

            return;
        }

        $pengajuan->update([
            'payment_status' => 'paid',
            'payment_confirmed_at' => now(),
        ]);

        session()->flash('status', 'Pembayaran telah dikonfirmasi.');
    }

    public function uploadShippingReceipt(int $pengajuanId): void
    {
        abort_unless(in_array(auth()->user()->role, ['staf', 'superadmin'], true), 403);

        $pengajuan = Pengajuan::query()->with('status')->findOrFail($pengajuanId);

        $statusCode = $pengajuan->status?->code;

        if (! in_array($statusCode, ['DISETUJUI', 'SELESAI'], true)) {
            session()->flash('status', 'Resi hanya bisa dikirim setelah dokumen disetujui.');

            return;
        }

        $this->validate([
            "shippingReceipts.$pengajuanId.number" => ['required', 'string', 'max:191'],
            "shippingReceiptUploads.$pengajuanId" => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ], [], [
            "shippingReceipts.$pengajuanId.number" => 'nomor resi',
            "shippingReceiptUploads.$pengajuanId" => 'lampiran resi',
        ]);

        $upload = $this->shippingReceiptUploads[$pengajuanId];
        $number = $this->shippingReceipts[$pengajuanId]['number'];

        $path = $upload->store('shipping-receipts/'.$pengajuanId, 'public');

        $pengajuan->update([
            'shipping_receipt_number' => $number,
            'shipping_receipt_path' => $path,
            'shipping_sent_at' => now(),
        ]);

        Notifikasi::query()->create([
            'user_id' => $pengajuan->user_id,
            'pengajuan_id' => $pengajuan->id,
            'title' => 'Dokumen sedang dikirim',
            'body' => 'Pengajuan '.$pengajuan->kode.' telah dikirim dengan resi '.$number.'.',
            'type' => 'PENGAJUAN_SHIPPING',
            'data' => [
                'kode' => $pengajuan->kode,
                'resi' => $number,
            ],
        ]);

        unset($this->shippingReceiptUploads[$pengajuanId]);

        session()->flash('status', 'Nomor resi berhasil dikirim ke alumni.');
    }

    public function render()
    {
        return view('livewire.admin.pengajuan.index');
    }
}
