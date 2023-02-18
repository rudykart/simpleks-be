<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DataHitung extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'kue_id',
        'persediaan_bahan_baku_id',
        'hitung_id',
    ];

    public function kue()
    {
        return $this->belongsTo(Kue::class);
    }

    public function persediaanBahanBaku()
    {
        return $this->belongsTo(PersediaanBahanBaku::class);
    }
}
