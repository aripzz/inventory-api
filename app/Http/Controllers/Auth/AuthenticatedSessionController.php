<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // $request->authenticate();

        // $request->session()->regenerate();

        // return response()->noContent();
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 2. Coba Otentikasi (Auth::attempt)
        if (! Auth::attempt($request->only('email', 'password'))) {
            // Jika otentikasi gagal, lempar exception JSON
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }

        // 3. Ambil User yang Terotentikasi
        $user = Auth::user();

        // 4. Hapus Token Lama (Opsional, untuk keamanan)
        // Ini memastikan setiap login hanya menghasilkan satu token baru
        $user->tokens()->delete();

        // 5. Generate Personal Access Token (Bearer Token)
        $token = $user->createToken('auth_token')->plainTextToken;

        // 6. Respon Sukses (Mengembalikan Token)
        return response()->json([
            'message' => 'Login berhasil dan token dibuat.',
            'user' => $user->only(['id', 'name', 'email']), // Kirim detail user
            'token' => $token, // Kirim token untuk Authorization header
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
