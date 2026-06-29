<?php
session_start();
include '../config/database.php';

if($_SESSION['role'] != "admin") { header("location:../index.php"); exit(); }

$page = isset($_GET['page']) ? $_GET['page'] : 'main';

// Ambil Data Admin yang sedang login untuk form Pengaturan
$admin_session_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['id']) ? $_SESSION['id'] : '');
if($admin_session_id != ''){
    $q_admin = mysqli_query($conn, "SELECT id, username FROM users WHERE id='$admin_session_id'");
} else {
    // Fallback jika tidak pakai session ID, ambil admin utama
    $q_admin = mysqli_query($conn, "SELECT id, username FROM users WHERE role='admin' LIMIT 1");
}
$d_admin = mysqli_fetch_assoc($q_admin);
$admin_id = $d_admin['id'];
$admin_username = $d_admin['username'];

// ==========================================
// PROSES AJAX UPDATE PROFIL (USERNAME & PASSWORD)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    header('Content-Type: application/json');
    $new_username = mysqli_real_escape_string($conn, trim($_POST['username']));
    $password_baru = trim($_POST['password_baru']);
    $konfirmasi_password = trim($_POST['konfirmasi_password']);

    if (empty($new_username)) {
        echo json_encode(['status' => 'error', 'message' => 'Username tidak boleh kosong.']);
        exit;
    }

    $cek_username = mysqli_query($conn, "SELECT id FROM users WHERE username = '$new_username' AND id != '$admin_id'");
    if (mysqli_num_rows($cek_username) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username sudah digunakan oleh akun lain.']);
        exit;
    }

    if (!empty($password_baru)) {
        if ($password_baru !== $konfirmasi_password) {
            echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password baru tidak cocok.']);
            exit;
        }
        $password_fix = md5($password_baru); // Sesuaikan metode enkripsi (md5, bcrypt, atau polos)
        $update_query = "UPDATE users SET username = '$new_username', password = '$password_fix' WHERE id = '$admin_id'";
    } else {
        $update_query = "UPDATE users SET username = '$new_username' WHERE id = '$admin_id'";
    }

    if (mysqli_query($conn, $update_query)) {
        echo json_encode(['status' => 'success', 'message' => 'Akun admin berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data akun.']);
    }
    exit;
}

// ==========================================
// 1. PROSES AKSI HAPUS (DELETE)
// ==========================================
if (isset($_GET['action']) && $_GET['action'] == 'delete') {
    $id = mysqli_real_escape_string($conn, $_GET['id']);
    
    if ($page == 'mahasiswa') {
        $get_mhs = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM mahasiswa WHERE id='$id'"));
        $uid = $get_mhs['user_id'];
        mysqli_query($conn, "DELETE FROM mahasiswa WHERE id='$id'");
        mysqli_query($conn, "DELETE FROM users WHERE id='$uid'");
    } elseif ($page == 'dosen') {
        $get_dsn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM dosen WHERE id='$id'"));
        $uid = $get_dsn['user_id'];
        mysqli_query($conn, "DELETE FROM dosen WHERE id='$id'");
        mysqli_query($conn, "DELETE FROM users WHERE id='$uid'");
    } elseif ($page == 'matkul') {
        mysqli_query($conn, "DELETE FROM mata_kuliah WHERE id='$id'");
    } elseif ($page == 'kelas') {
        mysqli_query($conn, "DELETE FROM kelas WHERE id='$id'");
    } elseif ($page == 'jadwal') {
        mysqli_query($conn, "DELETE FROM jadwal_kuliah WHERE id='$id'");
    }
    header("location:dashboard.php?page=$page&msg=deleted");
    exit();
}

// ==========================================
// 2. PROSES AKSI TAMBAH (CREATE) & EDIT (UPDATE)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['action'])) {
    
    // --- KELAS ---
    if (isset($_POST['tambah_kelas'])) {
        $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
        mysqli_query($conn, "INSERT INTO kelas (nama_kelas) VALUES ('$nama_kelas')");
        header("location:dashboard.php?page=kelas&msg=success"); exit();
    }
    if (isset($_POST['edit_kelas'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $nama_kelas = mysqli_real_escape_string($conn, $_POST['nama_kelas']);
        mysqli_query($conn, "UPDATE kelas SET nama_kelas='$nama_kelas' WHERE id='$id'");
        header("location:dashboard.php?page=kelas&msg=updated"); exit();
    }

    // --- JADWAL KULIAH ---
    if (isset($_POST['tambah_jadwal'])) {
        $matkul_id = mysqli_real_escape_string($conn, $_POST['mata_kuliah_id']);
        $dosen_id  = mysqli_real_escape_string($conn, $_POST['dosen_id']);
        $kelas_id  = mysqli_real_escape_string($conn, $_POST['kelas_id']);
        $hari      = mysqli_real_escape_string($conn, $_POST['hari']);
        $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
        $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
        
        mysqli_query($conn, "INSERT INTO jadwal_kuliah (mata_kuliah_id, dosen_id, kelas_id, hari, jam_mulai, jam_selesai) 
                             VALUES ('$matkul_id', '$dosen_id', '$kelas_id', '$hari', '$jam_mulai', '$jam_selesai')");
        header("location:dashboard.php?page=jadwal&msg=success"); exit();
    }
    if (isset($_POST['edit_jadwal'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $matkul_id = mysqli_real_escape_string($conn, $_POST['mata_kuliah_id']);
        $dosen_id  = mysqli_real_escape_string($conn, $_POST['dosen_id']);
        $kelas_id  = mysqli_real_escape_string($conn, $_POST['kelas_id']);
        $hari      = mysqli_real_escape_string($conn, $_POST['hari']);
        $jam_mulai = mysqli_real_escape_string($conn, $_POST['jam_mulai']);
        $jam_selesai = mysqli_real_escape_string($conn, $_POST['jam_selesai']);
        
        mysqli_query($conn, "UPDATE jadwal_kuliah SET mata_kuliah_id='$matkul_id', dosen_id='$dosen_id', kelas_id='$kelas_id', hari='$hari', jam_mulai='$jam_mulai', jam_selesai='$jam_selesai' WHERE id='$id'");
        header("location:dashboard.php?page=jadwal&msg=updated"); exit();
    }

    // --- MAHASISWA ---
    if (isset($_POST['tambah_mahasiswa'])) {
        $nim = mysqli_real_escape_string($conn, $_POST['nim']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $prodi = mysqli_real_escape_string($conn, $_POST['program_studi']);
        $angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);
        $kelas_id = mysqli_real_escape_string($conn, $_POST['kelas_id']); 
        
        $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$nim'");
        if(mysqli_num_rows($cek_user) > 0) {
            echo "<script>alert('Gagal: NIM sudah terdaftar!'); window.location='dashboard.php?page=mahasiswa';</script>"; exit();
        }

        $hashed_password = password_hash($nim, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$nim', '$hashed_password', 'mahasiswa')");
        $user_id = mysqli_insert_id($conn);
        
        $query = "INSERT INTO mahasiswa (user_id, nim, nama_lengkap, program_studi, angkatan, kelas_id) 
                  VALUES ('$user_id', '$nim', '$nama', '$prodi', '$angkatan', '$kelas_id')";
        if(mysqli_query($conn, $query)) { header("location:dashboard.php?page=mahasiswa&msg=success"); exit(); } 
        else { mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'"); }
    }
    if (isset($_POST['edit_mahasiswa'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $nim = mysqli_real_escape_string($conn, $_POST['nim']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        $prodi = mysqli_real_escape_string($conn, $_POST['program_studi']);
        $angkatan = mysqli_real_escape_string($conn, $_POST['angkatan']);
        $kelas_id = mysqli_real_escape_string($conn, $_POST['kelas_id']);

        $get_uid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM mahasiswa WHERE id='$id'"));
        $uid = $get_uid['user_id'];
        mysqli_query($conn, "UPDATE users SET username='$nim' WHERE id='$uid'");
        mysqli_query($conn, "UPDATE mahasiswa SET nim='$nim', nama_lengkap='$nama', program_studi='$prodi', angkatan='$angkatan', kelas_id='$kelas_id' WHERE id='$id'");
        header("location:dashboard.php?page=mahasiswa&msg=updated"); exit();
    }
    
    // --- DOSEN ---
    if (isset($_POST['tambah_dosen'])) {
        $nidn = mysqli_real_escape_string($conn, $_POST['nidn']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
        
        $cek_user = mysqli_query($conn, "SELECT id FROM users WHERE username='$nidn'");
        if(mysqli_num_rows($cek_user) > 0) { echo "<script>alert('Gagal: NIDN sudah terdaftar!'); window.location='dashboard.php?page=dosen';</script>"; exit(); }

        $hashed_password = password_hash($nidn, PASSWORD_DEFAULT);
        mysqli_query($conn, "INSERT INTO users (username, password, role) VALUES ('$nidn', '$hashed_password', 'dosen')");
        $user_id = mysqli_insert_id($conn);
        
        if(mysqli_query($conn, "INSERT INTO dosen (user_id, nidn, nama_lengkap) VALUES ('$user_id', '$nidn', '$nama')")) {
            header("location:dashboard.php?page=dosen&msg=success"); exit();
        } else { mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'"); }
    }
    if (isset($_POST['edit_dosen'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $nidn = mysqli_real_escape_string($conn, $_POST['nidn']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);

        $get_uid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT user_id FROM dosen WHERE id='$id'"));
        $uid = $get_uid['user_id'];
        mysqli_query($conn, "UPDATE users SET username='$nidn' WHERE id='$uid'");
        mysqli_query($conn, "UPDATE dosen SET nidn='$nidn', nama_lengkap='$nama' WHERE id='$id'");
        header("location:dashboard.php?page=dosen&msg=updated"); exit();
    }
    
    // --- MATA KULIAH ---
    if (isset($_POST['tambah_matkul'])) {
        $kode = mysqli_real_escape_string($conn, $_POST['kode_mk']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $sks = mysqli_real_escape_string($conn, $_POST['sks']);
        $smt = mysqli_real_escape_string($conn, $_POST['semester']);
        mysqli_query($conn, "INSERT INTO mata_kuliah (kode_mk, nama_mk, sks, semester) VALUES ('$kode', '$nama', '$sks', '$smt')");
        header("location:dashboard.php?page=matkul&msg=success"); exit();
    }
    if (isset($_POST['edit_matkul'])) {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        $kode = mysqli_real_escape_string($conn, $_POST['kode_mk']);
        $nama = mysqli_real_escape_string($conn, $_POST['nama_mk']);
        $sks = mysqli_real_escape_string($conn, $_POST['sks']);
        $smt = mysqli_real_escape_string($conn, $_POST['semester']);
        mysqli_query($conn, "UPDATE mata_kuliah SET kode_mk='$kode', nama_mk='$nama', sks='$sks', semester='$smt' WHERE id='$id'");
        header("location:dashboard.php?page=matkul&msg=updated"); exit();
    }
}

// Statistik
$jml_mhs = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM mahasiswa"));
$jml_dsn = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM dosen"));
$jml_mk  = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM mata_kuliah"));
$jml_kls = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM kelas"));
$jml_jdw = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM jadwal_kuliah"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f4f6f9; /* Abu-abu sangat terang dan bersih */
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        
        /* Sidebar Fix Kiri */
        .sidebar {
            width: 260px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background-color: #ffffff;
            border-right: 1px solid #e9ecef;
            z-index: 100;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 8px rgba(0,0,0,0.02);
        }

        /* Area Konten Utama */
        .main-content {
            margin-left: 260px;
            padding: 2.5rem;
            min-height: 100vh;
        }

        /* Menu Navigasi */
        .nav-link {
            color: #495057;
            padding: 0.85rem 1.25rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .nav-link:hover {
            background-color: #f8f9fa;
            color: #212529;
        }
        .nav-link.active {
            background-color: #e9ecef;
            color: #0d6efd;
            font-weight: 600;
        }
        .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
            color: inherit;
        }

        /* Desain Tabel Bersih */
        .table-wrapper {
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .table { margin-bottom: 0; }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .table td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #e9ecef;
            color: #212529;
        }

        /* Form Controls */
        .form-control, .form-select {
            padding: 0.6rem 1rem;
            border-radius: 6px;
        }

        /* Responsive Sidebar */
        @media (max-width: 768px) {
            .sidebar { position: relative; width: 100%; height: auto; border-right: none; border-bottom: 1px solid #dee2e6; }
            .main-content { margin-left: 0; padding: 1.5rem; }
        }
    </style>
</head>
<body>

    <aside class="sidebar p-3">
        <div class="mb-4 px-2 mt-2">
            <h4 class="m-0 fw-bold text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Admin Panel</h4>
        </div>
        
        <nav class="nav flex-column flex-grow-1">
            <a class="nav-link <?php echo $page == 'main' ? 'active' : ''; ?>" href="dashboard.php?page=main">
                <i class="bi bi-house-door"></i> Beranda
            </a>
            <a class="nav-link <?php echo $page == 'jadwal' ? 'active' : ''; ?>" href="dashboard.php?page=jadwal">
                <i class="bi bi-calendar-event"></i> Jadwal Kuliah
            </a>
            <a class="nav-link <?php echo $page == 'kelas' ? 'active' : ''; ?>" href="dashboard.php?page=kelas">
                <i class="bi bi-building"></i> Ruang Kelas
            </a>
            <a class="nav-link <?php echo $page == 'mahasiswa' ? 'active' : ''; ?>" href="dashboard.php?page=mahasiswa">
                <i class="bi bi-people"></i> Data Mahasiswa
            </a>
            <a class="nav-link <?php echo $page == 'dosen' ? 'active' : ''; ?>" href="dashboard.php?page=dosen">
                <i class="bi bi-person-badge"></i> Data Dosen
            </a>
            <a class="nav-link <?php echo $page == 'matkul' ? 'active' : ''; ?>" href="dashboard.php?page=matkul">
                <i class="bi bi-journal-text"></i> Mata Kuliah
            </a>
        </nav>

        <div class="mt-auto border-top pt-3">
            <a class="nav-link text-secondary <?php echo $page == 'setting' ? 'active' : ''; ?>" href="dashboard.php?page=setting">
                <i class="bi bi-gear text-secondary"></i> Pengaturan
            </a>
            <a class="nav-link text-danger mt-1" href="../index.php">
                <i class="bi bi-box-arrow-left text-danger"></i> Keluar
            </a>
        </div>
    </aside>

    <main class="main-content">
        
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-<?php echo $_GET['msg'] == 'deleted' ? 'warning' : 'success'; ?> alert-dismissible fade show shadow-sm" role="alert">
                <?php if($_GET['msg'] == 'success'): ?>Data berhasil ditambahkan.
                <?php elseif($_GET['msg'] == 'updated'): ?>Data berhasil diperbarui.
                <?php elseif($_GET['msg'] == 'deleted'): ?>Data berhasil dihapus.
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if($page == 'main'): ?>
            <h3 class="fw-bold mb-4">Ringkasan Sistem</h3>
            
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Mahasiswa</h6>
                            <h2 class="m-0 fw-bold"><?php echo $jml_mhs; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Ruang Kelas</h6>
                            <h2 class="m-0 fw-bold"><?php echo $jml_kls; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Data Dosen</h6>
                            <h2 class="m-0 fw-bold"><?php echo $jml_dsn; ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <h6 class="text-muted fw-bold mb-2 text-uppercase">Jadwal Aktif</h6>
                            <h2 class="m-0 fw-bold"><?php echo $jml_jdw; ?></h2>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'jadwal'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Jadwal Kuliah</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalJadwal"><i class="bi bi-plus-lg me-1"></i> Tambah Jadwal</button>
            </div>
            
            <div class="table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Mata Kuliah</th><th>Dosen</th><th>Kelas</th><th>Waktu</th><th class="text-center" style="width: 180px;">Aksi</th></tr></thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT j.*, m.nama_mk, d.nama_lengkap AS nama_dosen, k.nama_kelas FROM jadwal_kuliah j JOIN mata_kuliah m ON j.mata_kuliah_id = m.id JOIN dosen d ON j.dosen_id = d.id JOIN kelas k ON j.kelas_id = k.id ORDER BY j.hari ASC");
                            if(mysqli_num_rows($q) > 0) {
                                while($r = mysqli_fetch_assoc($q)){
                                    echo "<tr>
                                            <td class='fw-semibold'>{$r['nama_mk']}</td>
                                            <td>{$r['nama_dosen']}</td>
                                            <td>{$r['nama_kelas']}</td>
                                            <td>{$r['hari']}, {$r['jam_mulai']} - {$r['jam_selesai']}</td>
                                            <td class='text-center'>
                                                <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#editJadwal{$r['id']}'><i class='bi bi-pencil'></i> Edit</button>
                                                <a href='dashboard.php?page=jadwal&action=delete&id={$r['id']}' class='btn btn-sm btn-outline-danger ms-1' onclick='return confirm(\"Yakin hapus jadwal ini?\")'><i class='bi bi-trash'></i></a>
                                            </td>
                                          </tr>";
                                    
                                    // Modal Edit
                                    $opsi_matkul = ""; $qm = mysqli_query($conn,"SELECT * FROM mata_kuliah"); while($m=mysqli_fetch_assoc($qm)){ $sel = ($m['id']==$r['mata_kuliah_id'])?"selected":""; $opsi_matkul.="<option value='{$m['id']}' $sel>{$m['nama_mk']}</option>"; }
                                    $opsi_dosen = ""; $qd = mysqli_query($conn,"SELECT * FROM dosen"); while($d=mysqli_fetch_assoc($qd)){ $sel = ($d['id']==$r['dosen_id'])?"selected":""; $opsi_dosen.="<option value='{$d['id']}' $sel>{$d['nama_lengkap']}</option>"; }
                                    $opsi_kelas = ""; $qk = mysqli_query($conn,"SELECT * FROM kelas"); while($k=mysqli_fetch_assoc($qk)){ $sel = ($k['id']==$r['kelas_id'])?"selected":""; $opsi_kelas.="<option value='{$k['id']}' $sel>{$k['nama_kelas']}</option>"; }
                                    $hari_arr = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
                                    $opsi_hari = ""; foreach($hari_arr as $h){ $sel = ($h==$r['hari'])?"selected":""; $opsi_hari.="<option value='$h' $sel>$h</option>"; }

                                    echo "<div class='modal fade' id='editJadwal{$r['id']}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><form method='POST'><div class='modal-header border-bottom-0'><h5 class='modal-title fw-bold'>Edit Jadwal</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body pt-0'><input type='hidden' name='id' value='{$r['id']}'>
                                        <div class='mb-3'><label class='form-label text-muted small fw-bold'>Mata Kuliah</label><select name='mata_kuliah_id' class='form-select' required>{$opsi_matkul}</select></div>
                                        <div class='mb-3'><label class='form-label text-muted small fw-bold'>Dosen</label><select name='dosen_id' class='form-select' required>{$opsi_dosen}</select></div>
                                        <div class='mb-3'><label class='form-label text-muted small fw-bold'>Kelas</label><select name='kelas_id' class='form-select' required>{$opsi_kelas}</select></div>
                                        <div class='mb-3'><label class='form-label text-muted small fw-bold'>Hari</label><select name='hari' class='form-select' required>{$opsi_hari}</select></div>
                                        <div class='row'><div class='col-6'><label class='form-label text-muted small fw-bold'>Mulai</label><input type='time' name='jam_mulai' class='form-control' value='{$r['jam_mulai']}' required></div><div class='col-6'><label class='form-label text-muted small fw-bold'>Selesai</label><input type='time' name='jam_selesai' class='form-control' value='{$r['jam_selesai']}' required></div></div>
                                        </div><div class='modal-footer border-top-0'><button type='button' class='btn btn-light' data-bs-dismiss='modal'>Batal</button><button type='submit' name='edit_jadwal' class='btn btn-primary'>Simpan</button></div></form></div></div></div>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center text-muted py-4'>Tidak ada data jadwal kuliah.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif($page == 'kelas'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Ruang Kelas</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKelas"><i class="bi bi-plus-lg me-1"></i> Tambah Kelas</button>
            </div>
            
            <div class="row">
                <div class="col-md-7">
                    <div class="table-wrapper">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>Nama Kelas</th><th class="text-center" style="width: 150px;">Aksi</th></tr></thead>
                                <tbody>
                                    <?php
                                    $q = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                                    while($r = mysqli_fetch_assoc($q)){
                                        echo "<tr>
                                                <td class='fw-semibold'>{$r['nama_kelas']}</td>
                                                <td class='text-center'>
                                                    <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#editKelas{$r['id']}'><i class='bi bi-pencil'></i></button>
                                                    <a href='dashboard.php?page=kelas&action=delete&id={$r['id']}' class='btn btn-sm btn-outline-danger ms-1' onclick='return confirm(\"Yakin hapus kelas?\")'><i class='bi bi-trash'></i></a>
                                                </td>
                                              </tr>";
                                        
                                        echo "<div class='modal fade' id='editKelas{$r['id']}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><form method='POST'><div class='modal-header border-bottom-0'><h5 class='modal-title fw-bold'>Edit Kelas</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body pt-0'><input type='hidden' name='id' value='{$r['id']}'>
                                        <label class='form-label text-muted small fw-bold'>Nama Kelas</label><input type='text' class='form-control' name='nama_kelas' value='{$r['nama_kelas']}' required>
                                        </div><div class='modal-footer border-top-0'><button type='button' class='btn btn-light' data-bs-dismiss='modal'>Batal</button><button type='submit' name='edit_kelas' class='btn btn-primary'>Simpan</button></div></form></div></div></div>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'mahasiswa'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Data Mahasiswa</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMhs"><i class="bi bi-plus-lg me-1"></i> Tambah Data</button>
            </div>
            
            <div class="table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>NIM</th><th>Nama Lengkap</th><th>Program Studi</th><th>Kelas</th><th>Angkatan</th><th class="text-center">Aksi</th></tr></thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT m.*, k.nama_kelas FROM mahasiswa m LEFT JOIN kelas k ON m.kelas_id = k.id ORDER BY m.nim ASC");
                            while($r = mysqli_fetch_assoc($q)){
                                $kelas = $r['nama_kelas'] ? $r['nama_kelas'] : "-";
                                echo "<tr>
                                        <td>{$r['nim']}</td>
                                        <td class='fw-semibold'>{$r['nama_lengkap']}</td>
                                        <td>{$r['program_studi']}</td>
                                        <td>{$kelas}</td>
                                        <td>{$r['angkatan']}</td>
                                        <td class='text-center'>
                                            <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#editMhs{$r['id']}'><i class='bi bi-pencil'></i></button>
                                            <a href='dashboard.php?page=mahasiswa&action=delete&id={$r['id']}' class='btn btn-sm btn-outline-danger ms-1' onclick='return confirm(\"Yakin hapus data?\")'><i class='bi bi-trash'></i></a>
                                        </td>
                                      </tr>";
                                
                                $opsi_kelas = "<option value=''>-- Pilih Kelas --</option>";
                                $q_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC");
                                while($k = mysqli_fetch_assoc($q_kelas)){ $selected = ($k['id'] == $r['kelas_id']) ? "selected" : ""; $opsi_kelas .= "<option value='{$k['id']}' $selected>{$k['nama_kelas']}</option>"; }

                                echo "<div class='modal fade' id='editMhs{$r['id']}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><form method='POST'><div class='modal-header border-bottom-0'><h5 class='modal-title fw-bold'>Edit Mahasiswa</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body pt-0'><input type='hidden' name='id' value='{$r['id']}'>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>NIM</label><input type='text' name='nim' class='form-control' value='{$r['nim']}' required></div>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>Nama Lengkap</label><input type='text' name='nama_lengkap' class='form-control' value='{$r['nama_lengkap']}' required></div>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>Program Studi</label><input type='text' name='program_studi' class='form-control' value='{$r['program_studi']}' required></div>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>Kelas</label><select name='kelas_id' class='form-select' required>{$opsi_kelas}</select></div>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>Angkatan</label><input type='number' name='angkatan' class='form-control' value='{$r['angkatan']}' required></div>
                                </div><div class='modal-footer border-top-0'><button type='button' class='btn btn-light' data-bs-dismiss='modal'>Batal</button><button type='submit' name='edit_mahasiswa' class='btn btn-primary'>Simpan</button></div></form></div></div></div>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif($page == 'dosen'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Data Dosen</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalDosen"><i class="bi bi-plus-lg me-1"></i> Tambah Data</button>
            </div>
            
            <div class="row">
                <div class="col-md-9">
                    <div class="table-wrapper">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead><tr><th>NIDN</th><th>Nama Lengkap</th><th class="text-center" style="width: 150px;">Aksi</th></tr></thead>
                                <tbody>
                                    <?php
                                    $q = mysqli_query($conn, "SELECT * FROM dosen ORDER BY nidn ASC");
                                    while($r = mysqli_fetch_assoc($q)){
                                        echo "<tr>
                                                <td>{$r['nidn']}</td>
                                                <td class='fw-semibold'>{$r['nama_lengkap']}</td>
                                                <td class='text-center'>
                                                    <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#editDosen{$r['id']}'><i class='bi bi-pencil'></i></button>
                                                    <a href='dashboard.php?page=dosen&action=delete&id={$r['id']}' class='btn btn-sm btn-outline-danger ms-1' onclick='return confirm(\"Yakin hapus data?\")'><i class='bi bi-trash'></i></a>
                                                </td>
                                              </tr>";
                                        
                                        echo "<div class='modal fade' id='editDosen{$r['id']}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><form method='POST'><div class='modal-header border-bottom-0'><h5 class='modal-title fw-bold'>Edit Dosen</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body pt-0'><input type='hidden' name='id' value='{$r['id']}'>
                                        <div class='mb-3'><label class='form-label text-muted small fw-bold'>NIDN</label><input type='text' name='nidn' class='form-control' value='{$r['nidn']}' required></div>
                                        <div class='mb-3'><label class='form-label text-muted small fw-bold'>Nama Lengkap</label><input type='text' name='nama_lengkap' class='form-control' value='{$r['nama_lengkap']}' required></div>
                                        </div><div class='modal-footer border-top-0'><button type='button' class='btn btn-light' data-bs-dismiss='modal'>Batal</button><button type='submit' name='edit_dosen' class='btn btn-primary'>Simpan</button></div></form></div></div></div>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif($page == 'matkul'): ?>
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold m-0">Mata Kuliah</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMatkul"><i class="bi bi-plus-lg me-1"></i> Tambah Matkul</button>
            </div>
            
            <div class="table-wrapper">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Kode MK</th><th>Nama Mata Kuliah</th><th>SKS</th><th>Semester</th><th class="text-center" style="width: 150px;">Aksi</th></tr></thead>
                        <tbody>
                            <?php
                            $q = mysqli_query($conn, "SELECT * FROM mata_kuliah ORDER BY kode_mk ASC");
                            while($r = mysqli_fetch_assoc($q)){
                                echo "<tr>
                                        <td>{$r['kode_mk']}</td>
                                        <td class='fw-semibold'>{$r['nama_mk']}</td>
                                        <td>{$r['sks']}</td>
                                        <td>{$r['semester']}</td>
                                        <td class='text-center'>
                                            <button class='btn btn-sm btn-outline-secondary' data-bs-toggle='modal' data-bs-target='#editMatkul{$r['id']}'><i class='bi bi-pencil'></i></button>
                                            <a href='dashboard.php?page=matkul&action=delete&id={$r['id']}' class='btn btn-sm btn-outline-danger ms-1' onclick='return confirm(\"Yakin hapus matkul?\")'><i class='bi bi-trash'></i></a>
                                        </td>
                                      </tr>";
                                
                                echo "<div class='modal fade' id='editMatkul{$r['id']}' tabindex='-1'><div class='modal-dialog'><div class='modal-content'><form method='POST'><div class='modal-header border-bottom-0'><h5 class='modal-title fw-bold'>Edit Matkul</h5><button type='button' class='btn-close' data-bs-dismiss='modal'></button></div><div class='modal-body pt-0'><input type='hidden' name='id' value='{$r['id']}'>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>Kode MK</label><input type='text' name='kode_mk' class='form-control' value='{$r['kode_mk']}' required></div>
                                <div class='mb-3'><label class='form-label text-muted small fw-bold'>Nama Matkul</label><input type='text' name='nama_mk' class='form-control' value='{$r['nama_mk']}' required></div>
                                <div class='row'><div class='col-6'><label class='form-label text-muted small fw-bold'>SKS</label><input type='number' name='sks' class='form-control' value='{$r['sks']}' required></div><div class='col-6'><label class='form-label text-muted small fw-bold'>Semester</label><input type='number' name='semester' class='form-control' value='{$r['semester']}' required></div></div>
                                </div><div class='modal-footer border-top-0'><button type='button' class='btn btn-light' data-bs-dismiss='modal'>Batal</button><button type='submit' name='edit_matkul' class='btn btn-primary'>Simpan</button></div></form></div></div></div>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif($page == 'setting'): ?>
            <h3 class="fw-bold mb-4">Pengaturan Akun Admin</h3>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-body p-4">
                            <div id="alert-setting-container"></div>

                            <form id="form-update-profile">
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Username</label>
                                    <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($admin_username); ?>" required>
                                </div>
                                
                                <hr class="my-4 text-muted border-light">
                                <p class="text-muted small mb-3">Kosongkan sandi di bawah jika Anda hanya ingin merubah Username.</p>

                                <div class="mb-3">
                                    <label class="form-label text-muted small fw-bold">Password Baru</label>
                                    <input type="password" name="password_baru" class="form-control" placeholder="Masukkan password baru...">
                                </div>
                                <div class="mb-4">
                                    <label class="form-label text-muted small fw-bold">Ulangi Password Baru</label>
                                    <input type="password" name="konfirmasi_password" class="form-control" placeholder="Konfirmasi password...">
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                                    <span id="btn-setting-text">Simpan Perubahan</span>
                                    <span id="btn-setting-spinner" class="spinner-border spinner-border-sm d-none"></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <div class="modal fade" id="modalJadwal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header border-bottom-0"><h5 class="modal-title fw-bold">Tambah Jadwal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body pt-0">
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Mata Kuliah</label><select name="mata_kuliah_id" class="form-select" required><option value="">Pilih Matkul...</option><?php $qm = mysqli_query($conn,"SELECT * FROM mata_kuliah"); while($m=mysqli_fetch_assoc($qm)){ echo "<option value='{$m['id']}'>{$m['nama_mk']}</option>"; } ?></select></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Dosen Pengampu</label><select name="dosen_id" class="form-select" required><option value="">Pilih Dosen...</option><?php $qd = mysqli_query($conn,"SELECT * FROM dosen"); while($d=mysqli_fetch_assoc($qd)){ echo "<option value='{$d['id']}'>{$d['nama_lengkap']}</option>"; } ?></select></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Kelas</label><select name="kelas_id" class="form-select" required><option value="">Pilih Kelas...</option><?php $qk = mysqli_query($conn,"SELECT * FROM kelas"); while($k=mysqli_fetch_assoc($qk)){ echo "<option value='{$k['id']}'>{$k['nama_kelas']}</option>"; } ?></select></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Hari</label><select name="hari" class="form-select" required><option value="Senin">Senin</option><option value="Selasa">Selasa</option><option value="Rabu">Rabu</option><option value="Kamis">Kamis</option><option value="Jumat">Jumat</option><option value="Sabtu">Sabtu</option></select></div>
                        <div class="row">
                            <div class="col-6"><label class="form-label text-muted small fw-bold">Jam Mulai</label><input type="time" name="jam_mulai" class="form-control" required></div>
                            <div class="col-6"><label class="form-label text-muted small fw-bold">Jam Selesai</label><input type="time" name="jam_selesai" class="form-control" required></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_jadwal" class="btn btn-primary">Simpan Data</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalKelas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header border-bottom-0"><h5 class="modal-title fw-bold">Tambah Kelas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body pt-0">
                        <label class="form-label text-muted small fw-bold">Nama Kelas</label>
                        <input type="text" class="form-control" name="nama_kelas" required placeholder="Contoh: A">
                    </div>
                    <div class="modal-footer border-top-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_kelas" class="btn btn-primary">Simpan Data</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMhs" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header border-bottom-0"><h5 class="modal-title fw-bold">Tambah Mahasiswa</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body pt-0">
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">NIM</label><input type="text" class="form-control" name="nim" required></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Nama Lengkap</label><input type="text" class="form-control" name="nama_lengkap" required></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Program Studi</label><input type="text" class="form-control" name="program_studi" required></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Kelas</label><select name="kelas_id" class="form-select" required><option value="">Pilih Kelas...</option><?php $q_kelas = mysqli_query($conn, "SELECT * FROM kelas ORDER BY nama_kelas ASC"); while($k = mysqli_fetch_assoc($q_kelas)){ echo "<option value='{$k['id']}'>{$k['nama_kelas']}</option>"; } ?></select></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Angkatan</label><input type="number" class="form-control" name="angkatan" required></div>
                    </div>
                    <div class="modal-footer border-top-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_mahasiswa" class="btn btn-primary">Simpan Data</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDosen" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header border-bottom-0"><h5 class="modal-title fw-bold">Tambah Dosen</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body pt-0">
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">NIDN</label><input type="text" class="form-control" name="nidn" required></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Nama Lengkap</label><input type="text" class="form-control" name="nama_lengkap" required></div>
                    </div>
                    <div class="modal-footer border-top-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_dosen" class="btn btn-primary">Simpan Data</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalMatkul" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header border-bottom-0"><h5 class="modal-title fw-bold">Tambah Mata Kuliah</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body pt-0">
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Kode MK</label><input type="text" class="form-control" name="kode_mk" required></div>
                        <div class="mb-3"><label class="form-label text-muted small fw-bold">Nama Mata Kuliah</label><input type="text" class="form-control" name="nama_mk" required></div>
                        <div class="row">
                            <div class="col-6 mb-3"><label class="form-label text-muted small fw-bold">SKS</label><input type="number" class="form-control" name="sks" required></div>
                            <div class="col-6 mb-3"><label class="form-label text-muted small fw-bold">Semester</label><input type="number" class="form-control" name="semester" required></div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button type="submit" name="tambah_matkul" class="btn btn-primary">Simpan Data</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fitur AJAX Update Profil Khusus Menu Pengaturan Admin
        const formProfile = document.getElementById('form-update-profile');
        if (formProfile) {
            formProfile.addEventListener('submit', function(e) {
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
                    alertContainer.innerHTML = "<div class='alert alert-danger border-0 small py-2.5'>Terjadi kesalahan sistem.</div>";
                });
            });
        }
    </script>
</body>
</html>