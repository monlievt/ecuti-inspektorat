<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus file yang tidak perlu (sudah digabung ke cuti_bersama migration)
        // File ini dibuat terpisah tapi schema sudah ada di migration sebelumnya
        // Biarkan kosong agar urutan timestamp tidak bergeser
    }

    public function down(): void
    {
        //
    }
};
