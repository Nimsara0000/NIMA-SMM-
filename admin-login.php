<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';

if (isAdmin()) {
    header('Location: ' . SITE_URL . '/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if ($password === ADMIN_PASSWORD) {
        $_SESSION['admin_session'] = true;
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    } else {
        $error = 'Wrong admin password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — <?= SITE_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
<style>
body{
  background:#07070f;
  background-image:
    radial-gradient(ellipse 60% 50% at 15% 0%,rgba(245,158,11,.15),transparent 60%),
    radial-gradient(ellipse 50% 40% at 85% 100%,rgba(239,68,68,.15),transparent 60%);
}
.admin-shield{
  width:80px;height:80px;margin:0 auto 20px;
  display:grid;place-items:center;
  font-size:40px;
  background:linear-gradient(135deg,#f59e0b,#ef4444);
  border-radius:24px;
  box-shadow:0 10px 40px rgba(245,158,11,.5);
}
</style>
</head>
<body>
<div style="min-height:100vh;display:grid;place-items:center;padding:40px 20px">
    <div style="width:100%;max-width:420px;background:var(--card);border:1px solid rgba(245,158,11,.3);border-radius:22px;padding:36px;backdrop-filter:blur(10px)">
        <div class="admin-shield">🛡️</div>
        <h2 style="text-align:center;font-size:24px;font-weight:700;margin-bottom:6px">Admin Access</h2>
        <p style="text-align:center;color:var(--muted);font-size:14px;margin-bottom:28px">Enter admin password to continue</p>
        
        <?php if ($error): ?>
        <div class="toast error" style="position:static;margin-bottom:18px"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="field">
                <label>🔑 Admin Password</label>
                <input type="password" name="password" required autofocus placeholder="Enter password">
            </div>
            <button class="btn-primary" style="width:100%;justify-content:center;background:linear-gradient(135deg,#f59e0b,#ef4444);box-shadow:0 8px 24px rgba(245,158,11,.4)">
                🛡️ Login to Admin Panel
            </button>
        </form>
        
        <p style="text-align:center;margin-top:20px;font-size:13px">
            <a href="<?= SITE_URL ?>/index.php" style="color:var(--muted);text-decoration:none">← Back to Site</a>
        </p>
    </div>
</div>
</body>
</html>
