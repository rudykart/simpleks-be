<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StokBahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'bahan_baku_id',
        'persediaan_bahan_baku_id',
        'stok_bb',
    ];

    public function persediaanBahanBaku()
    {
        return $this->belongsTo(PersediaanBahanBaku::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
