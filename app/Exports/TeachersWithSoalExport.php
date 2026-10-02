<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TeachersWithSoalExport implements FromCollection, WithHeadings, WithStyles
{
    private function getDescriptiveFieldName($soalNumber, $fieldName = null)
    {
        $fieldNameMap = [
            '1' => [
                'tingkat_pendidikan' => 'Tingkat Pendidikan',
                'nilai' => 'Nilai',
            ],
            '2' => [
                'tp3' => 'TP3',
                'lpmp_diknas' => 'LPMP Diknas',
                'guru_lain_ipbi_1' => 'Guru Lain IPBI Pertama',
                'guru_lain_ipbi_2' => 'Guru Lain IPBI Kedua',
                'guru_lain_ipbi_3' => 'Guru Lain IPBI Ketiga',
                'guru_lain_ipbi_4' => 'Guru Lain IPBI Keempat',
                'training_trainer' => 'Training Trainer',
                'nilai' => 'Nilai',
            ],
            '3' => [
                'bahasa_inggris' => 'Bahasa Inggris',
                'bahasa_lain1' => 'Bahasa Lain Pertama',
                'bahasa_lain2' => 'Bahasa Lain Kedua',
                'bahasa_lain3' => 'Bahasa Lain Ketiga',
                'bahasa_lain4' => 'Bahasa Lain Keempat',
                'nilai' => 'Nilai',
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
                'nilai' => 'Nilai',
            ],
            '5' => [
                'sertifikat_1' => 'Sertifikat Pertama',
                'sertifikat_2' => 'Sertifikat Kedua',
                'sertifikat_3' => 'Sertifikat Ketiga',
                'nilai' => 'Nilai',
            ],
            '6' => [
                'penghargaan_daerah' => 'Penghargaan Daerah',
                'penghargaan_nasional' => 'Penghargaan Nasional',
                'penghargaan_internasional' => 'Penghargaan Internasional',
                'nilai' => 'Nilai',
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
                'nilai' => 'Nilai',
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
                'nilai' => 'Nilai',
            ],
            '9' => [
                'pembina_demonstrator' => 'Pembina Demonstrator',
                'panitia' => 'Panitia',
                'peserta' => 'Peserta',
                'nilai' => 'Nilai',
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
                'nilai' => 'Nilai',
            ],
            '11' => [
                'penguji_sertifikasi1' => 'Penguji Sertifikasi Pertama',
                'penguji_sertifikasi2' => 'Penguji Sertifikasi Kedua',
                'juri_ipbi1' => 'Juri IPBI Pertama',
                'juri_ipbi2' => 'Juri IPBI Kedua',
                'juri_non_ipbi1' => 'Juri Non IPBI Pertama',
                'juri_non_ipbi2' => 'Juri Non IPBI Kedua',
                'nilai' => 'Nilai',
            ],
            '12' => [
                'jabatan' => 'Jabatan',
                'nilai' => 'Nilai',
            ],
            '13' => [
                'guru_tetap' => 'Guru Tetap',
                'asisten_guru' => 'Asisten Guru',
                'owner_sekolah' => 'Pemilik Sekolah',
                'guru_tidak_tetap_offline' => 'Guru Tidak Tetap Offline',
                'guru_tidak_tetap_online' => 'Guru Tidak Tetap Online',
                'guru_luar_negeri1' => 'Guru Luar Negeri Pertama',
                'guru_luar_negeri2' => 'Guru Luar Negeri Kedua',
                'nilai' => 'Nilai',
            ],
            '14' => [
                'ngajar_online' => 'Mengajar Online',
                'nilai' => 'Nilai',
            ],
            '15' => [
                'ikebana_murid' => 'Ikebana Murid',
                'ikebana_guru' => 'Ikebana Guru',
                'rangkaian_tradisional' => 'Rangkaian Tradisional',
                'lainnya' => 'Lainnya',
                'nilai' => 'Nilai',
            ],
            '16' => [
                'aktif_merangkai' => 'Aktif Merangkai',
                'owner_berbadan_hukum' => 'Pemilik Berbadan Hukum',
                'owner_tanpa_badan_hukum' => 'Pemilik Tanpa Badan Hukum',
                'freelance_designer' => 'Desainer Freelance',
                'nilai' => 'Nilai',
            ],
            '17' => [
                'media_cetak_nasional' => 'Media Cetak Nasional',
                'media_cetak_internasional' => 'Media Cetak Internasional',
                'buku_merangkai_bunga' => 'Buku Merangkai Bunga',
                'kontributor_buku1' => 'Kontributor Buku Pertama',
                'kontributor_buku2' => 'Kontributor Buku Kedua',
                'kontributor_tv1' => 'Kontributor TV Pertama',
                'kontributor_tv2' => 'Kontributor TV Kedua',
                'nilai' => 'Nilai',
            ],
        ];

        if ($fieldName === null) {
            return $fieldNameMap[$soalNumber] ?? [];
        }

        return $fieldNameMap[$soalNumber][$fieldName] ?? ucwords(str_replace('_', ' ', $fieldName));
    }

    // Fungsi untuk menentukan apakah field adalah field upload
    private function isUploadField($field, $soalNumber)
    {
        // Soal 1, 3, 12, 14 tidak memiliki field upload
        $noUploadSoals = ['1', '3', '12', '14'];
        if (in_array($soalNumber, $noUploadSoals)) {
            return false;
        }
        // Hanya field dengan '_file' atau 'sertifikat' yang dianggap upload
        return str_contains($field, '_file') || str_contains($field, 'sertifikat');
    }

    public function collection()
{
    $users = User::whereNotNull('last_submission_date')->get();
    $data = [];

    // Baris pertama menggunakan "Data Diri" sebagai header kolom pertama
    $data[] = array_merge(['Data Diri'], array_map(fn($user, $index) => "Data " . ($index + 1), $users->all(), array_keys($users->all())));

    // Tambahkan data pengguna lainnya
    $data[] = array_merge(['Nama'], array_map(fn($user) => $user->name, $users->all()));
    $data[] = array_merge(['Email'], array_map(fn($user) => $user->email, $users->all()));
    $data[] = array_merge(['Tanggal Registrasi'], array_map(fn($user) => $user->created_at, $users->all()));
    $data[] = array_merge(['Tanggal Pengumpulan Terakhir'], array_map(fn($user) => $user->last_submission_date, $users->all()));
    $data[] = array_merge(['Terverifikasi'], array_map(fn($user) => $user->is_verified ? 'Ya' : 'Tidak', $users->all()));
    $data[] = array_merge(['Nilai Total'], array_map(fn($user) => $user->nilai, $users->all()));

    // Tambahkan baris kosong sebagai pemisah
    $data[] = [''];

    // Tambahkan data untuk setiap soal dengan nomor soal
    for ($i = 1; $i <= 17; $i++) {
        $model = "\\App\\Models\\Soal{$i}";
        $fields = $this->getDescriptiveFieldName((string)$i);

        // Tambahkan baris nomor soal
        $data[] = ["Soal $i"];

        foreach (array_keys($fields) as $field) {
            $row = [$this->getDescriptiveFieldName((string)$i, $field)];
            foreach ($users as $user) {
                $soal = $model::where('user_id', $user->id)->first();
                $value = $soal ? ($soal->$field ?? '') : '';
                
                // Jika bukan soal 3, 12, atau 14, bukan field 'nilai', dan ada datanya, tampilkan "Sudah Dijawab"
                if (!in_array((string)$i, ['3', '12', '14']) && $field !== 'nilai' && !empty($value)) {
                    $row[] = 'Sudah Dijawab';
                } else {
                    $row[] = $value; // Tampilkan nilai asli untuk soal 3, 12, 14 atau field 'nilai'
                }
            }
            $data[] = $row;
        }
    }

    return collect($data);
}

    public function headings(): array
    {
        $users = User::whereNotNull('last_submission_date')->get();
        return array_merge(['Data Diri'], array_map(fn($user, $index) => "Data " . ($index + 1), $users->all(), array_keys($users->all())));
    }

    public function styles(Worksheet $sheet)
    {
        $users = User::whereNotNull('last_submission_date')->get();
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($users->count() + 1);
        $highestRow = $sheet->getHighestRow();

        // Meratakan ke kiri untuk semua kolom
        $sheet->getStyle('A1:' . $lastColumn . $highestRow)
              ->getAlignment()
              ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

        // Membuat header tebal (bold)
        $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);

        // Mengatur baris "Nilai" dan "Soal X" menjadi bold
        for ($row = 1; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue === 'Nilai' || preg_match('/^Soal \d+$/', $cellValue)) {
                $sheet->getStyle('A' . $row . ':' . $lastColumn . $row)
                      ->getFont()
                      ->setBold(true);
            }
        }

        // Mengatur lebar kolom otomatis
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return $sheet;
    }
}