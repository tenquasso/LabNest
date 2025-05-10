<?php
require_once 'config.php';

if (isLoggedIn()) {
    // log logout activity
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, 'logout', 'User logged out', ?)");
    $stmt->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);

    // destroy all session data
    session_destroy();
}

// redirect to login page
header('Location: login.php');
exit(); 