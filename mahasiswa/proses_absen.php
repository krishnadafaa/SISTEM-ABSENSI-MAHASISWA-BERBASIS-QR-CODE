<?php
session_start();
date_default_timezone_set('Asia/Makassar');
error_reporting(0); 
include '../config/database.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Akses ditolak.'];

// Fungsi hitung jarak koordinat
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    $R = 6371000; 
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $R * $c; 
}

if(isset($_POST['kode_sesi']) && isset($_SESSION['mahasiswa_id'])) {
    $token = mysqli_real_escape_string($conn, $_POST['kode_sesi']);
    $mahasiswa_id = $_SESSION['mahasiswa_id']; 

    // Ambil GPS mahasiswa
    $lat_mahasiswa = isset($_POST['latitude']) ? floatval($_POST['latitude']) : 0;
    $lng_mahasiswa = isset($_POST['longitude']) ? floatval($_POST['longitude']) : 0;

    // Cek sesi aktif
    $query_sesi = "SELECT id, waktu_berakhir, jadwal_id, latitude, longitude, tipe_kelas FROM sesi_absensi WHERE session_token='$token' AND is_active=1";
    $cek_sesi = mysqli_query($conn, $query_sesi);
    
    if (!$cek_sesi) {
        echo json_encode(['status' => 'error', 'message' => 'Database Error.']);
        exit();
    }
    
    if(mysqli_num_rows($cek_sesi) > 0) {
        $sesi = mysqli_fetch_assoc($cek_sesi);
        $sesi_id = $sesi['id'];
        $jadwal_id = $sesi['jadwal_id'];
        
        // Ambil GPS kelas
        $lat_sesi = floatval($sesi['latitude']);
        $lng_sesi = floatval($sesi['longitude']);

        // Validasi lokasi offline
        if ($sesi['tipe_kelas'] == 'offline') {
            // Cek akses GPS
            if ($lat_mahasiswa == 0 || $lng_mahasiswa == 0) {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mendapatkan koordinat GPS Anda. Pastikan GPS aktif dan izin lokasi browser diberikan.']);
                exit();
            }

            // Cek radius jarak
            $jarak = hitungJarak($lat_mahasiswa, $lng_mahasiswa, $lat_sesi, $lng_sesi);
            
            if ($jarak > 50) { // Batas 10 meter
                $jarak_tampil = round($jarak); 
                echo json_encode(['status' => 'error', 'message' => "Posisi Anda terlalu jauh dari kelas ($jarak_tampil meter). Absensi ditolak!"]);
                exit();
            }
        }

        // Validasi kecocokan kelas
        $q_mhs = mysqli_query($conn, "SELECT kelas_id FROM mahasiswa WHERE id='$mahasiswa_id'");
        $d_mhs = mysqli_fetch_assoc($q_mhs);
        
        $q_jdwl = mysqli_query($conn, "SELECT kelas_id FROM jadwal_kuliah WHERE id='$jadwal_id'");
        $d_jdwl = mysqli_fetch_assoc($q_jdwl);
        
        if($d_mhs && $d_jdwl && $d_mhs['kelas_id'] != $d_jdwl['kelas_id']) {
            echo json_encode(['status' => 'error', 'message' => 'Akses Ditolak! Anda terdeteksi memindai QR Code milik kelas atau jadwal yang berbeda.']);
            exit(); 
        }

        // Cek batas waktu
        if(strtotime($sesi['waktu_berakhir']) < time()) {
            echo json_encode(['status' => 'error', 'message' => 'Sesi absensi telah ditutup oleh dosen!']);
            exit();
        }

        // Cegah absen ganda
        $cek_absen = mysqli_query($conn, "SELECT id FROM absensi WHERE sesi_id='$sesi_id' AND mahasiswa_id='$mahasiswa_id'");
        
        if(mysqli_num_rows($cek_absen) > 0) {
            echo json_encode(['status' => 'warning', 'message' => 'Anda sudah melakukan absensi untuk sesi kelas ini sebelumnya.']);
            exit();
        } else {
            // Simpan data absensi
            $waktu_absen = date('Y-m-d H:i:s');
            $insert = mysqli_query($conn, "INSERT INTO absensi (sesi_id, mahasiswa_id, waktu_absen, status) VALUES ('$sesi_id', '$mahasiswa_id', '$waktu_absen', 'hadir')");
            
            if($insert) {
                echo json_encode(['status' => 'success', 'message' => 'Berhasil! Kehadiran Anda telah dicatat oleh sistem.']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan data absensi ke database.']);
            }
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'QR Code tidak valid atau sesi absensi belum dibuka!']);
        exit();
    }
} else {
    echo json_encode($response);
    exit();
}
?>