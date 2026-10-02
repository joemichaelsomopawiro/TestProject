<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Mapping untuk nama field yang lebih deskriptif berdasarkan nomor soal
     */
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

    /**
     * Memperbarui nilai soal berdasarkan data terbaru
     */
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

    /**
     * Menampilkan daftar notifikasi untuk pengguna yang sedang login
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $comments = Comment::where('user_id', $userId)
            ->latest('created_at')
            ->get()
            ->map(function ($comment) {
                $descriptiveFieldName = $comment->field_name 
                    ? $this->getDescriptiveFieldName($comment->soal_number, $comment->field_name) 
                    : null;
                return [
                    'id' => $comment->id,
                    'soal_number' => $comment->soal_number, // Bisa null untuk notifikasi verifikasi
                    'field_name' => $descriptiveFieldName ?? ($comment->soal_number ? 'Seluruh Soal' : null),
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->toIso8601String(),
                    'admin_name' => $comment->admin ? $comment->admin->name : 'Admin',
                    'deleted_files' => $comment->deleted_files ?? [],
                    'read_at' => $comment->read_at ? $comment->read_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'data' => $comments,
            'message' => 'Notifikasi berhasil diambil',
        ], 200);
    }

    /**
     * Mengunggah file pengganti untuk notifikasi tertentu
     */
    public function uploadReplacementFile(Request $request, $commentId): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'soal_number' => 'required|integer|between:1,17',
            'field_name' => 'required|string',
        ]);

        $userId = $request->user()->id;
        $comment = Comment::where('id', $commentId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $soalNumber = $request->input('soal_number');
        $fieldName = $request->input('field_name');

        $modelClass = "App\\Models\\Soal{$soalNumber}";
        if (!class_exists($modelClass)) {
            return response()->json(['message' => 'Soal tidak ditemukan'], 404);
        }

        $soal = $modelClass::where('user_id', $userId)->first();
        if (!$soal) {
            $soal = new $modelClass(['user_id' => $userId]);
        }

        if (!array_key_exists($fieldName, $soal->getAttributes())) {
            return response()->json(['message' => 'Field tidak valid'], 400);
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('uploads', 'public');
            $soal->$fieldName = $path;
            $soal->save();

            $comment->read_at = now();
            $comment->save();

            $this->updateSoalNilai($soalNumber, $userId);

            return response()->json([
                'data' => [
                    'id' => $comment->id,
                    'soal_number' => $comment->soal_number,
                    'field_name' => $this->getDescriptiveFieldName($soalNumber, $fieldName),
                    'comment' => $comment->comment,
                    'created_at' => $comment->created_at->toIso8601String(),
                    'admin_name' => $comment->admin ? $comment->admin->name : 'Admin',
                    'deleted_files' => $comment->deleted_files ?? [],
                    'read_at' => $comment->read_at->toIso8601String(),
                ],
                'message' => 'File berhasil diunggah dan notifikasi ditandai sebagai dibaca',
            ], 200);
        }

        return response()->json(['message' => 'File tidak ditemukan'], 400);
    }

    /**
     * Menandai notifikasi sebagai dibaca
     */
    public function markAsRead($id, Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $comment = Comment::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        if ($comment->read_at) {
            return response()->json(['message' => 'Notifikasi sudah dibaca sebelumnya'], 400);
        }

        $comment->read_at = now();
        $comment->save();

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'soal_number' => $comment->soal_number,
                'field_name' => $comment->field_name ? $this->getDescriptiveFieldName($comment->soal_number, $comment->field_name) : ($comment->soal_number ? 'Seluruh Soal' : null),
                'comment' => $comment->comment,
                'created_at' => $comment->created_at->toIso8601String(),
                'admin_name' => $comment->admin ? $comment->admin->name : 'Admin',
                'deleted_files' => $comment->deleted_files ?? [],
                'read_at' => $comment->read_at->toIso8601String(),
            ],
            'message' => 'Notifikasi berhasil ditandai sebagai dibaca',
        ], 200);
    }
}