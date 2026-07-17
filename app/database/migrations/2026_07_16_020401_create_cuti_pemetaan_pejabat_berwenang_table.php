<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_pemetaan_pejabat_berwenang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_kerja_id')->nullable()->constrained('unit_kerja')->nullOnDelete();
            $table->foreignId('pejabat_id')->constrained('pegawai')
                ->comment('Pegawai yang didelegasikan wewenang PyBMC');
            $table->foreignId('jenis_cuti_id')->constrained('cuti_jenis')
                ->comment('Delegasi bisa dibatasi per jenis cuti (CLTN tidak bisa didelegasikan)');
            $table->string('nomor_sk_delegasi', 100)->nullable()
                ->comment('Nomor SK pendelegasian wewenang');
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai')->nullable();
            $table->timestamps();

            $table->index(['unit_kerja_id', 'jenis_cuti_id', 'berlaku_sampai'], 'idx_pejabat_berwenang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_pemetaan_pejabat_berwenang');
    }
};
