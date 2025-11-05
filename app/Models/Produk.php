<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function lokasis()
    {
        return $this->belongsToMany(Lokasi::class, 'produk_lokasis')
                ->using(ProdukLokasi::class)
                ->withPivot('stok', 'id');
    }

    public function produkLokasis()
    {
        return $this->hasMany(ProdukLokasi::class);
    }
}
