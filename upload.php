<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// create upload folder structure
$upload_dir = createUploadStructure();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ((isset($_FILES['files']) || isset($_FILES['file'])) && isset($_POST['category'])) {
        if (isset($_FILES['file'])) {
            $files = [
                'name' => [$_FILES['file']['name']],
                'size' => [$_FILES['file']['size']],
                'tmp_name' => [$_FILES['file']['tmp_name']],
                'type' => [$_FILES['file']['type']],
                'error' => [$_FILES['file']['error']]
            ];
        } else {
        $files = $_FILES['files'];
        }
        $category = $_POST['category'];
        $success_count = 0;
        $error_count = 0;
        $errors = [];

        // validate category
        if (!array_key_exists($category, FILE_CATEGORIES)) {
            $response['message'] = "Kategori tidak valid.";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit();
        }

        // make sure category folder exists
        $category_dir = $upload_dir . $category;
        if (!file_exists($category_dir)) {
            if (!mkdir($category_dir, 0777, true)) {
                $response['message'] = "Gagal membuat folder kategori. Silakan hubungi administrator.";
                header('Content-Type: application/json');
                echo json_encode($response);
                exit();
            }
        }

        // loop through each uploaded file
        for ($i = 0; $i < count($files['name']); $i++) {
            $file_name = $files['name'][$i];
            $file_size = $files['size'][$i];
            $file_tmp = $files['tmp_name'][$i];
            $file_type = $files['type'][$i];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $file_error = $files['error'][$i];

            // check upload error
            if ($file_error !== UPLOAD_ERR_OK) {
                $errors[] = "File '$file_name' gagal diupload: " . getUploadErrorMessage($file_error);
                $error_count++;
                continue;
            }

            // use original file name
            $new_file_name = $file_name;
            $upload_path = $category_dir . '/' . $new_file_name;

            // if file with the same name exists, add timestamp
            if (file_exists($upload_path)) {
                $file_name_without_ext = pathinfo($file_name, PATHINFO_FILENAME);
                $new_file_name = $file_name_without_ext . '_' . time() . '.' . $file_ext;
                $upload_path = $category_dir . '/' . $new_file_name;
            }

            if (move_uploaded_file($file_tmp, $upload_path)) {
                try {
                    // save file info to database
                $stmt = $pdo->prepare("INSERT INTO files (filename, original_name, file_path, file_size, file_type, uploaded_by, category) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $new_file_name,
                    $file_name,
                    $upload_path,
                    $file_size,
                    $file_type,
                    $_SESSION['user_id'],
                    $category
                ]);
                $file_id = $pdo->lastInsertId();

                    // log activity
                $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, file_id, details, ip_address) VALUES (?, 'upload', ?, ?, ?)");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $file_id,
                    "File uploaded: " . $file_name . " (Kategori: " . FILE_CATEGORIES[$category] . ")",
                    $_SERVER['REMOTE_ADDR']
                ]);

                $success_count++;
                } catch (PDOException $e) {
                    // if failed to save to database, delete uploaded file
                    unlink($upload_path);
                    $errors[] = "File '$file_name' gagal disimpan ke database.";
                    $error_count++;
                }
            } else {
                $errors[] = "File '$file_name' gagal dipindahkan ke server.";
                $error_count++;
            }
        }

        if ($success_count > 0) {
            $response['success'] = true;
            $response['message'] = "$success_count file berhasil diupload.";
            if ($error_count > 0) {
                $response['message'] .= " $error_count file gagal diupload.";
                $response['errors'] = $errors;
            }
        } else {
            $response['message'] = "Gagal mengupload file.";
            $response['errors'] = $errors;
        }
    } else {
        $response['message'] = "Tidak ada file yang dipilih atau kategori tidak dipilih.";
    }
header('Content-Type: application/json');
echo json_encode($response);
    exit();
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return 'Ukuran file melebihi batas yang diizinkan oleh server.';
        case UPLOAD_ERR_FORM_SIZE:
            return 'Ukuran file melebihi batas yang diizinkan oleh form.';
        case UPLOAD_ERR_PARTIAL:
            return 'File hanya terupload sebagian.';
        case UPLOAD_ERR_NO_FILE:
            return 'Tidak ada file yang diupload.';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Folder temporary tidak ditemukan.';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Gagal menulis file ke disk.';
        case UPLOAD_ERR_EXTENSION:
            return 'Upload dihentikan oleh ekstensi PHP.';
        default:
            return 'Unknown upload error.';
    }
}

function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// handle upload error for non-ajax (get) request
if (isset($_GET['error'])) {
    $errorMsg = htmlspecialchars($_GET['error']);
    echo '<div style="max-width:500px;margin:2rem auto 0;" class="alert alert-danger">' . $errorMsg . '</div>';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File - LabNest</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        html, body { height: 100%; margin: 0; overflow-x: hidden; }
        body { min-height: 100vh; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); }
        .navbar {
            padding: 1rem 2rem;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 1000;
            background: var(--glass-bg);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }
        .navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        .navbar-brand {
            font-size: 1.5rem;
            text-decoration: none;
            color: var(--text-color);
            font-weight: bold;
            letter-spacing: 1px;
        }
        .hamburger-menu {
            display: none;
            cursor: pointer;
            font-size: 1.5rem;
            color: var(--text-color);
        }
        .navbar-nav {
            display: flex;
            gap: 1.5rem;
            align-items: center;
        }
        .nav-item {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color);
            font-weight: 500;
        }
        .main-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--glass-bg);
            border-radius: 16px;
            box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
        }
        .upload-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .drop-zone {
            border: 2px dashed var(--glass-border);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(15, 23, 42, 0.3);
            margin-bottom: 1rem;
        }
        .drop-zone:hover, .drop-zone.dragover {
            background: rgba(15, 23, 42, 0.5);
            border-color: var(--primary-color);
        }
        .drop-zone-content {
            color: var(--text-color);
        }
        .drop-zone-content i {
            margin-bottom: 1rem;
            color: var(--primary-color);
            font-size: 2.5rem;
        }
        .file-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            background: rgba(15, 23, 42, 0.3);
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }
        .file-item i {
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        .file-item .remove-file {
            margin-left: auto;
            color: #ef4444;
            cursor: pointer;
            padding: 0.25rem;
        }
        .file-item .remove-file:hover {
            color: #dc2626;
        }
        .form-group { margin-bottom: 1.2rem; }
        .btn-primary {
            background: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: background 0.2s;
        }
        .btn-primary:hover {
            background: var(--secondary-color);
        }
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }
            .hamburger-menu {
                display: block;
            }
            .navbar-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--glass-bg);
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }
            .navbar-nav.active {
                display: flex;
            }
            .main-content {
                margin: 0 0.5rem;
                padding: 1rem;
            }
            .upload-title {
                font-size: 1.2rem;
                margin-bottom: 1rem;
            }
            .form-group label,
            .form-select,
            .form-control {
                font-size: 1rem;
            }
            .btn-primary {
                width: 100%;
                font-size: 1.08rem;
                padding: 1rem 0;
            }
            .drop-zone {
                padding: 1.2rem;
                font-size: 0.98rem;
            }
            .file-item {
                font-size: 0.98rem;
                padding: 0.5rem;
            }
        }
        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem 0.1rem;
            }
            .upload-title {
                font-size: 1.05rem;
            }
            .form-group label,
            .form-select,
            .form-control {
                font-size: 0.95rem;
            }
            .btn-primary {
                font-size: 1rem;
                padding: 0.8rem 0;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a class="navbar-brand" href="home.php"><i class="fas fa-folder-open"></i> LabNest</a>
            <div class="hamburger-menu" id="hamburgerMenu">
                <i class="fas fa-bars"></i>
            </div>
            <div class="navbar-nav" id="navbarNav">
                <span class="nav-item"> <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['username']); ?> </span>
                <a class="nav-item" href="explorer.php">
                    <i class="fas fa-sitemap"></i> Explorer
                </a>
                <a class="nav-item" href="upload.php">
                    <i class="fas fa-upload"></i> Upload
                </a>
                <a class="nav-item" href="activity_logs.php">
                    <i class="fas fa-history"></i> Log Aktivitas
                </a>
                <a class="nav-item" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    <div class="main-content">
        <div class="upload-title">Upload File</div>
        <div id="uploadAlert" style="display:none;margin-bottom:1rem;"></div>
        <form action="upload.php" method="post" enctype="multipart/form-data" id="uploadForm">
            <div class="form-group">
                <div class="drop-zone" id="dropZone">
                    <div class="drop-zone-content">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <p>Drag & Drop file di sini atau klik untuk memilih file</p>
                    </div>
                    <input type="file" class="form-control" name="files[]" multiple required id="fileInput" style="display: none;">
                </div>
                <div id="fileList" class="mt-3"></div>
                <div id="progressList" class="mt-3"></div>
            </div>
            <div class="form-group">
                <label>Kategori</label>
                <select class="form-select" name="category" required id="categorySelect">
                    <option value="">Pilih Kategori</option>
                    <?php if(defined('FILE_CATEGORIES')) foreach(FILE_CATEGORIES as $key => $value): ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // drag and drop functionality
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        const progressList = document.getElementById('progressList');
        let selectedFiles = [];

        dropZone.addEventListener('click', () => fileInput.click());
        dropZone.addEventListener('dragover', (e) => { e.preventDefault(); dropZone.classList.add('dragover'); });
        dropZone.addEventListener('dragleave', () => { dropZone.classList.remove('dragover'); });
        dropZone.addEventListener('drop', (e) => { e.preventDefault(); dropZone.classList.remove('dragover'); handleFiles(e.dataTransfer.files); });
        fileInput.addEventListener('change', (e) => { handleFiles(e.target.files); });

        function handleFiles(files) {
            selectedFiles = Array.from(files);
            fileList.innerHTML = '';
            progressList.innerHTML = '';
            selectedFiles.forEach((file, idx) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <i class="fas fa-file"></i>
                    <span>${file.name}</span>
                    <i class="fas fa-times remove-file" data-idx="${idx}"></i>
                `;
                fileList.appendChild(fileItem);
            });
        }

        fileList.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-file')) {
                const idx = parseInt(e.target.getAttribute('data-idx'));
                selectedFiles.splice(idx, 1);
                handleFiles(selectedFiles);
            }
        });

        // upload with progress bar (per file, bukan sekaligus)
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const category = document.getElementById('categorySelect').value;
            if (!category) {
                alert('Pilih kategori terlebih dahulu!');
                return;
            }
            if (selectedFiles.length === 0) {
                alert('Pilih file yang akan diupload!');
                return;
            }
            progressList.innerHTML = '';
            const alertBox = document.getElementById('uploadAlert');
            alertBox.style.display = 'none';
            alertBox.innerHTML = '';
            let completed = 0;
            let success = 0;
            let failed = 0;
            function uploadNext(idx) {
                if (idx >= selectedFiles.length) {
                    // selesai semua
                    alertBox.style.display = 'block';
                    if (success > 0 && failed === 0) {
                        alertBox.className = 'alert alert-success';
                        alertBox.innerHTML = `${success} file berhasil diupload.`;
                    } else if (success > 0 && failed > 0) {
                        alertBox.className = 'alert alert-warning';
                        alertBox.innerHTML = `${success} file berhasil, ${failed} gagal.`;
                    } else {
                        alertBox.className = 'alert alert-danger';
                        alertBox.innerHTML = 'Semua file gagal diupload.';
                    }
                    fileList.innerHTML = '';
                    selectedFiles = [];
                    return;
                }
                const file = selectedFiles[idx];
                const formData = new FormData();
                formData.append('file', file);
                formData.append('category', category);
                // progress bar
                const progressWrapper = document.createElement('div');
                progressWrapper.style.marginBottom = '0.5rem';
                progressWrapper.innerHTML = `
                    <div style="font-size:0.98rem;margin-bottom:0.2rem;">${file.name}</div>
                    <div class="progress-bar-bg" style="background:#222;height:10px;border-radius:6px;overflow:hidden;">
                        <div class="progress-bar" style="background:#38bdf8;height:10px;width:0%;transition:width 0.2s;"></div>
                    </div>
                    <div class="progress-status" style="font-size:0.93rem;opacity:0.7;margin-top:0.1rem;"></div>
                `;
                progressList.appendChild(progressWrapper);
                const progressBar = progressWrapper.querySelector('.progress-bar');
                const progressStatus = progressWrapper.querySelector('.progress-status');
                // upload via xhr
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'upload.php', true);
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        const percent = Math.round((e.loaded / e.total) * 100);
                        progressBar.style.width = percent + '%';
                        progressStatus.textContent = percent + '%';
                    }
                };
                xhr.onload = function() {
                    completed++;
                    if (xhr.status === 200) {
                        try {
                            const result = JSON.parse(xhr.responseText);
                            if (result.success) {
                                progressBar.style.background = '#22c55e';
                                progressStatus.textContent = 'Sukses';
                                success++;
                            } else {
                                progressBar.style.background = '#ef4444';
                                progressStatus.textContent = 'Gagal: ' + (result.message || 'Error');
                                failed++;
                            }
                        } catch {
                            progressBar.style.background = '#ef4444';
                            progressStatus.textContent = 'Gagal: Error parsing response';
                            failed++;
                        }
                    } else {
                        progressBar.style.background = '#ef4444';
                        progressStatus.textContent = 'Gagal: ' + xhr.statusText;
                        failed++;
                    }
                    uploadNext(idx + 1);
                };
                xhr.onerror = function() {
                    completed++;
                    progressBar.style.background = '#ef4444';
                    progressStatus.textContent = 'Gagal: Koneksi error';
                    failed++;
                    uploadNext(idx + 1);
                };
                xhr.send(formData);
            }
            uploadNext(0);
        });

        // Hamburger menu functionality
        const hamburgerMenu = document.getElementById('hamburgerMenu');
        const navbarNav = document.getElementById('navbarNav');
        
        hamburgerMenu.addEventListener('click', () => {
            navbarNav.classList.toggle('active');
        });

        // Close menu when clicking outside
        document.addEventListener('click', (e) => {
            if (!hamburgerMenu.contains(e.target) && !navbarNav.contains(e.target)) {
                navbarNav.classList.remove('active');
            }
        });
    </script>
</body>
</html> 