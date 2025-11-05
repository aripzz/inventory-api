<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Mutasi extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi ke User (requirement 9)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke ProdukLokasi (requirement 9)
    public function produkLokasis()
    {
        return $this->belongsTo(ProdukLokasi::class, 'produk_lokasi_id');
    }
}
