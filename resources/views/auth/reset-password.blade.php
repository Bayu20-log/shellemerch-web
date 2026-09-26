<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Ulang Password - Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { background-color: #f8fafc; font-family: 'Poppins', sans-serif; }
        .login-card { border-radius: 20px; border: none; box-shadow: 0 15px 35px rgba(0,0,0,0.08); }
        .btn-primary { background-color: #2A6CA2; border-color: #2A6CA2; border-radius: 50px; padding: 10px; font-weight: 600; transition: 0.3s; }
        .btn-primary:hover { background-color: #1e4e78; border-color: #1e4e78; }
        .form-control { border-radius: 50px; padding: 10px 20px; }
        .form-control:focus { border-color: #2A6CA2; box-shadow: 0 0 0 0.2rem rgba(42, 108, 162, 0.2); }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-4" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5 col-xl-4">
                <div class="card login-card p-4">
                    <div class="text-center mb-4 mt-2">
                        <img src="{{ asset('assets/img/Group.png') }}" alt="Logo" style="height: 60px; object-fit: contain;">
                        <h5 class="mt-3 fw-bold text-dark">Buat Password Baru</h5>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger py-2 text-center" style="font-size: 14px; border-radius: 10px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('password.update') }}" method="POST" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        <div class="mb-3">
                            <label for="email" class="form-label text-muted small fw-bold ms-2">Email</label>
                            <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>
                            @error('email')<div class="invalid-feedback ms-2">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label text-muted small fw-bold ms-2">Password baru</label>
                            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                            @error('password')<div class="invalid-feedback ms-2">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label text-muted small fw-bold ms-2">Ulangi password baru</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Simpan Password Baru</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
