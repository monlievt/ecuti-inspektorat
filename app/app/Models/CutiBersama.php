<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CutiBersama extends Model
{
    protected $table = 'cuti_bersama';
    public $timestamps = false;

    protected $fillable = ['tanggal', 'keterangan', 'nomor_keppres'];
    protected $casts    = ['tanggal' => 'date'];

    public function pengecualian(): HasMany
    {
        return $this->hasMany(CutiBersamaPengecualian::class);
    }
}
