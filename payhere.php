<?php
// ============================================
// PAYHERE CONFIGURATION
// ============================================
define('PAYHERE_MERCHANT_ID', '1237983');
define('PAYHERE_MERCHANT_SECRET', 'MzU0MDAzNDM0MDI3NTAyMTA5MTE5NTUzMTg4MDMzMjYzOTE0OTAw');
define('PAYHERE_MODE', 'sandbox'); // 'sandbox' හෝ 'live'

// ============================================
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();

$amount = (float)($_GET['amount'] ?? 0);
if ($amount < 5) {
    header('Location: ' . SITE_URL . '/add-funds.php');
    exit;
}

$orderId = 'DEP' . $user['id'] . time();
$currency = 'USD';
$amountFormatted = number_format($amount, 2, '.', '');

// ============================================
// CORRECT PAYHERE HASH FORMULA
// ============================================
// Step 1: Hash the merchant secret
$hashedSecret = strtoupper(md5(PAYHERE_MERCHANT_SECRET));

// Step 2: Concatenate all values + hashed secret
$hashString = PAYHERE_MERCHANT_ID . $orderId . $amountFormatted . $currency . $hashedSecret;

// Step 3: MD5 hash and uppercase
$hash = strtoupper(md5($hashString));

// ============================================

// Save pending deposit
$pdo->prepare("INSERT INTO deposits (user_id, amount, method, status, note) VALUES (?,?,'PayHere','pending',?)")
    ->execute([$user['id'], $amount, $orderId]);

// PayHere URL
$payhereUrl = PAYHERE_MODE === 'live' 
    ? 'https://www.payhere.lk/pay/checkout'
    : 'https://sandbox.payhere.lk/pay/checkout';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Redirecting to PayHere...</title>
<style>
body{background:#07070f;color:#e8e8f0;font-family:'Poppins',sans-serif;display:grid;place-items:center;min-height:100vh;margin:0;text-align:center}
.spinner{width:50px;height:50px;border:4px solid rgba(168,85,247,.3);border-top-color:#a855f7;border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 20px}
@keyframes spin{to{transform:rotate(360deg)}}
h2{font-size:20px;margin-bottom:10px}
p{color:#8b8ba0;font-size:14px}
</style>
</head>
<body>
<div>
    <div class="spinner"></div>
    <h2>Redirecting to PayHere...</h2>
    <p>Please wait, do not close this page.</p>
</div>

<form method="post" action="<?= $payhereUrl ?>" id="payhereForm">
    <input type="hidden" name="merchant_id" value="<?= PAYHERE_MERCHANT_ID ?>">
    <input type="hidden" name="return_url" value="<?= SITE_URL ?>/payment-success.php">
    <input type="hidden" name="cancel_url" value="<?= SITE_URL ?>/payment-cancel.php">
    <input type="hidden" name="notify_url" value="<?= SITE_URL ?>/payhere-notify.php">
    
    <input type="hidden" name="order_id" value="<?= $orderId ?>">
    <input type="hidden" name="items" value="Nima SMM Balance Top-up">
    <input type="hidden" name="currency" value="<?= $currency ?>">
    <input type="hidden" name="amount" value="<?= $amountFormatted ?>">
    
    <input type="hidden" name="first_name" value="<?= htmlspecialchars($user['username']) ?>">
    <input type="hidden" name="last_name" value="User">
    <input type="hidden" name="email" value="<?= htmlspecialchars($user['email']) ?>">
    <input type="hidden" name="phone" value="0000000000">
    <input type="hidden" name="address" value="Colombo">
    <input type="hidden" name="city" value="Colombo">
    <input type="hidden" name="country" value="Sri Lanka">
    
    <input type="hidden" name="hash" value="<?= $hash ?>">
</form>

<script>
document.getElementById('payhereForm').submit();
</script>
</body>
</html>
