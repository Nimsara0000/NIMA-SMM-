<?php
require_once 'includes/auth.php';
require_once 'includes/api.php';
requireLogin();
$user = currentUser();
$api = new ProviderApi();

// Sync statuses from provider
$orders = $pdo->prepare("SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON s.id = o.service_id WHERE o.user_id = ? ORDER BY o.id DESC");
$orders->execute([$user['id']]);
$orders = $orders->fetchAll();

$ids = array_filter(array_column($orders, 'order_id'));
if ($ids) {
    $statuses = $api->multiStatus($ids);
    if (is_array($statuses)) {
        foreach ($statuses as $st) {
            if (!isset($st->order)) continue;
            $pdo->prepare("UPDATE orders SET status=?, start_count=?, remains=? WHERE order_id=?")
                ->execute([$st->status ?? 'Pending', $st->start_count ?? 0, $st->remains ?? 0, $st->order]);
        }
        // Refresh
        $orders = $pdo->prepare("SELECT o.*, s.name AS service_name FROM orders o JOIN services s ON s.id = o.service_id WHERE o.user_id = ? ORDER BY o.id DESC");
        $orders->execute([$user['id']]);
        $orders = $orders->fetchAll();
    }
}

$pageTitle = 'My Orders — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card">
    <h2>My Orders</h2>
    <p class="sub">Track all your orders and their statuses</p>
    <?php if ($orders): ?>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Service</th><th>Link</th><th>Qty</th><th>Charge</th><th>Start</th><th>Remains</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['service_name']) ?></td>
                <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($o['link']) ?></td>
                <td><?= number_format($o['quantity']) ?></td>
                <td>$<?= number_format($o['charge'], 4) ?></td>
                <td><?= $o['start_count'] ?></td>
                <td><?= $o['remains'] ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $o['status'])) ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                <td><?= date('M d, H:i', strtotime($o['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php else: ?>
    <div class="empty"><div class="empty-icon">📦</div><p>No orders found.</p></div>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
