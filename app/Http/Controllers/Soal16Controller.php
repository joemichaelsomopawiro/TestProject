<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Soal16;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class Soal16Controller extends Controller
{
    private $fields = [
        'A' => [
            'nama_florist' => ['type' => 'text', 'points' => 0],
            'd_alamat_florist' => ['type' => 'text', 'points' => 0],
            'a_foto_lokasi1' => ['type' => 'file', 'points' => 10], // Section A gives 10 points if any file is uploaded
            'a_foto_lokasi2' => ['type' => 'file', 'points' => 0],
            'a_foto_lokasi3' => ['type' => 'file', 'points' => 0],
            'a_foto_kegiatan1' => ['type' => 'file', 'points' => 0],
            'a_foto_kegiatan2' => ['type' => 'file', 'points' => 0],
            'a_foto_kegiatan3' => ['type' => 'file', 'points' => 0],
        ],
        'B' => [
            'c_nama_florist' => ['type' => 'text', 'points' => 0],
            'b_alamat_florist' => ['type' => 'text', 'points' => 0],
            'b_bukti_akta1' => ['type' => 'file', 'points' => 10], // Section B gives 10 points if any file is uploaded
            'b_bukti_akta2' => ['type' => 'file', 'points' => 0],
            'b_bukti_akta3' => ['type' => 'file', 'points' => 0],
            'b_bukti_akta4' => ['type' => 'file', 'points' => 0],
            'b_bukti_akta5' => ['type' => 'file', 'points' => 0],
            'b_bukti_akta6' => ['type' => 'file', 'points' => 0],
        ],
        'C' => [
            'c2_nama_florist' => ['type' => 'text', 'points' => 0],
            'c2_alamat_florist' => ['type' => 'text', 'points' => 0],
            'c_foto_lokasi1' => ['type' => 'file', 'points' => 5], // Section C gives 5 points if any file is uploaded
            'c_foto_lokasi2' => ['type' => 'file', 'points' => 0],
            'c_foto_lokasi3' => ['type' => 'file', 'points' => 0],
            'c_foto_lokasi4' => ['type' => 'file', 'points' => 0],
            'c_foto_lokasi5' => ['type' => 'file', 'points' => 0],
            'c_foto_lokasi6' => ['type' => 'file', 'points' => 0],
        ],
        'D' => [
            'd_pemberi_order1' => ['type' => 'text', 'points' => 0],
            'd_tempat_lokasi1' => ['type' => 'text', 'points' => 0],
            'd_tanggal_tahun1' => ['type' => 'text', 'points' => 0],
            'd_deskripsi1' => ['type' => 'text', 'points' => 0],
            'd_foto_lokasi1' => ['type' => 'file', 'points' => 5], // Section D gives 5 points if any file is uploaded
            'd_foto_lokasi2' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi3' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi4' => ['type' => 'file', 'points' => 0],
            'd_pemberi_order2' => ['type' => 'text', 'points' => 0],
            'd_tempat_lokasi2' => ['type' => 'text', 'points' => 0],
            'd_tanggal_tahun2' => ['type' => 'text', 'points' => 0],
            'd_deskripsi2' => ['type' => 'text', 'points' => 0],
            'd_foto_lokasi5' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi6' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi7' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi8' => ['type' => 'file', 'points' => 0],
            'd_pemberi_order3' => ['type' => 'text', 'points' => 0],
            'd_tempat_lokasi3' => ['type' => 'text', 'points' => 0],
            'd_tanggal_tahun3' => ['type' => 'text', 'points' => 0],
            'd_deskripsi3' => ['type' => 'text', 'points' => 0],
            'd_foto_lokasi9' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi10' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi11' => ['type' => 'file', 'points' => 0],
            'd_foto_lokasi12' => ['type' => 'file', 'points' => 0],
        ]
    ];

    private $sectionPoints = [
        'A' => 10,
        'B' => 10,
        'C' => 5,
        'D' => 5
    ];

    public function index()
    {
        $soal16 = Soal16::where('user_id', Auth::id())->first();
        if ($soal16 && $soal16->active_section === 'D') {
            $soal16->projects = [
                [
                    'pemberi_order' => $soal16->d_pemberi_order1,
                    'tempat_lokasi' => $soal16->d_tempat_lokasi1,
                    'tanggal_tahun' => $soal16->d_tanggal_tahun1,
                    'deskripsi' => $soal16->d_deskripsi1
                ],
                [
                    'pemberi_order' => $soal16->d_pemberi_order2,
                    'tempat_lokasi' => $soal16->d_tempat_lokasi2,
                    'tanggal_tahun' => $soal16->d_tanggal_tahun2,
                    'deskripsi' => $soal16->d_deskripsi2
                ],
                [
                    'pemberi_order' => $soal16->d_pemberi_order3,
                    'tempat_lokasi' => $soal16->d_tempat_lokasi3,
                    'tanggal_tahun' => $soal16->d_tanggal_tahun3,
                    'deskripsi' => $soal16->d_deskripsi3
                ]
            ];
        }
        return response()->json(['data' => $soal16 ?: []]);
    }

    public function store(Request $request)
    {
        $this->validateRequest($request);
        $data = $this->processRequest($request);
        $data['user_id'] = Auth::id();
        $soal16 = Soal16::create($data);

        return response()->json([
            'message' => 'BerRosy Berhasil mengunggah data!',
            'data' => $soal16
        ], 201);
    }

    public function update(Request $request)
    {
        $soal16 = Soal16::where('user_id', Auth::id())->first();
        if (!$soal16) {
            return response()->json(['message' => 'Data tidak ditemukan!'], 404);
        }

        $this->validateRequest($request);
        $data = $this->processRequest($request, $soal16);
        $soal16->update($data);

        return response()->json([
            'message' => 'Berhasil memperbarui data!',
            'data' => $soal16
        ]);
    }

    public function destroy()
    {
        $soal16 = Soal16::where('user_id', Auth::id())->first();
        if (!$soal16) {
            return response()->json(['message' => 'Data tidak ditemukan!'], 404);
        }

        // Delete all files from all sections
        foreach (['A', 'B', 'C', 'D'] as $section) {
            foreach ($this->fields[$section] as $field => $config) {
                if ($config['type'] === 'file' && $soal16->$field && Storage::disk('public')->exists($soal16->$field)) {
                    Storage::disk('public')->delete($soal16->$field);
                }
            }
        }

        $soal16->delete();
        return response()->json(['message' => 'Data berhasil dihapus!'], 200);
    }

    private function validateRequest(Request $request)
    {
        $rules = [
            'active_section' => 'required|in:A,B,C,D',
            'nama_florist' => 'required_if:active_section,A|string|max:255',
            'd_alamat_florist' => 'required_if:active_section,A|string',
            'c_nama_florist' => 'required_if:active_section,B|string|max:255',
            'b_alamat_florist' => 'required_if:active_section,B|string',
            'c2_nama_florist' => 'required_if:active_section,C|string|max:255',
            'c2_alamat_florist' => 'required_if:active_section,C|string',
            'd_pemberi_order1' => 'required_if:active_section,D|string|max:255',
            'd_tempat_lokasi1' => 'required_if:active_section,D|string',
            'd_tanggal_tahun1' => 'required_if:active_section,D|string|max:50',
            'd_deskripsi1' => 'required_if:active_section,D|string',
            'd_pemberi_order2' => 'required_if:active_section,D|string|max:255',
            'd_tempat_lokasi2' => 'required_if:active_section,D|string',
            'd_tanggal_tahun2' => 'required_if:active_section,D|string|max:50',
            'd_deskripsi2' => 'required_if:active_section,D|string',
            'd_pemberi_order3' => 'required_if:active_section,D|string|max:255',
            'd_tempat_lokasi3' => 'required_if:active_section,D|string',
            'd_tanggal_tahun3' => 'required_if:active_section,D|string|max:50',
            'd_deskripsi3' => 'required_if:active_section,D|string',
        ];

        foreach ($this->fields as $section => $fields) {
            foreach ($fields as $field => $config) {
                if ($config['type'] === 'file') {
                    $rules[$field] = 'nullable|file|mimes:pdf,jpeg,jpg,png|max:2048';
                }
            }
        }

        $request->validate($rules);
    }

    private function processRequest(Request $request, $soal16 = null)
    {
        $activeSection = $request->input('active_section');
        $data = $soal16 ? $soal16->toArray() : []; // Start with existing data if updating
        $data['active_section'] = $activeSection;

        // Process the current section's fields
        $hasFile = false;
        foreach ($this->fields[$activeSection] as $field => $config) {
            if ($config['type'] === 'text') {
                $data[$field] = $request->input($field);
            } elseif ($config['type'] === 'file' && $request->hasFile($field)) {
                // Delete old file if exists
                if ($soal16 && $soal16->$field && Storage::disk('public')->exists($soal16->$field)) {
                    Storage::disk('public')->delete($soal16->$field);
                }
                $data[$field] = $request->file($field)->store('uploads/soal16', 'public');
                $hasFile = true;
            }
        }

        // Calculate total points
        $nilai = 0;
        foreach (['A', 'B', 'C', 'D'] as $section) {
            $hasSectionFile = false;
            foreach ($this->fields[$section] as $field => $config) {
                if ($config['type'] === 'file' && isset($data[$field]) && $data[$field]) {
                    $hasSectionFile = true;
                    break;
                }
            }
            if ($hasSectionFile) {
                $nilai += $this->sectionPoints[$section];
            }
        }

        // Cap the total points at 20
        $data['nilai'] = min($nilai, 20);

        return $data;
    }
}