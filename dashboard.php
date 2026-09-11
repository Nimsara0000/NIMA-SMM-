<?php
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();

$totalOrders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$totalOrders->execute([$user['id']]);
$totalOrders = $totalOrders->fetchColumn();

$totalSpent = $pdo->prepare("SELECT COALESCE(SUM(charge),0) FROM orders WHERE user_id = ?");
$totalSpent->execute([$user['id']]);
$totalSpent = $totalSpent->fetchColumn();

$pending = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ? AND status IN ('Pending','In progress')");
$pending->execute([$user['id']]);
$pending = $pending->fetchColumn();

$recent = $pdo->prepare("SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON s.id = o.service_id WHERE o.user_id = ? ORDER BY o.id DESC LIMIT 5");
$recent->execute([$user['id']]);
$recent = $recent->fetchAll();

$pageTitle = 'Dashboard — ' . SITE_NAME;
include 'includes/header.php';
?>
<h2 style="margin:10px 0 20px;font-size:26px">Welcome back, <?= htmlspecialchars($user['username']) ?> 👋</h2>

<div class="stats">
    <div class="stat"><div class="label">Balance</div><div class="value grad">$<?= number_format($user['balance'], 2) ?></div></div>
    <div class="stat"><div class="label">Total Orders</div><div class="value"><?= $totalOrders ?></div></div>
    <div class="stat"><div class="label">Total Spent</div><div class="value">$<?= number_format($totalSpent, 2) ?></div></div>
    <div class="stat"><div class="label">Pending Orders</div><div class="value"><?= $pending ?></div></div>
</div>

<div class="card">
    <h2>Recent Orders</h2>
    <p class="sub">Your 5 most recent orders</p>
    <?php if ($recent): ?>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Service</th><th>Link</th><th>Qty</th><th>Charge</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['service_name']) ?></td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($o['link']) ?></td>
                <td><?= number_format($o['quantity']) ?></td>
                <td>$<?= number_format($o['charge'], 4) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $o['status'])) ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                <td><?= date('M d, H:i', strtotime($o['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php else: ?>
    <div class="empty"><div class="empty-icon">📦</div><p>No orders yet. <a href="new-order.php" style="color:var(--primary)">Place your first order</a></p></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
