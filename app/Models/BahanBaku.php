<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama_bb', 'user_id'];

    public function stokBahanBakus()
    {
        return $this->hasMany(StokBahanBaku::class);
    }
}