<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_pengajuan', 50)->unique()
                ->comment('Auto-generated, format dikonfigurasi Admin');
            $table->foreignId('pegawai_id')->constrained('pegawai');
            $table->foreignId('jenis_cuti_id')->constrained('cuti_jenis');
            $table->text('alasan');
            $table->string('alasan_kategori', 50)->nullable()
                ->comment('Khusus cuti alasan penting: keluarga_sakit_keras, menikah, dst');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->smallInteger('jumlah_hari_kerja')->unsigned()
                ->comment('Dihitung otomatis, exclude weekend & libur kecuali melahirkan/besar/CLTN');
            $table->enum('satuan_hari', ['hari_kerja', 'hari_kalender'])->default('hari_kerja');
            $table->string('alamat_selama_cuti', 255)->nullable();
            $table->string('telp_selama_cuti', 30)->nullable();
            $table->enum('status', [
                'diajukan',
                'menunggu_atasan',
                'disetujui_atasan',
                'ditolak_atasan',
                'direvisi',
                'menunggu_pyBMC',
                'disetujui_pyBMC',
                'ditangguhkan_pyBMC',
                'ditolak_pyBMC',
                'izin_sementara_aktif',
                'menunggu_ratifikasi',
                'diratifikasi',
                'ditolak_ratifikasi',
                'diterbitkan',
                'dipanggil_kembali',
            ])->default('diajukan');
            $table->enum('dibuat_via', ['normal', 'izin_sementara'])->default('normal');
            $table->timestamps();

            $table->index(['pegawai_id', 'status']);
            $table->index(['status', 'jenis_cuti_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_pengajuan');
    }
};
