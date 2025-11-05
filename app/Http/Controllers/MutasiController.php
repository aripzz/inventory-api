<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mutasi;
use App\Models\User;
use App\Models\ProdukLokasi;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class MutasiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Mutasi::with('user', 'produkLokasis.produk', 'produkLokasis.lokasi')->latest()->get());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'produk_lokasi_id' => 'required|exists:produk_lokasis,id',
                'tanggal' => 'required|date',
                'jenis_mutasi' => 'required|in:MASUK,KELUAR',
                'jumlah' => 'required|integer|min:1',
                'keterangan' => 'nullable|string|max:500',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
        return DB::transaction(function () use ($validated, $request) {

            $produkLokasi = ProdukLokasi::findOrFail($validated['produk_lokasi_id']);
            $jumlahMutasi = $validated['jumlah'];
            $jenisMutasi = $validated['jenis_mutasi'];
            $stokLama = $produkLokasi->stok;
            if ($jenisMutasi == 'MASUK') {
                $stokBaru = $stokLama + $jumlahMutasi;
            } else {
                if ($stokLama < $jumlahMutasi) {
                    throw ValidationException::withMessages([
                        'jumlah' => ['Stok tidak mencukupi untuk mutasi keluar ini. Stok saat ini: ' . $stokLama]
                    ]);
                }
                $stokBaru = $stokLama - $jumlahMutasi;
            }
            $produkLokasi->update(['stok' => $stokBaru]);
            $mutasi = Mutasi::create([
                'user_id' => $request->user()->id,
                'produk_lokasi_id' => $validated['produk_lokasi_id'],
                'tanggal' => $validated['tanggal'],
                'jenis_mutasi' => $jenisMutasi,
                'jumlah' => $jumlahMutasi,
                'keterangan' => $validated['keterangan'],
            ]);

            return response()->json([
                'message' => 'Mutasi berhasil dicatat dan stok diperbarui.',
                'mutasi' => $mutasi->load('user', 'produkLokasi'),
                'stok_lama' => $stokLama,
                'stok_baru' => $stokBaru,
            ], 201);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Mutasi $mutasi)
    {
        return response()->json($mutasi->load('user', 'produkLokasis.produk', 'produkLokasis.lokasi'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mutasi $mutasi)
    {
        $validated = $request->validate([
            'keterangan' => 'sometimes|nullable|string|max:500',
        ]);

        $mutasi->update($validated);

        return response()->json([
            'message' => 'Keterangan mutasi berhasil diperbarui.',
            'mutasi' => $mutasi
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mutasi $mutasi)
    {
        return DB::transaction(function () use ($mutasi) {
            $produkLokasi = $mutasi->produkLokasis;
            $jumlah = $mutasi->jumlah;
            $jenis = $mutasi->jenis_mutasi;
            if ($jenis == 'MASUK') {
                if ($produkLokasi->stok < $jumlah) {
                    throw new \Exception("Gagal membatalkan mutasi MASUK, stok tidak cukup.");
                }
                $produkLokasi->decrement('stok', $jumlah);
            } else {
                $produkLokasi->increment('stok', $jumlah);
            }

            $mutasi->delete();

            return response()->json([
                'message' => 'Mutasi berhasil dihapus dan stok dikembalikan.',
                'stok_baru' => $produkLokasi->fresh()->stok
            ], 204);
        });
    }

    /**
     * Menampilkan history mutasi untuk user tertentu.
     */
    public function historyMutasiByUser(User $user)
    {
        // Ambil semua Mutasi yang dibuat oleh user ini
        $history = $user->mutasis()
            ->with('produkLokasis.produk', 'produkLokasis.lokasi') // Tampilkan produk dan lokasi
            ->orderBy('tanggal', 'desc')
            ->latest()
            ->get();

        return response()->json([
            'user' => $user->only('id', 'name', 'email'),
            'history_mutasi' => $history
        ]);
    }
}
