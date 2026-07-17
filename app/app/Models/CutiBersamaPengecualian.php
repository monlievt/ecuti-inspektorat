<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiBersamaPengecualian extends Model
{
    protected $table = 'cuti_bersama_pengecualian';
    public $timestamps = false;

    protected $fillable = ['cuti_bersama_id', 'pegawai_id', 'keterangan'];

    public function cutiBersama(): BelongsTo
    {
        return $this->belongsTo(CutiBersama::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
