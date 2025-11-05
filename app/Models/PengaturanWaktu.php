<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PengaturanWaktu extends Model
{
    protected $table = 'pengaturan_waktu';
    
    protected $fillable = [
        'jenis', 'nama', 'jam_mulai', 'jam_selesai', 
        'toleransi_keterlambatan', 'aktif'
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'toleransi_keterlambatan' => 'integer',
    ];

    /**
     * Get waktu presensi aktual (untuk sholat ambil dari jadwal hari ini)
     */
    public function getWaktuPresensi($tanggal = null)
    {
        $tanggal = $tanggal ?? today();
        
        if ($this->jenis === 'sholat') {
            // Ambil jadwal sholat hari ini
            $jadwalSholat = \App\Models\JadwalSholat::whereDate('tanggal', $tanggal)->first();
            
            if (!$jadwalSholat) {
                return null;
            }
            
            // Map nama waktu sholat ke field database
            // Nama bisa 'Subuh', 'Dzuhur', dll → lowercase jadi 'subuh', 'dzuhur'
            $waktuField = strtolower($this->nama);
            
            if (!isset($jadwalSholat->$waktuField)) {
                return null;
            }
            
            $jamMulai = $jadwalSholat->$waktuField;
            
            // Hitung jam selesai = jam mulai + toleransi
            $jamSelesai = Carbon::parse($jamMulai)
                ->addMinutes($this->toleransi_keterlambatan)
                ->format('H:i:s');
            
            return [
                'jam_mulai' => $jamMulai,
                'jam_selesai' => $jamSelesai,
                'toleransi' => $this->toleransi_keterlambatan
            ];
        } else {
            // Untuk sekolah/kustom gunakan jam dari database
            return [
                'jam_mulai' => $this->jam_mulai,
                'jam_selesai' => $this->jam_selesai,
                'toleransi' => $this->toleransi_keterlambatan
            ];
        }
    }

    /**
     * Cek apakah waktu masih dalam toleransi
     */
    public function isWithinTolerance($waktuPresensi, $tanggal = null)
    {
        $waktu = $this->getWaktuPresensi($tanggal);
        
        if (!$waktu) {
            return false;
        }
        
        $waktuPresensiCarbon = Carbon::parse($waktuPresensi);
        $batasAkhir = Carbon::parse($waktu['jam_selesai']);
        
        return $waktuPresensiCarbon->lte($batasAkhir);
    }

    /**
     * Get display waktu presensi (untuk tampilan di view)
     */
    public function getDisplayWaktuAttribute()
    {
        if ($this->jenis === 'sholat') {
            $waktu = $this->getWaktuPresensi();
            
            if (!$waktu) {
                return [
                    'jam_mulai' => 'Ikuti jadwal adzan',
                    'jam_selesai' => "+ {$this->toleransi_keterlambatan} menit"
                ];
            }
            
            return [
                'jam_mulai' => substr($waktu['jam_mulai'], 0, 5), // HH:MM
                'jam_selesai' => substr($waktu['jam_selesai'], 0, 5)
            ];
        }
        
        return [
            'jam_mulai' => substr($this->jam_mulai, 0, 5),
            'jam_selesai' => substr($this->jam_selesai, 0, 5)
        ];
    }

    // Helper methods existing
    public static function getSekolahMasuk()
    {
        return self::where('jenis', 'sekolah')
            ->where('nama', 'Jam Masuk')
            ->where('aktif', true)
            ->first();
    }

    public static function getSekolahPulang()
    {
        return self::where('jenis', 'sekolah')
            ->where('nama', 'Jam Pulang')
            ->where('aktif', true)
            ->first();
    }

    public static function getSholatByNama($nama)
    {
        return self::where('jenis', 'sholat')
            ->whereRaw('LOWER(nama) = ?', [strtolower($nama)])
            ->where('aktif', true)
            ->first();
    }

    public static function getSholatToleransi()
    {
        return self::where('jenis', 'sholat')
            ->where('aktif', true)
            ->first();
    }

    public static function getAllActiveSholat()
    {
        return self::where('jenis', 'sholat')
            ->where('aktif', true)
            ->orderByRaw("FIELD(LOWER(nama), 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya')")
            ->get();
    }
}