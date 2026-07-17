<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_luar_tanggungan_negara', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->enum('status_bkn', [
                'menunggu_diajukan_ke_bkn',
                'menunggu_jawaban_bkn',
                'disetujui_bkn',
                'ditolak_bkn',
            ])->default('menunggu_diajukan_ke_bkn');
            $table->string('nomor_surat_ke_bkn', 100)->nullable();
            $table->date('tanggal_surat_ke_bkn')->nullable();
            $table->string('dokumen_persetujuan_bkn_path', 255)->nullable()
                ->comment('Scan surat balasan BKN, di storage/app private');
            $table->date('tanggal_lapor_diri')->nullable()
                ->comment('Diisi setelah CLTN selesai oleh pegawai');
            $table->enum('status_pengaktifan_kembali', [
                'belum', 'diajukan', 'disetujui', 'tidak_ada_lowongan',
            ])->nullable();
        });

        Schema::create('cuti_surat_terbit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->string('nomor_surat', 100)->unique()
                ->comment('Format dikonfirmasi ke Admin Inspektorat');
            $table->foreignId('ditandatangani_oleh')->constrained('pegawai');
            $table->date('tanggal_terbit');
            $table->string('path_pdf', 255)->comment('Path PDF di storage/app private');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_surat_terbit');
        Schema::dropIfExists('cuti_luar_tanggungan_negara');
    }
};
