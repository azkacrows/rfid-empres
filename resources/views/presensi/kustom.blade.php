<!-- resources/views/presensi/kustom.blade.php - UPDATED -->
@extends('layouts.app')

@section('title', 'Presensi Kustom')

@section('styles')
<style>
    .alert-sm {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
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
    
    .scan-input:focus {
        border: 2px solid #0d6efd;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
        transition: all 0.3s ease;
    }
    
    .badge-ontime {
        background-color: #28a745;
        color: white;
    }
    
    .badge-terlambat {
        background-color: #ffc107;
        color: #000;
    }
    
    .card {
        transition: all 0.3s ease;
    }
    
    .card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .stats-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
    
    .belum-presensi-list {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .belum-presensi-list::-webkit-scrollbar {
        width: 8px;
    }
    
    .belum-presensi-list::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .belum-presensi-list::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Presensi Kustom</h2>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('presensi.kustom.jadwal.index') }}" class="btn btn-primary">
            <i class="fas fa-calendar-plus me-2"></i>Kelola Jadwal
        </a>
        @endif
    </div>
    
    <!-- Jadwal Hari Ini -->
    @if($jadwalHariIni->count() > 0)
    <div class="row mb-4">
        @foreach($jadwalHariIni as $jadwal)
        <div class="col-md-4 mb-3">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            {{ $jadwal->nama_kegiatan }}
                        </h6>
                        <button class="btn btn-sm btn-light" onclick="toggleBelumPresensi({{ $jadwal->id }})" title="Lihat yang belum presensi">
                            <i class="fas fa-users"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stats -->
                    <div class="mb-3" id="stats_{{ $jadwal->id }}">
                        <div class="d-flex justify-content-between">
                            <span class="stats-badge bg-success text-white">
                                <i class="fas fa-check-circle"></i> Sudah: <strong id="sudah_{{ $jadwal->id }}">0</strong>
                            </span>
                            <span class="stats-badge bg-danger text-white">
                                <i class="fas fa-times-circle"></i> Belum: <strong id="belum_{{ $jadwal->id }}">0</strong>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Daftar Belum Presensi (Hidden by default) -->
                    <div id="belum_list_{{ $jadwal->id }}" class="mb-3" style="display:none;">
                        <div class="alert alert-warning mb-2">
                            <strong>Belum Presensi:</strong>
                        </div>
                        <div class="belum-presensi-list">
                            <ul class="list-group list-group-flush" id="belum_items_{{ $jadwal->id }}">
                                <!-- Will be populated via AJAX -->
                            </ul>
                        </div>
                    </div>
                    
                    <p class="mb-2">
                        <strong>Waktu:</strong> {{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}
                    </p>
                    @if($jadwal->keterangan)
                    <p class="mb-3"><small class="text-muted">{{ $jadwal->keterangan }}</small></p>
                    @endif
                    
                    <input type="text" 
                           class="form-control scan-input mb-2" 
                           data-jadwal-id="{{ $jadwal->id }}" 
                           placeholder="Scan RFID untuk presensi..."
                           autocomplete="off">
                    <div id="result_{{ $jadwal->id }}"></div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Tidak ada jadwal kegiatan untuk hari ini. 
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('presensi.kustom.jadwal.index') }}">Tambah Jadwal</a>
        @endif
    </div>
    @endif

    <!-- Daftar Presensi Hari Ini -->
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Daftar Presensi Hari Ini</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Kegiatan</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Jam Scan</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($presensi as $index => $p)
                        <tr>
                            <td>{{ $presensi->firstItem() + $index }}</td>
                            <td>{{ $p->user->name }}</td>
                            <td>{{ $p->kepentingan }}</td>
                            <td>{{ $p->jam_mulai }}</td>
                            <td>{{ $p->jam_selesai }}</td>
                            <td>
                                {{ $p->jam_scan ?? '-' }}
                                @if($p->terlambat && $p->jam_scan)
                                    <br><small class="text-danger">(+{{ $p->menit_terlambat }} menit)</small>
                                @endif
                            </td>
                            <td>
                                @if($p->status == 'hadir' && !$p->terlambat)
                                    <span class="badge badge-ontime">Tepat Waktu</span>
                                @elseif($p->status == 'terlambat' || $p->terlambat)
                                    <span class="badge badge-terlambat">Terlambat</span>
                                @else
                                    <span class="badge bg-secondary">Tanpa Keterangan</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->keterangan == 'hadir' ? 'success' : ($p->keterangan == 'izin' ? 'info' : ($p->keterangan == 'sakit' ? 'warning' : 'secondary')) }}">
                                    {{ ucfirst(str_replace('_', ' ', $p->keterangan)) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="updateKeterangan({{ $p->id }}, '{{ $p->keterangan }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">Belum ada presensi hari ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $presensi->links() }}
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
    let csrf_token = '{{ csrf_token() }}';

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
        // Auto focus ke input pertama
        $('.scan-input').first().focus();
        currentFocusedInput = $('.scan-input').first();
        
        // Load initial stats untuk semua jadwal
        $('.scan-input').each(function() {
            let jadwalId = $(this).data('jadwal-id');
            updateStats(jadwalId);
        });
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
            let jadwalId = $(this).data('jadwal-id');
            let inputElement = $(this);
            
            if(rfid) {
                scanPresensi(rfid, jadwalId, inputElement);
            }
        }
    });

    // ============================================
    // 📡 SCAN PRESENSI
    // ============================================
    function scanPresensi(rfid, jadwalId, inputElement) {
        $.ajax({
            url: '{{ route("presensi.kustom.scan") }}',
            method: 'POST',
            data: {
                _token: csrf_token,
                rfid_card: rfid,
                jadwal_id: jadwalId
            },
            beforeSend: function() {
                $(`#result_${jadwalId}`).html(`
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
                
                $(`#result_${jadwalId}`).html(notifHtml);
                
                // Play beep sound
                playSound(soundType);
                
                // Clear input & focus
                inputElement.val('');
                setTimeout(() => inputElement.focus(), 100);
                
                // Update stats & tabel tanpa reload
                updateStats(jadwalId);
                updateTable();
                
                // Auto clear notifikasi setelah 5 detik
                setTimeout(() => {
                    $(`#result_${jadwalId}`).fadeOut('slow', function() {
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
                
                $(`#result_${jadwalId}`).html(notifHtml);
                
                // Play beep sound
                playSound(soundType);
                
                // Clear input & focus
                inputElement.val('');
                setTimeout(() => inputElement.focus(), 100);
                
                // Auto clear notifikasi error setelah 5 detik
                setTimeout(() => {
                    $(`#result_${jadwalId}`).fadeOut('slow', function() {
                        $(this).html('').show();
                    });
                }, 5000);
            }
        });
    }

    // ============================================
    // 📊 UPDATE STATS (Sudah/Belum Presensi)
    // ============================================
    function updateStats(jadwalId) {
        $.ajax({
            url: `/presensi-kustom/belum-presensi/${jadwalId}`,
            method: 'GET',
            success: function(response) {
                $(`#sudah_${jadwalId}`).text(response.data.total_sudah);
                $(`#belum_${jadwalId}`).text(response.data.total_belum);
                
                // Update list belum presensi
                updateBelumPresensiList(jadwalId, response.data.belum_presensi);
            }
        });
    }

    // ============================================
    // 👥 TOGGLE & UPDATE BELUM PRESENSI LIST
    // ============================================
    function toggleBelumPresensi(jadwalId) {
        $(`#belum_list_${jadwalId}`).slideToggle();
    }

    function updateBelumPresensiList(jadwalId, belumPresensi) {
        let html = '';
        
        if (belumPresensi.length === 0) {
            html = '<li class="list-group-item text-center text-success">Semua santri sudah presensi! ✓</li>';
        } else {
            belumPresensi.forEach((user, index) => {
                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${index + 1}. ${user.name}</span>
                        <span class="badge bg-danger">Belum</span>
                    </li>
                `;
            });
        }
        
        $(`#belum_items_${jadwalId}`).html(html);
    }

    // ============================================
    // 📊 UPDATE TABLE (TANPA RELOAD)
    // ============================================
    function updateTable() {
        $.ajax({
            url: '{{ route("presensi.kustom.index") }}',
            method: 'GET',
            success: function(html) {
                // Update hanya bagian tbody tabel
                let newTableBody = $(html).find('.table-hover tbody').html();
                $('.table-hover tbody').html(newTableBody);
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
    function updateKeterangan(id, currentKeterangan) {
        $('#presensi_id').val(id);
        $(`input[name="keterangan"][value="${currentKeterangan}"]`).prop('checked', true);
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
            url: '{{ route("presensi.kustom.update") }}',
            method: 'POST',
            data: {
                _token: csrf_token,
                presensi_id: presensiId,
                keterangan: keterangan
            },
            success: function(response) {
                $('#modalKeterangan').modal('hide');
                
                // Update tabel tanpa reload
                updateTable();
                
                // Play beep sound
                playSound('success');
                
                // Show toast notifikasi
                showToast('success', response.message);
            },
            error: function(xhr) {
                alert('Terjadi kesalahan: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }

    // ============================================
    // 🔔 SHOW TOAST NOTIFICATION
    // ============================================
    function showToast(type, message) {
        let bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        
        $('body').append(`
            <div class="toast-notification position-fixed top-0 end-0 m-3 ${bgClass} text-white p-3 rounded shadow-lg" style="z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease-out;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle me-2 fs-5"></i>
                    <div>${message}</div>
                </div>
            </div>
        `);
        
        setTimeout(() => {
            $('.toast-notification').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 3000);
    }

    // ============================================
    // 🔄 AUTO REFRESH STATS & TABEL
    // ============================================
    setInterval(function() {
        // Update stats untuk semua jadwal
        $('.scan-input').each(function() {
            let jadwalId = $(this).data('jadwal-id');
            updateStats(jadwalId);
        });
        
        // Update tabel
        updateTable();
    }, 60000); // Refresh setiap 1 menit
</script>

<style>
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
</style>
@endsection