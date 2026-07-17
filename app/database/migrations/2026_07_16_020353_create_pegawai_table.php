<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('nip', 18)->unique()->comment('NIP 18 digit');
            $table->string('nip_lama', 9)->nullable()->comment('NIP lama 9 digit');
            $table->string('nama_lengkap', 150);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tmt_cpns')->comment('Tanggal mulai tugas CPNS — dasar hitung masa kerja');
            $table->date('tmt_pns')->nullable()->comment('Tanggal diangkat PNS');
            $table->string('pangkat_golongan', 30)->comment('mis. III/b, IV/a');
            $table->string('jabatan', 150);
            $table->foreignId('unit_kerja_id')->constrained('unit_kerja');
            $table->enum('jenis_pegawai', ['PNS', 'CPNS', 'PPPK'])->default('PNS');
            $table->string('nomor_hp', 20)->nullable()->comment('Untuk notifikasi WhatsApp');
            $table->text('alamat')->nullable();
            $table->string('foto', 255)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
