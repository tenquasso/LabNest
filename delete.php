<?php
require_once 'config.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

$response = ['success' => false, 'message' => ''];

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];
    
    // get file info from database
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if ($file) {
        // delete physical file
        if (file_exists($file['file_path'])) {
            unlink($file['file_path']);
        }

        // delete record from database
        $stmt = $pdo->prepare("DELETE FROM files WHERE id = ?");
        $stmt->execute([$file_id]);

        // log delete activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'delete', ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            "File deleted: " . $file['original_name'],
            $_SERVER['REMOTE_ADDR']
        ]);

        $response['success'] = true;
        $response['message'] = "File berhasil dihapus.";
    } else {
        $response['message'] = "File tidak ditemukan.";
    }
} else {
    $response['message'] = "ID file tidak valid.";
}

header('Location: index.php');
exit(); 