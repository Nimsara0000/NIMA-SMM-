<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();

// Sync services from provider if empty
$count = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();
if ($count == 0) {
    require_once 'includes/api.php';
    $api = new ProviderApi();
    $services = $api->services();
    if (is_array($services)) {
        $stmt = $pdo->prepare("INSERT INTO services (provider_service_id,name,category,type,rate,min,max) VALUES (?,?,?,?,?,?,?)");
        foreach ($services as $s) {
            $stmt->execute([$s->service, $s->name, $s->category ?? '', $s->type ?? 'Default', $s->rate, $s->min ?? 1, $s->max ?? 100000]);
        }
    }
}

$services = $pdo->query("SELECT * FROM services WHERE status='active' ORDER BY category, name")->fetchAll();
$pageTitle = 'Services — ' . SITE_NAME;
include 'includes/header.php';
?>
<h2 style="margin:10px 0 6px;font-size:26px">Available Services</h2>
<p style="color:var(--muted);margin-bottom:22px">Click a service to place an order</p>

<div class="field" style="margin-bottom:18px">
    <input type="text" id="svcSearch" placeholder="🔍  Search services...">
</div>

<div class="services-grid" id="servicesGrid">
<?php foreach ($services as $s): ?>
    <div class="svc" data-id="<?= $s['id'] ?>" onclick="location.href='<?= SITE_URL ?>/new-order.php?service=<?= $s['id'] ?>'">
        <div class="svc-head">
            <span class="svc-id">#<?= $s['provider_service_id'] ?></span>
            <span class="svc-rate">$<?= $s['rate'] ?>/1k</span>
        </div>
        <div class="svc-name"><?= htmlspecialchars($s['name']) ?></div>
        <div class="svc-meta">
            <span><?= htmlspecialchars($s['type']) ?></span>
            <span>Min <?= $s['min'] ?></span>
            <span>Max <?= number_format($s['max']) ?></span>
            <?php if ($s['category']): ?><span><?= htmlspecialchars($s['category']) ?></span><?php endif; ?>
        </div>
    </div>
<?php endforeach; ?>
</div>

<script>
document.getElementById('svcSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.svc').forEach(el => {
        el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});
</script>
<?php include 'includes/footer.php'; ?>
