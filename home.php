<?php
require_once 'config.php';

// redirect to login page if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// function to render folder & file explorer recursively
function renderExplorer($dir, $base = 'uploads') {
    $html = '<ul class="explorer-list">';
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . '/' . $item;
        $relPath = substr($path, strlen($base) + 1);
        if (is_dir($path)) {
            $html .= '<li class="folder"><i class="fas fa-folder"></i> ' . htmlspecialchars($item);
            $html .= renderExplorer($path, $base);
            $html .= '</li>';
        } else {
            $html .= '<li class="file"><i class="fas fa-file"></i> <a href="download.php?path=' . urlencode($relPath) . '" target="_blank">' . htmlspecialchars($item) . '</a></li>';
        }
    }
    $html .= '</ul>';
    return $html;
}

// function to format file size
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
    <title>LabNest</title>
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
        .nav-item.btn-upload {
            background: var(--primary-color);
            color: #fff;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: background 0.2s;
            cursor: pointer;
        }
        .nav-item.btn-upload:hover {
            background: var(--secondary-color);
        }
        .main-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background: var(--glass-bg);
            border-radius: 16px;
            box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
        }
        .about-title {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 1rem;
        }
        .about-desc {
            color: var(--text-color);
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
            opacity: 0.85;
        }
        .about-list {
            color: var(--text-color);
            opacity: 0.8;
            margin-bottom: 1.5rem;
        }
        .about-list li {
            margin-bottom: 0.5rem;
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
                margin: 0 1rem;
                padding: 1.5rem;
            }
        }
        @media (max-width: 480px) {
            .main-content {
                padding: 0.5rem 0.1rem;
            }
            .about-title {
                font-size: 1.05rem;
            }
            .about-desc, .about-list {
                font-size: 0.93rem;
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
        <div class="about-title" style="text-align:center;">Selamat Datang di <span style="color:#38bdf8">LabNest</span></div>
        <div class="about-desc" style="font-size:1.15rem; text-align:center;">
            <p><b>LabNest</b> adalah aplikasi web modern untuk mengelola, mengunggah, dan berbagi file secara aman di lingkungan sekolah.</p>
        <div style="margin-top:2.5rem; text-align:center; color:var(--text-color); opacity:0.7; font-size:0.98rem;">
            &copy; 2025 LabNest - SOFTWARE ENGINEERING
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