<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_saldo_tahunan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->year('tahun');
            $table->tinyInteger('jatah_tahun_berjalan')->default(12)->unsigned();
            $table->tinyInteger('carry_over_n1')->default(0)->unsigned()
                ->comment('Sisa dari tahun N-1, maks 6 hari');
            $table->tinyInteger('carry_over_n2')->default(0)->unsigned()
                ->comment('Sisa dari tahun N-2, maks 6 hari, hangus di akhir tahun ini');
            $table->tinyInteger('tambahan_cuti_bersama')->default(0)->unsigned()
                ->comment('Dari pengecualian cuti bersama, tidak carry-over');
            $table->tinyInteger('terpakai')->default(0)->unsigned()
                ->comment('Akumulasi hari yang sudah dipotong dari pengajuan disetujui');
            $table->boolean('jatah_dibekukan')->default(false)
                ->comment('True jika cuti besar/CLTN disetujui — jatah_tahun_berjalan tidak bisa dipakai');
            $table->boolean('ditangguhkan')->default(false)
                ->comment('True jika PyBMC menangguhkan cuti tahunan tahun ini');
            $table->timestamps();

            $table->unique(['pegawai_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_saldo_tahunan');
    }
};
