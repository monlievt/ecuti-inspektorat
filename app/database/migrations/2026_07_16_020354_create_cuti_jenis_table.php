<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_jenis', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique()
                ->comment('tahunan, besar, sakit, melahirkan, alasan_penting, bersama, cltn');
            $table->string('nama', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('aktif')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_jenis');
    }
};
