<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    public function forgotPassword(Request $request)
    {
        // Fitur forgotPassword dengan token telah dinonaktifkan
        return response()->json(['message' => 'Feature disabled.'], 400);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'password' => 'required|confirmed|min:8',
        ], [
            'email.required' => 'Email atau Nomor HP terdaftar wajib diisi.',
            'tanggal_lahir.required' => 'Tanggal lahir wajib diisi untuk verifikasi identitas.',
            'tanggal_lahir.date' => 'Format tanggal lahir tidak valid.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
        ]);

        $identifier = trim($request->email);

        // Cari user berdasarkan email atau NoHp
        $user = User::where('email', $identifier)
                    ->orWhere('NoHp', $identifier)
                    ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Akun dengan email atau nomor HP tersebut tidak ditemukan.'
            ], 404);
        }

        if (empty($user->tanggal_lahir)) {
            return response()->json([
                'message' => 'Akun ini belum memiliki data tanggal lahir terdaftar. Silakan hubungi admin untuk bantuan pengaturan kata sandi.'
            ], 422);
        }

        // Bandingkan tanggal lahir dalam format Y-m-d
        $inputDate = date('Y-m-d', strtotime($request->tanggal_lahir));
        $userDate = date('Y-m-d', strtotime($user->tanggal_lahir));

        if ($inputDate !== $userDate) {
            return response()->json([
                'message' => 'Tanggal lahir tidak sesuai dengan data akun Anda. Silakan periksa kembali.'
            ], 422);
        }

        // Update password user
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Kata sandi berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.'
        ], 200);
    }
}