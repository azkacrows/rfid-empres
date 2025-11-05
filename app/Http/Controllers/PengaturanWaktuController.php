<?php
namespace App\Http\Controllers;

use App\Models\PengaturanWaktu;
use App\Models\JadwalSholat;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PengaturanWaktuController extends Controller
{
    public function index()
    {
        $pengaturan = PengaturanWaktu::orderBy('jenis')
            ->orderByRaw("FIELD(LOWER(nama), 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya')")
            ->orderBy('nama')
            ->get();
        
        return view('pengaturan.waktu', compact('pengaturan'));
    }

    public function create(Request $request)
    {
        // Validasi conditional berdasarkan jenis
        $rules = [
            'jenis' => 'required|in:sekolah,sholat,kustom',
            'nama' => 'required|string|max:255',
            'toleransi_keterlambatan' => 'required|integer|min:0'
        ];
        
        // Hanya wajib jam_mulai & jam_selesai untuk sekolah/kustom
        if (in_array($request->jenis, ['sekolah', 'kustom'])) {
            $rules['jam_mulai'] = 'required|date_format:H:i';
            $rules['jam_selesai'] = 'required|date_format:H:i|after:jam_mulai';
        }
        
        $request->validate($rules);

        try {
            // Logic berbeda untuk sholat vs sekolah/kustom
            if ($request->jenis === 'sholat') {
                // Untuk sholat, jam_mulai dan jam_selesai = NULL
                // Akan dihitung otomatis dari jadwal_sholat saat presensi
                
                // Cek apakah sudah ada pengaturan untuk waktu sholat ini
                $existing = PengaturanWaktu::where('jenis', 'sholat')
                    ->whereRaw('LOWER(nama) = ?', [strtolower($request->nama)])
                    ->first();
                
                if ($existing) {
                    return response()->json([
                        'success' => false,
                        'message' => "Pengaturan untuk {$request->nama} sudah ada. Gunakan Edit untuk mengubahnya."
                    ], 422);
                }
                
                $pengaturan = PengaturanWaktu::create([
                    'jenis' => 'sholat',
                    'nama' => ucfirst(strtolower($request->nama)), // Normalize: Subuh, Dzuhur, dll
                    'jam_mulai' => null,
                    'jam_selesai' => null,
                    'toleransi_keterlambatan' => $request->toleransi_keterlambatan,
                    'aktif' => true
                ]);
                
                $message = "Pengaturan waktu {$request->nama} berhasil ditambahkan! Waktu presensi akan otomatis mengikuti jadwal adzan.";
                
            } else {
                // Untuk sekolah/kustom gunakan jam yang diinput
                $pengaturan = PengaturanWaktu::create([
                    'jenis' => $request->jenis,
                    'nama' => $request->nama,
                    'jam_mulai' => $request->jam_mulai,
                    'jam_selesai' => $request->jam_selesai,
                    'toleransi_keterlambatan' => $request->toleransi_keterlambatan,
                    'aktif' => true
                ]);
                
                $message = "Pengaturan waktu berhasil ditambahkan!";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $pengaturan
            ]);

        } catch (\Exception $e) {
            \Log::error('Error create pengaturan waktu', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, PengaturanWaktu $pengaturan)
    {
        // Validasi conditional
        $rules = [
            'nama' => 'required|string|max:255',
            'toleransi_keterlambatan' => 'required|integer|min:0',
            'aktif' => 'required|boolean'
        ];
        
        // Hanya validasi jam untuk sekolah/kustom
        if (in_array($pengaturan->jenis, ['sekolah', 'kustom'])) {
            $rules['jam_mulai'] = 'required|date_format:H:i';
            $rules['jam_selesai'] = 'required|date_format:H:i|after:jam_mulai';
        }
        
        $request->validate($rules);

        try {
            $updateData = [
                'nama' => $pengaturan->jenis === 'sholat' 
                    ? ucfirst(strtolower($request->nama)) 
                    : $request->nama,
                'toleransi_keterlambatan' => $request->toleransi_keterlambatan,
                'aktif' => $request->aktif
            ];
            
            // Hanya update jam untuk sekolah/kustom
            if (in_array($pengaturan->jenis, ['sekolah', 'kustom'])) {
                $updateData['jam_mulai'] = $request->jam_mulai;
                $updateData['jam_selesai'] = $request->jam_selesai;
            }
            
            $pengaturan->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan waktu berhasil diperbarui!'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error update pengaturan waktu', [
                'error' => $e->getMessage(),
                'id' => $pengaturan->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PengaturanWaktu $pengaturan)
    {
        try {
            // Prevent delete pengaturan sekolah & sholat default
            if ($pengaturan->jenis === 'sekolah' || $pengaturan->jenis === 'sholat') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pengaturan sekolah dan sholat tidak dapat dihapus. Gunakan fitur nonaktifkan jika tidak digunakan.'
                ], 403);
            }
            
            $pengaturan->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan waktu berhasil dihapus!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus: ' . $e->getMessage()
            ], 500);
        }
    }
}