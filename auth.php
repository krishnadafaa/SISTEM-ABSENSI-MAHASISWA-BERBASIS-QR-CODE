<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'config/database.php';

if (!$conn) {
    die("<h1>KONEKSI DATABASE MATI: " . mysqli_connect_error() . "</h1>");
}

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = $_POST['password']; // Jangan di-escape jika pakai password_verify

// Cek username di database
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");

if ($query && mysqli_num_rows($query) > 0) {
    $user = mysqli_fetch_assoc($query);
    
    // VERIFIKASI PASSWORD (Cek Hash ATAU Teks Biasa untuk akun lama)
    if (password_verify($password, $user['password']) || $password == $user['password']) {
        
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        if ($user['role'] == 'mahasiswa') {
            $mhs = mysqli_query($conn, "SELECT id, nama_lengkap FROM mahasiswa WHERE user_id = " . $user['id']);
            if($data_mhs = mysqli_fetch_assoc($mhs)){
                $_SESSION['mahasiswa_id'] = $data_mhs['id'];
                $_SESSION['nama_lengkap'] = $data_mhs['nama_lengkap'];
            }
            header("location:mahasiswa/dashboard.php");
            exit();

        } elseif ($user['role'] == 'dosen') {
            $dsn = mysqli_query($conn, "SELECT id, nama_lengkap FROM dosen WHERE user_id = " . $user['id']);
            if($data_dsn = mysqli_fetch_assoc($dsn)){
                $_SESSION['dosen_id']     = $data_dsn['id'];
                $_SESSION['nama_lengkap'] = $data_dsn['nama_lengkap'];
            }
            header("location:dosen/dashboard.php");
            exit();
            
        } else {
            header("location:admin/dashboard.php");
            exit();
        }
    } else {
        echo "<script>alert('Password salah!'); window.location='index.php';</script>";
    }
} else {
    echo "<script>alert('Username tidak ditemukan!'); window.location='index.php';</script>";
}
?>