<?php
require_once 'config.php';

$username = '123';
$password = '123'; // change to your desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
$stmt->execute([$username, $hashed_password]);

echo "User berhasil dibuat!";
echo "Username: " . $username . "\n";
echo "Password: " . $password . "\n";
?>