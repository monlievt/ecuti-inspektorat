<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiPemetaanAtasan extends Model
{
    protected $table = 'cuti_pemetaan_atasan';

    protected $fillable = ['pegawai_id', 'atasan_id', 'berlaku_mulai', 'berlaku_sampai'];

    protected $casts = [
        'berlaku_mulai'  => 'date',
        'berlaku_sampai' => 'date',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'atasan_id');
    }

    public function scopeAktif($query, ?string $tanggal = null)
    {
        $tgl = $tanggal ?? now()->toDateString();
        return $query->where('berlaku_mulai', '<=', $tgl)
            ->where(fn($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', $tgl));
    }
}
