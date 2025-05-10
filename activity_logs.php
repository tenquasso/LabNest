<?php
require_once 'config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// get activity log data
$stmt = $pdo->prepare("SELECT l.*, u.username FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC");
    $stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - LabNest</title>
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
        .log-title {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--text-color);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .log-list {
            background: var(--glass-bg);
            border-radius: 16px;
            box-shadow: 0 4px 24px 0 rgba(0,0,0,0.08);
            padding: 1.5rem;
            overflow-x: auto;
        }
        .log-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        .log-table th {
            text-align: left;
            padding: 1rem;
            font-weight: 600;
            color: var(--text-color);
            border-bottom: 1px solid var(--glass-border);
        }
        .log-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-color);
            font-size: 0.98rem;
        }
        .log-table tr:hover {
            background: rgba(15, 23, 42, 0.3);
        }
        .badge-action {
            display: inline-block;
            padding: 0.3em 0.8em;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 600;
            color: #fff;
        }
        .badge-upload { background: #38bdf8; }
        .badge-download { background: #a3e635; }
        .badge-delete { background: #ef4444; }
        .badge-login { background: #6366f1; }
        .badge-other { background: #64748b; }
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
            .log-list {
                padding: 0.5rem;
            }
            .log-table {
                display: none;
            }
            .log-cards {
                display: block;
            }
        }
        @media (min-width: 769px) {
            .log-cards {
                display: none;
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
        <div class="log-title">Log Aktivitas</div>
        <div class="log-list">
            <table class="log-table">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                        <th>User</th>
                                        <th>Aksi</th>
                                        <th>Detail</th>
                        <th>IP</th>
                                    </tr>
                                </thead>
                                <tbody>
                    <?php foreach($logs as $log): ?>
                                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($log['username']); ?></td>
                                        <td>
                                            <?php
                            $action = strtolower($log['action']);
                            $badgeClass = 'badge-other';
                            if ($action === 'upload') $badgeClass = 'badge-upload';
                            elseif ($action === 'download') $badgeClass = 'badge-download';
                            elseif ($action === 'delete') $badgeClass = 'badge-delete';
                            elseif ($action === 'login') $badgeClass = 'badge-login';
                            ?>
                            <span class="badge-action <?php echo $badgeClass; ?>"><?php echo ucfirst($action); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($log['details']); ?></td>
                                        <td><?php echo htmlspecialchars($log['ip_address']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
            <div class="log-cards">
                <?php foreach($logs as $log): ?>
                <?php
                    $action = strtolower($log['action']);
                    $badgeClass = 'badge-other';
                    if ($action === 'upload') $badgeClass = 'badge-upload';
                    elseif ($action === 'download') $badgeClass = 'badge-download';
                    elseif ($action === 'delete') $badgeClass = 'badge-delete';
                    elseif ($action === 'login') $badgeClass = 'badge-login';
                ?>
                <div style="background:var(--glass-bg);border-radius:12px;padding:1rem;margin-bottom:1rem;box-shadow:0 2px 8px 0 rgba(0,0,0,0.06);">
                    <div style="font-size:1.05rem;font-weight:600;color:var(--text-color);margin-bottom:0.5rem;">
                        <span class="badge-action <?php echo $badgeClass; ?>"><?php echo ucfirst($action); ?></span>
                        <span style="float:right;opacity:0.7;font-size:0.95rem;"><?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?></span>
                    </div>
                    <div style="margin-bottom:0.3rem;"><b>User:</b> <?php echo htmlspecialchars($log['username']); ?></div>
                    <div style="margin-bottom:0.3rem;"><b>Detail:</b> <?php echo htmlspecialchars($log['details']); ?></div>
                    <div style="opacity:0.7;font-size:0.93rem;"><b>IP:</b> <?php echo htmlspecialchars($log['ip_address']); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
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