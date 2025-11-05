<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Mutasi;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $produks = Produk::with('lokasis')->get(); // Sertakan lokasi
        return response()->json($produks);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'kode_produk' => 'required|unique:produks|string|max:255',
                'nama_produk' => 'required|string|max:255',
                'kategori' => 'required|string|max:255',
                'satuan' => 'required|string|max:50',
            ]);

            $produk = Produk::create($validated);

            // Output data berupa JSON (Requirement 11)
            return response()->json($produk, 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produk $produk)
    {
        return response()->json($produk->load('lokasis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        try {
            $validated = $request->validate([
                'kode_produk' => 'sometimes|unique:produks,kode_produk,' . $produk->id . '|string|max:255',
                'nama_produk' => 'sometimes|string|max:255',
                'kategori' => 'sometimes|string|max:255',
                'satuan' => 'sometimes|string|max:50',
            ]);

            $produk->update($validated);

            return response()->json($produk);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Produk::where('id',$id)->delete();
        return response()->json(null, 204);
    }

    /**
     * Menampilkan history mutasi untuk produk tertentu.
     * Menggunakan relasi hasManyThrough atau join manual.
     */
    public function historyMutasi(Produk $produk)
    {
        $produkLokasiIds = $produk->produkLokasis()->pluck('id');
        $history = Mutasi::whereIn('produk_lokasi_id', $produkLokasiIds)
            ->with('user', 'produkLokasi.lokasi')
            ->orderBy('tanggal', 'desc')
            ->latest()
            ->get();
        return response()->json([
            'produk' => $produk->only('id', 'nama_produk', 'kode_produk'),
            'history_mutasi' => $history
        ]);
    }

    /**
     * Menetapkan atau memperbarui stok produk di lokasi tertentu.
     * Ini berfungsi sebagai CRUD untuk tabel ProdukLokasi (pivot).
     */
    public function setStok(Request $request, Produk $produk)
    {
        try {
            $validated = $request->validate([
                'lokasi_id' => 'required|exists:lokasis,id',
                'stok' => 'required|integer|min:0',
            ]);

            $produk->lokasis()->syncWithoutDetaching([
                $validated['lokasi_id'] => ['stok' => $validated['stok']]
            ]);

            $produkLokasi = $produk->lokasis()->where('lokasi_id', $validated['lokasi_id'])->first()->pivot;

            return response()->json([
                'message' => 'Stok berhasil diperbarui/ditetapkan.',
                'produk_lokasi' => $produkLokasi
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
