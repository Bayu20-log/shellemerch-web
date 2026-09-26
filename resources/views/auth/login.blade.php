<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Shellemerch</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/Group.png') }}">
    
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { 
            background-color: #f8fafc; 
            font-family: 'Poppins', sans-serif; 
        }
        .login-card { 
            border-radius: 20px; 
            border: none; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.08); 
        }
        .btn-primary { 
            background-color: #2A6CA2; 
            border-color: #2A6CA2; 
            border-radius: 50px; 
            padding: 10px; 
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-primary:hover { 
            background-color: #1e4e78; 
            border-color: #1e4e78; 
        }
        .form-control {
            border-radius: 50px;
            padding: 10px 20px;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 py-4">
    
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card p-4">
                    <div class="text-center mb-4 mt-2">
                        <img src="{{ asset('assets/img/Group.png') }}" alt="Logo" style="height: 60px; object-fit: contain;">
                        <h5 class="mt-3 fw-bold text-dark">Masuk ke Akun</h5>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success py-2 text-center" style="font-size: 14px; border-radius: 10px;">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger py-2 text-sm text-center" style="font-size: 14px; border-radius: 10px;">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form action="{{ route('login.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold ms-2">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Masukkan email..." required autofocus>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center ms-2 me-2">
                                <label class="form-label text-muted small fw-bold mb-1">Password</label>
                                <a href="{{ route('password.request') }}" class="small text-decoration-none mb-1" style="color: #2A6CA2;">Lupa password?</a>
                            </div>
                            <input type="password" name="password" class="form-control" placeholder="Masukkan password..." required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 shadow-sm">Masuk</button>
                    </form>
                    
                    <div class="text-center mt-4" style="font-size: 14px;">
                        <span class="text-muted">Belum punya akun?</span>
                        <a href="{{ route('register') }}" class="fw-semibold text-decoration-none" style="color: #2A6CA2;">Daftar</a>
                    </div>

                    <div class="text-center mt-3 mb-2">
                        <a href="{{ url('/') }}" class="text-decoration-none text-muted" style="font-size: 13px;">
                            &larr; Kembali ke beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>