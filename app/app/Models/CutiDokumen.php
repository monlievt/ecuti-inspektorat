<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiDokumen extends Model
{
    protected $table = 'cuti_dokumen';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'jenis_dokumen', 'kategori_dokter', 'path_file',
    ];

    protected $casts = ['uploaded_at' => 'datetime'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(CutiPengajuan::class);
    }

    /** Apakah dokumen ini memenuhi syarat dokter pemerintah (untuk cuti sakit > 14 hari) */
    public function isDokterPemerintah(): bool
    {
        return in_array($this->kategori_dokter, ['pns', 'faskes_pemerintah']);
    }
}
