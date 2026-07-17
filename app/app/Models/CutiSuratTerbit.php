<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiSuratTerbit extends Model
{
    protected $table = 'cuti_surat_terbit';
    public $timestamps = false;

    protected $fillable = [
        'pengajuan_id', 'nomor_surat', 'ditandatangani_oleh', 'tanggal_terbit', 'path_pdf',
    ];

    protected $casts = ['tanggal_terbit' => 'date'];

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(CutiPengajuan::class);
    }

    public function ditandatanganiOleh(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'ditandatangani_oleh');
    }
}
