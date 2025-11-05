<!-- resources/views/presensi/kustom-jadwal.blade.php - FINAL VERSION -->
@extends('layouts.app')

@section('title', 'Kelola Jadwal Presensi Kustom')

@section('styles')
<style>
    .alert-sm {
        padding: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    
    .badge {
        font-size: 0.85rem;
    }
    
    .btn-action {
        transition: all 0.3s ease;
    }
    
    .btn-action:hover {
        transform: scale(1.1);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kelola Jadwal Presensi Kustom</h2>
        <div>
            <a href="{{ route('presensi.kustom.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-plus me-2"></i>Tambah Jadwal
            </button>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kegiatan</th>
                            <th>Tanggal</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwal as $index => $j)
                        <tr>
                            <td>{{ $jadwal->firstItem() + $index }}</td>
                            <td><strong>{{ $j->nama_kegiatan }}</strong></td>
                            <td>{{ $j->tanggal->format('d/m/Y') }}</td>
                            <td>{{ $j->jam_mulai }}</td>
                            <td>{{ $j->jam_selesai }}</td>
                            <td>{{ $j->keterangan ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $j->aktif ? 'success' : 'secondary' }}">
                                    {{ $j->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-action" onclick="editJadwal({{ json_encode($j) }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger btn-action" onclick="hapusJadwal({{ $j->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Belum ada jadwal</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $jadwal->links() }}
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jadwal Presensi Kustom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" name="nama_kegiatan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Jadwal Presensi Kustom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                <input type="hidden" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" id="edit_nama_kegiatan" name="nama_kegiatan" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" id="edit_tanggal" name="tanggal" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                            <input type="time" id="edit_jam_mulai" name="jam_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                            <input type="time" id="edit_jam_selesai" name="jam_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea id="edit_keterangan" name="keterangan" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select id="edit_aktif" name="aktif" class="form-select" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
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
            }
        } catch(e) {
            console.log('Audio tidak didukung');
        }
    }

    // ============================================
    // 🔔 SHOW TOAST NOTIFICATION
    // ============================================
    function showToast(type, message) {
        let bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
        let icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        $('body').append(`
            <div class="toast-notification position-fixed top-0 end-0 m-3 ${bgClass} text-white p-3 rounded shadow-lg" style="z-index: 9999; min-width: 300px; animation: slideInRight 0.3s ease-out;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-${icon} me-2 fs-5"></i>
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
    // 📊 UPDATE TABLE (TANPA RELOAD)
    // ============================================
    function updateTable() {
        $.ajax({
            url: '{{ route("presensi.kustom.jadwal.index") }}',
            method: 'GET',
            success: function(html) {
                let newTableBody = $(html).find('.table tbody').html();
                $('.table tbody').html(newTableBody);
            }
        });
    }

    // ============================================
    // ➕ TAMBAH JADWAL
    // ============================================
    $('#formTambah').on('submit', function(e) {
        e.preventDefault();
        
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
        
        $.ajax({
            url: '{{ route("presensi.kustom.jadwal.store") }}',
            method: 'POST',
            data: $(this).serialize() + '&_token=' + csrf_token,
            success: function(response) {
                playSound('success');
                
                $('#modalTambah').modal('hide');
                $('#formTambah')[0].reset();
                
                showToast('success', response.message);
                
                // Update tabel tanpa reload
                updateTable();
            },
            error: function(xhr) {
                playSound('error');
                
                let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                
                if (xhr.responseJSON?.errors) {
                    let errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                
                showToast('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Simpan');
            }
        });
    });

    // ============================================
    // ✏️ EDIT JADWAL
    // ============================================
    function editJadwal(data) {
        $('#edit_id').val(data.id);
        $('#edit_nama_kegiatan').val(data.nama_kegiatan);
        $('#edit_tanggal').val(data.tanggal);
        $('#edit_jam_mulai').val(data.jam_mulai);
        $('#edit_jam_selesai').val(data.jam_selesai);
        $('#edit_keterangan').val(data.keterangan);
        $('#edit_aktif').val(data.aktif ? '1' : '0');
        $('#modalEdit').modal('show');
    }

    $('#formEdit').on('submit', function(e) {
        e.preventDefault();
        
        let id = $('#edit_id').val();
        let submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Memperbarui...');
        
        $.ajax({
            url: `/presensi-kustom/jadwal/${id}`,
            method: 'PUT',
            data: $(this).serialize() + '&_token=' + csrf_token,
            success: function(response) {
                playSound('success');
                
                $('#modalEdit').modal('hide');
                
                showToast('success', response.message);
                
                // Update tabel tanpa reload
                updateTable();
            },
            error: function(xhr) {
                playSound('error');
                
                let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                
                if (xhr.responseJSON?.errors) {
                    let errors = xhr.responseJSON.errors;
                    errorMsg = Object.values(errors).flat().join('<br>');
                }
                
                showToast('error', errorMsg);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('Update');
            }
        });
    });

    // ============================================
    // 🗑️ HAPUS JADWAL
    // ============================================
    function hapusJadwal(id) {
        if(confirm('Yakin ingin menghapus jadwal ini?\n\nPerhatian: Semua data presensi terkait jadwal ini juga akan terhapus!')) {
            $.ajax({
                url: `/presensi-kustom/jadwal/${id}`,
                method: 'DELETE',
                data: {
                    _token: csrf_token
                },
                success: function(response) {
                    playSound('success');
                    
                    showToast('success', response.message);
                    
                    // Update tabel tanpa reload
                    updateTable();
                },
                error: function(xhr) {
                    playSound('error');
                    
                    let errorMsg = xhr.responseJSON?.message || 'Terjadi kesalahan';
                    showToast('error', errorMsg);
                }
            });
        }
    }

    // ============================================
    // 🔄 AUTO CLEAR MODAL ON CLOSE
    // ============================================
    $('#modalTambah').on('hidden.bs.modal', function () {
        $('#formTambah')[0].reset();
    });

    $('#modalEdit').on('hidden.bs.modal', function () {
        $('#formEdit')[0].reset();
    });
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