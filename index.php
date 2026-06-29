<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi QR</title>
    <!-- Bootstrap 5.3.0 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        /* Pengaturan Dasar */
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f0f4f8;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        /* --- Elemen Background Kreatif (Animated Blobs) --- */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.7;
            animation: float 8s infinite alternate ease-in-out;
        }
        .blob-1 {
            width: 500px; height: 500px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            top: -150px; left: -100px;
            border-radius: 50%;
        }
        .blob-2 {
            width: 400px; height: 400px;
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
            bottom: -100px; right: -50px;
            border-radius: 50%;
            animation-delay: 2s;
        }
        .blob-3 {
            width: 300px; height: 300px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            border-radius: 50%;
            animation-delay: 4s;
            opacity: 0.4;
        }

        @keyframes float {
            0% { transform: translateY(0px) scale(1); }
            100% { transform: translateY(40px) scale(1.05); }
        }

        /* --- Desain Kartu Login (Glassmorphism Penuh) --- */
        .glass-card { 
            border-radius: 28px; 
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
        }

        .glass-card:hover {
            box-shadow: 0 40px 70px rgba(0, 0, 0, 0.12);
        }

        /* --- Ikon QR dengan Animasi Scanner --- */
        .qr-wrapper {
            position: relative;
            width: 75px;
            height: 75px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 15px 30px rgba(30, 60, 114, 0.3);
            overflow: hidden;
        }

        .qr-wrapper i {
            font-size: 38px;
            color: #ffffff;
            z-index: 2;
        }

        .scanner-line {
            position: absolute;
            width: 100%;
            height: 3px;
            background: #00f2fe;
            box-shadow: 0 0 12px #00f2fe;
            top: 0;
            left: 0;
            z-index: 3;
            animation: scan 2.5s infinite ease-in-out;
        }

        @keyframes scan {
            0%, 100% { top: 10%; opacity: 0; }
            10%, 90% { opacity: 1; }
            50% { top: 90%; }
        }

        /* --- Kustomisasi Form --- */
        .form-floating > .form-control {
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.7);
            border: 2px solid transparent;
            padding-left: 50px;
            font-weight: 600;
            color: #1e293b;
            transition: all 0.3s ease;
        }

        /* Khusus input password diberi space di kanan untuk ikon mata */
        .password-input {
            padding-right: 50px !important;
        }

        .form-floating > .form-control:focus {
            background: #ffffff;
            border-color: #4facfe;
            box-shadow: 0 0 0 5px rgba(79, 172, 254, 0.15);
        }

        .form-floating > label {
            padding-left: 50px;
            color: #64748b;
            font-weight: 500;
        }

        .input-group-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #475569;
            font-size: 20px;
            transition: all 0.3s ease;
        }

        /* --- Fitur Baru: Tombol Mata Toggle Password --- */
        .password-toggle-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            color: #64748b;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .password-toggle-icon:hover {
            color: #4facfe;
            transform: translateY(-50%) scale(1.1);
        }

        .form-control:focus ~ .input-group-icon {
            color: #4facfe;
        }

        /* --- Kustomisasi Tombol --- */
        .btn-login {
            border-radius: 14px;
            padding: 14px;
            font-weight: 800;
            font-size: 16px;
            letter-spacing: 1.5px;
            color: white;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(30, 60, 114, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center p-3">

    <!-- Latar Belakang Abstrak -->
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>

    <!-- Kartu Login -->
    <div class="card glass-card w-100 border-0" style="max-width: 440px; position: relative; z-index: 10;">
        <div class="card-body p-4 p-sm-5">
            
            <!-- Header & Identitas -->
            <div class="text-center mb-4 pb-2">
                <div class="qr-wrapper">
                    <div class="scanner-line"></div>
                    <i class="bi bi-qr-code-scan"></i>
                </div>
                <h3 class="fw-bolder text-dark mb-2" style="letter-spacing: -0.5px;">Sistem Absensi</h3>
                <p class="text-secondary" style="font-size: 14px; font-weight: 500;">
                    Portal akses terpadu untuk Admin, Dosen, dan Mahasiswa.
                </p>
            </div>

            <!-- Form -->
            <form action="auth.php" method="POST">
                <!-- Username -->
                <div class="form-floating mb-3 position-relative">
                    <input type="text" class="form-control" id="usernameInput" name="username" placeholder="Username" required autocomplete="off">
                    <i class="bi bi-person-bounding-box input-group-icon"></i>
                    <label for="usernameInput">Username</label>
                </div>
                
                <!-- Password (Dengan Fitur Mata) -->
                <div class="form-floating mb-5 position-relative">
                    <input type="password" class="form-control password-input" id="passwordInput" name="password" placeholder="Password" required>
                    <i class="bi bi-shield-lock input-group-icon"></i>
                    <!-- Ikon Mata -->
                    <i class="bi bi-eye password-toggle-icon" id="togglePassword"></i>
                    <label for="passwordInput">Password</label>
                </div>

                <!-- Tombol LOGIN -->
                <button type="submit" class="btn btn-login w-100">
                    LOGIN
                </button>
            </form>
            
        </div>
    </div>

    <!-- Logika JavaScript untuk Toggle Show/Hide Password -->
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordInput = document.querySelector('#passwordInput');

        togglePassword.addEventListener('click', function () {
            // Tukar tipe input antara 'password' dan 'text'
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Tukar ikon antara mata terbuka (bi-eye) dan mata tertutup (bi-eye-slash)
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });
    </script>

</body>
</html>