@extends('layouts.app')

@section('title', 'Edit User')

@section('styles')
<style>
    .rfid-input-group {
        position: relative;
    }
    .rfid-input-group input {
        font-family: 'Courier New', monospace;
        font-size: 1.1rem;
        font-weight: bold;
        letter-spacing: 2px;
        background: #f8f9fa;
        border: 2px solid #dee2e6;
        transition: all 0.3s ease;
    }
    .rfid-input-group input:focus {
        background: #fff;
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    .card-header {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
        color: white !important;
    }
    .info-box {
        background: #e7f3ff;
        border-left: 4px solid #2196F3;
        padding: 15px;
        border-radius: 5px;
    }
    .balance-display {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    .balance-display h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: bold;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-edit me-2"></i>Edit User: {{ $user->name }}</h2>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
    
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i>Form Edit User</h5>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            <strong><i class="fas fa-exclamation-circle me-2"></i>Error Validasi:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('users.update', $user) }}" id="formUser">
                        @csrf
                        @method('PUT')
                        
                        <!-- Info Box -->
                        <div class="info-box mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Cara Mengisi RFID:</strong> Klik di field RFID Card, lalu tap kartu RFID pada reader.
                        </div>

                        <!-- RFID & Nama -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-id-card me-1"></i>RFID Card 
                                    <span class="text-danger">*</span>
                                </label>
                                <div class="rfid-input-group">
                                    <input type="text" 
                                           name="rfid_card" 
                                           id="rfid_card"
                                           class="form-control form-control-lg @error('rfid_card') is-invalid @enderror" 
                                           value="{{ old('rfid_card', $user->rfid_card) }}" 
                                           required
                                           autocomplete="off"
                                           placeholder="Tap kartu RFID...">
                                    @error('rfid_card')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-hand-pointer me-1"></i>Focus di sini, lalu tap kartu RFID
                                </small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user me-1"></i>Nama Lengkap 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Email & Password -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email 
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-lock me-1"></i>Password Baru
                                </label>
                                <input type="password" 
                                       name="password" 
                                       class="form-control @error('password') is-invalid @enderror"
                                       minlength="6"
                                       placeholder="Kosongkan jika tidak ingin mengubah">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    <i class="fas fa-key me-1"></i>Kosongkan jika tidak ingin mengubah password
                                </small>
                            </div>
                        </div>

                        <!-- Role -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-user-shield me-1"></i>Role 
                                    <span class="text-danger">*</span>
                                </label>
                                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                    <option value="">-- Pilih Role --</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>
                                        👑 Admin
                                    </option>
                                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>
                                        👤 User
                                    </option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-money-bill-wave me-1"></i>Limit Harian (Rp)
                                </label>
                                <input type="number" 
                                       name="limit_harian" 
                                       class="form-control @error('limit_harian') is-invalid @enderror" 
                                       value="{{ old('limit_harian', $user->limit_harian) }}" 
                                       min="0" 
                                       step="1000"
                                       placeholder="10000">
                                @error('limit_harian')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Default: Rp 10.000</small>
                            </div>
                        </div>

                        <!-- Tempat & Tanggal Lahir -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt me-1"></i>Tempat Lahir
                                </label>
                                <input type="text" 
                                       name="tempat_lahir" 
                                       class="form-control @error('tempat_lahir') is-invalid @enderror" 
                                       value="{{ old('tempat_lahir', $user->tempat_lahir) }}">
                                @error('tempat_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-calendar me-1"></i>Tanggal Lahir
                                </label>
                                <input type="date" 
                                       name="tanggal_lahir" 
                                       class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                                       value="{{ old('tanggal_lahir', is_string($user->tanggal_lahir) ? $user->tanggal_lahir : ($user->tanggal_lahir?->format('Y-m-d') ?? '')) }}"
                                       max="{{ date('Y-m-d') }}">
                                @error('tanggal_lahir')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Jenis Kelamin & Saldo -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-venus-mars me-1"></i>Jenis Kelamin
                                </label>
                                <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                                    <option value="">-- Pilih --</option>
                                    <option value="L" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'L' ? 'selected' : '' }}>
                                        👨 Laki-laki
                                    </option>
                                    <option value="P" {{ old('jenis_kelamin', $user->jenis_kelamin) == 'P' ? 'selected' : '' }}>
                                        👩 Perempuan
                                    </option>
                                </select>
                                @error('jenis_kelamin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="fas fa-wallet me-1"></i>Saldo (Rp)
                                </label>
                                <input type="number" 
                                       name="saldo" 
                                       class="form-control @error('saldo') is-invalid @enderror" 
                                       value="{{ old('saldo', $user->saldo) }}" 
                                       min="0" 
                                       step="1000">
                                @error('saldo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Saldo saat ini: Rp {{ number_format($user->saldo, 0, ',', '.') }}</small>
                            </div>
                        </div>

                        <!-- Alamat -->
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-home me-1"></i>Alamat
                            </label>
                            <textarea name="alamat" 
                                      class="form-control @error('alamat') is-invalid @enderror" 
                                      rows="3"
                                      placeholder="Alamat lengkap...">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status Limit Saldo -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="limit_saldo_aktif" 
                                       id="limitSaldoAktif"
                                       {{ old('limit_saldo_aktif', $user->limit_saldo_aktif) ? 'checked' : '' }}
                                       style="width: 50px; height: 25px; cursor: pointer;">
                                <label class="form-check-label ms-2" for="limitSaldoAktif" style="font-size: 1.1rem;">
                                    <i class="fas fa-shield-alt me-2"></i>
                                    <strong>Aktifkan Limit Saldo Harian</strong>
                                </label>
                            </div>
                            <small class="text-muted ms-5">
                                Jika diaktifkan, user hanya bisa bertransaksi sesuai limit harian yang ditentukan
                            </small>
                        </div>

                        <!-- Current Balance Display -->
                        <div class="balance-display mb-4">
                            <p class="mb-1">💰 Saldo Terkini</p>
                            <h3>Rp {{ number_format($user->saldo, 0, ',', '.') }}</h3>
                            <small>Terakhir update: {{ $user->updated_at->format('d M Y H:i') }}</small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-warning btn-lg">
                                <i class="fas fa-save me-2"></i>Update User
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-focus RFID input when page loads
    $('#rfid_card').focus();

    // Cegah form submit saat Enter ditekan di input RFID
    $('#rfid_card').on('keypress', function(e) {
        if(e.which === 13) {
            e.preventDefault();
            // Setelah scan, pindah ke input nama
            $('input[name="name"]').focus();
            return false;
        }
    });

    // Validasi sebelum submit
    $('#formUser').on('submit', function(e) {
        let rfid = $('#rfid_card').val().trim();
        let name = $('input[name="name"]').val().trim();
        let email = $('input[name="email"]').val().trim();
        
        if(!rfid || rfid.length < 3) {
            e.preventDefault();
            alert('⚠️ RFID Card harus diisi!\nTap kartu RFID pada reader.');
            $('#rfid_card').focus();
            return false;
        }

        if(!name) {
            e.preventDefault();
            alert('⚠️ Nama lengkap harus diisi!');
            $('input[name="name"]').focus();
            return false;
        }

        if(!email) {
            e.preventDefault();
            alert('⚠️ Email harus diisi!');
            $('input[name="email"]').focus();
            return false;
        }

        // Show loading
        $(this).find('button[type="submit"]').prop('disabled', true).html(
            '<i class="fas fa-spinner fa-spin me-2"></i>Menyimpan...'
        );
    });

    // Format number input (limit harian & saldo)
    $('input[name="limit_harian"], input[name="saldo"]').on('input', function() {
        let val = $(this).val();
        // Remove non-numeric characters
        val = val.replace(/[^0-9]/g, '');
        $(this).val(val);
    });

    // Visual feedback untuk RFID scan
    $('#rfid_card').on('input', function() {
        if($(this).val().length > 0) {
            $(this).addClass('border-success');
        } else {
            $(this).removeClass('border-success');
        }
    });
});
</script>
@endsection