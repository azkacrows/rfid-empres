@extends('layouts.app')

@section('title', 'Pengaturan Waktu')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Pengaturan Waktu Presensi</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-2"></i>Tambah Pengaturan
        </button>
    </div>
    
    <!-- Pengaturan Sekolah -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fas fa-school me-2"></i>Pengaturan Presensi Sekolah</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Toleransi (Menit)</th>
                            <th>Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengaturan->where('jenis', 'sekolah') as $p)
                        <tr>
                            <td><strong>{{ $p->nama }}</strong></td>
                            <td>{{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) : '-' }}</td>
                            <td>{{ $p->jam_selesai ? substr($p->jam_selesai, 0, 5) : '-' }}</td>
                            <td>{{ $p->toleransi_keterlambatan }} menit</td>
                            <td>
                                <span class="badge bg-{{ $p->aktif ? 'success' : 'secondary' }}">
                                    {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editPengaturan(@json($p))' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Belum ada pengaturan sekolah</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pengaturan Sholat -->
    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fas fa-mosque me-2"></i>Pengaturan Presensi Sholat</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Catatan:</strong> Waktu presensi sholat otomatis mengikuti jadwal adzan yang sudah tersinkronisasi. 
                Batas presensi dihitung dari waktu adzan + toleransi.
                <br><small><strong>Contoh:</strong> Dzuhur adzan 12:00, batas presensi 20 menit → Presensi maksimal 12:20</small>
            </div>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu Sholat</th>
                            <th>Batas Presensi (Menit Setelah Adzan)</th>
                            <th>Status</th>
                            <th width="100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengaturan->where('jenis', 'sholat') as $p)
                        <tr>
                            <td><strong>{{ ucfirst($p->nama) }}</strong></td>
                            <td>
                                <span class="badge bg-warning text-dark fs-6">
                                    {{ $p->toleransi_keterlambatan }} menit
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $p->aktif ? 'success' : 'secondary' }}">
                                    {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editPengaturan(@json($p))' title="Edit">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Belum ada pengaturan sholat. <a href="#" data-bs-toggle="modal" data-bs-target="#modalTambah">Tambah sekarang</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pengaturan Kustom -->
    @if($pengaturan->where('jenis', 'kustom')->count() > 0)
    <div class="card">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Pengaturan Presensi Kustom</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Toleransi (Menit)</th>
                            <th>Status</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengaturan->where('jenis', 'kustom') as $p)
                        <tr>
                            <td><strong>{{ $p->nama }}</strong></td>
                            <td>{{ $p->jam_mulai ? substr($p->jam_mulai, 0, 5) : '-' }}</td>
                            <td>{{ $p->jam_selesai ? substr($p->jam_selesai, 0, 5) : '-' }}</td>
                            <td>{{ $p->toleransi_keterlambatan }} menit</td>
                            <td>
                                <span class="badge bg-{{ $p->aktif ? 'success' : 'secondary' }}">
                                    {{ $p->aktif ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick='editPengaturan(@json($p))' title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="hapusPengaturan({{ $p->id }})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pengaturan Waktu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formTambah">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis <span class="text-danger">*</span></label>
                        <select name="jenis" id="jenis" class="form-select" required>
                            <option value="">-- Pilih Jenis --</option>
                            <option value="sekolah">Sekolah</option>
                            <option value="sholat">Sholat</option>
                            <option value="kustom">Kustom</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="divNama">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <div id="fieldNama">
                            <input type="text" name="nama" class="form-control" placeholder="Nama pengaturan" required>
                        </div>
                        <small class="text-muted" id="helpNama"></small>
                    </div>
                    
                    <div id="divJam" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Batas Presensi (Menit) <span class="text-danger">*</span></label>
                        <input type="number" name="toleransi_keterlambatan" class="form-control" min="0" value="20" required>
                        <small class="text-muted" id="helpToleransi">Waktu toleransi setelah jam mulai/adzan</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Simpan
                    </button>
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
                <h5 class="modal-title">Edit Pengaturan Waktu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formEdit">
                @csrf
                <input type="hidden" id="edit_id">
                <input type="hidden" id="edit_jenis">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <input type="text" id="edit_jenis_display" class="form-control" readonly>
                    </div>
                    
                    <div class="mb-3" id="divEditNama">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" id="edit_nama" name="nama" class="form-control" required>
                    </div>
                    
                    <div id="divEditJam">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Mulai</label>
                                <input type="time" id="edit_jam_mulai" name="jam_mulai" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jam Selesai</label>
                                <input type="time" id="edit_jam_selesai" name="jam_selesai" class="form-control">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Batas Presensi (Menit Setelah Jam Mulai/Adzan) <span class="text-danger">*</span></label>
                        <input type="number" id="edit_toleransi" name="toleransi_keterlambatan" class="form-control" min="0" required>
                        <small class="text-muted" id="helpEditToleransi"></small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select id="edit_aktif" name="aktif" class="form-select" required>
                            <option value="1">Aktif</option>
                            <option value="0">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// CSRF Setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// === FORM TAMBAH: Dynamic Fields ===
$('#jenis').on('change', function() {
    const jenis = $(this).val();
    
    if (jenis === 'sholat') {
        // Hide jam fields
        $('#divJam').hide();
        $('[name="jam_mulai"], [name="jam_selesai"]').prop('required', false).val('');
        
        // Change nama field to dropdown
        $('#fieldNama').html(`
            <select name="nama" class="form-select" required>
                <option value="">-- Pilih Waktu Sholat --</option>
                <option value="Subuh">Subuh</option>
                <option value="Dzuhur">Dzuhur</option>
                <option value="Ashar">Ashar</option>
                <option value="Maghrib">Maghrib</option>
                <option value="Isya">Isya</option>
            </select>
        `);
        
        $('#helpNama').text('Pilih waktu sholat yang ingin diatur');
        $('#helpToleransi').html('<strong>Batas presensi setelah adzan.</strong> Contoh: Dzuhur adzan 12:00, batas 20 menit → presensi maksimal 12:20');
        
    } else if (jenis === 'sekolah') {
        // Show jam fields
        $('#divJam').show();
        $('[name="jam_mulai"], [name="jam_selesai"]').prop('required', true);
        
        // Change nama field to input
        $('#fieldNama').html(`
            <input type="text" name="nama" class="form-control" placeholder="Contoh: Jam Masuk, Jam Pulang" required>
        `);
        
        $('#helpNama').text('Contoh: Jam Masuk, Jam Pulang, Istirahat');
        $('#helpToleransi').text('Waktu toleransi setelah jam mulai');
        
    } else if (jenis === 'kustom') {
        // Show jam fields
        $('#divJam').show();
        $('[name="jam_mulai"], [name="jam_selesai"]').prop('required', true);
        
        // Change nama field to input
        $('#fieldNama').html(`
            <input type="text" name="nama" class="form-control" placeholder="Contoh: Upacara, Rapat" required>
        `);
        
        $('#helpNama').text('Nama presensi kustom sesuai kebutuhan');
        $('#helpToleransi').text('Waktu toleransi setelah jam mulai');
    } else {
        $('#divJam').hide();
        $('#fieldNama').html(`
            <input type="text" name="nama" class="form-control" placeholder="Nama pengaturan" required>
        `);
        $('#helpNama').text('');
        $('#helpToleransi').text('Waktu toleransi setelah jam mulai/adzan');
    }
});

// === SUBMIT FORM TAMBAH ===
$('#formTambah').on('submit', function(e) {
    e.preventDefault();
    
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...');
    
    $.ajax({
        url: '{{ route("pengaturan.waktu.create") }}',
        method: 'POST',
        data: $(this).serialize(),
        success: function(response) {
            alert('✅ ' + response.message);
            location.reload();
        },
        error: function(xhr) {
            let errorMsg = 'Terjadi kesalahan';
            
            try {
                let response = JSON.parse(xhr.responseText);
                
                if (response.errors) {
                    errorMsg = 'Validasi gagal:\n';
                    for (let field in response.errors) {
                        errorMsg += '• ' + response.errors[field].join(', ') + '\n';
                    }
                } else if (response.message) {
                    errorMsg = response.message;
                }
            } catch(e) {
                errorMsg = xhr.statusText || errorMsg;
            }
            
            alert('❌ ' + errorMsg);
            btn.prop('disabled', false).html(originalText);
        }
    });
});

// === EDIT PENGATURAN ===
function editPengaturan(data) {
    console.log('Edit data:', data);
    
    $('#edit_id').val(data.id);
    $('#edit_jenis').val(data.jenis);
    $('#edit_jenis_display').val(data.jenis.charAt(0).toUpperCase() + data.jenis.slice(1));
    $('#edit_nama').val(data.nama);
    $('#edit_toleransi').val(data.toleransi_keterlambatan);
    $('#edit_aktif').val(data.aktif ? '1' : '0');
    
    // Show/hide jam fields berdasarkan jenis
    if (data.jenis === 'sholat') {
        $('#divEditJam').hide();
        $('#edit_jam_mulai, #edit_jam_selesai').prop('required', false);
        $('#edit_nama').prop('readonly', true);
        $('#helpEditToleransi').html('<strong>Batas presensi setelah adzan.</strong> Jadwal adzan otomatis mengikuti jadwal sholat yang tersinkronisasi.');
    } else {
        $('#divEditJam').show();
        $('#edit_jam_mulai').val(data.jam_mulai ? data.jam_mulai.substring(0, 5) : '').prop('required', true);
        $('#edit_jam_selesai').val(data.jam_selesai ? data.jam_selesai.substring(0, 5) : '').prop('required', true);
        $('#edit_nama').prop('readonly', false);
        $('#helpEditToleransi').text('Waktu toleransi setelah jam mulai');
    }
    
    $('#modalEdit').modal('show');
}

// === SUBMIT FORM EDIT ===
$('#formEdit').on('submit', function(e) {
    e.preventDefault();
    
    let id = $('#edit_id').val();
    const btn = $(this).find('button[type="submit"]');
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
    
    $.ajax({
        url: `/pengaturan-waktu/${id}`,
        method: 'PUT',
        data: $(this).serialize(),
        success: function(response) {
            alert('✅ ' + response.message);
            location.reload();
        },
        error: function(xhr) {
            let errorMsg = 'Terjadi kesalahan';
            
            try {
                let response = JSON.parse(xhr.responseText);
                
                if (response.errors) {
                    errorMsg = 'Validasi gagal:\n';
                    for (let field in response.errors) {
                        errorMsg += '• ' + response.errors[field].join(', ') + '\n';
                    }
                } else if (response.message) {
                    errorMsg = response.message;
                }
            } catch(e) {
                errorMsg = xhr.statusText || errorMsg;
            }
            
            alert('❌ ' + errorMsg);
            btn.prop('disabled', false).html(originalText);
        }
    });
});

// === HAPUS PENGATURAN ===
function hapusPengaturan(id) {
    if(confirm('⚠️ Yakin ingin menghapus pengaturan ini?')) {
        $.ajax({
            url: `/pengaturan-waktu/${id}`,
            method: 'DELETE',
            success: function(response) {
                alert('✅ ' + response.message);
                location.reload();
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan';
                
                try {
                    let response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.statusText || errorMsg;
                }
                
                alert('❌ ' + errorMsg);
            }
        });
    }
}
</script>
@endsection