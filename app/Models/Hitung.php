<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Hitung extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'keterangan_hitung'];

    public function dataHitungs()
    {
        return $this->hasMany(DataHitung::class);
    }

    public function dataHitung()
    {
        return $this->hasOne(DataHitung::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($hitung) {
            $hitung->dataHitungs->each(function ($dataHitung) {
                $dataHitung->delete();
            });
        });
    }
}
