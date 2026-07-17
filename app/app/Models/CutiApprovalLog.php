<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiApprovalLog extends Model
{
    protected $table = 'cuti_approval_log';

    // Append-only — tidak ada update
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'status_sebelum', 'status_sesudah',
        'aktor_id', 'peran_aktor', 'catatan',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(CutiPengajuan::class);
    }

    public function aktor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aktor_id');
    }
}
