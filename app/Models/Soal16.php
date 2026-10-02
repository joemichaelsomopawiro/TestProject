<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal16 extends Model
{
    use HasFactory;

    protected $table = 'soal16';

    protected $fillable = [
        'user_id', 'active_section',
        'nama_florist', 'd_alamat_florist', 'a_foto_lokasi1', 'a_foto_lokasi2', 'a_foto_lokasi3',
        'a_foto_kegiatan1', 'a_foto_kegiatan2', 'a_foto_kegiatan3',
        'c_nama_florist', 'b_alamat_florist', 'b_bukti_akta1', 'b_bukti_akta2', 'b_bukti_akta3',
        'b_bukti_akta4', 'b_bukti_akta5', 'b_bukti_akta6',
        'c2_nama_florist', 'c2_alamat_florist', 'c_foto_lokasi1', 'c_foto_lokasi2', 'c_foto_lokasi3',
        'c_foto_lokasi4', 'c_foto_lokasi5', 'c_foto_lokasi6',
        'd_pemberi_order1', 'd_tempat_lokasi1', 'd_tanggal_tahun1', 'd_deskripsi1',
        'd_foto_lokasi1', 'd_foto_lokasi2', 'd_foto_lokasi3', 'd_foto_lokasi4',
        'd_pemberi_order2', 'd_tempat_lokasi2', 'd_tanggal_tahun2', 'd_deskripsi2',
        'd_foto_lokasi5', 'd_foto_lokasi6', 'd_foto_lokasi7', 'd_foto_lokasi8',
        'd_pemberi_order3', 'd_tempat_lokasi3', 'd_tanggal_tahun3', 'd_deskripsi3',
        'd_foto_lokasi9', 'd_foto_lokasi10', 'd_foto_lokasi11', 'd_foto_lokasi12',
        'nilai'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}