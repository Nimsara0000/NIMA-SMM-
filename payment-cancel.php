<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();

$pageTitle = 'Payment Cancelled — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card" style="max-width:500px;margin:50px auto;text-align:center">
    <div style="font-size:64px;margin-bottom:16px">❌</div>
    <h2 style="color:var(--danger)">Payment Cancelled</h2>
    <p class="sub">Your payment was not completed.</p>
    <a href="<?= SITE_URL ?>/add-funds.php" class="btn-primary">Try Again</a>
</div>
<?php include 'includes/footer.php'; ?>
