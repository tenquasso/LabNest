<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $file_id = $_GET['id'];
    
    // get file info from database
    $stmt = $pdo->prepare("SELECT * FROM files WHERE id = ?");
    $stmt->execute([$file_id]);
    $file = $stmt->fetch();

    if ($file && file_exists($file['file_path'])) {
        // log download activity
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, file_id, details, ip_address) VALUES (?, 'download', ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $file_id,
            "File downloaded: " . $file['original_name'],
            $_SERVER['REMOTE_ADDR']
        ]);

        // set header for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($file['file_path']));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: public');
        
        // output file
        readfile($file['file_path']);
        exit();
    }
}

// if file not found, redirect to home
header('Location: index.php');
exit(); 