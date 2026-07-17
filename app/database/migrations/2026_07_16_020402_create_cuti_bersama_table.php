<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_bersama', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal')->unique();
            $table->string('keterangan', 255);
            $table->string('nomor_keppres', 100)->nullable();
        });

        Schema::create('cuti_bersama_pengecualian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuti_bersama_id')->constrained('cuti_bersama')->cascadeOnDelete();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete();
            $table->string('keterangan', 255)->nullable()->comment('mis. tugas piket lebaran');

            $table->unique(['cuti_bersama_id', 'pegawai_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_bersama_pengecualian');
        Schema::dropIfExists('cuti_bersama');
    }
};
