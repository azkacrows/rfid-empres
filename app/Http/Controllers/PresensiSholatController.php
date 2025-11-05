<?php
// app/Http/Controllers/PresensiSholatController.php
namespace App\Http\Controllers;

use App\Models\PresensiSholat;
use App\Models\JadwalSholat;
use App\Models\PengaturanWaktu;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PresensiSholatController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $presensi = PresensiSholat::with('user')
            ->whereDate('tanggal', $today)
            ->latest()
            ->paginate(20);
        
        $jadwal = JadwalSholat::whereDate('tanggal', $today)->first();
        $toleransi = PengaturanWaktu::getSholatToleransi();
        
        // TAMBAHAN: Format hari dan tanggal dalam bahasa Indonesia
        $hari = Carbon::parse($today)->locale('id')->translatedFormat('l'); // Senin, Selasa, dst
        $tanggal = Carbon::parse($today)->locale('id')->translatedFormat('d F Y'); // 03 November 2025
        
        return view('presensi.sholat', compact('presensi', 'jadwal', 'toleransi', 'hari', 'tanggal'));
    }

public function scan(Request $request)
{
    $request->validate([
        'rfid_card' => 'required|string',
        'waktu_sholat' => 'required|in:subuh,dzuhur,ashar,maghrib,isya',
    ]);

    $user = User::where('rfid_card', $request->rfid_card)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Kartu RFID tidak terdaftar!'
        ], 404);
    }

    $today = Carbon::today();
    $jamSekarang = Carbon::now()->format('H:i:s');
    
    $existing = PresensiSholat::where('user_id', $user->id)
        ->whereDate('tanggal', $today)
        ->where('waktu_sholat', $request->waktu_sholat)
        ->first();

    // PERBAIKAN: Return dengan HTTP status 400 (Bad Request) bukan 200
    if ($existing) {
        return response()->json([
            'success' => false,
            'message' => 'Anda sudah melakukan presensi sholat ' . ucfirst($request->waktu_sholat) . ' hari ini!',
            'data' => [
                'user' => $user,
                'presensi' => $existing
            ]
        ], 400); // TAMBAHKAN STATUS CODE 400
    }

    // Ambil jadwal sholat
    $jadwal = JadwalSholat::whereDate('tanggal', $today)->first();
    if (!$jadwal) {
        return response()->json([
            'success' => false,
            'message' => 'Jadwal sholat hari ini belum tersedia!'
        ], 404);
    }

    $jamAdzan = $jadwal->{$request->waktu_sholat};
    
    // Hitung keterlambatan
    $keterlambatan = PresensiSholat::hitungKeterlambatan($jamAdzan, $jamSekarang);

    $presensi = PresensiSholat::create([
        'user_id' => $user->id,
        'tanggal' => $today,
        'waktu_sholat' => $request->waktu_sholat,
        'jam_presensi' => $jamSekarang,
        'keterangan' => 'hadir',
        'terlambat' => $keterlambatan['terlambat'],
        'menit_terlambat' => $keterlambatan['menit']
    ]);

    $pesan = $keterlambatan['terlambat'] 
        ? "Presensi berhasil! Terlambat {$keterlambatan['menit']} menit dari adzan" 
        : "Presensi sholat berhasil!";

    return response()->json([
        'success' => true,
        'message' => $pesan,
        'data' => [
            'user' => $user,
            'presensi' => $presensi
        ]
    ]);
}

    public function updateKeterangan(Request $request)
    {
        $request->validate([
            'presensi_id' => 'required|exists:presensi_sholat,id',
            'keterangan' => 'required|in:hadir,izin,sakit,tanpa_keterangan'
        ]);

        $presensi = PresensiSholat::findOrFail($request->presensi_id);
        $presensi->keterangan = $request->keterangan;
        $presensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Keterangan berhasil diperbarui!'
        ]);
    }

    public function getJadwal()
    {
        $today = Carbon::today();
        $jadwal = JadwalSholat::whereDate('tanggal', $today)->first();
        $toleransi = PengaturanWaktu::getSholatToleransi();
        
        // TAMBAHAN: Format hari dan tanggal
        $hari = Carbon::parse($today)->locale('id')->translatedFormat('l');
        $tanggal = Carbon::parse($today)->locale('id')->translatedFormat('d F Y');
        
        return response()->json([
            'success' => true,
            'jadwal' => $jadwal,
            'toleransi' => $toleransi ? $toleransi->toleransi_keterlambatan : 20,
            'hari' => $hari,
            'tanggal' => $tanggal
        ]);
    }
    public function getLatestPresensi()
{
    $today = Carbon::today();
    $presensi = PresensiSholat::with('user')
        ->whereDate('tanggal', $today)
        ->latest()
        ->get();
    
    $allUsers = User::where('role', 'user')->orderBy('name')->get();
    $presensiGrouped = $presensi->groupBy('user_id');
    
    $jadwal = JadwalSholat::whereDate('tanggal', $today)->first();
    
    return response()->json([
        'success' => true,
        'presensi' => $presensi,
        'all_users' => $allUsers,
        'presensi_grouped' => $presensiGrouped,
        'jadwal' => $jadwal
    ]);
}
public function export(Request $request)
{
    $tanggal = $request->tanggal ?? Carbon::today();
    
    $presensi = PresensiSholat::with('user')
        ->whereDate('tanggal', $tanggal)
        ->orderBy('waktu_sholat')
        ->orderBy('jam_presensi')
        ->get();
    
    $jadwal = JadwalSholat::whereDate('tanggal', $tanggal)->first();
    
    $filename = "Presensi_Sholat_" . Carbon::parse($tanggal)->format('d-m-Y') . ".csv";
    
    $headers = [
        'Content-Type' => 'text/csv; charset=utf-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
        'Pragma' => 'no-cache',
        'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        'Expires' => '0'
    ];
    
    $callback = function() use ($presensi, $jadwal) {
        $file = fopen('php://output', 'w');
        
        // BOM untuk UTF-8 (agar Excel bisa baca karakter Indonesia)
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Header CSV
        fputcsv($file, [
            'No', 
            'Nama', 
            'Waktu Sholat', 
            'Jam Adzan',
            'Jam Presensi', 
            'Status', 
            'Keterangan', 
            'Terlambat (menit)'
        ]);
        
        // Data
        foreach ($presensi as $index => $p) {
            $jamAdzan = $jadwal ? $jadwal->{$p->waktu_sholat} : '-';
            
            fputcsv($file, [
                $index + 1,
                $p->user->name,
                ucfirst($p->waktu_sholat),
                $jamAdzan,
                $p->jam_presensi,
                $p->terlambat ? 'Terlambat' : 'Tepat Waktu',
                ucfirst(str_replace('_', ' ', $p->keterangan)),
                $p->menit_terlambat ?? 0
            ]);
        }
        
        fclose($file);
    };
    
    return Response::stream($callback, 200, $headers);
}
}
