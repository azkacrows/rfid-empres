@extends('layouts.app')

@section('title', 'Presensi Sholat')

@section('styles')
<style>
    .waktu-subuh { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .waktu-dzuhur { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
    .waktu-ashar { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
    .waktu-maghrib { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
    .waktu-isya { background: linear-gradient(135deg, #30cfd0 0%, #330867 100%); }
    .user-sudah-absen { background-color: #d4edda !important; }
    .user-belum-absen { background-color: #f8d7da !important; }
    
    /* Animasi notifikasi */
    .alert-sm {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Pulse loading */
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }
    
    .alert-info.alert-sm {
        animation: pulse 1s ease-in-out infinite;
    }
    
    /* Highlight input aktif */
    .scan-input:focus {
        border: 2px solid #28a745;
        box-shadow: 0 0 10px rgba(40, 167, 69, 0.3);
        transition: all 0.3s ease;
    }
    
    /* Loading overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        display: none;
    }
    
    .loading-spinner {
        background: white;
        padding: 30px;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Presensi Sholat</h2>
    
    <!-- Card Jadwal Sholat dengan Hari & Tanggal -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Jadwal Sholat Hari Ini</h5>
                </div>
                <div class="card-body">
                    <!-- Hari dan Tanggal -->
                    <div class="text-center mb-3 pb-3 border-bottom">
                        <h3 class="mb-1 text-success fw-bold">{{ $hari ?? 'N/A' }}</h3>
                        <h5 class="text-muted">{{ $tanggal ?? 'N/A' }}</h5>
                    </div>
                    
                    @if($jadwal)
                    <div class="row text-center">
                        <div class="col">
                            <h6>Subuh</h6>
                            <h4>{{ $jadwal->subuh }}</h4>
                        </div>
                        <div class="col">
                            <h6>Dzuhur</h6>
                            <h4>{{ $jadwal->dzuhur }}</h4>
                        </div>
                        <div class="col">
                            <h6>Ashar</h6>
                            <h4>{{ $jadwal->ashar }}</h4>
                        </div>
                        <div class="col">
                            <h6>Maghrib</h6>
                            <h4>{{ $jadwal->maghrib }}</h4>
                        </div>
                        <div class="col">
                            <h6>Isya</h6>
                            <h4>{{ $jadwal->isya }}</h4>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Batas Presensi :</strong> {{ $toleransi ? $toleransi->toleransi_keterlambatan : 20 }} menit setelah adzan
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Jadwal sholat belum tersedia untuk hari ini. Silakan hubungi admin untuk sinkronisasi.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Card Input Presensi per Waktu Sholat -->
    <div class="row">
        @foreach(['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'] as $waktu)
        @php
            $sudahAbsen = $presensi->where('waktu_sholat', $waktu)->pluck('user_id')->toArray();
            $colorClass = 'waktu-' . $waktu;
        @endphp
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="card">
                <div class="card-header {{ $colorClass }} text-white">
                    <h6 class="mb-0">
                        <i class="fas fa-mosque me-2"></i>
                        Presensi {{ ucfirst($waktu) }}
                    </h6>
                </div>
                <div class="card-body">
    <div class="mb-2" id="stats_{{ $waktu }}">
        <small class="text-muted">
            <i class="fas fa-check-circle text-success"></i> Sudah: <strong>{{ count($sudahAbsen) }}</strong> | 
            <i class="fas fa-times-circle text-danger"></i> Belum: <strong>{{ \App\Models\User::where('role', 'user')->count() - count($sudahAbsen) }}</strong>
        </small>
    </div>
    <input type="text" 
           class="form-control scan-input" 
           data-waktu="{{ $waktu }}" 
           placeholder="Scan kartu RFID..."
           autocomplete="off">
    <div id="result_{{ $waktu }}" class="mt-2"></div>
</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Tabel Status Absensi Semua User -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Status Absensi Semua User Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th class="text-center">Subuh</th>
                            <th class="text-center">Dzuhur</th>
                            <th class="text-center">Ashar</th>
                            <th class="text-center">Maghrib</th>
                            <th class="text-center">Isya</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $allUsers = \App\Models\User::where('role', 'user')->orderBy('name')->get();
                            $presensiGrouped = $presensi->groupBy('user_id');
                        @endphp
                        @foreach($allUsers as $index => $user)
                        @php
                            $userPresensi = $presensiGrouped->get($user->id, collect());
                            $waktuSholat = ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'];
                            $totalHadir = 0;
                        @endphp
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $user->name }}</strong></td>
                            @foreach($waktuSholat as $waktu)
                                @php
                                    $p = $userPresensi->where('waktu_sholat', $waktu)->first();
                                    $hadir = $p != null;
                                    if ($hadir) $totalHadir++;
                                @endphp
                                <td class="text-center {{ $hadir ? 'user-sudah-absen' : 'user-belum-absen' }}">
                                    @if($hadir)
                                        <i class="fas fa-check-circle text-success"></i>
                                        <br><small>{{ $p->jam_presensi }}</small>
                                        @if($p->terlambat)
                                            <br><small class="text-danger">(+{{ $p->menit_terlambat }}m)</small>
                                        @endif
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center"><strong>{{ $totalHadir }}/5</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tabel Detail Presensi Sholat -->
    <div class="card mt-4">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Detail Presensi Sholat Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Waktu Sholat</th>
                            <th>Jam Adzan</th>
                            <th>Jam Presensi</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensi as $index => $p)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $p->user->name }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($p->waktu_sholat) }}
                                </span>
                            </td>
                            <td>{{ $jadwal ? $jadwal->{$p->waktu_sholat} : '-' }}</td>
                            <td>
                                {{ $p->jam_presensi }}
                                @if($p->terlambat)
                                    <br><small class="text-danger">(+{{ $p->menit_terlambat }} menit)</small>
                                @endif
                            </td>
                            <td>
                                @if($p->terlambat)
                                    <span class="badge bg-warning">Terlambat</span>
                                @else
                                    <span class="badge bg-success">Tepat Waktu</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->keterangan == 'hadir' ? 'success' : ($p->keterangan == 'izin' ? 'info' : ($p->keterangan == 'sakit' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $p->keterangan)) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="updateKeterangan({{ $p->id }})">
                                    <i class="fas fa-edit"></i> Ubah
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada presensi sholat hari ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Keterangan -->
<div class="modal fade" id="modalKeterangan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Keterangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="presensi_id">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="keterangan" value="hadir" id="hadir">
                    <label class="form-check-label" for="hadir">Hadir</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="keterangan" value="izin" id="izin">
                    <label class="form-check-label" for="izin">Izin</label>
                </div>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="keterangan" value="sakit" id="sakit">
                    <label class="form-check-label" for="sakit">Sakit</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="keterangan" value="tanpa_keterangan" id="tanpa_keterangan">
                    <label class="form-check-label" for="tanpa_keterangan">Tanpa Keterangan</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="simpanKeterangan()">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentFocusedInput = null;

    // ============================================
    // 🎵 SOUND EFFECTS
    // ============================================
    function playSound(type) {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            if (type === 'success') {
                oscillator.frequency.value = 800;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.1);
            } else if (type === 'error') {
                oscillator.frequency.value = 200;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
            } else if (type === 'warning') {
                oscillator.frequency.value = 500;
                gainNode.gain.value = 0.3;
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            }
        } catch(e) {
            console.log('Audio tidak didukung di browser ini');
        }
    }

    // ============================================
    // 🚀 PAGE LOAD
    // ============================================
    $(document).ready(function() {
        $('.scan-input').first().focus();
        currentFocusedInput = $('.scan-input').first();
    });

    // ============================================
    // 🎯 EVENT HANDLERS
    // ============================================
    $('.scan-input').on('focus', function() {
        currentFocusedInput = $(this);
    });

    $('.scan-input').on('keypress', function(e) {
        if(e.which === 13) {
            e.preventDefault();
            
            let rfid = $(this).val().trim();
            let waktu = $(this).data('waktu');
            let inputElement = $(this);
            
            if(rfid) {
                scanPresensi(rfid, waktu, inputElement);
            }
        }
    });

    // ⌨️ Keyboard shortcuts
    $(document).on('keydown', function(e) {
        if ($('.modal').hasClass('show')) return;
        
        if (e.altKey && e.which === 49) {
            e.preventDefault();
            $('.scan-input[data-waktu="subuh"]').focus();
        } else if (e.altKey && e.which === 50) {
            e.preventDefault();
            $('.scan-input[data-waktu="dzuhur"]').focus();
        } else if (e.altKey && e.which === 51) {
            e.preventDefault();
            $('.scan-input[data-waktu="ashar"]').focus();
        } else if (e.altKey && e.which === 52) {
            e.preventDefault();
            $('.scan-input[data-waktu="maghrib"]').focus();
        } else if (e.altKey && e.which === 53) {
            e.preventDefault();
            $('.scan-input[data-waktu="isya"]').focus();
        }
    });

    // ============================================
    // 📡 SCAN PRESENSI
    // ============================================
    function scanPresensi(rfid, waktu, inputElement) {
        $.ajax({
            url: '{{ route("presensi.sholat.scan") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rfid_card: rfid,
                waktu_sholat: waktu
            },
            beforeSend: function() {
                $(`#result_${waktu}`).html(`
                    <div class="alert alert-info alert-sm">
                        <i class="fas fa-spinner fa-spin me-2"></i>
                        Memproses...
                    </div>
                `);
            },
            success: function(response) {
                // ✅ PRESENSI BERHASIL
                let alertClass = response.data.presensi.terlambat ? 'alert-warning' : 'alert-success';
                let soundType = response.data.presensi.terlambat ? 'warning' : 'success';
                
                let notifHtml = `
                    <div class="alert ${alertClass} alert-sm">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>${response.data.user.name}</strong><br>
                        ${response.message}
                    </div>
                `;
                
                $(`#result_${waktu}`).html(notifHtml);
                
                // Play beep sound
                playSound(soundType);
                
                // Clear input & focus
                inputElement.val('');
                setTimeout(() => inputElement.focus(), 100);
                
                // Update counter tanpa reload
                updateCounter(waktu);
                updateTable(response.data);
                
                // Auto clear notifikasi setelah 5 detik
                setTimeout(() => {
                    $(`#result_${waktu}`).fadeOut('slow', function() {
                        $(this).html('').show();
                    });
                }, 5000);
            },
            error: function(xhr) {
                // ❌ ERROR
                let errorMessage = 'Terjadi kesalahan';
                let userName = '';
                
                if (xhr.responseJSON) {
                    errorMessage = xhr.responseJSON.message || errorMessage;
                    
                    if (xhr.responseJSON.data && xhr.responseJSON.data.user) {
                        userName = xhr.responseJSON.data.user.name;
                    }
                }
                
                let alertClass = 'alert-danger';
                let iconClass = 'fa-exclamation-circle';
                let soundType = 'error';
                
                if (xhr.status === 400) {
                    alertClass = 'alert-warning';
                    iconClass = 'fa-info-circle';
                    soundType = 'warning';
                }
                
                let notifHtml = `
                    <div class="alert ${alertClass} alert-sm">
                        <i class="fas ${iconClass} me-2"></i>
                        ${userName ? '<strong>' + userName + '</strong><br>' : ''}
                        ${errorMessage}
                    </div>
                `;
                
                $(`#result_${waktu}`).html(notifHtml);
                
                // Play beep sound
                playSound(soundType);
                
                // Clear input & focus
                inputElement.val('');
                setTimeout(() => inputElement.focus(), 100);
                
                // Auto clear notifikasi error setelah 5 detik
                setTimeout(() => {
                    $(`#result_${waktu}`).fadeOut('slow', function() {
                        $(this).html('').show();
                    });
                }, 5000);
            }
        });
    }

    // ============================================
    // 🔄 UPDATE COUNTER (TANPA RELOAD)
    // ============================================
    function updateCounter(waktu) {
        // Update counter "Sudah" di card
        let currentCount = parseInt($(`#stats_${waktu} strong`).first().text()) || 0;
        let newCount = currentCount + 1;
        
        $(`#stats_${waktu}`).html(`
            <small class="text-muted">
                <i class="fas fa-check-circle text-success"></i> Sudah: <strong>${newCount}</strong> | 
                <i class="fas fa-times-circle text-danger"></i> Belum: <strong>${parseInt($(`#stats_${waktu} strong`).last().text()) - 1}</strong>
            </small>
        `);
    }

    // ============================================
    // 📊 UPDATE TABLE (TANPA RELOAD)
    // ============================================
    function updateTable(data) {
        // Refresh tabel status absensi via AJAX
        $.ajax({
            url: '{{ route("presensi.sholat.index") }}',
            method: 'GET',
            success: function(html) {
                // Update hanya bagian tbody tabel
                let newTableBody = $(html).find('.table-bordered tbody').html();
                $('.table-bordered tbody').html(newTableBody);
                
                // Update tabel detail presensi
                let newDetailBody = $(html).find('.table-hover tbody').html();
                $('.table-hover tbody').html(newDetailBody);
            }
        });
    }

    // ============================================
    // 🔄 AUTO RE-FOCUS
    // ============================================
    setInterval(function() {
        if (!$('input:focus').length && !$('select:focus').length && !$('textarea:focus').length && !$('.modal').hasClass('show')) {
            if (currentFocusedInput) {
                currentFocusedInput.focus();
            }
        }
    }, 3000);

    // ============================================
    // 📝 UPDATE KETERANGAN
    // ============================================
    function updateKeterangan(id) {
        $('#presensi_id').val(id);
        $('#modalKeterangan').modal('show');
    }

    function simpanKeterangan() {
        let presensiId = $('#presensi_id').val();
        let keterangan = $('input[name="keterangan"]:checked').val();

        if(!keterangan) {
            alert('Pilih keterangan terlebih dahulu!');
            return;
        }

        $.ajax({
            url: '{{ route("presensi.sholat.update") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                presensi_id: presensiId,
                keterangan: keterangan
            },
            success: function(response) {
                $('#modalKeterangan').modal('hide');
                
                // Update tabel tanpa reload
                updateTable({});
                
                // Notifikasi
                alert('Keterangan berhasil diubah!');
            },
            error: function(xhr) {
                alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }

    // ============================================
    // 🔄 AUTO REFRESH JADWAL & TABEL
    // ============================================
    setInterval(function() {
        // Refresh jadwal
        $.get('{{ route("presensi.sholat.jadwal") }}', function(response) {
            // Update jadwal jika diperlukan
        });
        
        // Refresh tabel
        updateTable({});
    }, 60000); // Refresh setiap 1 menit
</script>
@endsection