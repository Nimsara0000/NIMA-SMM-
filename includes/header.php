<?php 
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php'; 
$user = currentUser(); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?? SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar">
    <div class="nav-inner">
        <a href="<?= SITE_URL ?>/dashboard.php" class="logo">
            <div class="logo-icon">N</div>
            <span>Nima <b>SMM</b></span>
        </a>
        <div class="nav-links">
            <?php if (isLoggedIn()): ?>
                <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a>
                <a href="<?= SITE_URL ?>/new-order.php">New Order</a>
                <a href="<?= SITE_URL ?>/services.php">Services</a>
                <a href="<?= SITE_URL ?>/orders.php">Orders</a>
                <a href="<?= SITE_URL ?>/add-funds.php">Add Funds</a>
                <?php if (isAdmin()): ?><a href="<?= SITE_URL ?>/admin/index.php">Admin</a><?php endif; ?>
                <div class="balance-chip">
                    <span class="dot"></span>
                    <b>$<?= number_format($user['balance'] ?? 0, 2) ?></b>
                </div>
                <a href="<?= SITE_URL ?>/logout.php" class="btn-sm">Logout</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>/login.php">Login</a>
                <a href="<?= SITE_URL ?>/register.php" class="btn-primary">Sign Up</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container">
