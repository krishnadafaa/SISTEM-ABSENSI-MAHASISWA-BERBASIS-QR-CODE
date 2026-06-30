<?php
session_start();
date_default_timezone_set('Asia/Makassar');
include '../config/database.php';

// KONTROL AKSES: Keamanan Sistem Informasi
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'dosen') {
    header("Location: ../index.php");
    exit();
}

$dosen_id = $_SESSION['dosen_id'];
$nama_dosen = $_SESSION['nama_lengkap'];

// Ambil parameter sesi_id dari URL
if(!isset($_GET['sesi_id']) || empty($_GET['sesi_id'])) {
    die("Sesi ID tidak ditemukan.");
}
$sesi_id = mysqli_real_escape_string($conn, $_GET['sesi_id']);

// 1. Ambil detail sesi yang dipilih (JOIN Relasional antara Sesi, Jadwal, MK, dan Kelas)
$q_sesi = mysqli_query($conn, "
    SELECT sa.*, mk.nama_mk, k.nama_kelas, jk.kelas_id
    FROM sesi_absensi sa
    JOIN jadwal_kuliah jk ON sa.jadwal_id = jk.id
    JOIN mata_kuliah mk ON jk.mata_kuliah_id = mk.id
    JOIN kelas k ON jk.kelas_id = k.id
    WHERE sa.id = '$sesi_id' AND jk.dosen_id = '$dosen_id'
");

if(mysqli_num_rows($q_sesi) == 0) {
    die("Data sesi tidak valid atau Anda tidak memiliki akses ke sesi ini.");
}
$sesi = mysqli_fetch_assoc($q_sesi);
$kelas_id = $sesi['kelas_id'];

// 2. Ambil data mahasiswa di kelas tersebut beserta status absensinya di sesi ini
$q_mhs_absen = mysqli_query($conn, "
    SELECT m.nama_lengkap, u.username AS nim, a.waktu_absen, a.status
    FROM mahasiswa m
    JOIN users u ON m.user_id = u.id
    LEFT JOIN absensi a ON m.id = a.mahasiswa_id AND a.sesi_id = '$sesi_id'
    WHERE m.kelas_id = '$kelas_id'
    ORDER BY m.nama_lengkap ASC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi - <?php echo htmlspecialchars($sesi['nama_mk']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; color: #222; line-height: 1.4; padding: 20px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; }
        .header h2 { margin: 0; text-transform: uppercase; font-size: 14pt; }
        .header p { margin: 5px 0 0 0; font-size: 10pt; color: #444; }
        
        .meta-info { margin-bottom: 15px; font-size: 10pt; width: 100%; border-collapse: collapse; }
        .meta-info td { border: none; padding: 3px 5px; }
        
        .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table-data th, .table-data td { border: 1px solid #000; padding: 6px 8px; font-size: 9.5pt; text-align: left; }
        .table-data th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center !important; }
        
        @media print {
            body { padding: 0; }
            @page { margin: 1.5cm; }
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>BERITA ACARA & REKAPITULASI PRESENSI PERKULIAHAN</h2>
        <p>Sistem Informasi Manajemen Absensi Kampus Berbasis QR Code</p>
    </div>

    <table class="meta-info">
        <tr>
            <td style="width: 18%;"><strong>Mata Kuliah</strong></td>
            <td style="width: 42%;">: <?php echo htmlspecialchars($sesi['nama_mk']); ?> (Kelas <?php echo htmlspecialchars($sesi['nama_kelas']); ?>)</td>
            <td style="width: 15%;"><strong>Pertemuan Ke</strong></td>
            <td style="width: 25%;">: <?php echo $sesi['pertemuan_ke']; ?></td>
        </tr>
        <tr>
            <td><strong>Dosen Pengampu</strong></td>
            <td>: <?php echo htmlspecialchars($nama_dosen); ?></td>
            <td><strong>Topik / Bahasan</strong></td>
            <td>: <?php echo htmlspecialchars($sesi['topik']); ?></td>
        </tr>
        <tr>
            <td><strong>Waktu Sesi</strong></td>
            <td>: <?php echo date('d-m-Y H:i', strtotime($sesi['waktu_mulai'])); ?> WITA</td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: <?php echo date('d-m-Y H:i'); ?> WITA</td>
        </tr>
    </table>

    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 18%;">NIM</th>
                <th style="width: 40%;">Nama Lengkap Mahasiswa</th>
                <th style="width: 18%;">Waktu Scan QR</th>
                <th style="width: 18%;">Status</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = mysqli_fetch_assoc($q_mhs_absen)) { 
                $waktu = $row['waktu_absen'] ? date('H:i:s', strtotime($row['waktu_absen'])) . ' WITA' : '-';
                $status_text = $row['status'] ? ucfirst($row['status']) : 'Belum Absen';
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td class="text-center"><?php echo htmlspecialchars($row['nim']); ?></td>
                <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                <td class="text-center" style="font-family: monospace;"><?php echo $waktu; ?></td>
                <td class="text-center" style="font-weight: bold;"><?php echo $status_text; ?></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>