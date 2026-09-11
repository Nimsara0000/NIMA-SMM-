<?php
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$orders = $pdo->query("SELECT o.*, u.username, s.name AS service_name FROM orders o JOIN users u ON u.id = o.user_id JOIN services s ON s.id = o.service_id ORDER BY o.id DESC")->fetchAll();
$pageTitle = 'Manage Orders — ' . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>All Orders</h2>
    <p class="sub"><?= count($orders) ?> orders total</p>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>User</th><th>Service</th><th>Link</th><th>Qty</th><th>Charge</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td>#<?= $o['id'] ?></td>
                <td><?= htmlspecialchars($o['username']) ?></td>
                <td><?= htmlspecialchars($o['service_name']) ?></td>
                <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($o['link']) ?></td>
                <td><?= number_format($o['quantity']) ?></td>
                <td>$<?= number_format($o['charge'], 4) ?></td>
                <td><span class="badge badge-<?= strtolower(str_replace(' ', '', $o['status'])) ?>"><?= htmlspecialchars($o['status']) ?></span></td>
                <td><?= date('M d, H:i', strtotime($o['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
