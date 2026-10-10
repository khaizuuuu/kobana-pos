@extends('layouts.guest')

@section('title', 'Login Sistem')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5 col-xl-4">
        <div class="card login-card border-0 bg-white">
            <div class="login-header text-white">
                <div class="d-inline-flex p-3 rounded-circle bg-white bg-opacity-20 mb-2">
                    <i class="bi bi-fire fs-2"></i>
                </div>
                <h4 class="fw-bold mb-1">Kobana Fried Chicken</h4>
                <p class="small text-white-50 mb-0">Sistem POS & Market Basket Analysis</p>
            </div>

            <div class="card-body p-4">
                <form action="{{ route('login.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-dark">Email Pengguna</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope-fill"></i></span>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@kobana.test" required autofocus>
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold text-dark">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-muted" for="remember">
                                Ingat Saya
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Sistem
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top text-center">
                    <div class="text-muted small mb-2">Akun Demo Default:</div>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge bg-light text-dark border small" role="button" onclick="fillDemo('admin@kobana.test', 'admin123')">
                            Admin (admin@kobana.test)
                        </span>
                        <span class="badge bg-light text-dark border small" role="button" onclick="fillDemo('kasir@kobana.test', 'kasir123')">
                            Kasir (kasir@kobana.test)
                        </span>
                        <span class="badge bg-light text-dark border small" role="button" onclick="fillDemo('owner@kobana.test', 'owner123')">
                            Owner (owner@kobana.test)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email, pass) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = pass;
}
</script>
@endsection
