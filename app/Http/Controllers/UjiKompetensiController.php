<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UjiKompetensiController extends Controller
{
    public function checkAvailability(Request $request)
    {
        $user = Auth::user();

        // Cek apakah user sudah diverifikasi oleh admin
        $canTakeTest = $user->is_verified == 0;

        return response()->json([
            'can_take_test' => $canTakeTest,
            'remaining_seconds' => 0, // Tidak perlu countdown karena pakai is_verified
            'message' => $canTakeTest 
                ? 'Selamat, Anda dapat mengikuti Pemetaan Data Guru sekarang!' 
                : 'Anda sudah menyelesaikan Uji Kompetensi dan sudah dinilai oleh pihak IPBI. Silahkan cek nilai final anda di Profile.'
        ]);
    }

    public function submitCompetency(Request $request)
    {
        $user = Auth::user();
        $temporaryScore = $request->input('temporary_score');

        // Logika penyimpanan skor atau submission
        $user->submissions()->create([
            'temporary_score' => $temporaryScore,
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Submission successful']);
    }

    public function submit(Request $request)
    {
        $user = Auth::user();

        // Update last_submission_date di tabel users
        $user->update([
            'last_submission_date' => now(),
        ]);

        return response()->json([
            'message' => 'Pemetaan data berhasil dikumpulkan!',
            'last_submission_date' => $user->last_submission_date,
        ]);
    }
}