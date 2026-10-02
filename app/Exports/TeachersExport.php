<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeachersExport implements FromCollection, WithHeadings
{
    /**
     * Mengambil semua data pengguna dari database dan menambahkan nomor urut.
     */
    public function collection()
    {
        $users = User::all([
            'name',
            'email',
            'status',
            'pekerjaan',
            'tanggal_lahir',
            'informasi_ipbi',
            'domisili',
            'NoHp'
        ]);

        // Tambahkan nomor urut ke setiap baris
        $data = $users->map(function ($user, $index) {
            return [
                'Nomor' => $index + 1, // Nomor urut mulai dari 1
                'Nama' => $user->name,
                'Email' => $user->email,
                'Status' => $user->status,
                'Pekerjaan' => $user->pekerjaan,
                'Tanggal Lahir' => $user->tanggal_lahir,
                'Informasi IPBI' => $user->informasi_ipbi,
                'Domisili' => $user->domisili,
                'No HP' => $user->NoHp,
            ];
        });

        return collect($data);
    }

    /**
     * Menentukan judul kolom untuk file Excel.
     */
    public function headings(): array
    {
        return [
            'Nomor',
            'Nama',
            'Email',
            'Status',
            'Pekerjaan',
            'Tanggal Lahir',
            'Informasi IPBI',
            'Domisili',
            'No HP',
        ];
    }
}