<?php
require_once 'config.php';

// redirect to login page if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// redirect to home page
header('Location: home.php');
exit();
?> 