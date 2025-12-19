<?php

namespace App\Livewire\Pengajuan;

use App\Models\Pengajuan;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Pengajuan Saya')]
class Index extends Component
{
    use WithFileUploads;

    public ?int $paymentProofPengajuanId = null;

    public $paymentProofFile;

    public string $paymentProofNote = '';

    public function getPengajuansProperty()
    {
        return Pengajuan::query()
            ->with('status')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();
    }

    public function getActivePengajuansProperty()
    {
        return $this->pengajuans->filter(fn (Pengajuan $pengajuan) => ! $pengajuan->status?->is_final);
    }

    public function getHistoryPengajuansProperty()
    {
        return $this->pengajuans->filter(fn (Pengajuan $pengajuan) => $pengajuan->status?->is_final);
    }

    public function showPaymentProofForm(int $pengajuanId): void
    {
        $this->resetValidation();
        $this->paymentProofPengajuanId = $pengajuanId;
        $this->paymentProofFile = null;
        $this->paymentProofNote = '';
    }

    public function cancelPaymentProof(): void
    {
        $this->resetPaymentProofState();
    }

    public function submitPaymentProof(): void
    {
        if (! $this->paymentProofPengajuanId) {
            return;
        }

        $validated = $this->validate([
            'paymentProofFile' => ['required', 'image', 'max:5120'],
            'paymentProofNote' => ['nullable', 'string', 'max:500'],
        ], [], [
            'paymentProofFile' => 'bukti pembayaran',
        ]);

        $pengajuan = Pengajuan::query()
            ->where('id', $this->paymentProofPengajuanId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $path = $this->paymentProofFile->store('payment-proof/'.auth()->id(), 'public');

        $noteSnippet = trim($validated['paymentProofNote'] ?? '');
        $newNotes = $pengajuan->payment_notes;

        if ($noteSnippet !== '') {
            $notePrefix = 'Catatan pemohon ('.now()->format('d/m/Y H:i').'): '.$noteSnippet;
            $newNotes = $newNotes ? $newNotes."\n\n".$notePrefix : $notePrefix;
        }

        $pengajuan->update([
            'payment_proof_path' => $path,
            'payment_status' => 'pending',
            'payment_confirmed_at' => null,
            'payment_notes' => $newNotes,
        ]);

        $this->resetPaymentProofState();

        session()->flash('status', 'Bukti pembayaran berhasil dikirim. Tim akan memverifikasi.');
    }

    private function resetPaymentProofState(): void
    {
        $this->paymentProofPengajuanId = null;
        $this->paymentProofFile = null;
        $this->paymentProofNote = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.pengajuan.index');
    }
}
