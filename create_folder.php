<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['folder_name']) && !empty($_POST['folder_name'])) {
        $folder_name = trim($_POST['folder_name']);
        $folder_name = preg_replace('/[^a-zA-Z0-9-_]/', '_', $folder_name); // sanitize folder name
        $folder_path = UPLOAD_DIR . $folder_name;

        if (!file_exists($folder_path)) {
            if (mkdir($folder_path, 0777, true)) {
                // log create folder activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'create_folder', ?, ?)");
                $stmt->execute([
                    $_SESSION['user_id'],
                    "Folder created: " . $folder_name,
                    $_SERVER['REMOTE_ADDR']
                ]);

                $response['success'] = true;
                $response['message'] = "Folder berhasil dibuat.";
            } else {
                $response['message'] = "Gagal membuat folder.";
            }
        } else {
            $response['message'] = "Folder sudah ada.";
        }
    } else {
        $response['message'] = "Nama folder tidak boleh kosong.";
    }
}

header('Location: index.php');
exit(); 