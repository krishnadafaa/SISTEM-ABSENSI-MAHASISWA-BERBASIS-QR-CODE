<?php
session_start();
date_default_timezone_set('Asia/Makassar');
include '../config/database.php';

if($_SESSION['role'] != 'dosen') {
    header("Location: ../index.php");
    exit();
}

$dosen_id = $_SESSION['dosen_id'];

// 1. Ambil Data Profil Dosen & Akun (Join ke tabel users)
$q_profil = mysqli_query($conn, "
    SELECT d.nama_lengkap, d.user_id, u.username 
    FROM dosen d 
    JOIN users u ON d.user_id = u.id 
    WHERE d.id = '$dosen_id'
");
$d_profil = mysqli_fetch_assoc($q_profil);
$user_id_akun = $d_profil['user_id'];
$username_dosen = $d_profil['username'];

// 2. Handle POST Request untuk update profil (Disimpan ke tabel users)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    error_reporting(0); // Matikan error agar JSON tidak rusak
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
        
        $password_fix = md5($password_baru); // Sesuaikan dengan enkripsi aplikasi Anda
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

// ==============================================================
// 3. PROSES CRUD JADWAL KULIAH (HANYA MILIK DOSEN YANG LOGIN)
// ==============================================================
if (isset($_GET['action']) && $_GET['action'] == 'delete_jadwal') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    // Filter ketat: Pastikan jadwal yang dihapus benar-benar milik dosen_id ini
    mysqli_query($conn, "DELETE FROM jadwal_kuliah WHERE id='$id' AND dosen_id='$dosen_id'");
    header("location:dashboard.php?msg=jadwal_deleted"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_jadwal'])) {
    $matkul = mysqli_real_escape_string($conn, $_POST['mata_kuliah_id']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $hari = mysqli_real_escape_string($conn, $_POST['hari']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);

    mysqli_query($conn, "INSERT INTO jadwal_kuliah (mata_kuliah_id, dosen_id, kelas_id, hari, jam_mulai, jam_selesai) 
                         VALUES ('$matkul', '$dosen_id', '$kelas', '$hari', '$jam_mulai', '$jam_selesai')");
    header("location:dashboard.php?msg=jadwal_success"); exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_jadwal'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id']);
    $matkul = mysqli_real_escape_string($conn, $_POST['mata_kuliah_id']);
    $kelas = mysqli_real_escape_string($conn, $_POST['kelas_id']);
    $hari = mysqli_real_escape_string($conn, $_POST['hari']);
    $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
    $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);

    // Filter ketat: Hanya bisa update jika jadwal adalah milik dosen_id ini
    mysqli_query($conn, "UPDATE jadwal_kuliah SET mata_kuliah_id='$matkul', kelas_id='$kelas', hari='$hari', jam_mulai='$jam_mulai', jam_selesai='$jam_selesai' WHERE id='$id' AND dosen_id='$dosen_id'");
    header("location:dashboard.php?msg=jadwal_updated"); exit();
}

// ==============================================================
// 4. PROSES SESI ABSENSI
// ==============================================================
if(isset($_POST['buka_sesi'])) {
    $jadwal_id = mysqli_real_escape_string($conn, $_POST['jadwal_id']);
    $topik = mysqli_real_escape_string($conn, $_POST['topik']);
    $tipe_kelas = mysqli_real_escape_string($conn, $_POST['tipe_kelas']);
    $latitude = mysqli_real_escape_string($conn, $_POST['latitude']);
    $longitude = mysqli_real_escape_string($conn, $_POST['longitude']); 

    if(isset($_POST['pertemuan_ke']) && is_array($_POST['pertemuan_ke'])) {
        $pertemuan_ke = mysqli_real_escape_string($conn, implode(',', $_POST['pertemuan_ke']));
    } else {
        echo "<script>alert('Pilih minimal satu pertemuan!'); window.history.back();</script>";
        exit();
    }

    if($tipe_kelas == 'offline' && (empty($latitude) || empty($longitude))) {
        echo "<script>alert('Gagal! Lokasi tidak terdeteksi'); window.location='dashboard.php';</script>";
        exit();
    }

    $token = "SESI-" . strtoupper(substr(md5(time() . $jadwal_id), 0, 8));
    $waktu_mulai = date ('Y-m-d H:i:s');
    $waktu_berakhir = date('Y-m-d H:i:s', strtotime(' +5 minutes'));
    
    $query_sesi = "INSERT INTO sesi_absensi (jadwal_id, pertemuan_ke, topik, tipe_kelas, latitude, longitude, session_token, waktu_mulai, waktu_berakhir, is_active) VALUES ('$jadwal_id', '$pertemuan_ke', '$topik', '$tipe_kelas', '$latitude', '$longitude', '$token', '$waktu_mulai', '$waktu_berakhir', 1)";

    if(mysqli_query($conn, $query_sesi)) {
        header("location: dashboard.php?msg=sesi_dibuka"); exit();
    }
}

if(isset($_POST['tutup_sesi'])) {
    $sesi_id = mysqli_real_escape_string($conn, $_POST['sesi_id']);
    $q_kelas = mysqli_query($conn, "SELECT jk.kelas_id FROM sesi_absensi sa JOIN jadwal_kuliah jk ON sa.jadwal_id=jk.id WHERE sa.id='$sesi_id'");
    $d_kelas = mysqli_fetch_assoc($q_kelas);
    $kelas_id = $d_kelas['kelas_id'];
    
    $q_mhs = mysqli_query($conn, "SELECT id FROM mahasiswa WHERE kelas_id='$kelas_id'");
    while($mhs = mysqli_fetch_assoc($q_mhs)) {
        $mhs_id = $mhs['id'];
        $cek_absen = mysqli_query($conn, "SELECT id FROM absensi WHERE sesi_id='$sesi_id' AND mahasiswa_id='$mhs_id'");
        if(mysqli_num_rows($cek_absen) == 0) {
            mysqli_query($conn, "INSERT INTO absensi (sesi_id, mahasiswa_id, waktu_absen, status) VALUES ('$sesi_id', '$mhs_id', NOW(), 'alfa')");
        }
    }
    mysqli_query($conn, "UPDATE sesi_absensi SET is_active=0, waktu_berakhir = NOW() WHERE id='$sesi_id'");
    header("location: dashboard.php?msg=sesi_ditutup"); exit();
}

$q_aktif = mysqli_query($conn, "SELECT sa.*, m.nama_mk, k.nama_kelas FROM sesi_absensi sa JOIN jadwal_kuliah jk ON sa.jadwal_id = jk.id JOIN mata_kuliah m ON jk.mata_kuliah_id = m.id JOIN kelas k ON jk.kelas_id = k.id WHERE jk.dosen_id = '$dosen_id' AND sa.is_active = 1 ORDER BY sa.id DESC LIMIT 1");
$sesi_aktif = mysqli_fetch_assoc($q_aktif);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Dosen - Sistem Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f3f4f6; color: #1f2937; }
        .sidebar { width: 260px; position: fixed; top: 0; left: 0; height: 100vh; background-color: #ffffff; border-right: 1px solid #e5e7eb; padding: 1.5rem 1rem; }
        .main-content { margin-left: 260px; padding: 2rem; min-height: 100vh; }
        .nav-pills .nav-link { color: #4b5563; border-radius: 10px; padding: 0.8rem 1rem; margin-bottom: 0.5rem; font-weight: 500; text-align: left; width: 100%; border: none; background: none; transition: 0.2s; }
        .nav-pills .nav-link:hover { background-color: #f9fafb; color: #4f46e5; }
        .nav-pills .nav-link.active { background-color: #e0e7ff; color: #4f46e5; font-weight: 600; }
        .card { border-radius: 16px; border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .grid-checkbox { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 110px)); gap: 8px; max-height: 150px; overflow-y: auto; padding: 10px; background: #f9fafb; border-radius: 10px; }
    </style>
</head>
<body>

    <div class="sidebar d-flex flex-column">
        <div class="d-flex align-items-center mb-4 px-2">
            <div class="bg-primary text-white rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <div>
                <h6 class="m-0 fw-bold text-dark">Portal Dosen</h6>
                <small class="text-muted">Smart Presensi</small>
            </div>
        </div>
        
        <ul class="nav nav-pills flex-column mb-auto" id="dashboardTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button"><i class="bi bi-house-door me-2"></i>Dashboard Home</button>
            </li>
            <li class="nav-item">
                <button class="nav-link active" id="mulai-tab" data-bs-toggle="tab" data-bs-target="#mulai" type="button"><i class="bi bi-play-circle me-2"></i>Sesi Kelas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="jadwal-tab" data-bs-toggle="tab" data-bs-target="#jadwal" type="button"><i class="bi bi-calendar-week me-2"></i>Kelola Jadwal</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#rekap" type="button"><i class="bi bi-journal-check me-2"></i>Rekap Absensi</button>
            </li>
            <li class="nav-item mt-2">
                <hr class="my-2 border-secondary opacity-25">
            </li>
            <li class="nav-item">
                <button class="nav-link" id="setting-tab" data-bs-toggle="tab" data-bs-target="#setting" type="button"><i class="bi bi-gear me-2"></i>Pengaturan Akun</button>
            </li>
        </ul>

        <div class="px-2 mt-4">
            <small class="d-block text-muted mb-2">Login Sebagai:</small>
            <strong class="d-block text-dark small mb-3"><?php echo $_SESSION['nama_lengkap']; ?></strong>
            <a href="../index.php" class="btn btn-light text-danger border w-100 btn-sm"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
        </div>
    </div>

    <div class="main-content">
        <div class="container-fluid">
            
            <?php if(isset($_GET['msg'])): ?>
                <?php if($_GET['msg'] == 'sesi_dibuka'): ?>
                    <div class="alert alert-primary border-0 shadow-sm alert-dismissible"><i class="bi bi-play-circle-fill me-2"></i>Sesi kelas berhasil dibuka! Berlaku selama 5 menit. <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php elseif($_GET['msg'] == 'sesi_ditutup'): ?>
                    <div class="alert alert-warning border-0 shadow-sm alert-dismissible"><i class="bi bi-stop-circle-fill me-2"></i>Sesi ditutup. Mahasiswa yang belum absen otomatis tercatat ALFA. <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php elseif($_GET['msg'] == 'jadwal_success'): ?>
                    <div class="alert alert-success border-0 shadow-sm alert-dismissible"><i class="bi bi-check-circle-fill me-2"></i>Jadwal kuliah Anda berhasil ditambahkan. <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php elseif($_GET['msg'] == 'jadwal_updated'): ?>
                    <div class="alert alert-info border-0 shadow-sm alert-dismissible"><i class="bi bi-info-circle-fill me-2"></i>Jadwal kuliah Anda berhasil diperbarui. <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php elseif($_GET['msg'] == 'jadwal_deleted'): ?>
                    <div class="alert alert-danger border-0 shadow-sm alert-dismissible"><i class="bi bi-trash-fill me-2"></i>Jadwal kuliah telah dihapus. <button class="btn-close" data-bs-dismiss="alert"></button></div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="tab-content" id="dashboardTabsContent">
                
                <div class="tab-pane fade" id="home" role="tabpanel">
                    <h4 class="fw-bold text-dark mb-4">Selamat Datang, <?php echo $_SESSION['nama_lengkap']; ?></h4>
                    <div class="card p-4 bg-white border-0 shadow-sm">
                        <p class="text-muted mb-0">Halaman beranda dosen. Anda bisa menambahkan ringkasan jadwal mengajar hari ini di sini nantinya.</p>
                    </div>
                </div>

                <div class="tab-pane fade show active" id="mulai" role="tabpanel">
                    <div class="mb-4">
                        <h4 class="fw-bold text-dark">Kelola Sesi Perkuliahan</h4>
                        <p class="text-muted">Masa aktif QR code diatur otomatis mati setelah 5 menit sejak sesi dibuka.</p>
                    </div>

                    <?php if($sesi_aktif): ?>
                        <div class="card bg-white p-5 text-center shadow-sm mx-auto" style="max-width: 700px; border-radius: 20px;">
                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-pill mx-auto mb-3" style="max-width: 200px;">
                                <i class="bi bi-record-circle-fill me-1 animate-pulse"></i> Sesi Berjalan (<?php echo strtoupper($sesi_aktif['tipe_kelas']); ?>)
                            </span>
                            <h3 class="fw-bold mb-1"><?php echo $sesi_aktif['nama_mk']; ?></h3>
                            <p class="text-muted mb-4">Pertemuan: <strong>Minggu Ke-<?php echo $sesi_aktif['pertemuan_ke']; ?></strong> | Topik: <strong><?php echo $sesi_aktif['topik']; ?></strong></p>
                            
                            <div class="alert alert-warning border-0 mx-auto d-flex flex-column align-items-center justify-content-center py-3 shadow-sm mb-4" style="max-width: 350px; border-radius: 15px; background: #fffbeb;">
                                <div class="d-flex align-items-center mb-1">
                                    <i class="bi bi-stopwatch text-danger fs-3 me-2"></i>
                                    <h2 class="fw-bold text-danger mb-0" id="countdown-timer" style="font-variant-numeric: tabular-nums;">05:00</h2>
                                </div>
                                <small class="fw-semibold text-muted">Sesi otomatis ditutup pada: <?php echo date('H:i:s', strtotime($sesi_aktif['waktu_berakhir'])); ?> WITA</small>
                            </div>

                            <div class="mb-4 position-relative">
                                <div id="qrcode" class="d-inline-block p-3 bg-white rounded-4 border shadow-sm transition-all" style="transition: 0.5s;"></div>
                                <div id="qr-expired-msg" class="text-danger fw-bold mt-3" style="display: none;">
                                    <i class="bi bi-x-circle-fill me-1"></i> Waktu Habis! QR Code Tidak Berlaku.
                                </div>
                            </div>

                            <form method="POST" id="form-tutup-sesi" onsubmit="return confirm('Akhiri sesi kelas sekarang? Mahasiswa yang belum absen otomatis Alfa.')">
                                <input type="hidden" name="sesi_id" value="<?php echo $sesi_aktif['id']; ?>">
                                <button type="submit" name="tutup_sesi" id="btn-tutup-sesi" class="btn btn-danger px-5 py-3 fw-bold rounded-pill shadow-sm">
                                    <i class="bi bi-stop-circle me-2"></i> Akhiri & Simpan Absensi
                                </button>
                            </form>
                        </div>
                        
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
                        <script>
                            // 1. Generate QR Code
                            var qrcode = new QRCode(document.getElementById("qrcode"), { 
                                text: "<?php echo $sesi_aktif['session_token']; ?>", 
                                width: 220, 
                                height: 220,
                                colorDark : "#1f2937",
                                colorLight : "#ffffff",
                                correctLevel : QRCode.CorrectLevel.H
                            });

                            // 2. Logika Mesin Stopwatch Real-Time
                            var endTime = <?php echo strtotime($sesi_aktif['waktu_berakhir']) * 1000; ?>;
                            
                            var timerInterval = setInterval(function() {
                                var now = new Date().getTime();
                                var distance = endTime - now;

                                if (distance < 0) {
                                    clearInterval(timerInterval);
                                    document.getElementById("countdown-timer").innerHTML = "00:00";
                                    document.getElementById("qrcode").style.opacity = "0.15"; 
                                    document.getElementById("qrcode").style.filter = "blur(3px)";
                                    document.getElementById("qr-expired-msg").style.display = "block";
                                } else {
                                    var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                    var seconds = Math.floor((distance % (1000 * 60)) / 1000);

                                    minutes = minutes < 10 ? "0" + minutes : minutes;
                                    seconds = seconds < 10 ? "0" + seconds : seconds;

                                    document.getElementById("countdown-timer").innerHTML = minutes + ":" + seconds;
                                }
                            }, 1000); 
                        </script>

                    <?php else: ?>
                        <div class="card p-4 bg-white" style="max-width: 650px;">
                            <div id="location-status" class="alert alert-info border-0 small py-2 text-primary shadow-sm mb-3">
                                <div class="spinner-border spinner-border-sm me-2" role="status"></div> Mengambil koordinat GPS...
                            </div>
                            
                            <form method="POST" id="formSesi">
                                <input type="hidden" name="latitude" id="lat_dosen">
                                <input type="hidden" name="longitude" id="lng_dosen">

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">Mata Kuliah & Kelas</label>
                                    <select name="jadwal_id" class="form-select bg-light border-0 py-2.5" required>
                                        <option value="">-- Pilih Jadwal Mengajar --</option>
                                        <?php
                                        $jadwal_query = mysqli_query($conn, "SELECT j.id, m.nama_mk, k.nama_kelas FROM jadwal_kuliah j JOIN mata_kuliah m ON j.mata_kuliah_id = m.id JOIN kelas k ON j.kelas_id = k.id WHERE j.dosen_id = '$dosen_id'");
                                        while($row = mysqli_fetch_assoc($jadwal_query)){
                                            echo "<option value='".$row['id']."'>".$row['nama_mk']." (Kelas ".$row['nama_kelas'].")</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted d-block">Pertemuan Ke- (Bisa pilih lebih dari 1)</label>
                                    <div class="grid-checkbox border">
                                        <?php for($i=1; $i<=16; $i++): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="pertemuan_ke[]" value="<?php echo $i; ?>" id="p<?php echo $i; ?>">
                                                <label class="form-check-label small" for="p<?php echo $i; ?>">Minggu <?php echo $i; ?></label>
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">Metode Perkuliahan</label>
                                    <select name="tipe_kelas" id="tipe_kelas" class="form-select bg-light border-0 py-2.5" required onchange="toggleLocationRequired(this.value)">
                                        <option value="offline">Offline (Validasi Radius Lokasi)</option>
                                        <option value="online">Online (Bebas Lokasi)</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-muted">Topik Perkuliahan</label>
                                    <input type="text" name="topik" class="form-control bg-light border-0 py-2.5" placeholder="Masukkan materi/topik pembahasan hari ini" required>
                                </div>

                                <button type="submit" name="buka_sesi" id="btnSubmit" class="btn btn-primary w-100 py-3 fw-bold shadow-sm" disabled>
                                    <i class="bi bi-qr-code-scan me-2"></i> Buka Sesi (Aktif 5 Menit)
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="tab-pane fade" id="jadwal" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Kelola Jadwal Saya</h4>
                            <p class="text-muted m-0">Atur jadwal mata kuliah dan kelas yang Anda ajar.</p>
                        </div>
                        <button class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahJadwal">
                            <i class="bi bi-plus-lg me-1"></i> Tambah Jadwal
                        </button>
                    </div>

                    <div class="card p-3 bg-white border-0 shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 py-3">Mata Kuliah & Kelas</th>
                                        <th class="py-3">Hari</th>
                                        <th class="py-3">Jam</th>
                                        <th class="text-center pe-3 py-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $q_jadwal = mysqli_query($conn, "SELECT j.*, m.nama_mk, k.nama_kelas FROM jadwal_kuliah j JOIN mata_kuliah m ON j.mata_kuliah_id = m.id JOIN kelas k ON j.kelas_id = k.id WHERE j.dosen_id = '$dosen_id' ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai ASC");
                                    if(mysqli_num_rows($q_jadwal) > 0){
                                        while($r = mysqli_fetch_assoc($q_jadwal)){
                                            echo "<tr>
                                                    <td class='ps-3 py-3'>
                                                        <div class='fw-bold text-dark'>{$r['nama_mk']}</div>
                                                        <div class='text-muted small'>Kelas {$r['nama_kelas']}</div>
                                                    </td>
                                                    <td><span class='badge bg-light text-dark border'>{$r['hari']}</span></td>
                                                    <td class='text-muted'>{$r['jam_mulai']} - {$r['jam_selesai']}</td>
                                                    <td class='text-center pe-3'>
                                                        <button class='btn btn-sm btn-light text-primary border me-1' data-bs-toggle='modal' data-bs-target='#editJdwl{$r['id']}'><i class='bi bi-pencil-fill'></i></button>
                                                        <a href='dashboard.php?action=delete_jadwal&id={$r['id']}' class='btn btn-sm btn-light text-danger border' onclick='return confirm(\"Hapus jadwal ini?\")'><i class='bi bi-trash-fill'></i></a>
                                                    </td>
                                                  </tr>";
                                            
                                            // MODAL EDIT JADWAL DOSEN
                                            $o_mk = ""; $qm = mysqli_query($conn,"SELECT * FROM mata_kuliah"); while($m=mysqli_fetch_assoc($qm)){ $s = ($m['id']==$r['mata_kuliah_id'])?"selected":""; $o_mk.="<option value='{$m['id']}' $s>{$m['nama_mk']}</option>"; }
                                            $o_kl = ""; $qk = mysqli_query($conn,"SELECT * FROM kelas"); while($k=mysqli_fetch_assoc($qk)){ $s = ($k['id']==$r['kelas_id'])?"selected":""; $o_kl.="<option value='{$k['id']}' $s>Kelas {$k['nama_kelas']}</option>"; }
                                            $hr_arr = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                            $o_hr = ""; foreach($hr_arr as $h){ $s = ($h==$r['hari'])?"selected":""; $o_hr.="<option value='$h' $s>$h</option>"; }

                                            echo "
                                            <div class='modal fade' id='editJdwl{$r['id']}' tabindex='-1'>
                                                <div class='modal-dialog modal-dialog-centered'>
                                                    <div class='modal-content border-0'>
                                                        <form method='POST'>
                                                            <div class='modal-header p-4 pb-3 border-0'>
                                                                <h5 class='fw-bold mb-0'>Edit Jadwal</h5>
                                                                <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                                                            </div>
                                                            <div class='modal-body px-4 py-0 text-start'>
                                                                <input type='hidden' name='id' value='{$r['id']}'>
                                                                <div class='mb-3'>
                                                                    <label class='form-label small text-muted fw-semibold'>Mata Kuliah</label>
                                                                    <select name='mata_kuliah_id' class='form-select bg-light border-0 py-2' required>{$o_mk}</select>
                                                                </div>
                                                                <div class='mb-3'>
                                                                    <label class='form-label small text-muted fw-semibold'>Kelas</label>
                                                                    <select name='kelas_id' class='form-select bg-light border-0 py-2' required>{$o_kl}</select>
                                                                </div>
                                                                <div class='mb-3'>
                                                                    <label class='form-label small text-muted fw-semibold'>Hari</label>
                                                                    <select name='hari' class='form-select bg-light border-0 py-2' required>{$o_hr}</select>
                                                                </div>
                                                                <div class='row'>
                                                                    <div class='col-6 mb-4'>
                                                                        <label class='form-label small text-muted fw-semibold'>Mulai</label>
                                                                        <input type='time' name='jam_mulai' class='form-control bg-light border-0 py-2' value='{$r['jam_mulai']}' required>
                                                                    </div>
                                                                    <div class='col-6 mb-4'>
                                                                        <label class='form-label small text-muted fw-semibold'>Selesai</label>
                                                                        <input type='time' name='jam_selesai' class='form-control bg-light border-0 py-2' value='{$r['jam_selesai']}' required>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class='modal-footer p-4 pt-0 border-0'>
                                                                <button type='submit' name='edit_jadwal' class='btn btn-primary w-100 py-2 fw-bold'>Simpan Perubahan</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center py-5 text-muted'>Anda belum memiliki jadwal yang terdaftar.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="tab-pane fade" id="rekap" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-end mb-4">
                        <div>
                            <h4 class="fw-bold text-dark mb-1">Riwayat & Rekap Absensi</h4>
                            <p class="text-muted small mb-0">Klik pada nama sesi perkuliahan untuk melihat detail kehadiran mahasiswa.</p>
                        </div>
                    </div>

                    <div class="accordion" id="accordionRekap">
                        <?php
                        $q_riwayat_sesi = mysqli_query($conn, "
                            SELECT sa.*, mk.nama_mk, k.nama_kelas, jk.kelas_id
                            FROM sesi_absensi sa
                            JOIN jadwal_kuliah jk ON sa.jadwal_id = jk.id
                            JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
                            JOIN kelas k ON jk.kelas_id = k.id
                            WHERE jk.dosen_id = '$dosen_id'
                            ORDER BY sa.waktu_mulai DESC
                        ");

                        if(mysqli_num_rows($q_riwayat_sesi) > 0) {
                            $count = 0;
                            while($sesi = mysqli_fetch_assoc($q_riwayat_sesi)) {
                                $count++;
                                $collapse_id = "collapse" . $count;
                                $heading_id = "heading" . $count;
                                
                                $tgl = date('d M Y, H:i', strtotime($sesi['waktu_mulai']));
                                $status_sesi = ($sesi['is_active'] == 1) ? "<span class='badge bg-success rounded-pill'><i class='bi bi-circle-fill me-1 small'></i>Aktif</span>" : "<span class='badge bg-secondary rounded-pill'><i class='bi bi-check2-all me-1'></i>Selesai</span>";

                                echo '
                                <div class="accordion-item mb-3 border-0 shadow-sm rounded-4 overflow-hidden">
                                    <h2 class="accordion-header" id="'.$heading_id.'">
                                        <button class="accordion-button collapsed bg-white fw-bold text-dark py-3" type="button" data-bs-toggle="collapse" data-bs-target="#'.$collapse_id.'" aria-expanded="false" aria-controls="'.$collapse_id.'">
                                            <div class="d-flex justify-content-between w-100 pe-3 align-items-center">
                                                <div>
                                                    <i class="bi bi-journal-bookmark-fill text-primary me-2 fs-5"></i>
                                                    <span class="fs-6">'.$sesi['nama_mk'].' (Kelas '.$sesi['nama_kelas'].')</span>
                                                    <div class="text-muted small mt-1 fw-normal ms-4">Pertemuan Ke-'.$sesi['pertemuan_ke'].' &bull; Topik: '.$sesi['topik'].'</div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="small text-muted mb-1 fw-normal">'.$tgl.' WITA</div>
                                                    '.$status_sesi.'
                                                </div>
                                            </div>
                                        </button>
                                    </h2>
                                    <div id="'.$collapse_id.'" class="accordion-collapse collapse" aria-labelledby="'.$heading_id.'" data-bs-parent="#accordionRekap">
                                        <div class="accordion-body bg-light p-4">
                                            <div class="table-responsive bg-white rounded-3 shadow-sm border">
                                                <table class="table table-hover mb-0 align-middle">
                                                    <thead class="table-light text-muted small">
                                                        <tr>
                                                            <th width="5%" class="text-center py-3">No</th>
                                                            <th class="py-3">Nama Mahasiswa</th>
                                                            <th class="text-center py-3">Waktu Absen</th>
                                                            <th class="text-center py-3">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="small">';
                                                    
                                                    $kelas_id = $sesi['kelas_id'];
                                                    $sesi_id = $sesi['id'];
                                                    $q_mhs_absen = mysqli_query($conn, "
                                                        SELECT m.id, m.nama_lengkap, a.waktu_absen, a.status
                                                        FROM mahasiswa m
                                                        LEFT JOIN absensi a ON m.id = a.mahasiswa_id AND a.sesi_id = '$sesi_id'
                                                        WHERE m.kelas_id = '$kelas_id'
                                                        ORDER BY m.nama_lengkap ASC
                                                    ");

                                                    if(mysqli_num_rows($q_mhs_absen) > 0) {
                                                        $no = 1;
                                                        while($mhs = mysqli_fetch_assoc($q_mhs_absen)) {
                                                            $waktu = $mhs['waktu_absen'] ? date('H:i:s', strtotime($mhs['waktu_absen'])) : '-';
                                                            
                                                            $badge = "<span class='badge bg-light text-secondary border'>Belum Absen</span>";
                                                            if($mhs['status'] == 'hadir') $badge = "<span class='badge bg-success bg-opacity-10 text-success border border-success'>Hadir</span>";
                                                            if($mhs['status'] == 'izin') $badge = "<span class='badge bg-warning bg-opacity-10 text-warning border border-warning'>Izin</span>";
                                                            if($mhs['status'] == 'alfa') $badge = "<span class='badge bg-danger bg-opacity-10 text-danger border border-danger'>Alfa</span>";

                                                            echo '
                                                            <tr>
                                                                <td class="text-center text-muted">'.$no++.'</td>
                                                                <td class="fw-semibold">'.$mhs['nama_lengkap'].'</td>
                                                                <td class="text-center font-monospace text-muted">'.$waktu.'</td>
                                                                <td class="text-center">'.$badge.'</td>
                                                            </tr>';
                                                        }
                                                    } else {
                                                        echo '<tr><td colspan="4" class="text-center text-muted py-4">Belum ada data mahasiswa di kelas ini.</td></tr>';
                                                    }

                                echo '              </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>';
                            }
                        } else {
                            echo '
                            <div class="card p-5 bg-white border-0 shadow-sm text-center rounded-4">
                                <i class="bi bi-inbox text-muted mb-3" style="font-size: 3rem;"></i>
                                <h5 class="fw-bold text-dark">Riwayat Kosong</h5>
                                <p class="text-muted mb-0">Anda belum pernah menyelenggarakan sesi perkuliahan apa pun.</p>
                            </div>';
                        }
                        ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="setting" role="tabpanel">
                    <h4 class="fw-bold text-dark mb-4">Pengaturan Akun</h4>
                    
                    <div class="card p-4 bg-white border-0 shadow-sm" style="max-width: 650px;">
                        <h6 class="fw-bold mb-3 text-secondary"><i class="bi bi-shield-lock me-1"></i> Keamanan Akun Dosen</h6>
                        
                        <div id="alert-setting-container"></div>

                        <form id="form-update-profile" class="mt-2">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Username Akun</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-secondary"></i></span>
                                    <input type="text" name="username" class="form-control border-start-0 bg-light" value="<?php echo htmlspecialchars($username_dosen); ?>" required autocomplete="off">
                                </div>
                            </div>
                            
                            <hr class="my-4 text-muted opacity-25">
                            <p class="small text-muted mb-3"><i class="bi bi-info-circle-fill me-1 text-primary"></i> Kosongkan kolom password di bawah jika Anda tidak ingin mengganti password.</p>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-secondary"></i></span>
                                    <input type="password" name="password_baru" class="form-control border-start-0 bg-light" placeholder="Masukkan password baru">
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label small fw-bold text-muted">Konfirmasi Password Baru</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock-fill text-secondary"></i></span>
                                    <input type="password" name="konfirmasi_password" class="form-control border-start-0 bg-light" placeholder="Ulangi password baru">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-bold shadow-sm">
                                <span id="btn-setting-text">Simpan Perubahan</span>
                                <span id="btn-setting-spinner" class="spinner-border spinner-border-sm d-none"></span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahJadwal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <form method="POST">
                    <div class="modal-header p-4 pb-3 border-0">
                        <h5 class="modal-title fw-bold">Tambah Jadwal Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body px-4 py-0">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">Mata Kuliah</label>
                            <select name="mata_kuliah_id" class="form-select bg-light border-0 py-2" required>
                                <option value="">-- Pilih Mata Kuliah --</option>
                                <?php $qm = mysqli_query($conn,"SELECT * FROM mata_kuliah"); while($m=mysqli_fetch_assoc($qm)){ echo "<option value='{$m['id']}'>{$m['nama_mk']}</option>"; } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">Kelas</label>
                            <select name="kelas_id" class="form-select bg-light border-0 py-2" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php $qk = mysqli_query($conn,"SELECT * FROM kelas"); while($k=mysqli_fetch_assoc($qk)){ echo "<option value='{$k['id']}'>Kelas {$k['nama_kelas']}</option>"; } ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-semibold">Hari</label>
                            <select name="hari" class="form-select bg-light border-0 py-2" required>
                                <option value="">-- Pilih Hari --</option>
                                <option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-4">
                                <label class="form-label small text-muted fw-semibold">Jam Mulai</label>
                                <input type="time" name="jam_mulai" class="form-control bg-light border-0 py-2" required>
                            </div>
                            <div class="col-6 mb-4">
                                <label class="form-label small text-muted fw-semibold">Jam Selesai</label>
                                <input type="time" name="jam_selesai" class="form-control bg-light border-0 py-2" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer p-4 pt-0 border-0">
                        <button type="submit" name="tambah_jadwal" class="btn btn-primary w-100 py-2 fw-bold">Tambahkan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const latInput = document.getElementById('lat_dosen');
        const lngInput = document.getElementById('lng_dosen');
        const statusDiv = document.getElementById('location-status');
        const btnSubmit = document.getElementById('btnSubmit');

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition, showError, { enableHighAccuracy: true });
        } else {
            if(statusDiv) {
                statusDiv.className = "alert alert-danger border-0 small py-2 text-danger";
                statusDiv.innerHTML = "❌ Browser tidak mendukung lokasi.";
            }
        }

        function showPosition(position) {
            if(latInput && lngInput) {
                latInput.value = position.coords.latitude;
                lngInput.value = position.coords.longitude;
                statusDiv.className = "alert alert-success border-0 small py-2 text-success shadow-sm";
                statusDiv.innerHTML = `✅ GPS Aktif (Lat: ${position.coords.latitude.toFixed(4)}, Lng: ${position.coords.longitude.toFixed(4)})`;
                if(btnSubmit) btnSubmit.disabled = false;
            }
        }

        function showError(error) {
            if(statusDiv) {
                statusDiv.className = "alert alert-warning border-0 small py-2 text-warning";
                statusDiv.innerHTML = "⚠️ Gagal mengunci GPS. Jika kelas online, sesi tetap bisa dibuka.";
                if(document.getElementById('tipe_kelas') && document.getElementById('tipe_kelas').value === 'online' && btnSubmit) {
                    btnSubmit.disabled = false;
                }
            }
        }

        function toggleLocationRequired(value) {
            if(value === 'online' && btnSubmit) {
                btnSubmit.disabled = false;
            } else if(value === 'offline' && (!latInput.value || !lngInput.value) && btnSubmit) {
                btnSubmit.disabled = true;
            }
        }

        // Script Mencegah Tab Pindah saat Halaman di Refresh (Submit Form Jadwal)
        document.addEventListener("DOMContentLoaded", function() {
            let activeTab = sessionStorage.getItem('activeTabDosenUtama');
            if (activeTab) {
                let tabTrigger = new bootstrap.Tab(document.querySelector('#' + activeTab));
                tabTrigger.show();
            }

            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function (e) {
                    sessionStorage.setItem('activeTabDosenUtama', e.target.id);
                });
            });
        });

        // Script AJAX untuk Update Profil Akun
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
                alertContainer.innerHTML = "<div class='alert alert-danger border-0 small py-2.5'>Terjadi kesalahan sistem atau format respon gagal diproses.</div>";
            });
        });
    </script>
</body>
</html>