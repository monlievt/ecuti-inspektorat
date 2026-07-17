<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiJenis extends Model
{
    protected $table = 'cuti_jenis';
    public $timestamps = false;

    protected $fillable = ['kode', 'nama', 'deskripsi', 'aktif'];
    protected $casts    = ['aktif' => 'boolean'];

    // Kode constants
    const TAHUNAN        = 'tahunan';
    const BESAR          = 'besar';
    const SAKIT          = 'sakit';
    const MELAHIRKAN     = 'melahirkan';
    const ALASAN_PENTING = 'alasan_penting';
    const BERSAMA        = 'bersama';
    const CLTN           = 'cltn';

    public function pengajuan(): HasMany
    {
        return $this->hasMany(CutiPengajuan::class, 'jenis_cuti_id');
    }
}
