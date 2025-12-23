<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';

    protected $fillable = [
        'user_id',
        'status_id',
        'kode',
        'jenis_dokumen',
        'jumlah',
        'catatan',
        'file_path',
        'payment_proof_path',
        'legalisir_fee',
        'photocopy_fee',
        'shipping_fee',
        'total_fee',
        'payment_status',
        'payment_confirmed_at',
        'payment_notes',
        'validated_by',
        'validated_at',
        'approved_by',
        'approved_at',
        'signed_pdf_path',
        'shipping_receipt_number',
        'shipping_receipt_path',
        'shipping_sent_at',
        'verification_token',
    ];

    protected function casts(): array
    {
        return [
            'validated_at' => 'datetime',
            'approved_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'shipping_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
