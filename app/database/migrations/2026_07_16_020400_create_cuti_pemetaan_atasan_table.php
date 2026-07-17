<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_pemetaan_atasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pegawai_id')->constrained('pegawai')->cascadeOnDelete()
                ->comment('Bawahan');
            $table->foreignId('atasan_id')->constrained('pegawai')
                ->comment('Atasan langsung');
            $table->date('berlaku_mulai');
            $table->date('berlaku_sampai')->nullable()->comment('Null = masih berlaku');
            $table->timestamps();

            $table->index(['pegawai_id', 'berlaku_sampai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_pemetaan_atasan');
    }
};
