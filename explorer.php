<?php
require_once 'config.php';
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// get current directory from GET, default to uploads
$base = 'uploads';
$dir = isset($_GET['dir']) ? $_GET['dir'] : $base;
if (!is_dir($dir) || strpos(realpath($dir), realpath($base)) !== 0) {
    $dir = $base;
}

// build breadcrumb
function renderBreadcrumb($dir, $base) {
    $parts = explode('/', trim(str_replace($base, '', $dir), '/'));
    $breadcrumb = '<nav class="breadcrumb">';
    $path = $base;
    $breadcrumb .= '<a href="?dir=' . $base . '">' . htmlspecialchars($base) . '</a>';
    foreach ($parts as $part) {
        if ($part === '') continue;
        $path .= '/' . $part;
        $breadcrumb .= ' / <a href="?dir=' . $path . '">' . htmlspecialchars($part) . '</a>';
    }
    $breadcrumb .= '</nav>';
    return $breadcrumb;
}

// get folder & file list
function getFolderFileList($dir) {
    $folders = $files = [];
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            $folders[] = $item;
        } else {
            $files[] = $item;
        }
    }
    return [$folders, $files];
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorer - LabNest</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        .breadcrumb {
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
            color: var(--text-color);
            opacity: 0.85;
            word-break: break-all;
        }
        .breadcrumb a {
            color: #38bdf8;
            text-decoration: underline;
        }
        .explorer-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.2rem;
        }
        .explorer-item {
            background: var(--glass-bg);
            border-radius: 12px;
            padding: 1.2rem 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 8px 0 rgba(0,0,0,0.06);
            transition: box-shadow 0.2s;
        }
        .explorer-item:hover {
            box-shadow: 0 4px 16px 0 rgba(56,189,248,0.12);
        }
        .explorer-icon {
            font-size: 2rem;
            min-width: 2.5rem;
        }
        .explorer-name {
            font-weight: 600;
            color: var(--text-color);
            flex: 1;
            word-break: break-all;
        }
        .explorer-size {
            color: #a3e635;
            font-size: 0.95rem;
            margin-left: 0.5rem;
        }
        .explorer-actions {
            display: flex;
            gap: 0.5rem;
        }
        .explorer-actions a, .explorer-actions button {
            background: none;
            border: none;
            color: #38bdf8;
            font-size: 1.1rem;
            cursor: pointer;
        }
        .explorer-actions .delete {
            color: #ef4444;
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
            .explorer-grid {
                grid-template-columns: 1fr;
                gap: 0.7rem;
            }
            .explorer-item {
                padding: 0.7rem 0.5rem;
                font-size: 1rem;
            }
            .explorer-icon {
                font-size: 1.3rem;
                min-width: 1.7rem;
            }
            .explorer-name {
                font-size: 1.05rem;
            }
            .explorer-size {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem 0.1rem;
            }
            .explorer-item {
                font-size: 0.93rem;
                padding: 0.5rem 0.2rem;
            }
            .explorer-name {
                font-size: 0.98rem;
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
        <h5 class="font-bold" style="margin-bottom:1.5rem;">File Explorer</h5>
        <?php echo renderBreadcrumb($dir, $base); ?>
        <div class="explorer-grid">
            <?php list($folders, $files) = getFolderFileList($dir); ?>
            <?php foreach ($folders as $folder): ?>
                <div class="explorer-item">
                    <span class="explorer-icon"><i class="fas fa-folder"></i></span>
                    <a class="explorer-name" href="?dir=<?php echo urlencode($dir . '/' . $folder); ?>"><?php echo htmlspecialchars($folder); ?></a>
                </div>
            <?php endforeach; ?>
            <?php foreach ($files as $file): ?>
                <div class="explorer-item">
                    <span class="explorer-icon"><i class="fas fa-file"></i></span>
                    <span class="explorer-name"><?php echo htmlspecialchars($file); ?></span>
                    <span class="explorer-size"><?php echo formatFileSize(filesize($dir . '/' . $file)); ?></span>
                    <div class="explorer-actions">
                        <a href="download.php?path=<?php echo urlencode(str_replace($base . '/', '', $dir . '/' . $file)); ?>" title="Download"><i class="fas fa-download"></i></a>
                        <!-- tombol delete bisa ditambah di sini jika ingin -->
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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