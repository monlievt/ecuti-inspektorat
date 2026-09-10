<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'bisa_beri_izin_sementara',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'password'                => 'hashed',
        'bisa_beri_izin_sementara'=> 'boolean',
    ];

    public function pegawai(): HasOne
    {
        return $this->hasOne(Pegawai::class);
    }

    // ── Role helpers ────────────────────────────────────────────────────────

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdminCuti(): bool
    {
        return in_array($this->role, ['admin_cuti', 'super_admin']);
    }

    public function isPegawai(): bool
    {
        return true; // semua user bisa berperan sebagai pegawai
    }

    /**
     * Cek apakah user ini adalah atasan langsung dari pegawai tertentu (hari ini).
     */
    public function isAtasanDari(Pegawai $bawahan): bool
    {
        return CutiPemetaanAtasan::where('pegawai_id', $bawahan->id)
            ->where('atasan_id', $this->pegawai?->id)
            ->where('berlaku_mulai', '<=', now()->toDateString())
            ->where(fn($q) => $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now()->toDateString()))
            ->exists();
    }

    /**
     * Cek apakah user berhak mengakses monitoring & laporan (Admin Cuti, PyBMC, Atasan, atau Pimpinan OPD).
     */
    public function isPimpinanOrAtasan(): bool
    {
        if ($this->isAdminCuti()) {
            return true;
        }

        if (!$this->pegawai) {
            return false;
        }

        // Cek apakah user adalah atasan bagi pegawai lain
        $isAtasan = CutiPemetaanAtasan::where('atasan_id', $this->pegawai->id)->aktif()->exists();
        if ($isAtasan) {
            return true;
        }

        // Cek apakah memiliki delegasi PyBMC aktif
        $isPyBMC = CutiPemetaanPejabatBerwenang::where('pejabat_id', $this->pegawai->id)->aktif()->exists();
        if ($isPyBMC) {
            return true;
        }

        // Cek jabatan pimpinan (Inspektur, Sekretaris, Irban)
        $jabatanUpper = strtoupper($this->pegawai->jabatan ?? '');
        if (\Illuminate\Support\Str::contains($jabatanUpper, ['INSPEKTUR', 'SEKRETARIS', 'IRBAN'])) {
            return true;
        }

        return false;
    }
}
