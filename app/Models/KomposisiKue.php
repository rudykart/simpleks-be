<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KomposisiKue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'bahan_baku_id', 'kue_id', 'jumlah_bb'];

    public function kue()
    {
        return $this->belongsTo(Kue::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
