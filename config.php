<?php
// database configuration
define('DB_HOST', 'localhost');
// Ganti dengan user dan password database Anda sebelum digunakan di produksi!
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'file_manager');

// application configuration
define('BASE_UPLOAD_DIR', 'uploads/');
define('UPLOAD_DIR', BASE_UPLOAD_DIR . date('Y/m/'));
// define('MAX_FILE_SIZE', 50 * 1024 * 1024); // 50MB
// define('ALLOWED_EXTENSIONS', ['zip', 'rar', 'exe', 'msi', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']);

// allowed file categories
define('FILE_CATEGORIES', [
    'software' => 'Software & Tools',
    'config' => 'File Konfigurasi',
    'document' => 'Dokumen',
    'other' => 'Lainnya'
]);

// initialize database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

// start session
session_start();

// check login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// create upload folder structure
function createUploadStructure() {
    $base_dir = BASE_UPLOAD_DIR;
    $year_dir = $base_dir . date('Y') . '/';
    $month_dir = $year_dir . date('m') . '/';
    
    // create main folder if not exists
    if (!file_exists($base_dir)) {
        mkdir($base_dir, 0777, true);
    }
    
    // create year folder if not exists
    if (!file_exists($year_dir)) {
        mkdir($year_dir, 0777, true);
    }
    
    // create month folder if not exists
    if (!file_exists($month_dir)) {
        mkdir($month_dir, 0777, true);
    }
    
    // create category folders
    foreach (FILE_CATEGORIES as $category => $name) {
        $category_dir = $month_dir . $category . '/';
        if (!file_exists($category_dir)) {
            mkdir($category_dir, 0777, true);
        }
    }
    
    return $month_dir;
}
?> 