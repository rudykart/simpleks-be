<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PersediaanBahanBaku extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'keterangan_pbb'];

    public function stokBahanBakus()
    {
        return $this->hasMany(StokBahanBaku::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($pbb) {
            $pbb->stokBahanBakus->each(function ($stokBahanBaku) {
                $stokBahanBaku->delete();
            });
        });
    }
}
