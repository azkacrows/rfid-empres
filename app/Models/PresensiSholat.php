<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PresensiSholat extends Model
{
    protected $table = 'presensi_sholat';
    
    protected $fillable = [
        'user_id',
        'tanggal',
        'waktu_sholat',
        'jam_presensi',
        'keterangan',
        'terlambat',
        'menit_terlambat'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'terlambat' => 'boolean'
    ];

    protected $attributes = [
        'keterangan' => 'tanpa_keterangan',
        'terlambat' => false,
        'menit_terlambat' => 0
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * ✅ FIXED: Hitung keterlambatan dengan urutan parameter yang BENAR
     */
    public static function hitungKeterlambatan($jamAdzan, $jamPresensi)
    {
        try {
            $adzan = Carbon::createFromFormat('H:i:s', $jamAdzan);
            $presensi = Carbon::createFromFormat('H:i:s', $jamPresensi);
            
            // ✅ FIXED: BALIK URUTAN PARAMETER!
            // Hitung dari ADZAN ke PRESENSI
            // Negatif (-) = Presensi SEBELUM adzan (terlalu awal)
            // Positif (+) = Presensi SETELAH adzan
            $selisihMenit = $adzan->diffInMinutes($presensi, false);
            
            // Ambil toleransi
            $toleransi = PengaturanWaktu::getSholatToleransi();
            $batasToleransi = $toleransi ? $toleransi->toleransi_keterlambatan : 20;
            
            \Log::info('🔍 Hitung Keterlambatan:', [
                'jam_adzan' => $jamAdzan,
                'jam_presensi' => $jamPresensi,
                'selisih_menit' => $selisihMenit,
                'batas_toleransi' => $batasToleransi
            ]);
            
            // ✅ LOGIC YANG BENAR
            if ($selisihMenit < 0) {
                // Presensi SEBELUM adzan (nilai negatif)
                return [
                    'terlambat' => false,
                    'menit' => 0,
                    'selisih_menit' => $selisihMenit,
                    'status' => 'terlalu_awal',
                    'keterangan' => 'Datang ' . abs($selisihMenit) . ' menit sebelum adzan'
                ];
            }
            
            if ($selisihMenit <= $batasToleransi) {
                // Presensi dalam toleransi (0 - batas toleransi menit setelah adzan)
                return [
                    'terlambat' => false,
                    'menit' => 0,
                    'selisih_menit' => $selisihMenit,
                    'status' => 'tepat_waktu',
                    'keterangan' => 'Tepat waktu (dalam toleransi ' . $batasToleransi . ' menit)'
                ];
            }
            
            // Terlambat (lebih dari batas toleransi)
            return [
                'terlambat' => true,
                'menit' => $selisihMenit,
                'selisih_menit' => $selisihMenit,
                'status' => 'terlambat',
                'keterangan' => 'Terlambat ' . $selisihMenit . ' menit dari adzan'
            ];
            
        } catch (\Exception $e) {
            \Log::error('❌ Error hitungKeterlambatan: ' . $e->getMessage());
            
            return [
                'terlambat' => false,
                'menit' => 0,
                'selisih_menit' => 0,
                'status' => 'error',
                'keterangan' => 'Error menghitung keterlambatan'
            ];
        }
    }
}