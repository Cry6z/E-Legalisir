<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $table = 'status';

    protected $fillable = [
        'code',
        'name',
        'sort_order',
        'is_final',
    ];

    protected function casts(): array
    {
        return [
            'is_final' => 'boolean',
        ];
    }

    public function pengajuans(): HasMany
    {
        return $this->hasMany(Pengajuan::class, 'status_id');
    }
}
