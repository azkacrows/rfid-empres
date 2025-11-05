<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class JadwalSholat extends Model
{
    protected $table = 'jadwal_sholat';
    
    protected $fillable = [
        'tanggal', 'hari', 'subuh', 'dzuhur', 'ashar', 'maghrib', 'isya',
        'is_manual', 'last_synced_at', 'edited_by'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_manual' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    // Relasi ke user yang edit
    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Sync jadwal dari API untuk range tanggal
     * Hanya update yang belum di-edit manual (is_manual = false)
     */
    public static function syncFromAPI($startDate, $endDate, $city = 'Jakarta', $country = 'Indonesia')
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $synced = 0;
        $skipped = 0;

        while ($start->lte($end)) {
            try {
                // Cek apakah sudah ada dan manual
                $existing = self::whereDate('tanggal', $start)->first();
                
                if ($existing && $existing->is_manual) {
                    // Skip jika sudah di-edit manual
                    $skipped++;
                    $start->addDay();
                    continue;
                }

                // Ambil dari API
                $response = Http::timeout(10)->get('https://api.aladhan.com/v1/timingsByCity', [
                    'city' => $city,
                    'country' => $country,
                    'method' => 2, // Metode ISNA
                    'date' => $start->format('d-m-Y')
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $timings = $data['data']['timings'];

                    // Format waktu (remove timezone)
                    $formatTime = function($time) {
                        return substr($time, 0, 5); // Ambil HH:MM saja
                    };

                    self::updateOrCreate(
                        ['tanggal' => $start->format('Y-m-d')],
                        [
                            'subuh' => $formatTime($timings['Fajr']),
                            'dzuhur' => $formatTime($timings['Dhuhr']),
                            'ashar' => $formatTime($timings['Asr']),
                            'maghrib' => $formatTime($timings['Maghrib']),
                            'isya' => $formatTime($timings['Isha']),
                            'is_manual' => false,
                            'last_synced_at' => now(),
                            'edited_by' => null
                        ]
                    );

                    $synced++;
                }

                // Rate limiting (max 1 request per second)
                sleep(1);

            } catch (\Exception $e) {
                \Log::error('Error syncing jadwal for ' . $start->format('Y-m-d') . ': ' . $e->getMessage());
            }

            $start->addDay();
        }

        return [
            'synced' => $synced,
            'skipped' => $skipped
        ];
    }

    /**
     * Bulk update waktu sholat tertentu untuk range tanggal
     * Contoh: Update semua Dzuhur jadi 12:00 untuk bulan ini
     */
    public static function bulkUpdateWaktu($startDate, $endDate, $waktuSholat, $jamBaru, $userId)
    {
        $updated = self::whereBetween('tanggal', [$startDate, $endDate])
            ->update([
                $waktuSholat => $jamBaru,
                'is_manual' => true,
                'edited_by' => $userId,
                'updated_at' => now()
            ]);

        return $updated;
    }
}