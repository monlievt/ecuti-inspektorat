<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiLuarTanggunganNegara extends Model
{
    protected $table = 'cuti_luar_tanggungan_negara';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'status_bkn', 'nomor_surat_ke_bkn',
        'tanggal_surat_ke_bkn', 'dokumen_persetujuan_bkn_path',
        'tanggal_lapor_diri', 'status_pengaktifan_kembali',
    ];

    protected $casts = [
        'tanggal_surat_ke_bkn' => 'date',
        'tanggal_lapor_diri'   => 'date',
    ];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(CutiPengajuan::class);
    }
}
