<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiSaldoKoreksi extends Model
{
    protected $table = 'cuti_saldo_koreksi';
    public $timestamps = false;

    protected $fillable = [
        'pegawai_id', 'tahun', 'jenis_koreksi', 'jumlah_hari', 'alasan', 'dikoreksi_oleh',
    ];

    protected $casts = ['created_at' => 'datetime'];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function dikoreksiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikoreksi_oleh');
    }
}
