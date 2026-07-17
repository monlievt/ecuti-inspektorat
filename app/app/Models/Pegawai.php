<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    protected $fillable = [
        'user_id', 'nip', 'nip_lama', 'nama_lengkap', 'jenis_kelamin',
        'tmt_cpns', 'tmt_pns', 'pangkat_golongan', 'jabatan',
        'unit_kerja_id', 'jenis_pegawai', 'nomor_hp', 'alamat', 'foto', 'aktif',
    ];

    protected $casts = [
        'tmt_cpns' => 'date',
        'tmt_pns'  => 'date',
        'aktif'    => 'boolean',
    ];

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function pengajuanCuti(): HasMany
    {
        return $this->hasMany(CutiPengajuan::class);
    }

    public function saldoTahunan(): HasMany
    {
        return $this->hasMany(CutiSaldoTahunan::class);
    }

    public function saldoTahunIni(): HasOne
    {
        return $this->hasOne(CutiSaldoTahunan::class)
            ->where('tahun', now()->year)
            ->latestOfMany('tahun');
    }

    public function saldoKoreksi(): HasMany
    {
        return $this->hasMany(CutiSaldoKoreksi::class);
    }

    public function pemetaanAtasanAktif(): HasMany
    {
        return $this->hasMany(CutiPemetaanAtasan::class, 'pegawai_id')
            ->where('berlaku_mulai', '<=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now()->toDateString()));
    }

    // ── Computed helpers ────────────────────────────────────────────────────

    /**
     * Hitung masa kerja dalam bulan (dari tmt_cpns).
     */
    public function getMasaKerjaBulanAttribute(): int
    {
        return $this->tmt_cpns->diffInMonths(now());
    }

    /**
     * Cek apakah memenuhi syarat masa kerja minimal untuk cuti tahunan (12 bulan).
     */
    public function getMemenuhiSyaratCutiTahunanAttribute(): bool
    {
        return $this->masa_kerja_bulan >= 12;
    }
}
