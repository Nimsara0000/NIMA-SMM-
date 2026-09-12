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
include __DIR__ . '/../includes/header.php';
?>
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
        <a href="<?= SITE_URL ?>/admin/deposits.php" class="btn-sm">💳 Deposits <?= $pendingDeposits > 0 ? "({$pendingDeposits})" : '' ?></a>
        <a href="<?= SITE_URL ?>/admin/users.php" class="btn-sm">👥 Manage Users</a>
        <a href="<?= SITE_URL ?>/admin/orders.php" class="btn-sm">📦 Manage Orders</a>
        <a href="<?= SITE_URL ?>/admin/services.php" class="btn-sm">🛒 Manage Services</a>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="btn-sm">⚙️ Settings</a>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
