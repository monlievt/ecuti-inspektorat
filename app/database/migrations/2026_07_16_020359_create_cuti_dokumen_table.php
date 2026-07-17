<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuti_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('cuti_pengajuan')->cascadeOnDelete();
            $table->string('jenis_dokumen', 50)
                ->comment('surat_dokter, surat_rawat_inap, surat_keterangan_rt, jadwal_haji, dst');
            $table->enum('kategori_dokter', ['pns', 'faskes_pemerintah', 'swasta'])->nullable()
                ->comment('Khusus untuk validasi cuti sakit > 14 hari');
            $table->string('path_file', 255)->comment('Path di storage/app (private)');
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuti_dokumen');
    }
};
