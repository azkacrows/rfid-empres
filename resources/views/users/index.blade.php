@extends('layouts.app')

@section('title', 'Kelola User')

@section('styles')
<style>
    .search-box {
        position: relative;
    }
    .search-box input {
        padding-left: 40px;
        border-radius: 25px;
        border: 2px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    .search-box input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }
    .clear-search {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #999;
        display: none;
    }
    .clear-search:hover {
        color: #e74c3c;
    }
    .highlight-row {
        background-color: #fff3cd !important;
        animation: highlight-fade 2s ease-in-out;
    }
    @keyframes highlight-fade {
        0% { background-color: #fff3cd; }
        100% { background-color: transparent; }
    }
    .pagination {
        margin: 0;
    }
    .pagination .page-link {
        border-radius: 5px;
        margin: 0 3px;
        border: 1px solid #dee2e6;
        color: #667eea;
        transition: all 0.2s ease;
    }
    .pagination .page-link:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-color: #667eea;
    }
    .pagination .page-item.disabled .page-link {
        background: #f8f9fa;
        border-color: #dee2e6;
    }
    .badge-role {
        font-size: 0.75rem;
        padding: 4px 8px;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
        cursor: pointer;
    }
    .stats-card {
        padding: 15px;
        border-radius: 10px;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px;
    }
    .stats-card h5 {
        margin: 0;
        color: #666;
        font-size: 0.9rem;
    }
    .stats-card h3 {
        margin: 5px 0 0 0;
        color: #333;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-users me-2"></i>Kelola User</h2>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah User
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stats-card">
                <h5><i class="fas fa-users me-2"></i>Total User</h5>
                <h3>{{ $users->total() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h5><i class="fas fa-user-shield me-2"></i>Admin</h5>
                <h3>{{ \App\Models\User::where('role', 'admin')->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h5><i class="fas fa-user me-2"></i>User</h5>
                <h3>{{ \App\Models\User::where('role', 'user')->count() }}</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <h5><i class="fas fa-id-card me-2"></i>RFID Terdaftar</h5>
                <h3>{{ \App\Models\User::whereNotNull('rfid_card')->count() }}</h3>
            </div>
        </div>
    </div>
    
    <!-- Search & Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('users.index') }}" method="GET" id="searchForm">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="fas fa-search me-2"></i>Cari User
                        </label>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" 
                                   name="search" 
                                   id="searchInput"
                                   class="form-control" 
                                   placeholder="Cari nama, email, atau RFID card..." 
                                   value="{{ request('search') }}"
                                   autocomplete="off">
                            <i class="fas fa-times clear-search" id="clearSearch"></i>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-filter me-2"></i>Filter Role
                        </label>
                        <select name="role" class="form-select" onchange="this.form.submit()">
                            <option value="">Semua Role</option>
                            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <i class="fas fa-sort me-2"></i>Urutkan
                        </label>
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama A-Z</option>
                            <option value="name_desc" {{ request('sort') === 'name_desc' ? 'selected' : '' }}>Nama Z-A</option>
                            <option value="saldo_desc" {{ request('sort') === 'saldo_desc' ? 'selected' : '' }}>Saldo Tertinggi</option>
                            <option value="saldo_asc" {{ request('sort') === 'saldo_asc' ? 'selected' : '' }}>Saldo Terendah</option>
                            <option value="created_desc" {{ request('sort') === 'created_desc' ? 'selected' : '' }}>Terbaru</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Table -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Daftar User
                    @if(request('search'))
                        <span class="badge bg-primary">
                            Hasil pencarian: "{{ request('search') }}"
                        </span>
                    @endif
                </h5>
                @if(request()->hasAny(['search', 'role', 'sort']))
                    <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i>Reset Filter
                    </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if($users->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>RFID Card</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Saldo</th>
                            <th>Limit Harian</th>
                            <th>Status Limit</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                        <tr class="{{ request('search') && $index === 0 ? 'highlight-row' : '' }}">
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <code class="bg-light px-2 py-1 rounded">
                                    {{ $user->rfid_card ?: '-' }}
                                </code>
                            </td>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>
                                <i class="fas fa-envelope me-1 text-muted"></i>
                                {{ $user->email }}
                            </td>
                            <td>
                                <span class="badge badge-role bg-{{ $user->role === 'admin' ? 'danger' : 'primary' }}">
                                    <i class="fas fa-{{ $user->role === 'admin' ? 'user-shield' : 'user' }} me-1"></i>
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>
                                <strong class="text-{{ $user->saldo > 0 ? 'success' : 'muted' }}">
                                    Rp {{ number_format($user->saldo, 0, ',', '.') }}
                                </strong>
                            </td>
                            <td>
                                <span class="text-muted">
                                    Rp {{ number_format($user->limit_harian, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input toggle-limit" 
                                           type="checkbox" 
                                           data-user-id="{{ $user->id }}"
                                           {{ $user->limit_saldo_aktif ? 'checked' : '' }}
                                           style="cursor: pointer;">
                                    <label class="form-check-label small">
                                        <span class="badge bg-{{ $user->limit_saldo_aktif ? 'success' : 'secondary' }}">
                                            {{ $user->limit_saldo_aktif ? 'Aktif' : 'Off' }}
                                        </span>
                                    </label>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('users.edit', $user) }}" 
                                       class="btn btn-sm btn-warning" 
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-sm btn-danger" 
                                            onclick="hapusUser({{ $user->id }}, '{{ $user->name }}')"
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }} dari {{ $users->total() }} user
                </div>
                <div>
                    {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">
                    @if(request('search'))
                        Tidak ada hasil untuk "{{ request('search') }}"
                    @else
                        Belum ada user terdaftar
                    @endif
                </h5>
                @if(request('search'))
                    <a href="{{ route('users.index') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-redo me-2"></i>Lihat Semua User
                    </a>
                @else
                    <a href="{{ route('users.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-2"></i>Tambah User Pertama
                    </a>
                @endif
            </div>
            @endif
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Real-time search dengan debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        const val = $(this).val();
        
        // Show/hide clear button
        if (val.length > 0) {
            $('#clearSearch').show();
        } else {
            $('#clearSearch').hide();
        }

        // Debounce search (tunggu 500ms setelah user berhenti mengetik)
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (val.length >= 3 || val.length === 0) {
                $('#searchForm').submit();
            }
        }, 500);
    });

    // Clear search
    $('#clearSearch').on('click', function() {
        $('#searchInput').val('');
        $(this).hide();
        $('#searchForm').submit();
    });

    // Show clear button if search has value
    if ($('#searchInput').val().length > 0) {
        $('#clearSearch').show();
    }

// Toggle limit saldo
$('.toggle-limit').on('change', function() {
    let userId = $(this).data('user-id');
    let status = $(this).is(':checked') ? 1 : 0; // ✅ Kirim 1 atau 0 (bukan true/false)
    let checkbox = $(this);
    let label = checkbox.next().find('.badge');
    
    // Disable checkbox during request
    checkbox.prop('disabled', true);
    
    $.ajax({
        url: '{{ route("kantin.toggle-limit") }}',
        method: 'POST',
        data: {
            user_id: userId,
            status: status // ✅ Kirim integer
        },
        success: function(response) {
            // Update badge
            if (status === 1) {
                label.removeClass('bg-secondary').addClass('bg-success').text('Aktif');
            } else {
                label.removeClass('bg-success').addClass('bg-secondary').text('Off');
            }
            
            // Show success toast
            showToast('success', response.message);
        },
        error: function(xhr) {
            // Revert checkbox
            checkbox.prop('checked', status === 0);
            
            let errorMsg = 'Terjadi kesalahan';
            try {
                let response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
            } catch(e) {
                console.error('Parse error:', e);
            }
            
            showToast('error', errorMsg);
            console.error('Toggle limit error:', xhr);
        },
        complete: function() {
            checkbox.prop('disabled', false);
        }
    });
});

    // Delete user with confirmation
    window.hapusUser = function(id, name) {
        if(confirm(`⚠️ Yakin ingin menghapus user "${name}"?\n\nData presensi dan transaksi terkait user ini akan tetap tersimpan.`)) {
            let form = $('#deleteForm');
            form.attr('action', `/users/${id}`);
            form.submit();
        }
    };

    // Toast notification function
    function showToast(type, message) {
        const bgColor = type === 'success' ? '#27ae60' : '#e74c3c';
        const icon = type === 'success' ? 'check-circle' : 'exclamation-circle';
        
        const toast = $(`
            <div class="toast-notification" style="
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${bgColor};
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 10px;
                animation: slideIn 0.3s ease;
            ">
                <i class="fas fa-${icon}"></i>
                <span>${message}</span>
            </div>
        `);
        
        $('body').append(toast);
        
        setTimeout(() => {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Add CSS animation
    $('<style>')
        .text(`
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `)
        .appendTo('head');
});
</script>
@endsection