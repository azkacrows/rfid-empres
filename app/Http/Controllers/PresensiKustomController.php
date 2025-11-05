<?php
// app/Http/Controllers/PresensiKustomController.php - UPDATED
namespace App\Http\Controllers;

use App\Models\PresensiKustom;
use App\Models\JadwalPresensiKustom;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PresensiKustomController extends Controller
{
    // Tampilkan halaman presensi kustom
    public function index()
    {
        $today = Carbon::today();
        
        // Jadwal yang aktif hari ini
        $jadwalHariIni = JadwalPresensiKustom::where('tanggal', $today)
            ->where('aktif', true)
            ->orderBy('jam_mulai')
            ->get();
        
        // Presensi hari ini
        $presensi = PresensiKustom::with('user', 'jadwal')
            ->whereDate('tanggal', $today)
            ->latest()
            ->paginate(20);
        
        return view('presensi.kustom', compact('presensi', 'jadwalHariIni'));
    }

    // Halaman kelola jadwal (Admin only)
    public function jadwalIndex()
    {
        $jadwal = JadwalPresensiKustom::orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai')
            ->paginate(20);
        
        return view('presensi.kustom-jadwal', compact('jadwal'));
    }

    // Tambah jadwal baru
    public function storeJadwal(Request $request)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'keterangan' => 'nullable|string',
        ]);

        $jadwal = JadwalPresensiKustom::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'keterangan' => $request->keterangan,
            'aktif' => true
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan!',
            'data' => $jadwal
        ]);
    }

    // Update jadwal
    public function updateJadwal(Request $request, $id)
    {
        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'keterangan' => 'nullable|string',
            'aktif' => 'required|boolean'
        ]);

        $jadwal = JadwalPresensiKustom::findOrFail($id);
        $jadwal->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui!'
        ]);
    }

    // Hapus jadwal
    public function destroyJadwal($id)
    {
        $jadwal = JadwalPresensiKustom::findOrFail($id);
        $jadwal->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus!'
        ]);
    }

// Scan RFID untuk presensi
public function scan(Request $request)
{
    $request->validate([
        'rfid_card' => 'required|string',
        'jadwal_id' => 'required|exists:jadwal_presensi_kustom,id',
    ]);

    $user = User::where('rfid_card', $request->rfid_card)->first();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'Kartu RFID tidak terdaftar!',
            'data' => null
        ], 404);
    }

    $jadwal = JadwalPresensiKustom::findOrFail($request->jadwal_id);
    
    // Cek apakah sudah presensi untuk jadwal ini
    $existing = PresensiKustom::where('user_id', $user->id)
        ->where('jadwal_id', $jadwal->id)
        ->whereDate('tanggal', $jadwal->tanggal)
        ->first();

    if ($existing) {
        return response()->json([
            'success' => false,
            'message' => 'Anda sudah melakukan presensi untuk kegiatan ini!',
            'data' => [
                'user' => $user,
                'presensi' => $existing
            ]
        ], 400);
    }

    $jamSekarang = Carbon::now()->format('H:i:s');
    $jamMulai = Carbon::parse($jadwal->jam_mulai);
    $jamSelesai = Carbon::parse($jadwal->jam_selesai);
    $jamScan = Carbon::parse($jamSekarang);

    // ✅ FIX: Logic Status dan Keterangan yang benar
    $status = 'tanpa_keterangan';
    $keterangan = 'tanpa_keterangan';
    $terlambat = false;
    $menitTerlambat = 0;

    // Cek apakah scan dalam rentang waktu kegiatan
    if ($jamScan->between($jamMulai, $jamSelesai)) {
        // Scan tepat waktu
        $status = 'hadir';
        $keterangan = 'hadir';
        $terlambat = false;
    } elseif ($jamScan->greaterThan($jamSelesai)) {
        // Scan setelah jam selesai = terlambat
        $status = 'terlambat';
        $keterangan = 'hadir'; // Tetap hadir tapi terlambat
        $terlambat = true;
        $menitTerlambat = $jamScan->diffInMinutes($jamSelesai);
    } else {
        // Scan sebelum jam mulai = tanpa keterangan
        $status = 'tanpa_keterangan';
        $keterangan = 'tanpa_keterangan';
        $terlambat = false;
    }

    $presensi = PresensiKustom::create([
        'user_id' => $user->id,
        'jadwal_id' => $jadwal->id,
        'tanggal' => $jadwal->tanggal,
        'jam_mulai' => $jadwal->jam_mulai,
        'jam_selesai' => $jadwal->jam_selesai,
        'jam_scan' => $jamSekarang,
        'kepentingan' => $jadwal->nama_kegiatan,
        'status' => $status,
        'terlambat' => $terlambat,
        'menit_terlambat' => $menitTerlambat,
        'keterangan' => $keterangan
    ]);

    // Pesan yang lebih jelas
    $pesan = '';
    if ($status === 'hadir' && !$terlambat) {
        $pesan = "Presensi berhasil! Tepat waktu ✓";
    } elseif ($status === 'terlambat') {
        $pesan = "Presensi berhasil! Terlambat {$menitTerlambat} menit ⚠️";
    } else {
        $pesan = "Presensi tercatat (scan terlalu awal)";
    }

    return response()->json([
        'success' => true,
        'message' => $pesan,
        'data' => [
            'user' => $user,
            'presensi' => $presensi,
            'jadwal' => $jadwal
        ]
    ]);
}

    // Update keterangan manual
    public function updateKeterangan(Request $request)
    {
        $request->validate([
            'presensi_id' => 'required|exists:presensi_kustom,id',
            'keterangan' => 'required|in:hadir,izin,sakit,tanpa_keterangan'
        ]);

        $presensi = PresensiKustom::findOrFail($request->presensi_id);
        $presensi->keterangan = $request->keterangan;
        $presensi->save();

        return response()->json([
            'success' => true,
            'message' => 'Keterangan berhasil diperbarui!'
        ]);
    }

    // 🆕 AJAX: Get latest presensi untuk update tabel tanpa reload
    public function getLatestPresensi(Request $request)
    {
        $today = Carbon::today();
        
        $presensi = PresensiKustom::with('user', 'jadwal')
            ->whereDate('tanggal', $today)
            ->latest()
            ->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $presensi
        ]);
    }

    // 🆕 AJAX: Get stats untuk dashboard
    public function getStats(Request $request)
    {
        $today = Carbon::today();
        
        $totalHadir = PresensiKustom::whereDate('tanggal', $today)
            ->where('status', 'hadir')
            ->count();
        
        $totalTerlambat = PresensiKustom::whereDate('tanggal', $today)
            ->where('terlambat', true)
            ->count();
        
        $totalPresensi = PresensiKustom::whereDate('tanggal', $today)->count();
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_hadir' => $totalHadir,
                'total_terlambat' => $totalTerlambat,
                'total_presensi' => $totalPresensi
            ]
        ]);
    }
    // Get daftar santri yang belum presensi per jadwal
public function getBelumPresensi(Request $request, $jadwalId)
{
    $jadwal = JadwalPresensiKustom::findOrFail($jadwalId);
    
    // Ambil semua user dengan role 'user' (santri)
    $allUsers = User::where('role', 'user')->get();
    
    // Ambil yang sudah presensi
    $sudahPresensi = PresensiKustom::where('jadwal_id', $jadwalId)
        ->whereDate('tanggal', $jadwal->tanggal)
        ->pluck('user_id')
        ->toArray();
    
    // Filter yang belum presensi
    $belumPresensi = $allUsers->filter(function($user) use ($sudahPresensi) {
        return !in_array($user->id, $sudahPresensi);
    });
    
    return response()->json([
        'success' => true,
        'data' => [
            'jadwal' => $jadwal,
            'belum_presensi' => $belumPresensi->values(),
            'total_belum' => $belumPresensi->count(),
            'total_sudah' => count($sudahPresensi),
            'total_santri' => $allUsers->count()
        ]
    ]);
}
}