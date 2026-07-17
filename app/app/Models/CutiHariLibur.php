<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutiHariLibur extends Model
{
    protected $table = 'cuti_hari_libur';
    public $timestamps = false;

    protected $fillable = ['tanggal', 'keterangan'];
    protected $casts    = ['tanggal' => 'date'];
}
