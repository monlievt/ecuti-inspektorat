<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_saldo_koreksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->year('tahun');
            $table->enum('jenis_koreksi', ['tambah', 'kurang']);
            $table->tinyInteger('jumlah_hari')->unsigned();
            $table->text('alasan')->comment('Wajib diisi — termasuk untuk input saldo awal migrasi');
            $table->foreignId('dikoreksi_oleh')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
            // Tidak ada updated_at — tabel ini immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_saldo_koreksi');
    }
};
