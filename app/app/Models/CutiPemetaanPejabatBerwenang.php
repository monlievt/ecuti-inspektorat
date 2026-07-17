<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiPemetaanPejabatBerwenang extends Model
{
    protected $table = 'cuti_pemetaan_pejabat_berwenang';

    protected $fillable = [
        'unit_kerja_id', 'pejabat_id', 'jenis_cuti_id',
        'nomor_sk_delegasi', 'berlaku_mulai', 'berlaku_sampai',
    ];

    protected $casts = [
        'berlaku_mulai'  => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function pejabat(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pejabat_id');
    }

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(CutiJenis::class);
    }

    public function scopeAktif($query, ?string $tanggal = null)
    {
        $tgl = $tanggal ?? now()->toDateString();
        return $query->where('berlaku_mulai', '<=', $tgl)
            ->where(fn($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tgl));
    }
}
