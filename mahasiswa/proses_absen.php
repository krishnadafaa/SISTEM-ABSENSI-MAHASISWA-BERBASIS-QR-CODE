<?php
session_start();
date_default_timezone_set('Asia/Makassar');
error_reporting(0); 
include '../config/database.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Akses ditolak.'];

if(isset($_POST['kode_sesi']) && isset($_SESSION['mahasiswa_id'])) {
    $token = mysqli_real_escape_string($conn, $_POST['kode_sesi']);
    $mahasiswa_id = $_SESSION['mahasiswa_id']; 

    // 1. Cek sesi
    $query_sesi = "SELECT id, waktu_berakhir, jadwal_id FROM sesi_absensi WHERE session_token='$token' AND is_active=1";
    $cek_sesi = mysqli_query($conn, $query_sesi);
    
    if (!$cek_sesi) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error.']);
        exit();
    }
    
    if(mysqli_num_rows($cek_sesi) > 0) {
        $sesi = mysqli_fetch_assoc($cek_sesi);
        $sesi_id = $sesi['id'];
        $jadwal_id = $sesi['jadwal_id'];
        
        
        // 2. VALIDASI KELAS
        $q_mhs = mysqli_query($conn, "SELECT kelas_id FROM mahasiswa WHERE id='$mahasiswa_id'");
        $d_mhs = mysqli_fetch_assoc($q_mhs);
        
        $q_jdwl = mysqli_query($conn, "SELECT kelas_id FROM jadwal_kuliah WHERE id='$jadwal_id'");
        $d_jdwl = mysqli_fetch_assoc($q_jdwl);
        
        if($d_mhs && $d_jdwl && $d_mhs['kelas_id'] != $d_jdwl['kelas_id']) {
            // Kunci array dikembalikan menjadi 'message' agar cocok dengan Javascript
            echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak! Kamu terdeteksi menscan QR Code milik kelas atau jadwal yang berbeda.']);
            exit(); 
        }

        // 3. Cek Batas Waktu
        if(strtotime($sesi['waktu_berakhir']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Sesi absensi sudah ditutup oleh dosen!']);
            exit();
        }

        // 4. Mencegah Double Scan
        $cek_absen = mysqli_query($conn, "SELECT id FROM absensi WHERE sesi_id='$sesi_id' AND mahasiswa_id='$mahasiswa_id'");
        
        if(mysqli_num_rows($cek_absen) > 0) {
            echo json_encode(['status' => 'warning', 'message' => 'Kamu sudah melakukan absen untuk sesi kelas ini sebelumnya.']);
            exit();
        } else {
            $waktu_absen = date('Y-m-d H:i:s');
            $insert = mysqli_query($conn, "INSERT INTO absensi (sesi_id, mahasiswa_id, waktu_absen, status) VALUES ('$sesi_id', '$mahasiswa_id', '$waktu_absen', 'hadir')");
            
            if($insert) {
                echo json_encode(['status' => 'success', 'message' => 'Berhasil! Kehadiranmu telah dicatat oleh sistem.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal simpan ke database.']);
            }
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'QR Code tidak valid atau sesi belum dibuka!']);
        exit();
    }
} else {
    echo json_encode($response);
    exit();
}
?>