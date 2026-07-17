<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitKerja extends Model
{
    protected $table = 'unit_kerja';

    protected $fillable = ['kode', 'nama', 'parent_id', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    /** Sub-unit di bawahnya */
    public function children(): HasMany
    {
        return $this->hasMany(UnitKerja::class, 'parent_id');
    }

    /** Unit kerja induk */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    /** Pegawai di unit ini */
    public function pegawai(): HasMany
    {
        return $this->hasMany(Pegawai::class);
    }
}
