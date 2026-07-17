<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CutiPengajuan extends Model
{
    protected $table = 'cuti_pengajuan';

    protected $fillable = [
        'nomor_pengajuan', 'pegawai_id', 'jenis_cuti_id', 'alasan',
        'alasan_kategori', 'tanggal_mulai', 'tanggal_selesai',
        'jumlah_hari_kerja', 'satuan_hari', 'alamat_selama_cuti',
        'telp_selama_cuti', 'status', 'dibuat_via',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    // Status constants — gunakan ini, bukan string literal
    const STATUS_DIAJUKAN                = 'diajukan';
    const STATUS_MENUNGGU_ATASAN         = 'menunggu_atasan';
    const STATUS_DISETUJUI_ATASAN        = 'disetujui_atasan';
    const STATUS_DITOLAK_ATASAN          = 'ditolak_atasan';
    const STATUS_DIREVISI                = 'direvisi';
    const STATUS_MENUNGGU_PYBMC          = 'menunggu_pyBMC';
    const STATUS_DISETUJUI_PYBMC         = 'disetujui_pyBMC';
    const STATUS_DITANGGUHKAN_PYBMC      = 'ditangguhkan_pyBMC';
    const STATUS_DITOLAK_PYBMC           = 'ditolak_pyBMC';
    const STATUS_IZIN_SEMENTARA_AKTIF    = 'izin_sementara_aktif';
    const STATUS_MENUNGGU_RATIFIKASI     = 'menunggu_ratifikasi';
    const STATUS_DIRATIFIKASI            = 'diratifikasi';
    const STATUS_DITOLAK_RATIFIKASI      = 'ditolak_ratifikasi';
    const STATUS_DITERBITKAN             = 'diterbitkan';
    const STATUS_DIPANGGIL_KEMBALI       = 'dipanggil_kembali';

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(CutiJenis::class, 'jenis_cuti_id');
    }

    public function approvalLogs(): HasMany
    {
        return $this->hasMany(CutiApprovalLog::class, 'pengajuan_id')->orderBy('created_at');
    }

    public function dokumen(): HasMany
    {
        return $this->hasMany(CutiDokumen::class, 'pengajuan_id');
    }

    public function suratTerbit(): HasOne
    {
        return $this->hasOne(CutiSuratTerbit::class, 'pengajuan_id');
    }

    public function cltn(): HasOne
    {
        return $this->hasOne(CutiLuarTanggunganNegara::class, 'pengajuan_id');
    }

    // ── Scopes ──────────────────────────────────────────────────────────────

    public function scopeDiterbitkan($query)
    {
        return $query->where('status', self::STATUS_DITERBITKAN);
    }

    public function scopeMenungguAtasan($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU_ATASAN);
    }

    public function scopeMenungguPyBMC($query)
    {
        return $query->where('status', self::STATUS_MENUNGGU_PYBMC);
    }

    // ── Helpers ─────────────────────────────────────────────────────────────

    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_DITOLAK_ATASAN,
            self::STATUS_DITOLAK_PYBMC,
            self::STATUS_DITANGGUHKAN_PYBMC,
            self::STATUS_DITOLAK_RATIFIKASI,
            self::STATUS_DITERBITKAN,
            self::STATUS_DIPANGGIL_KEMBALI,
        ]);
    }
}
