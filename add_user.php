<?php
// Pastikan file hanya bisa dijalankan dari CLI (Command Line Interface)
if (php_sapi_name() !== 'cli') {
    // Jika diakses dari browser, langsung keluar dan tampilkan pesan
    exit("Akses ditolak. File ini hanya bisa dijalankan dari CLI.\n");
}

// Load konfigurasi database dari file config.php
require_once 'config.php';

// Fungsi sederhana untuk membaca input dari terminal
function input($prompt) {
    echo $prompt;
    return trim(fgets(STDIN));
}

// Minta input username, password, dan role dari user
$username = input("Masukkan username: ");
$password = input("Masukkan password: ");
$role = input("Masukkan role (admin/user): ");

// Hash password sebelum disimpan ke database
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Simpan data user baru ke database menggunakan prepared statement
$stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
$stmt->execute([$username, $hashed_password, $role]);

echo "User berhasil ditambahkan.\n"; 