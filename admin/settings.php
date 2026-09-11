<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['site_name','site_url','currency','provider_api_url','provider_api_key','admin_email','min_deposit','signup_bonus'];
    $stmt = $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?");
    foreach ($allowed as $k) if (isset($_POST[$k])) $stmt->execute([$_POST[$k], $k]);
    $msg = '<div class="toast success" style="position:static">Settings saved.</div>';
    $settings = $pdo->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
}
$pageTitle = 'Settings — ' . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>Site Settings</h2>
    <?= $msg ?>
    <form method="POST">
        <div class="row">
            <div class="field"><label>Site Name</label><input name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>"></div>
            <div class="field"><label>Currency</label><input name="currency" value="<?= htmlspecialchars($settings['currency'] ?? 'USD') ?>"></div>
            <div class="field"><label>Site URL</label><input name="site_url" value="<?= htmlspecialchars($settings['site_url'] ?? '') ?>"></div>
            <div class="field"><label>Admin Email</label><input name="admin_email" value="<?= htmlspecialchars($settings['admin_email'] ?? '') ?>"></div>
            <div class="field"><label>Provider API URL</label><input name="provider_api_url" value="<?= htmlspecialchars($settings['provider_api_url'] ?? '') ?>"></div>
            <div class="field"><label>Provider API Key</label><input name="provider_api_key" value="<?= htmlspecialchars($settings['provider_api_key'] ?? '') ?>"></div>
            <div class="field"><label>Minimum Deposit ($)</label><input name="min_deposit" value="<?= htmlspecialchars($settings['min_deposit'] ?? '5') ?>"></div>
            <div class="field"><label>Signup Bonus ($)</label><input name="signup_bonus" value="<?= htmlspecialchars($settings['signup_bonus'] ?? '0.5') ?>"></div>
        </div>
        <button class="btn-primary">Save Settings</button>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
