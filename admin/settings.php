<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $allowed = ['site_name','site_url','currency','provider_api_url','provider_api_key','admin_email','min_deposit','signup_bonus'];
    $stmt = $pdo->prepare("UPDATE settings SET setting_value=? WHERE setting_key=?");
    foreach ($allowed as $k) if (isset($_POST[$k])) $stmt->execute([$_POST[$k], $k]);
    $msg = '<div class="toast success" style="position:static">✅ Settings saved successfully.</div>';
    $settings = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll(PDO::FETCH_KEY_PAIR);
}
$pageTitle = 'Settings — ' . SITE_NAME;
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
            <a href="<?= SITE_URL ?>/admin/index.php">Dashboard</a>
            <a href="<?= SITE_URL ?>/admin/deposits.php">💳 Deposits</a>
            <a href="<?= SITE_URL ?>/admin/users.php">👥 Users</a>
            <a href="<?= SITE_URL ?>/admin/orders.php">📦 Orders</a>
            <a href="<?= SITE_URL ?>/admin/services.php">🛒 Services</a>
            <a href="<?= SITE_URL ?>/admin/settings.php" style="color:#f59e0b">⚙️ Settings</a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="btn-sm" style="background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;border:none">🚪 Exit Admin</a>
        </div>
    </div>
</nav>
<main class="container">
<div class="card">
    <h2>⚙️ Site Settings</h2>
    <p class="sub">Update your panel configuration</p>
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
        <button class="btn-primary">💾 Save Settings</button>
    </form>
</div>
</main>
<footer class="footer">
    <div class="container"><p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> — Admin Panel</p></div>
</footer>
</body>
</html>
