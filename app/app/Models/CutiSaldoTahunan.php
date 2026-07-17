<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CutiSaldoTahunan extends Model
{
    protected $table = 'cuti_saldo_tahunan';

    protected $fillable = [
        'pegawai_id', 'tahun', 'jatah_tahun_berjalan',
        'carry_over_n1', 'carry_over_n2', 'tambahan_cuti_bersama',
        'terpakai', 'jatah_dibekukan', 'ditangguhkan',
    ];

    protected $casts = [
        'jatah_dibekukan' => 'boolean',
        'ditangguhkan'    => 'boolean',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    // ── Computed (tidak disimpan di DB) ─────────────────────────────────────

    /**
     * Saldo total aktif = semua komponen - terpakai.
     * Selalu dihitung, tidak disimpan, agar konsisten.
     */
    public function getSisaAttribute(): int
    {
        return max(0,
            $this->jatah_tahun_berjalan
            + $this->carry_over_n1
            + $this->carry_over_n2
            + $this->tambahan_cuti_bersama
            - $this->terpakai
        );
    }

    /**
     * Saldo yang benar-benar bisa dipakai (mempertimbangkan jatah_dibekukan).
     * Jika jatah dibekukan (cuti besar/CLTN): hanya carry-over yang bisa dipakai.
     */
    public function getSaldoBisaDipakaiAttribute(): int
    {
        if ($this->jatah_dibekukan) {
            return max(0,
                $this->carry_over_n1
                + $this->carry_over_n2
                - max(0, $this->terpakai - $this->jatah_tahun_berjalan - $this->tambahan_cuti_bersama)
            );
        }
        return $this->sisa;
    }
}
