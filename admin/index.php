<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(charge),0) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('Pending','In progress')")->fetchColumn();
$pendingDeposits = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn();
$totalDeposits = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM deposits WHERE status='approved'")->fetchColumn();

$pageTitle = 'Admin Dashboard — ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $pageTitle ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>
<nav class="navbar" style="background:rgba(7,7,15,.95)">
    <div class="nav-inner">
        <a href="<?= SITE_URL ?>/admin/index.php" class="logo">
            <div class="logo-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)">🛡️</div>
            <span>Nima <b>SMM Admin</b></span>
        </a>
        <div class="nav-links">
            <a href="<?= SITE_URL ?>/admin/index.php" style="color:#f59e0b">Dashboard</a>
            <a href="<?= SITE_URL ?>/admin/deposits.php">💳 Deposits<?php if ($pendingDeposits > 0): ?> <span style="background:#ef4444;color:#fff;padding:2px 8px;border-radius:10px;font-size:11px"><?= $pendingDeposits ?></span><?php endif; ?></a>
            <a href="<?= SITE_URL ?>/admin/users.php">👥 Users</a>
            <a href="<?= SITE_URL ?>/admin/orders.php">📦 Orders</a>
            <a href="<?= SITE_URL ?>/admin/services.php">🛒 Services</a>
            <a href="<?= SITE_URL ?>/admin/settings.php">⚙️ Settings</a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="btn-sm" style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;border:none">🚪 Exit Admin</a>
        </div>
    </div>
</nav>
<main class="container">
<h2 style="margin:10px 0 20px;font-size:26px">🛡️ Admin Dashboard</h2>

<div class="stats">
    <div class="stat"><div class="label">Total Users</div><div class="value grad"><?= $totalUsers ?></div></div>
    <div class="stat"><div class="label">Total Orders</div><div class="value grad"><?= $totalOrders ?></div></div>
    <div class="stat"><div class="label">Order Revenue</div><div class="value grad">$<?= number_format($totalRevenue, 2) ?></div></div>
    <div class="stat"><div class="label">Pending Orders</div><div class="value"><?= $pendingOrders ?></div></div>
</div>

<div class="stats">
    <div class="stat">
        <div class="label">⏳ Pending Deposits</div>
        <div class="value" style="color:<?= $pendingDeposits > 0 ? '#fbbf24' : 'var(--text)' ?>"><?= $pendingDeposits ?></div>
        <?php if ($pendingDeposits > 0): ?>
            <a href="<?= SITE_URL ?>/admin/deposits.php" class="btn-sm" style="margin-top:8px;display:inline-block">Review Now →</a>
        <?php endif; ?>
    </div>
    <div class="stat"><div class="label">💰 Total Deposits</div><div class="value grad">$<?= number_format($totalDeposits, 2) ?></div></div>
</div>

<div class="card">
    <h2>⚡ Quick Actions</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
        <a href="<?= SITE_URL ?>/admin/deposits.php" class="btn-sm">💳 Deposits</a>
        <a href="<?= SITE_URL ?>/admin/users.php" class="btn-sm">👥 Manage Users</a>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="btn-sm">📦 Manage Orders</a>
        <a href="<?= SITE_URL ?>/admin/services.php" class="btn-sm">🛒 Manage Services</a>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="btn-sm">⚙️ Settings</a>
    </div>
</div>
</main>
<footer class="footer">
    <div class="container"><p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> — Admin Panel</p></div>
</footer>
</body>
</html>
