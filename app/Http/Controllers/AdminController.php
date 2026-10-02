<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Exports\TeachersWithSoalExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Soal1;
use App\Models\Soal2;
use App\Models\Soal3;
use App\Models\Soal4;
use App\Models\Soal5;
use App\Models\Soal6;
use App\Models\Soal7;
use App\Models\Soal8;
use App\Models\Soal9;
use App\Models\Soal10;
use App\Models\Soal11;
use App\Models\Soal12;
use App\Models\Soal13;
use App\Models\Soal14;
use App\Models\Soal15;
use App\Models\Soal16;
use App\Models\Soal17;
use App\Models\Comment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::whereNotNull('last_submission_date')
            ->get(['id', 'name', 'email', 'created_at', 'last_submission_date', 'is_verified', 'nilai']);
        return response()->json(['data' => $users], 200);
    }

    public function publicTeachers()
    {
        try {
            // Ambil data users yang bukan admin dan punya domisili
            $teachers = User::where('is_admin', false)
                ->whereNotNull('domisili')
                ->where('domisili', '!=', '')
                ->select('id', 'name', 'domisili', 'NoHp', 'nilai')
                ->get();

            return response()->json([
                'message' => 'Data guru berhasil diambil',
                'data' => $teachers,
                'count' => $teachers->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengambil data guru',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email atau password salah'], 401);
        }

        if (!$user->is_admin) {
            return response()->json(['message' => 'Anda bukan admin'], 403);
        }

        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => $user->only(['id', 'name', 'email', 'is_admin']),
        ], 200);
    }

    private function calculateTotalNilai($userId)
    {
        $overview = [];
        for ($i = 1; $i <= 17; $i++) {
            $model = "\\App\\Models\\Soal{$i}";
            $exists = class_exists($model) && $model::where('user_id', $userId)->exists();
            $nilai = $exists ? $model::where('user_id', $userId)->value('nilai') ?? 0 : 0;
            $overview["soal{$i}"] = ['nilai' => $nilai];
        }
        return array_sum(array_column($overview, 'nilai'));
    }

    private function updateSoalNilai($soalNumber, $userId)
    {
        $controllerClass = "\\App\\Http\\Controllers\\Soal{$soalNumber}Controller";
        if (class_exists($controllerClass)) {
            $controller = app($controllerClass);
            $soal = ("\\App\\Models\\Soal{$soalNumber}")::where('user_id', $userId)->first();
            if ($soal) {
                $data = $soal->toArray();
                $request = new Request($data);
                $controller->update($request);
            }
        }
    }

    private function getDescriptiveFieldName($soalNumber, $fieldName)
    {
        $fieldNameMap = [
            '1' => [
                'tingkat_pendidikan' => 'Tingkat Pendidikan',
                'tingkat_pendidikan_file' => 'Berkas Tingkat Pendidikan',
            ],
            '2' => [
                'tp3' => 'TP3',
                'lpmp_diknas' => 'LPMP Diknas',
                'guru_lain_ipbi_1' => 'Guru Lain IPBI Pertama',
                'guru_lain_ipbi_2' => 'Guru Lain IPBI Kedua',
                'guru_lain_ipbi_3' => 'Guru Lain IPBI Ketiga',
                'guru_lain_ipbi_4' => 'Guru Lain IPBI Keempat',
                'training_trainer' => 'Training Trainer',
            ],
            '3' => [
                'bahasa_inggris' => 'Bahasa Inggris',
                'bahasa_lain1' => 'Bahasa Lain Pertama',
                'bahasa_lain2' => 'Bahasa Lain Kedua',
                'bahasa_lain3' => 'Bahasa Lain Ketiga',
                'bahasa_lain4' => 'Bahasa Lain Keempat',
            ],
            '4' => [
                'independent_org' => 'Organisasi Independen',
                'foreign_school_degree' => 'Gelar Sekolah Luar Negeri',
                'foreign_school_no_degree_1' => 'Sekolah Luar Negeri Tanpa Gelar Pertama',
                'foreign_school_no_degree_2' => 'Sekolah Luar Negeri Tanpa Gelar Kedua',
                'foreign_school_no_degree_3' => 'Sekolah Luar Negeri Tanpa Gelar Ketiga',
                'foreign_school_no_degree_4' => 'Sekolah Luar Negeri Tanpa Gelar Keempat',
                'foreign_school_no_degree_5' => 'Sekolah Luar Negeri Tanpa Gelar Kelima',
                'domestic_school_no_degree_1' => 'Sekolah Dalam Negeri Tanpa Gelar Pertama',
                'domestic_school_no_degree_2' => 'Sekolah Dalam Negeri Tanpa Gelar Kedua',
                'domestic_school_no_degree_3' => 'Sekolah Dalam Negeri Tanpa Gelar Ketiga',
                'domestic_school_no_degree_4' => 'Sekolah Dalam Negeri Tanpa Gelar Keempat',
                'domestic_school_no_degree_5' => 'Sekolah Dalam Negeri Tanpa Gelar Kelima',
            ],
            '5' => [
                'sertifikat_1' => 'Sertifikat Pertama',
                'sertifikat_2' => 'Sertifikat Kedua',
                'sertifikat_3' => 'Sertifikat Ketiga',
            ],
            '6' => [
                'penghargaan_daerah' => 'Penghargaan Daerah',
                'penghargaan_nasional' => 'Penghargaan Nasional',
                'penghargaan_internasional' => 'Penghargaan Internasional',
            ],
            '7' => [
                'juara_nasional_dpp' => 'Juara Nasional DPP',
                'juara_non_dpp' => 'Juara Non DPP',
                'juara_instansi_lain' => 'Juara Instansi Lain',
                'juara_internasional' => 'Juara Internasional',
                'peserta_lomba_1' => 'Peserta Lomba Pertama',
                'peserta_lomba_2' => 'Peserta Lomba Kedua',
                'peserta_lomba_3' => 'Peserta Lomba Ketiga',
                'peserta_lomba_4' => 'Peserta Lomba Keempat',
                'peserta_lomba_5' => 'Peserta Lomba Kelima',
                'juri_lomba_1' => 'Juri Lomba Pertama',
                'juri_lomba_2' => 'Juri Lomba Kedua',
            ],
            '8' => [
                'demo_dpp_dpd1' => 'Demo DPP/DPD Pertama',
                'demo_dpp_dpd2' => 'Demo DPP/DPD Kedua',
                'demo_dpp_dpd3' => 'Demo DPP/DPD Ketiga',
                'demo_dpp_dpd4' => 'Demo DPP/DPD Keempat',
                'demo_dpp_dpd5' => 'Demo DPP/DPD Kelima',
                'non_ipbi1' => 'Demo Non IPBI Pertama',
                'non_ipbi2' => 'Demo Non IPBI Kedua',
                'non_ipbi3' => 'Demo Non IPBI Ketiga',
                'non_ipbi4' => 'Demo Non IPBI Keempat',
                'non_ipbi5' => 'Demo Non IPBI Kelima',
                'international1' => 'Demo Internasional Pertama',
                'international2' => 'Demo Internasional Kedua',
            ],
            '9' => [
                'pembina_demonstrator' => 'Pembina Demonstrator',
                'panitia' => 'Panitia',
                'peserta' => 'Peserta',
            ],
            '10' => [
                'ipbi_offline1' => 'IPBI Offline Pertama',
                'ipbi_offline2' => 'IPBI Offline Kedua',
                'ipbi_offline3' => 'IPBI Offline Ketiga',
                'ipbi_online1' => 'IPBI Online Pertama',
                'ipbi_online2' => 'IPBI Online Kedua',
                'ipbi_online3' => 'IPBI Online Ketiga',
                'non_ipbi_offline1' => 'Non IPBI Offline Pertama',
                'non_ipbi_offline2' => 'Non IPBI Offline Kedua',
                'non_ipbi_offline3' => 'Non IPBI Offline Ketiga',
                'non_ipbi_online1' => 'Non IPBI Online Pertama',
                'non_ipbi_online2' => 'Non IPBI Online Kedua',
                'non_ipbi_online3' => 'Non IPBI Online Ketiga',
                'international_offline1' => 'Internasional Offline Pertama',
                'international_offline2' => 'Internasional Offline Kedua',
                'international_online1' => 'Internasional Online Pertama',
                'international_online2' => 'Internasional Online Kedua',
                'host_moderator1' => 'Host/Moderator Pertama',
                'host_moderator2' => 'Host/Moderator Kedua',
                'host_moderator3' => 'Host/Moderator Ketiga',
                'host_moderator4' => 'Host/Moderator Keempat',
                'host_moderator5' => 'Host/Moderator Kelima',
            ],
            '11' => [
                'penguji_sertifikasi1' => 'Penguji Sertifikasi Pertama',
                'penguji_sertifikasi2' => 'Penguji Sertifikasi Kedua',
                'juri_ipbi1' => 'Juri IPBI Pertama',
                'juri_ipbi2' => 'Juri IPBI Kedua',
                'juri_non_ipbi1' => 'Juri Non IPBI Pertama',
                'juri_non_ipbi2' => 'Juri Non IPBI Kedua',
            ],
            '12' => [
                'jabatan' => 'Jabatan',
            ],
            '13' => [
                'guru_tetap' => 'Guru Tetap',
                'asisten_guru' => 'Asisten Guru',
                'owner_sekolah' => 'Pemilik Sekolah',
                'guru_tidak_tetap_offline' => 'Guru Tidak Tetap Offline',
                'guru_tidak_tetap_online' => 'Guru Tidak Tetap Online',
                'guru_luar_negeri1' => 'Guru Luar Negeri Pertama',
                'guru_luar_negeri2' => 'Guru Luar Negeri Kedua',
            ],
            '14' => [
                'ngajar_online' => 'Mengajar Online',
            ],
            '15' => [
                'ikebana_murid' => 'Ikebana Murid',
                'ikebana_guru' => 'Ikebana Guru',
                'rangkaian_tradisional' => 'Rangkaian Tradisional',
                'lainnya' => 'Lainnya',
            ],
            '16' => [
                'aktif_merangkai' => 'Aktif Merangkai',
                'owner_berbadan_hukum' => 'Pemilik Berbadan Hukum',
                'owner_tanpa_badan_hukum' => 'Pemilik Tanpa Badan Hukum',
                'freelance_designer' => 'Desainer Freelance',
            ],
            '17' => [
                'media_cetak_nasional' => 'Media Cetak Nasional',
                'media_cetak_internasional' => 'Media Cetak Internasional',
                'buku_merangkai_bunga' => 'Buku Merangkai Bunga',
                'kontributor_buku1' => 'Kontributor Buku Pertama',
                'kontributor_buku2' => 'Kontributor Buku Kedua',
                'kontributor_tv1' => 'Kontributor TV Pertama',
                'kontributor_tv2' => 'Kontributor TV Kedua',
            ],
        ];

        return $fieldNameMap[$soalNumber][$fieldName] ?? ucwords(str_replace('_', ' ', $fieldName));
    }

    public function userDetail($userId)
    {
        $user = User::findOrFail($userId);
        
        $soals = [
            'soal1' => Soal1::where('user_id', $userId)->first(),
            'soal2' => Soal2::where('user_id', $userId)->first(),
            'soal3' => Soal3::where('user_id', $userId)->first(),
            'soal4' => Soal4::where('user_id', $userId)->first(),
            'soal5' => Soal5::where('user_id', $userId)->first(),
            'soal6' => Soal6::where('user_id', $userId)->first(),
            'soal7' => Soal7::where('user_id', $userId)->first(),
            'soal8' => Soal8::where('user_id', $userId)->first(),
            'soal9' => Soal9::where('user_id', $userId)->first(),
            'soal10' => Soal10::where('user_id', $userId)->first(),
            'soal11' => Soal11::where('user_id', $userId)->first(),
            'soal12' => Soal12::where('user_id', $userId)->first(),
            'soal13' => Soal13::where('user_id', $userId)->first(),
            'soal14' => Soal14::where('user_id', $userId)->first(),
            'soal15' => Soal15::where('user_id', $userId)->first(),
            'soal16' => Soal16::where('user_id', $userId)->first(),
            'soal17' => Soal17::where('user_id', $userId)->first(),
        ];

        $totalNilai = $user->is_verified ? $user->nilai : $this->calculateTotalNilai($userId);
        $notifications = Comment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                $descriptiveFieldName = $comment->field_name 
                    ? $this->getDescriptiveFieldName($comment->soal_number, $comment->field_name) 
                    : null;
                return [
                    'id' => $comment->id,
                    'soal_number' => $comment->soal_number,
                    'field_name' => $descriptiveFieldName ?? 'Seluruh Soal',
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'admin_name' => $comment->admin ? $comment->admin->name : 'Admin',
                    'deleted_files' => $comment->deleted_files ?? [],
                ];
            });

        return response()->json([
            'user' => $user,
            'soals' => $soals,
            'totalNilai' => $totalNilai,
            'notifications' => $notifications,
        ], 200);
    }

    public function getSoal($soalNumber, $userId)
    {
        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        
        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $user = User::findOrFail($userId);
        $soal->status = $user->last_submission_date ? 'submitted' : 'in_progress';
        
        $comments = Comment::where('user_id', $userId)
            ->where('soal_number', $soalNumber)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                $descriptiveFieldName = $comment->field_name 
                    ? $this->getDescriptiveFieldName($comment->soal_number, $comment->field_name) 
                    : null;
                return [
                    'id' => $comment->id,
                    'field_name' => $descriptiveFieldName ?? 'Seluruh Soal',
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'admin_name' => $comment->admin ? $comment->admin->name : 'Admin',
                    'deleted_files' => $comment->deleted_files ?? [],
                ];
            });

        return response()->json([
            'data' => $soal,
            'is_verified' => $user->is_verified,
            'notifications' => $comments,
        ], 200);
    }

    public function updateSoal(Request $request, $soalNumber, $userId)
    {
        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        
        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        
        $request->validate([
            'nilai' => 'nullable|numeric|min:0|max:100',
        ]);
        
        if ($request->has('nilai')) {
            $soal->nilai = $request->nilai;
            $soal->save();
        }
        
        $this->updateSoalNilai($soalNumber, $userId);
        $totalNilai = $this->calculateTotalNilai($userId);
        
        return response()->json([
            'message' => 'Berhasil mengupdate data',
            'data' => $soal,
            'totalNilai' => $totalNilai,
        ], 200);
    }

    public function deleteSoal(Request $request, $soalNumber, $userId)
    {
        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        
        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $user = User::findOrFail($userId);
        if ($user->is_verified) {
            return response()->json(['message' => 'Tidak dapat menghapus soal karena user sudah terverifikasi'], 403);
        }
        
        $commentText = $request->input('comment', "Jawaban Anda untuk Soal {$soalNumber} tidak terverifikasi");
        $fileFields = $this->getFileFields($soalNumber);
        $deletedFiles = [];
        foreach ($fileFields as $field) {
            if ($soal->$field && Storage::disk('public')->exists($soal->$field)) {
                $descriptiveName = $this->getDescriptiveFieldName($soalNumber, $field);
                $deletedFiles[] = [
                    'field_name' => $descriptiveName,
                    'file_path' => $soal->$field,
                    'technical_field_name' => $field, // Tambahkan nama teknis untuk upload ulang
                ];
                Storage::disk('public')->delete($soal->$field);
            }
        }

        Comment::create([
            'user_id' => $userId,
            'soal_number' => $soalNumber,
            'field_name' => null,
            'comment' => $commentText,
            'admin_id' => Auth::id(),
            'deleted_files' => $deletedFiles,
        ]);

        $soal->delete();
        $totalNilai = $this->calculateTotalNilai($userId);
        
        return response()->json([
            'message' => 'Berhasil menghapus soal',
            'comment' => $commentText,
            'deleted_files' => $deletedFiles,
            'totalNilai' => $totalNilai,
        ], 200);
    }

    public function deleteField(Request $request, $soalNumber, $userId, $fieldName)
    {
        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        
        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        
        if (!array_key_exists($fieldName, $soal->getAttributes())) {
            return response()->json(['message' => 'Field tidak valid'], 400);
        }

        $user = User::findOrFail($userId);
        if ($user->is_verified) {
            return response()->json(['message' => 'Tidak dapat menghapus field karena user sudah terverifikasi'], 403);
        }
        
        $descriptiveFieldName = $this->getDescriptiveFieldName($soalNumber, $fieldName);
        $commentText = $request->input('comment', "Jawaban Anda untuk Soal {$soalNumber} pada {$descriptiveFieldName} tidak terverifikasi");
        $fileFields = $this->getFileFields($soalNumber);
        $deletedFiles = [];
        
        if (in_array($fieldName, $fileFields) && $soal->$fieldName && Storage::disk('public')->exists($soal->$fieldName)) {
            $deletedFiles[] = [
                'field_name' => $descriptiveFieldName,
                'file_path' => $soal->$fieldName,
                'technical_field_name' => $fieldName, // Tambahkan nama teknis untuk upload ulang
            ];
            Storage::disk('public')->delete($soal->$fieldName);
        }

        Comment::create([
            'user_id' => $userId,
            'soal_number' => $soalNumber,
            'field_name' => $fieldName,
            'comment' => $commentText,
            'admin_id' => Auth::id(),
            'deleted_files' => $deletedFiles,
        ]);

        $pointsToDeduct = $this->getPointsPerField($soalNumber, $fieldName, $soal->$fieldName);
        $soal->$fieldName = null;
        $soal->nilai = max(0, $soal->nilai - $pointsToDeduct);
        $soal->save();
        
        $this->updateSoalNilai($soalNumber, $userId);
        $totalNilai = $this->calculateTotalNilai($userId);
        
        return response()->json([
            'message' => "Berhasil menghapus field $descriptiveFieldName",
            'comment' => $commentText,
            'deleted_files' => $deletedFiles,
            'newNilai' => $soal->nilai,
            'totalNilai' => $totalNilai,
        ], 200);
    }

    public function uploadReplacementFile(Request $request, $commentId)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048', // Sesuaikan validasi sesuai kebutuhan
            'soal_number' => 'required|integer|between:1,17',
            'field_name' => 'required|string', // Nama teknis field (misalnya guru_lain_ipbi_1)
        ]);

        $comment = Comment::findOrFail($commentId);
        $userId = $comment->user_id;
        $soalNumber = $request->input('soal_number');
        $fieldName = $request->input('field_name');

        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }

        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal) {
            $soal = new $modelClass(['user_id' => $userId]); // Buat baru jika belum ada
        }

        if (!array_key_exists($fieldName, $soal->getAttributes())) {
            return response()->json(['message' => 'Field tidak valid'], 400);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $soal->$fieldName = $path;
            $soal->save();

            // Tandai notifikasi sebagai dibaca
            $comment->read_at = now();
            $comment->save();

            $this->updateSoalNilai($soalNumber, $userId);
            $totalNilai = $this->calculateTotalNilai($userId);

            return response()->json([
                'message' => 'File berhasil diunggah dan notifikasi ditandai sebagai dibaca',
                'data' => $soal,
                'totalNilai' => $totalNilai,
            ], 200);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }

    public function verifyUser(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            if ($user->is_verified) {
                return response()->json(['message' => 'User sudah terverifikasi sebelumnya'], 400);
            }

            $totalNilai = $request->has('total_nilai') ? $request->input('total_nilai') : $this->calculateTotalNilai($userId);
            if ($request->has('total_nilai')) {
                $request->validate([
                    'total_nilai' => 'numeric|min:0',
                ]);
            }

            \DB::beginTransaction();

            $user->is_verified = 1;
            $user->nilai = $totalNilai;
            $user->save();

            if (!Auth::check()) {
                throw new \Exception('Admin tidak terautentikasi.');
            }

            \Log::info('Creating verification notification for user', [
                'user_id' => $userId,
                'admin_id' => Auth::id(),
                'total_nilai' => $totalNilai,
            ]);

            $comment = Comment::create([
                'user_id' => $userId,
                'soal_number' => null,
                'field_name' => null,
                'comment' => "Pengguna Anda telah diverifikasi oleh " . Auth::user()->name . " dengan nilai akhir {$totalNilai}. Silakan cek detail nilai di Profile anda!",
                'admin_id' => Auth::id(),
                'deleted_files' => json_encode([]),
            ]);

            \Log::info('User verified successfully', [
                'user_id' => $userId,
                'total_nilai' => $totalNilai,
                'admin_id' => Auth::id(),
                'comment_id' => $comment->id,
            ]);

            \DB::commit();

            return response()->json([
                'message' => "User {$user->name} telah diverifikasi dengan nilai akhir $totalNilai",
                'user' => $user,
                'totalNilai' => $totalNilai,
            ], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error verifying user: ' . $e->getMessage(), [
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'message' => 'Gagal memverifikasi pengguna: ' . $e->getMessage(),
                'error' => true,
            ], 500);
        }
    }

    public function unverifyUser($userId)
    {
        $user = User::findOrFail($userId);
        if (!$user->is_verified) {
            return response()->json(['message' => 'User belum terverifikasi'], 400);
        }

        $user->is_verified = 0;
        $user->nilai = null;
        $user->save();

        return response()->json([
            'message' => "Verifikasi user {$user->name} telah dibatalkan",
            'user' => $user,
            'totalNilai' => $this->calculateTotalNilai($userId),
        ], 200);
    }

    public function getUserNotifications($userId)
    {
        $user = User::findOrFail($userId);
        $comments = Comment::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($comment) {
                $descriptiveFieldName = $comment->field_name 
                    ? $this->getDescriptiveFieldName($comment->soal_number, $comment->field_name) 
                    : null;
                return [
                    'id' => $comment->id,
                    'soal_number' => $comment->soal_number,
                    'field_name' => $descriptiveFieldName ?? 'Seluruh Soal',
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at,
                    'admin_name' => $comment->admin ? $comment->admin->name : 'Admin',
                    'deleted_files' => $comment->deleted_files ?? [],
                    'read_at' => $comment->read_at,
                ];
            });

        return response()->json([
            'message' => 'Daftar notifikasi untuk user',
            'data' => $comments,
        ], 200);
    }

    private function getPointsPerField($soalNumber, $fieldName, $fieldValue)
    {
        switch ($soalNumber) {
            case '1':
                if ($fieldName === 'tingkat_pendidikan') {
                    switch ($fieldValue) {
                        case 'SMP-D3': return 2;
                        case 'S1': return 4;
                        case 'S2_atau_lebih': return 5;
                        default: return 0;
                    }
                }
                break;
            case '2':
                $pointsMap = [
                    'tp3' => 20,
                    'lpmp_diknas' => 30,
                    'guru_lain_ipbi_1' => 5,
                    'guru_lain_ipbi_2' => 5,
                    'guru_lain_ipbi_3' => 5,
                    'guru_lain_ipbi_4' => 5,
                    'training_trainer' => 10,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '3':
                if ($fieldName === 'bahasa_inggris') {
                    switch ($fieldValue) {
                        case 'Dasar': return 3;
                        case 'Fasih': return 5;
                        default: return 0;
                    }
                } elseif (in_array($fieldName, ['bahasa_lain1', 'bahasa_lain2', 'bahasa_lain3', 'bahasa_lain4'])) {
                    return $fieldValue ? 5 : 0;
                }
                break;
            case '4':
                $pointsMap = [
                    'independent_org' => 8,
                    'foreign_school_degree' => 7,
                    'foreign_school_no_degree_1' => 3,
                    'foreign_school_no_degree_2' => 3,
                    'foreign_school_no_degree_3' => 3,
                    'foreign_school_no_degree_4' => 3,
                    'foreign_school_no_degree_5' => 3,
                    'domestic_school_no_degree_1' => 3,
                    'domestic_school_no_degree_2' => 3,
                    'domestic_school_no_degree_3' => 3,
                    'domestic_school_no_degree_4' => 3,
                    'domestic_school_no_degree_5' => 3,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '5':
                $pointsMap = [
                    'sertifikat_1' => 3,
                    'sertifikat_2' => 4,
                    'sertifikat_3' => 5,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '6':
                $pointsMap = [
                    'penghargaan_daerah' => 5,
                    'penghargaan_nasional' => 10,
                    'penghargaan_internasional' => 15,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '7':
                $pointsMap = [
                    'juara_nasional_dpp' => 15,
                    'juara_non_dpp' => 10,
                    'juara_instansi_lain' => 5,
                    'juara_internasional' => 15,
                    'peserta_lomba_1' => 1,
                    'peserta_lomba_2' => 1,
                    'peserta_lomba_3' => 1,
                    'peserta_lomba_4' => 1,
                    'peserta_lomba_5' => 1,
                    'juri_lomba_1' => 3,
                    'juri_lomba_2' => 3,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '8':
                $pointsMap = [
                    'demo_dpp_dpd1' => 2,
                    'demo_dpp_dpd2' => 2,
                    'demo_dpp_dpd3' => 2,
                    'demo_dpp_dpd4' => 2,
                    'demo_dpp_dpd5' => 2,
                    'non_ipbi1' => 1,
                    'non_ipbi2' => 1,
                    'non_ipbi3' => 1,
                    'non_ipbi4' => 1,
                    'non_ipbi5' => 1,
                    'international1' => 2,
                    'international2' => 2,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '9':
                $pointsMap = [
                    'pembina_demonstrator' => 15,
                    'panitia' => 10,
                    'peserta' => 5,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '10':
                $pointsMap = [
                    'ipbi_offline1' => 5,
                    'ipbi_offline2' => 5,
                    'ipbi_offline3' => 5,
                    'ipbi_online1' => 3,
                    'ipbi_online2' => 3,
                    'ipbi_online3' => 3,
                    'non_ipbi_offline1' => 5,
                    'non_ipbi_offline2' => 5,
                    'non_ipbi_offline3' => 5,
                    'non_ipbi_online1' => 3,
                    'non_ipbi_online2' => 3,
                    'non_ipbi_online3' => 3,
                    'international_offline1' => 10,
                    'international_offline2' => 10,
                    'international_online1' => 5,
                    'international_online2' => 5,
                    'host_moderator1' => 1,
                    'host_moderator2' => 1,
                    'host_moderator3' => 1,
                    'host_moderator4' => 1,
                    'host_moderator5' => 1,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '11':
                $pointsMap = [
                    'penguji_sertifikasi1' => 10,
                    'penguji_sertifikasi2' => 10,
                    'juri_ipbi1' => 10,
                    'juri_ipbi2' => 10,
                    'juri_non_ipbi1' => 5,
                    'juri_non_ipbi2' => 5,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '12':
                if ($fieldName === 'jabatan') {
                    switch ($fieldValue) {
                        case 'inti': return 10;
                        case 'biasa': return 5;
                        default: return 0;
                    }
                }
                break;
            case '13':
                $pointsMap = [
                    'guru_tetap' => 15,
                    'asisten_guru' => 8,
                    'owner_sekolah' => 8,
                    'guru_tidak_tetap_offline' => 10,
                    'guru_tidak_tetap_online' => 10,
                    'guru_luar_negeri1' => 10,
                    'guru_luar_negeri2' => 10,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '14':
                if ($fieldName === 'ngajar_online') {
                    switch ($fieldValue) {
                        case 'sendiri': return 10;
                        case 'team': return 8;
                        default: return 0;
                    }
                }
                break;
            case '15':
                $pointsMap = [
                    'ikebana_murid' => 5,
                    'ikebana_guru' => 15,
                    'rangkaian_tradisional' => 10,
                    'lainnya' => 5,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '16':
                $pointsMap = [
                    'aktif_merangkai' => 10,
                    'owner_berbadan_hukum' => 10,
                    'owner_tanpa_badan_hukum' => 5,
                    'freelance_designer' => 5,
                ];
                return $pointsMap[$fieldName] ?? 0;
            case '17':
                $pointsMap = [
                    'media_cetak_nasional' => 5,
                    'media_cetak_internasional' => 10,
                    'buku_merangkai_bunga' => 20,
                    'kontributor_buku1' => 10,
                    'kontributor_buku2' => 10,
                    'kontributor_tv1' => 5,
                    'kontributor_tv2' => 5,
                ];
                return $pointsMap[$fieldName] ?? 0;
        }
        return 0;
    }

    private function getFileFields($soalNumber)
    {
        $fileFieldsMap = [
            '1' => ['tingkat_pendidikan_file'],
            '2' => ['tp3', 'lpmp_diknas', 'guru_lain_ipbi_1', 'guru_lain_ipbi_2', 'guru_lain_ipbi_3', 'guru_lain_ipbi_4', 'training_trainer'],
            '3' => ['bahasa_inggris', 'bahasa_lain1', 'bahasa_lain2', 'bahasa_lain3', 'bahasa_lain4'],
            '4' => [
                'independent_org', 'foreign_school_degree',
                'foreign_school_no_degree_1', 'foreign_school_no_degree_2', 'foreign_school_no_degree_3', 'foreign_school_no_degree_4', 'foreign_school_no_degree_5',
                'domestic_school_no_degree_1', 'domestic_school_no_degree_2', 'domestic_school_no_degree_3', 'domestic_school_no_degree_4', 'domestic_school_no_degree_5',
            ],
            '5' => ['sertifikat_1', 'sertifikat_2', 'sertifikat_3'],
            '6' => ['penghargaan_daerah', 'penghargaan_nasional', 'penghargaan_internasional'],
            '7' => [
                'juara_nasional_dpp', 'juara_non_dpp', 'juara_instansi_lain', 'juara_internasional',
                'peserta_lomba_1', 'peserta_lomba_2', 'peserta_lomba_3', 'peserta_lomba_4', 'peserta_lomba_5',
                'juri_lomba_1', 'juri_lomba_2',
            ],
            '8' => [
                'demo_dpp_dpd1', 'demo_dpp_dpd2', 'demo_dpp_dpd3', 'demo_dpp_dpd4', 'demo_dpp_dpd5',
                'non_ipbi1', 'non_ipbi2', 'non_ipbi3', 'non_ipbi4', 'non_ipbi5',
                'international1', 'international2',
            ],
            '9' => ['pembina_demonstrator', 'panitia', 'peserta'],
            '10' => [
                'ipbi_offline1', 'ipbi_offline2', 'ipbi_offline3',
                'ipbi_online1', 'ipbi_online2', 'ipbi_online3',
                'non_ipbi_offline1', 'non_ipbi_offline2', 'non_ipbi_offline3',
                'non_ipbi_online1', 'non_ipbi_online2', 'non_ipbi_online3',
                'international_offline1', 'international_offline2',
                'international_online1', 'international_online2',
                'host_moderator1', 'host_moderator2', 'host_moderator3', 'host_moderator4', 'host_moderator5',
            ],
            '11' => ['penguji_sertifikasi1', 'penguji_sertifikasi2', 'juri_ipbi1', 'juri_ipbi2', 'juri_non_ipbi1', 'juri_non_ipbi2'],
            '12' => ['jabatan'],
            '13' => [
                'guru_tetap', 'asisten_guru', 'owner_sekolah',
                'guru_tidak_tetap_offline', 'guru_tidak_tetap_online',
                'guru_luar_negeri1', 'guru_luar_negeri2',
            ],
            '14' => ['ngajar_online'],
            '15' => ['ikebana_murid', 'ikebana_guru', 'rangkaian_tradisional', 'lainnya'],
            '16' => ['aktif_merangkai', 'owner_berbadan_hukum', 'owner_tanpa_badan_hukum', 'freelance_designer'],
            '17' => [
                'media_cetak_nasional', 'media_cetak_internasional', 'buku_merangkai_bunga',
                'kontributor_buku1', 'kontributor_buku2', 'kontributor_tv1', 'kontributor_tv2',
            ],
        ];
        return $fileFieldsMap[$soalNumber] ?? [];
    }

    public function viewFile($soalNumber, $userId, $fieldName)
    {
        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }
        
        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal || !$soal->$fieldName) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }
        
        $path = $soal->$fieldName;
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'File tidak ditemukan di storage'], 404);
        }
        
        return response()->file(storage_path('app/public/' . $path));
    }
    
    public function exportTeachers()
    {
        return Excel::download(new TeachersExport, 'data_guru_terdaftar.xlsx');
    }

    public function exportTeachersWithSoal()
    {
        return Excel::download(new TeachersWithSoalExport, 'data_guru_dan_soal.xlsx');
    }
}