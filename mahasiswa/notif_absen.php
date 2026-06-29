<?php
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'mahasiswa') {
    header('Location: ../index.php');
    exit();
}

$status = isset($_GET['status']) ? $_GET['status'] : 'error';
$message = isset($_GET['msg']) ? urldecode($_GET['msg']) : 'Terjadi kesalahan saat melakukan absensi.';

if($status == 'success') {
    $card_title = 'ABSENSI BERHASIL';
    $icon_class = 'bi-check-circle text-success';
    $theme_class = 'border-success';
    $btn_action = '<a href="dashboard.php" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow">Selesai</a>';
} elseif ($status == 'warning') {
    $card_title = 'PEMBERITAHUAN';
    $icon_class = 'bi-exclamation-triangle text-warning';
    $theme_class = 'border-warning';
    $btn_action = '<a href="dashboard.php" class="btn btn-warning text-dark w-100 py-3 rounded-pill fw-bold shadow">Kembali ke Home</a>';
} else {
    $card_title = 'ABSENSI GAGAL';
    $icon_class = 'bi-x-circle text-danger';
    $theme_class = 'border-danger';
    $btn_action = '<a href="dashboard.php?active=scan" class="btn btn-outline-danger w-100 py-3 rounded-pill fw-bold shadow-sm">Coba Scan Lagi</a>';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Presensi - Smart Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root { --glass-bg: rgba(255, 255, 255, 0.95); }
        body { font-family: 'Outfit', sans-serif; background: #f3f4f6; background-image: radial-gradient(at 100% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 0% 0%, hsla(339,49%,30%,1) 0, transparent 50%); background-attachment: fixed; min-height: 100vh; color: #1f2937; }
        .glass-card { background: var(--glass-bg); backdrop-filter: blur(10px); border-radius: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.2); border-width: 3px; }
        .icon-box i { font-size: 5rem; animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block; }
        @keyframes popIn { from { transform: scale(0); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

    <div class="container px-4">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card glass-card text-center p-4 p-md-5 <?php echo $theme_class; ?>">
                    
                    <div class="icon-box mb-4">
                        <i class="bi <?php echo $icon_class; ?>"></i>
                    </div>

                    <h3 class="fw-bold mb-2"><?php echo $card_title; ?></h3>
                    <p class="text-muted mb-4 px-2"><?php echo htmlspecialchars($message); ?></p>
                    
                    <div class="mt-2">
                        <?php echo $btn_action; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>