<?php
require_once 'config.php';

// Hanya admin yang boleh menghapus file
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

// Ambil parameter file dan dir dari GET
if (isset($_GET['file']) && isset($_GET['dir'])) {
    $file = $_GET['file'];
    $dir = $_GET['dir'];
    $base = 'uploads';

    // Validasi path agar tidak keluar dari uploads
    $realBase = realpath($base);
    $realDir = realpath($dir);
    if ($realDir === false || strpos($realDir, $realBase) !== 0) {
        // Path tidak valid
        header('Location: explorer.php?dir=' . urlencode($base));
        exit();
    }

    $filePath = $realDir . DIRECTORY_SEPARATOR . $file;
    // Cek file benar-benar ada dan di dalam uploads
    if (file_exists($filePath) && strpos(realpath($filePath), $realBase) === 0) {
        unlink($filePath);
        // (Opsional) log aktivitas hapus file
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'delete', ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            "File deleted: " . $file,
            $_SERVER['REMOTE_ADDR']
        ]);
    }
    // Redirect kembali ke explorer dengan direktori yang sama
    header('Location: explorer.php?dir=' . urlencode($dir));
    exit();
} else {
    // Jika parameter tidak lengkap, kembali ke explorer root
    header('Location: explorer.php?dir=uploads');
    exit();
} 