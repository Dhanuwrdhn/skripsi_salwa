<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'shop_id',
        'nama',
        'harga',
        'foto',
        'kategori',
        'description'
    ];

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
