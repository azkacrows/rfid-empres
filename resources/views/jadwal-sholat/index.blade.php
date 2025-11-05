@extends('layouts.app')

@section('title', 'Jadwal Sholat')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">Kelola Jadwal Sholat</h2>
    
    <div class="row">
        <!-- KOLOM KIRI: Sync & Bulk Override -->
        <div class="col-md-4">
            <!-- Card 1: Sync dari API -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-sync me-2"></i>Sinkronisasi Jadwal</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Sinkronisasi jadwal sholat dari API untuk wilayah Mojokerto (terdekat dengan Pacet, Mojokerto)
                    </div>
                    
                    <form id="formSync">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Bulan</label>
                            <select name="bulan" class="form-select" required>
                                @for($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($i)->locale('id')->monthName }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tahun</label>
                            <select name="tahun" class="form-select" required>
                                @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                    <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100" id="btnSync">
                            <i class="fas fa-download me-2"></i>Sinkronisasi Sekarang
                        </button>
                    </form>
                    
                    <div id="syncResult" class="mt-3"></div>
                </div>
            </div>

            <!-- Card 2: Bulk Override (BARU) -->
            <div class="card mt-3">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Bulk Edit Waktu</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <small>Ubah semua waktu sholat tertentu untuk 1 bulan penuh.<br>
                        <strong>Contoh:</strong> Ubah semua Dzuhur jadi jam 12:00</small>
                    </div>
                    
                    <form id="formBulkOverride">
                        @csrf
                        <div class="row">
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Bulan</label>
                                    <select name="bulan" class="form-select" required>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ date('n') == $i ? 'selected' : '' }}>
                                                {{ \Carbon\Carbon::create()->month($i)->locale('id')->format('M') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun</label>
                                    <select name="tahun" class="form-select" required>
                                        @for($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                                            <option value="{{ $i }}" {{ date('Y') == $i ? 'selected' : '' }}>
                                                {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Waktu Sholat</label>
                            <select name="waktu_sholat" class="form-select" required>
                                <option value="subuh">Subuh</option>
                                <option value="dzuhur" selected>Dzuhur</option>
                                <option value="ashar">Ashar</option>
                                <option value="maghrib">Maghrib</option>
                                <option value="isya">Isya</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Jam Baru</label>
                            <input type="time" name="jam_baru" class="form-control" value="12:00" required>
                        </div>
                        
                        <button type="submit" class="btn btn-warning w-100" id="btnBulkOverride">
                            <i class="fas fa-edit me-2"></i>Update Semua Waktu
                        </button>
                    </form>
                    
                    <div id="bulkResult" class="mt-3"></div>
                </div>
            </div>

            <!-- Card 3: Info -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Lokasi:  Pacet, Mojokerto
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Sumber: API MyQuran
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-edit text-warning me-2"></i>
                            Edit manual tersedia
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN: Tabel Jadwal -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Daftar Jadwal Sholat</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th width="120">Tanggal</th>
                                    <th>Subuh</th>
                                    <th>Dzuhur</th>
                                    <th>Ashar</th>
                                    <th>Maghrib</th>
                                    <th>Isya</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jadwal as $j)
                                <tr class="{{ $j->tanggal->isToday() ? 'table-success' : '' }}" data-id="{{ $j->id }}">
<td>
    <div class="d-flex flex-column">
        <span class="fw-bold text-primary">{{ $j->hari }}</span>
        <small class="text-muted">{{ $j->tanggal->format('d/m/Y') }}</small>
    </div>
    @if($j->tanggal->isToday())
        <span class="badge bg-success mt-1">Hari Ini</span>
    @endif
</td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm edit-time" 
                                               data-field="subuh" value="{{ $j->subuh }}" disabled>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm edit-time" 
                                               data-field="dzuhur" value="{{ $j->dzuhur }}" disabled>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm edit-time" 
                                               data-field="ashar" value="{{ $j->ashar }}" disabled>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm edit-time" 
                                               data-field="maghrib" value="{{ $j->maghrib }}" disabled>
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm edit-time" 
                                               data-field="isya" value="{{ $j->isya }}" disabled>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-primary btn-edit" data-id="{{ $j->id }}" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-success btn-save" data-id="{{ $j->id }}" style="display:none;" title="Simpan">
                                                <i class="fas fa-save"></i>
                                            </button>
                                            <button class="btn btn-secondary btn-cancel" data-id="{{ $j->id }}" style="display:none;" title="Batal">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> Belum ada jadwal. Silakan lakukan sinkronisasi.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $jadwal->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // CSRF Token Setup
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Helper: Show Alert
    function showAlert(containerId, type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const icon = type === 'success' ? 'check-circle' : 'times-circle';
        
        const html = `
            <div class="alert ${alertClass} alert-dismissible fade show">
                <i class="fas fa-${icon} me-2"></i>
                <strong>${message}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        $(`#${containerId}`).html(html);
        
        setTimeout(() => {
            $(`#${containerId} .alert`).fadeOut('slow');
        }, 5000);
    }

    // ===== 1. SYNC DARI API =====
    $('#formSync').on('submit', function(e) {
        e.preventDefault();
        
        let btn = $('#btnSync');
        let originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Sinkronisasi...');
        
        $('#syncResult').html('');
        
        $.ajax({
            url: '{{ route("jadwal-sholat.sync") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log('Success response:', response);
                
                let message = response.message;
                if (response.data) {
                    message += `<br><small>Total: ${response.data.total} hari | Lokasi: ${response.data.lokasi}</small>`;
                }
                
                showAlert('syncResult', 'success', message);
                
                if (response.success) {
                    setTimeout(() => location.reload(), 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', xhr);
                
                let errorMsg = 'Terjadi kesalahan';
                try {
                    let response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.statusText || errorMsg;
                }
                
                showAlert('syncResult', 'error', errorMsg);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ===== 2. BULK OVERRIDE (BARU) =====
    $('#formBulkOverride').on('submit', function(e) {
        e.preventDefault();
        
        const waktu = $('[name="waktu_sholat"]').val();
        const jam = $('[name="jam_baru"]').val();
        const bulan = $('#formBulkOverride [name="bulan"] option:selected').text();
        const tahun = $('#formBulkOverride [name="tahun"]').val();
        
        if (!confirm(`Yakin ingin mengubah SEMUA ${waktu.toUpperCase()} menjadi ${jam} untuk bulan ${bulan} ${tahun}?`)) {
            return;
        }
        
        let btn = $('#btnBulkOverride');
        let originalText = btn.html();
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Updating...');
        
        $('#bulkResult').html('');
        
        $.ajax({
            url: '{{ route("jadwal-sholat.bulk-override") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                console.log('Bulk override success:', response);
                
                let message = response.message;
                if (response.data) {
                    message += `<br><small>Total diupdate: ${response.data.total_updated} hari</small>`;
                }
                
                showAlert('bulkResult', 'success', message);
                
                setTimeout(() => location.reload(), 2000);
            },
            error: function(xhr) {
                console.error('Bulk override error:', xhr);
                
                let errorMsg = 'Terjadi kesalahan saat bulk update';
                try {
                    let response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.statusText || errorMsg;
                }
                
                showAlert('bulkResult', 'error', errorMsg);
            },
            complete: function() {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // ===== 3. EDIT INDIVIDUAL (BARU) =====
    let originalData = {};
    
    // Tombol Edit
    $(document).on('click', '.btn-edit', function() {
        const row = $(this).closest('tr');
        const id = $(this).data('id');
        
        // Simpan data original
        originalData[id] = {
            subuh: row.find('[data-field="subuh"]').val(),
            dzuhur: row.find('[data-field="dzuhur"]').val(),
            ashar: row.find('[data-field="ashar"]').val(),
            maghrib: row.find('[data-field="maghrib"]').val(),
            isya: row.find('[data-field="isya"]').val()
        };
        
        // Enable inputs & toggle buttons
        row.find('.edit-time').prop('disabled', false).addClass('border-warning');
        row.find('.btn-edit').hide();
        row.find('.btn-save, .btn-cancel').show();
    });

    // Tombol Cancel
    $(document).on('click', '.btn-cancel', function() {
        const row = $(this).closest('tr');
        const id = $(this).data('id');
        
        // Restore original data
        if (originalData[id]) {
            row.find('[data-field="subuh"]').val(originalData[id].subuh);
            row.find('[data-field="dzuhur"]').val(originalData[id].dzuhur);
            row.find('[data-field="ashar"]').val(originalData[id].ashar);
            row.find('[data-field="maghrib"]').val(originalData[id].maghrib);
            row.find('[data-field="isya"]').val(originalData[id].isya);
        }
        
        // Disable inputs & toggle buttons
        row.find('.edit-time').prop('disabled', true).removeClass('border-warning');
        row.find('.btn-edit').show();
        row.find('.btn-save, .btn-cancel').hide();
    });

    // Tombol Save
    $(document).on('click', '.btn-save', function() {
        const row = $(this).closest('tr');
        const id = $(this).data('id');
        
        const data = {
            subuh: row.find('[data-field="subuh"]').val(),
            dzuhur: row.find('[data-field="dzuhur"]').val(),
            ashar: row.find('[data-field="ashar"]').val(),
            maghrib: row.find('[data-field="maghrib"]').val(),
            isya: row.find('[data-field="isya"]').val(),
            _method: 'PUT'
        };
        
        $.ajax({
            url: `/jadwal-sholat/${id}`,
            method: 'POST',
            data: data,
            success: function(response) {
                console.log('Update success:', response);
                
                // Visual feedback
                row.addClass('table-success');
                setTimeout(() => row.removeClass('table-success'), 2000);
                
                // Disable inputs & toggle buttons
                row.find('.edit-time').prop('disabled', true).removeClass('border-warning');
                row.find('.btn-edit').show();
                row.find('.btn-save, .btn-cancel').hide();
                
                // Show notification
                alert(response.message);
            },
            error: function(xhr) {
                console.error('Update error:', xhr);
                
                let errorMsg = 'Gagal menyimpan perubahan';
                try {
                    let response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.statusText || errorMsg;
                }
                
                alert(errorMsg);
            }
        });
    });
});
</script>
@endsection