<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lokasi extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function produks()
    {
        return $this->belongsToMany(Produk::class, 'produk_lokasi', 'lokasi_id', 'produk_id')
                    ->using(ProdukLokasi::class)
                    ->withPivot('stok', 'id');
    }
}
