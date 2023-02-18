<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'nama_kue', 'keuntungan_kue'];

    public function komposisiKues()
    {
        return $this->hasMany(KomposisiKue::class);
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($kue) {
            $kue->komposisiKues->each(function ($komposisiKue) {
                $komposisiKue->delete();
            });
        });
    }
}