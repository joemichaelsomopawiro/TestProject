<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProfileController extends Controller
{
    private function formatUserData($user)
    {
        $nilai = $user->is_verified ? $user->nilai : $user->temporary_score;
        $profilePictureUrl = $user->profile_picture ? asset('storage/' . $user->profile_picture) : null;

        $tanggalLahir = null;
        if (!empty($user->tanggal_lahir)) {
            $tanggalLahir = $user->tanggal_lahir instanceof Carbon 
                ? $user->tanggal_lahir->format('Y-m-d') 
                : Carbon::parse($user->tanggal_lahir)->format('Y-m-d');
        }

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'is_admin' => (bool)$user->is_admin,
            'NoHp' => $user->NoHp,
            'pekerjaan' => $user->pekerjaan,
            'tanggal_lahir' => $tanggalLahir,
            'domisili' => $user->domisili,
            'informasi_ipbi' => $user->informasi_ipbi,
            'profile_picture' => $user->profile_picture,
            'profile_picture_url' => $profilePictureUrl,
            'nilai' => $nilai,
            'temporary_score' => $user->temporary_score,
            'is_verified' => (bool)$user->is_verified,
            'can_take_test' => (bool)$user->can_take_test,
            'status' => $user->status,
        ];
    }

    public function show()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        return response()->json($this->formatUserData($user));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255|unique:users,email,' . $user->id,
            'NoHp' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'domisili' => 'nullable|string|max:255',
            'informasi_ipbi' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json($this->formatUserData($user));
    }

    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
        ]);

        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        // Delete old profile picture if exists
        if ($user->profile_picture) {
            $oldPath = public_path('storage/' . $user->profile_picture);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        // Store new profile picture
        $file = $request->file('profile_picture');
        $filename = time() . '_' . $user->id . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('profile-pictures', $filename, 'public');

        // Update user profile_picture field
        $user->profile_picture = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile picture berhasil diupload',
            'data' => [
                'profile_picture' => $path,
                'profile_picture_url' => asset('storage/' . $path)
            ]
        ], 200);
    }

    public function deleteProfilePicture(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        if ($user->profile_picture) {
            $oldPath = public_path('storage/' . $user->profile_picture);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }

            $user->profile_picture = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture berhasil dihapus'
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Profile picture tidak ditemukan'
        ], 404);
    }

    public function submitCompetency(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'temporary_score' => 'required|integer', // Menerima totalNilai dari frontend
        ]);

        // Simpan nilai sementara dari frontend ke temporary_score
        $user->temporary_score = $validated['temporary_score'];
        $user->save();

        return response()->json([
            'message' => 'Nilai sementara berhasil disimpan',
            'temporary_score' => $user->temporary_score,
        ]);
    }

    public function destroy()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(null, 204);
    }
}