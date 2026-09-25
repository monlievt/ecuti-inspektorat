<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Services\SuratCutiPdfService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $pegawaiList = DB::table('pegawai')->select('id', 'pangkat_golongan')->get();

        foreach ($pegawaiList as $p) {
            if (!empty($p->pangkat_golongan) && $p->pangkat_golongan !== '-') {
                $formatted = SuratCutiPdfService::formatPangkatGolongan($p->pangkat_golongan);
                if ($formatted !== $p->pangkat_golongan) {
                    DB::table('pegawai')
                        ->where('id', $p->id)
                        ->update(['pangkat_golongan' => $formatted]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed as title case normalization is idempotent and standard
    }
};
