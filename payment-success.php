<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Payment Success — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card" style="max-width:500px;margin:50px auto;text-align:center">
    <div style="font-size:64px;margin-bottom:16px">✅</div>
    <h2 style="color:var(--success)">Payment Successful!</h2>
    <p class="sub">Your balance will be updated within a few seconds.</p>
    <p style="color:var(--muted);font-size:14px;margin-bottom:24px">
        If balance doesn't update within 1 minute, please contact support.
    </p>
    <a href="<?= SITE_URL ?>/dashboard.php" class="btn-primary">Go to Dashboard</a>
</div>
<?php include 'includes/footer.php'; ?>
