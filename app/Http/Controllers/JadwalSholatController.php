<?php
// app/Http/Controllers/JadwalSholatController.php
namespace App\Http\Controllers;

use App\Models\JadwalSholat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JadwalSholatController extends Controller
{
    public function index()
    {
        $jadwal = JadwalSholat::orderBy('tanggal', 'desc')->paginate(31);
        return view('jadwal-sholat.index', compact('jadwal'));
    }

public function syncJadwal(Request $request)
{
    $request->validate([
        'bulan' => 'required|integer|min:1|max:12',
        'tahun' => 'required|integer|min:2020|max:2030'
    ]);

    try {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $kodeKota = 1632; // Kota Kediri (terdekat Pacet, Mojokerto)
        
        $url = "https://api.myquran.com/v2/sholat/jadwal/{$kodeKota}/{$tahun}/{$bulan}";
        
        $response = Http::timeout(30)->get($url);
        
        if (!$response->successful()) {
            throw new \Exception('Gagal mengambil data dari API');
        }
        
        $data = $response->json();
        
        if (!isset($data['data']['jadwal'])) {
            throw new \Exception('Format data tidak valid');
        }
        
        $jadwalList = $data['data']['jadwal'];
        $lokasi = $data['data']['lokasi'];
        $totalInserted = 0;
        
        foreach ($jadwalList as $jadwal) {
            // Parse tanggal dari API
            // Format: "Sabtu, 01/06/2024"
            $tanggalParts = explode(', ', $jadwal['tanggal']);
            $hari = $tanggalParts[0]; // "Sabtu"
            $tanggalStr = $jadwal['date']; // "2024-06-01"
            
            JadwalSholat::updateOrCreate(
                ['tanggal' => $tanggalStr],
                [
                    'hari' => $hari, // 🆕 Simpan hari
                    'subuh' => $jadwal['subuh'],
                    'dzuhur' => $jadwal['dzuhur'],
                    'ashar' => $jadwal['ashar'],
                    'maghrib' => $jadwal['maghrib'],
                    'isya' => $jadwal['isya']
                ]
            );
            
            $totalInserted++;
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil sinkronisasi {$totalInserted} hari untuk bulan {$bulan}/{$tahun}",
            'data' => [
                'total' => $totalInserted,
                'lokasi' => $lokasi
            ]
        ]);

    } catch (\Exception $e) {
        \Log::error('Error sync jadwal sholat', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal sinkronisasi: ' . $e->getMessage()
        ], 500);
    }
}
    public function bulkOverride(Request $request)
    {
        $request->validate([
            'bulan' => 'required|integer|min:1|max:12',
            'tahun' => 'required|integer|min:2020|max:2100',
            'waktu_sholat' => 'required|in:subuh,dzuhur,ashar,maghrib,isya',
            'jam_baru' => 'required|date_format:H:i'
        ]);

        try {
            $bulan = (int)$request->bulan;
            $tahun = (int)$request->tahun;
            $waktuSholat = $request->waktu_sholat;
            $jamBaru = $request->jam_baru;

            // Buat range tanggal untuk bulan tersebut
            $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
            $endDate = Carbon::create($tahun, $bulan, 1)->endOfMonth();

            // Update semua jadwal dalam range bulan tersebut
            $updated = JadwalSholat::whereBetween('tanggal', [
                    $startDate->format('Y-m-d'),
                    $endDate->format('Y-m-d')
                ])
                ->update([
                    $waktuSholat => $jamBaru,
                    'is_manual' => true,
                    'edited_by' => auth()->id()
                ]);

            return response()->json([
                'success' => true,
                'message' => "Berhasil update {$updated} jadwal {$waktuSholat} menjadi {$jamBaru} untuk bulan {$bulan}/{$tahun}",
                'data' => [
                    'total_updated' => $updated,
                    'waktu_sholat' => $waktuSholat,
                    'jam_baru' => $jamBaru,
                    'periode' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error bulk override", [
                'message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Edit jadwal individual
     */
/**
 * Edit jadwal individual
 */
public function update(Request $request, $id)
{
    // Validasi dengan format yang lebih fleksibel
    $request->validate([
        'subuh' => 'required|string',
        'dzuhur' => 'required|string',
        'ashar' => 'required|string',
        'maghrib' => 'required|string',
        'isya' => 'required|string',
    ]);

    try {
        $jadwal = JadwalSholat::findOrFail($id);
        
        // Helper function untuk normalize format waktu
        $normalizeTime = function($time) {
            // Ambil hanya HH:MM (buang detik jika ada)
            return substr($time, 0, 5);
        };
        
        $jadwal->update([
            'subuh' => $normalizeTime($request->subuh),
            'dzuhur' => $normalizeTime($request->dzuhur),
            'ashar' => $normalizeTime($request->ashar),
            'maghrib' => $normalizeTime($request->maghrib),
            'isya' => $normalizeTime($request->isya),
            'is_manual' => true,
            'edited_by' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diupdate untuk tanggal ' . $jadwal->tanggal->format('d M Y')
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Jadwal tidak ditemukan'
        ], 404);
        
    } catch (\Exception $e) {
        \Log::error('Error update jadwal', [
            'id' => $id,
            'request' => $request->all(),
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal update: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Hapus jadwal
     */
    public function destroy($id)
    {
        try {
            $jadwal = JadwalSholat::findOrFail($id);
            $tanggal = $jadwal->tanggal->format('d M Y');
            $jadwal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Jadwal tanggal ' . $tanggal . ' berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal hapus: ' . $e->getMessage()
            ], 500);
        }
    }
}