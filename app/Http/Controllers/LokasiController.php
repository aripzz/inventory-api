<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use Illuminate\Validation\ValidationException;

class LokasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Lokasi::with('produks')->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         try {
            $validated = $request->validate([
                'kode_lokasi' => 'required|unique:lokasis|string|max:255',
                'nama_lokasi' => 'required|string|max:255',
            ]);

            $lokasi = Lokasi::create($validated);

            return response()->json($lokasi, 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lokasi $lokasi)
    {
        return response()->json($lokasi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lokasi $lokasi)
    {
        try {
            $validated = $request->validate([
                'kode_produk' => 'sometimes|unique:produks,kode_produk,' . $lokasi->id . '|string|max:255',
                'nama_produk' => 'sometimes|string|max:255',
                'kategori' => 'sometimes|string|max:255',
                'satuan' => 'sometimes|string|max:50',
            ]);

            Lokasi::update($validated);

            return response()->json($lokasi);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Lokasi::where('id',$id)->delete();
        return response()->json(null, 204);
    }
}
