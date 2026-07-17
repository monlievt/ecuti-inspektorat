<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_approval_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->string('status_sebelum', 50);
            $table->string('status_sesudah', 50);
            $table->foreignId('aktor_id')->constrained('users');
            $table->string('peran_aktor', 50)
                ->comment('atasan_langsung, pyBMC, admin, sistem');
            $table->text('catatan')->nullable()
                ->comment('Wajib diisi untuk tolak/tangguhkan/revisi');
            $table->timestamp('created_at')->useCurrent();
            // Tidak ada updated_at — tabel ini IMMUTABLE (append-only)

            $table->index(['pengajuan_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_approval_log');
    }
};
