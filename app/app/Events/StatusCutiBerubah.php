<?php

namespace App\Events;

use App\Models\CutiPengajuan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusCutiBerubah
{
    use Dispatchable, SerializesModels;

    public CutiPengajuan $pengajuan;

    public function __construct(CutiPengajuan $pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }
}
