<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sync'])) {
        require_once __DIR__ . '/../includes/api.php';
        $api = new ProviderApi();
        $services = $api->services();
        if (is_array($services)) {
            $pdo->exec("DELETE FROM services");
            $stmt = $pdo->prepare("INSERT INTO services (provider_service_id,name,category,type,rate,min,max) VALUES (?,?,?,?,?,?,?)");
            foreach ($services as $s) $stmt->execute([$s->service, $s->name, $s->category ?? '', $s->type ?? 'Default', $s->rate, $s->min ?? 1, $s->max ?? 100000]);
        }
    }
    if (isset($_POST['toggle'])) {
        $sid = (int)$_POST['sid'];
        $pdo->prepare("UPDATE services SET status = IF(status='active','inactive','active') WHERE id=?")->execute([$sid]);
    }
    if (isset($_POST['update_rate'])) {
        $sid = (int)$_POST['sid']; $rate = (float)$_POST['rate'];
        $pdo->prepare("UPDATE services SET rate=? WHERE id=?")->execute([$rate, $sid]);
    }
    header('Location: ' . SITE_URL . '/admin/services.php'); exit;
}

$services = $pdo->query("SELECT * FROM services ORDER BY category, name")->fetchAll();
$pageTitle = 'Manage Services — ' . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>Manage Services</h2>
    <p class="sub"><?= count($services) ?> services</p>
    <form method="POST" style="margin-bottom:18px"><button name="sync" class="btn-primary">🔄 Sync from Provider API</button></form>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Type</th><th>Rate</th><th>Min/Max</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($services as $s): ?>
            <tr>
                <td>#<?= $s['provider_service_id'] ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['category']) ?></td>
                <td><?= htmlspecialchars($s['type']) ?></td>
                <td>$<?= $s['rate'] ?></td>
                <td><?= $s['min'] ?> / <?= number_format($s['max']) ?></td>
                <td><span class="badge badge-<?= $s['status']==='active'?'completed':'canceled' ?>"><?= $s['status'] ?></span></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap">
                    <form method="POST" style="display:inline-flex;gap:4px">
                        <input type="hidden" name="sid" value="<?= $s['id'] ?>">
                        <input type="number" name="rate" step="0.001" value="<?= $s['rate'] ?>" style="width:80px;padding:6px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
                        <button name="update_rate" class="btn-sm">💾</button>
                    </form>
                    <form method="POST" style="display:inline"><input type="hidden" name="sid" value="<?= $s['id'] ?>"><button name="toggle" class="btn-sm"><?= $s['status']==='active'?'Disable':'Enable' ?></button></form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
