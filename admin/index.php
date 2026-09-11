<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalRevenue = $pdo->query("SELECT COALESCE(SUM(charge),0) FROM orders")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status IN ('Pending','In progress')")->fetchColumn();

$pageTitle = 'Admin Dashboard — ' . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>
<h2 style="margin:10px 0 20px;font-size:26px">Admin Dashboard</h2>

<div class="stats">
    <div class="stat"><div class="label">Total Users</div><div class="value grad"><?= $totalUsers ?></div></div>
    <div class="stat"><div class="label">Total Orders</div><div class="value grad"><?= $totalOrders ?></div></div>
    <div class="stat"><div class="label">Revenue</div><div class="value grad">$<?= number_format($totalRevenue, 2) ?></div></div>
    <div class="stat"><div class="label">Pending</div><div class="value"><?= $pendingOrders ?></div></div>
</div>

<div class="card">
    <h2>Quick Links</h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px">
        <a href="users.php" class="btn-sm">👥 Manage Users</a>
        <a href="orders.php" class="btn-sm">📦 Manage Orders</a>
        <a href="services.php" class="btn-sm">🛒 Manage Services</a>
        <a href="settings.php" class="btn-sm">⚙️ Settings</a>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
