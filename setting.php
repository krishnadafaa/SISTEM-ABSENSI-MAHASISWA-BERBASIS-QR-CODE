<?php
session_start();
include 'config/database.php';

if(!isset($_SESSION['user_id'])) { header("location:index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$status_update = "";

// Jika tombol simpan diklik
if(isset($_POST['update_akun'])){
    $user_baru = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_baru = trim($_POST['password']);
    
    // Cek apakah user mengisi kolom password baru
    if(!empty($pass_baru)) {
        // Jika diisi, HASH password tersebut dan update keduanya
        $hashed_password = password_hash($pass_baru, PASSWORD_DEFAULT);
        $update = mysqli_query($conn, "UPDATE users SET username='$user_baru', password='$hashed_password' WHERE id='$user_id'");
    } else {
        // Jika dikosongkan, update username saja
        $update = mysqli_query($conn, "UPDATE users SET username='$user_baru' WHERE id='$user_id'");
    }
    
    if($update){
        $_SESSION['username'] = $user_baru; 
        $status_update = "<div class='alert alert-success'>Akun berhasil diperbarui!</div>";
    } else {
        $status_update = "<div class='alert alert-danger'>Gagal: " . mysqli_error($conn) . "</div>";
    }
}

// Ambil data user saat ini (password tidak perlu ditampilkan)
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT username FROM users WHERE id='$user_id'"));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengaturan Akun</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white fw-bold">⚙️ Pengaturan Profile</div>
                    <div class="card-body p-4">
                        <?php echo $status_update; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" value="<?php echo $user['username']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-danger fw-bold">Password Baru (Opsional)</label>
                                <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                            </div>
                            <button type="submit" name="update_akun" class="btn btn-primary w-100">Simpan Perubahan</button>
                            
                            <?php 
                                $link = ($_SESSION['role'] == 'admin') ? 'admin/dashboard.php' : (($_SESSION['role'] == 'dosen') ? 'dosen/dashboard.php' : 'mahasiswa/dashboard.php');
                            ?>
                            <a href="<?php echo $link; ?>" class="btn btn-outline-secondary w-100 mt-2">Kembali ke Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>