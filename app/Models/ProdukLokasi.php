<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProdukLokasi extends Pivot
{
    use HasFactory;
    protected $table = 'produk_lokasis';
    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }
    public function mutasis()
    {
        return $this->hasMany(Mutasi::class);
    }
}
