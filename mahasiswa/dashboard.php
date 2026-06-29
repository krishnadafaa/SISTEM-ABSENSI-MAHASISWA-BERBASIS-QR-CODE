<?php
session_start();
date_default_timezone_set('Asia/Makassar');
error_reporting(0); // Mencegah PHP membocorkan teks warning yang bisa merusak format JSON
include '../config/database.php';

if(!isset($_SESSION['role']) || $_SESSION['role'] != "mahasiswa") { 
    header("location:../index.php"); 
    exit(); 
}

$mahasiswa_id = $_SESSION['mahasiswa_id'];

// 1. Ambil Data Profil Mahasiswa (Dengan JOIN ke tabel users)
$query_mhs = mysqli_query($conn, "
    SELECT m.nama_lengkap, m.kelas_id, m.user_id, u.username 
    FROM mahasiswa m 
    JOIN users u ON m.user_id = u.id 
    WHERE m.id = '$mahasiswa_id'
");
$data_mhs = mysqli_fetch_assoc($query_mhs);
$nama_mahasiswa = ($data_mhs) ? $data_mhs['nama_lengkap'] : "Mahasiswa";
$mhs_kelas_id = $data_mhs['kelas_id'];
$user_id_akun = $data_mhs['user_id']; // ID untuk tabel users
$username_mahasiswa = $data_mhs['username'];

// 2. Handle POST Request untuk update profil (Disimpan ke tabel users)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    header('Content-Type: application/json');
    $new_username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi_password = trim($_POST['konfirmasi_password']);

    if (empty($new_username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak boleh kosong.']);
        exit;
    }

    $cek_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '$new_username' AND id != '$user_id_akun'");
    if (mysqli_num_rows($cek_username) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh pengguna lain.']);
        exit;
    }

    if (!empty($password_baru)) {
        if ($password_baru !== $konfirmasi_password) {
            echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password baru tidak cocok.']);
            exit;
        }
        
        $password_fix = md5($password_baru); // Sesuaikan enkripsi dengan sistem login (md5/polos/bcrypt)
        $update_query = "UPDATE users SET username = '$new_username', password = '$password_fix' WHERE id = '$user_id_akun'";
    } else {
        $update_query = "UPDATE users SET username = '$new_username' WHERE id = '$user_id_akun'";
    }

    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Username dan password berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data.']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Presensi - Mahasiswa</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --primary-grad: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); --glass-bg: rgba(255, 255, 255, 0.9); }
        body { font-family: 'Outfit', sans-serif; background: #f3f4f6; background-image: radial-gradient(at 100% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 0% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; min-height: 100vh; color: #1f2937; }
        .header-wrapper { padding: 40px 0 80px; color: white; }
        .glass-card { background: var(--glass-bg); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); margin-top: -60px; }
        .nav-pills-custom { background: #f3f4f6; padding: 8px; border-radius: 20px; display: flex; gap: 5px; }
        .nav-pills-custom .nav-link { border-radius: 15px; color: #6b7280; font-weight: 600; border: none; padding: 12px; transition: 0.3s; }
        .nav-pills-custom .nav-link.active { background: white; color: #6366f1; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        #reader { width: 100%; border-radius: 25px; overflow: hidden; border: 8px solid #fff; box-shadow: 0 0 25px rgba(99, 102, 241, 0.3); background: #000; }
        .subject-card { background: white; border-radius: 20px; border: 1px solid #e5e7eb; padding: 20px; margin-bottom: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .history-item { background: white; border-radius: 15px; padding: 15px; margin-bottom: 10px; border: 1px solid #f3f4f6; }
        .status-pill { padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .logout-btn { background: rgba(255, 255, 255, 0.1); border: 1px solid rgb(255, 255, 255); color: white; border-radius: 12px; }
    </style>
</head>
<body>

    <div class="container">
        <div class="header-wrapper d-flex justify-content-between align-items-center">
            <div>
                <h6 class="opacity-75 mb-0">Selamat Datang,</h6>
                <h4 class="fw-bold"><?php echo htmlspecialchars($nama_mahasiswa); ?></h4>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn logout-btn text-danger-emphasis"><i class="bi bi-power text-danger"></i></a>
            </div>
        </div>

        <div class="card glass-card border-0 mb-5">
            <div class="card-body p-4 p-md-5">
                
                <ul class="nav nav-pills-custom mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item flex-fill">
                        <button class="nav-link active w-100" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"><i class="bi bi-house-door me-2"></i>Home</button>
                    </li>
                    <li class="nav-item flex-fill">
                        <button class="nav-link w-100" id="scan-tab" data-bs-toggle="tab" data-bs-target="#scan" type="button"><i class="bi bi-qr-code-scan me-2"></i>Absen</button>
                    </li>
                    <li class="nav-item flex-fill">
                        <button class="nav-link w-100" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#riwayat" type="button"><i class="bi bi-journal-text me-2"></i>Riwayat</button>
                    </li>
                    <li class="nav-item flex-fill">
                        <button class="nav-link w-100" id="setting-tab" data-bs-toggle="tab" data-bs-target="#setting" type="button"><i class="bi bi-gear me-2"></i>Pengaturan</button>
                    </li>
                </ul>

                <div class="tab-content">
                    
                    <div class="tab-pane fade show active" id="home">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-bookmark-star me-1"></i> Progres Kehadiran</h6>
                        
                        <?php
                        // Ambil seluruh daftar mata kuliah yang ada di jadwal kelas mahasiswa saat ini
                        $q_mk = mysqli_query($conn, "SELECT jk.id as jadwal_id, mk.nama_mk FROM jadwal_kuliah jk JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id WHERE jk.kelas_id = '$mhs_kelas_id'");
                        
                        if(mysqli_num_rows($q_mk) > 0) {
                            while($row_mk = mysqli_fetch_assoc($q_mk)) {
                                $j_id = $row_mk['jadwal_id'];
                                
                                // 1. Hitung total sesi yang sudah diselenggarakan untuk matakuliah ini
                                $q_total_sesi = mysqli_query($conn, "SELECT COUNT(id) as total FROM sesi_absensi WHERE jadwal_id = '$j_id'");
                                $d_total_sesi = mysqli_fetch_assoc($q_total_sesi);
                                $total_sesi = $d_total_sesi['total'] ? $d_total_sesi['total'] : 0;
                                
                                // 2. Hitung statistik kehadiran siswa pada matakuliah ini
                                $q_stat = mysqli_query($conn, "SELECT status, COUNT(id) as jml FROM absensi WHERE mahasiswa_id='$mahasiswa_id' AND sesi_id IN (SELECT id FROM sesi_absensi WHERE jadwal_id = '$j_id') GROUP BY status");
                                
                                $hadir = 0; $alfa = 0; $izin = 0;
                                while($row_stat = mysqli_fetch_assoc($q_stat)){
                                    if($row_stat['status'] == 'hadir') $hadir = $row_stat['jml'];
                                    if($row_stat['status'] == 'alfa') $alfa = $row_stat['jml'];
                                    if($row_stat['status'] == 'izin') $izin = $row_stat['jml'];
                                }
                                
                                // 3. Hitung persentase
                                $persentase = ($total_sesi > 0) ? round(($hadir / $total_sesi) * 100) : 0;
                                
                                // Atur warna progress bar berdasarkan persentase kehadiran (asumsi batas minimal 75%)
                                $bar_color = ($persentase >= 75) ? 'bg-success' : 'bg-warning';
                                ?>
                                <div class="subject-card">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div style="max-width: 75%;">
                                            <h6 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($row_mk['nama_mk']); ?></h6>
                                            <small class="text-muted">Total Kelas Diadakan: <?php echo $total_sesi; ?> Sesi</small>
                                        </div>
                                        <h5 class="fw-bold text-primary mb-0"><?php echo $persentase; ?>%</h5>
                                    </div>
                                    
                                    <div class="progress mb-3" style="height: 8px; border-radius: 10px;">
                                        <div class="progress-bar <?php echo $bar_color; ?>" role="progressbar" style="width: <?php echo $persentase; ?>%"></div>
                                    </div>
                                    
                                    <div class="d-flex gap-3 text-center small fw-semibold">
                                        <span class="text-success"><i class="bi bi-check-circle me-1"></i> Hadir: <?php echo $hadir; ?></span>
                                        <span class="text-warning"><i class="bi bi-info-circle me-1"></i> Izin: <?php echo $izin; ?></span>
                                        <span class="text-danger"><i class="bi bi-x-circle me-1"></i> Alfa: <?php echo $alfa; ?></span>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo "<div class='text-center py-5 text-muted small'>Belum ada jadwal mata kuliah terdaftar untuk kelas Anda.</div>";
                        }
                        ?>
                    </div>

                    <div class="tab-pane fade" id="scan">
                        <div class="text-center mx-auto" style="max-width: 400px;">
                            <div id="reader" style="min-height: 300px;"></div>
                            <div id="result" class="mt-4">
                                <p class="text-muted small mb-3">Dekatkan QR Code ke kamera untuk verifikasi kehadiran otomatis.</p>
                                <button class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow" id="btn-start-camera" style="background: var(--primary-grad); border: none;" onclick="startCamera()">
                                    <i class="bi bi-camera me-2"></i>Aktifkan Kamera
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="riwayat">
                        <h6 class="fw-bold mb-3 text-secondary">Aktivitas Terakhir</h6>
                        <div class="riwayat-list">
                            <?php
                            $query_riwayat = "SELECT m.nama_mk, a.waktu_absen, a.status FROM absensi a JOIN sesi_absensi s ON a.sesi_id = s.id JOIN jadwal_kuliah j ON s.jadwal_id = j.id JOIN mata_kuliah m ON j.mata_kuliah_id = m.id WHERE a.mahasiswa_id = '$mahasiswa_id' ORDER BY a.waktu_absen DESC";
                            $res = mysqli_query($conn, $query_riwayat);
                            if(mysqli_num_rows($res) > 0){
                                while($row = mysqli_fetch_assoc($res)){
                                    $bg_pill = ($row['status'] == 'hadir') ? 'bg-success-subtle text-success' : (($row['status'] == 'izin') ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger');
                                    echo "<div class='history-item d-flex justify-content-between align-items-center mb-2'>
                                        <div><div class='fw-bold text-dark'>{$row['nama_mk']}</div><small class='text-muted'>{$row['waktu_absen']}</small></div>
                                        <span class='status-pill $bg_pill'>{$row['status']}</span>
                                    </div>";
                                }
                            } else { echo "<div class='text-center py-5 text-muted small'>Belum ada riwayat absensi.</div>"; }
                            ?>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="setting">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-shield-lock me-1"></i> Keamanan Akun</h6>
                        
                        <div id="alert-setting-container"></div>

                        <form id="form-update-profile" class="mt-2">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Username Akun</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-person text-secondary"></i></span>
                                    <input type="text" name="username" class="form-control border-start-0" value="<?php echo htmlspecialchars($username_mahasiswa); ?>" required autocomplete="off">
                                </div>
                            </div>
                            
                            <hr class="my-4 text-muted opacity-25">
                            <p class="small text-muted mb-3"><i class="bi bi-info-circle-fill me-1 text-primary"></i> Kosongkan kolom password di bawah jika Anda tidak ingin mengganti password.</p>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock text-secondary"></i></span>
                                    <input type="password" name="password_baru" class="form-control border-start-0" placeholder="Masukkan password baru">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-lock-fill text-secondary"></i></span>
                                    <input type="password" name="konfirmasi_password" class="form-control border-start-0" placeholder="Ulangi password baru">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold" style="background: var(--primary-grad); border: none;">
                                <span id="btn-setting-text">Simpan Perubahan</span>
                                <span id="btn-setting-spinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        const html5QrCode = new Html5Qrcode("reader");
        const resultDiv = document.getElementById('result');
        let cameraRunning = false;
        let mhsLat = null; let mhsLng = null;

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                mhsLat = position.coords.latitude; mhsLng = position.coords.longitude;
            }, err => console.log(err), { enableHighAccuracy: true });
        }

        async function startCamera() {
            if(cameraRunning) return;
            resultDiv.innerHTML = "<div class='text-muted small'><div class='spinner-grow spinner-grow-sm me-2'></div>Connecting...</div>";
            try {
                await html5QrCode.start({ facingMode: "environment" }, { fps: 15, qrbox: { width: 250, height: 250 } }, onScanSuccess);
                cameraRunning = true;
                resultDiv.innerHTML = "<div class='alert alert-light border-0 small py-2 fw-bold text-primary shadow-sm'><i class='bi bi-record-circle-fill text-danger me-2'></i>Kamera Aktif</div>";
            } catch (err) { resultDiv.innerHTML = `<div class='alert alert-danger border-0 small'>Error: ${err}</div>`; }
        }

        function stopCamera() {
            if(cameraRunning) { html5QrCode.stop().then(() => { cameraRunning = false; }).catch(err => console.log(err)); }
        }

        function onScanSuccess(decodedText) {
            html5QrCode.stop().then(() => {
                cameraRunning = false;
                resultDiv.innerHTML = "<div class='p-3 text-center'><div class='spinner-border text-primary mb-2'></div><br>Memproses...</div>";
                let formData = new FormData();
                formData.append('kode_sesi', decodedText);
                formData.append('latitude', mhsLat);
                formData.append('longitude', mhsLng);
                
                fetch('proses_absen.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => { window.location.href = 'notif_absen.php?status=' + data.status + '&msg=' + encodeURIComponent(data.message); })
                .catch(err => { window.location.href = 'notif_absen.php?status=error&msg=' + encodeURIComponent('Terjadi masalah jaringan atau kadaluarsa.'); });
            });
        }

        document.getElementById('scan-tab').addEventListener('shown.bs.tab', function () { startCamera(); });
        document.getElementById('home-tab').addEventListener('shown.bs.tab', function () { stopCamera(); });
        document.getElementById('riwayat-tab').addEventListener('shown.bs.tab', function () { stopCamera(); });
        document.getElementById('setting-tab').addEventListener('shown.bs.tab', function () { stopCamera(); });

        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('active') === 'scan') {
                new bootstrap.Tab(document.getElementById('scan-tab')).show();
            }
        });

        // AJAX Update Profile Account
        document.getElementById('form-update-profile').addEventListener('submit', function(e) {
            e.preventDefault();
            const btnText = document.getElementById('btn-setting-text');
            const btnSpinner = document.getElementById('btn-setting-spinner');
            const alertContainer = document.getElementById('alert-setting-container');
            
            btnText.classList.add('d-none');
            btnSpinner.classList.remove('d-none');
            alertContainer.innerHTML = '';

            fetch('', { method: 'POST', body: new FormData(this) })
            .then(res => res.json())
            .then(data => {
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
                if(data.status === 'success') {
                    alertContainer.innerHTML = `<div class='alert alert-success border-0 small py-2.5'><i class='bi bi-check-circle-fill me-2'></i>${data.message}</div>`;
                    document.getElementsByName('password_baru')[0].value = '';
                    document.getElementsByName('konfirmasi_password')[0].value = '';
                } else {
                    alertContainer.innerHTML = `<div class='alert alert-danger border-0 small py-2.5'><i class='bi bi-exclamation-circle-fill me-2'></i>${data.message}</div>`;
                }
            })
            .catch(err => {
                btnText.classList.remove('d-none');
                btnSpinner.classList.add('d-none');
                alertContainer.innerHTML = "<div class='alert alert-danger border-0 small py-2.5'>Terjadi kesalahan sistem / respon dari server gagal diuraikan.</div>";
            });
        });
    </script>
</body>
</html>