<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    if ($amount < 5) $message = '<div class="toast error" style="position:static">Minimum deposit is $5.</div>';
    elseif (!$method) $message = '<div class="toast error" style="position:static">Select a payment method.</div>';
    else {
        $newBalance = $user['balance'] + $amount;
        $pdo->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$newBalance, $user['id']]);
        $pdo->prepare("INSERT INTO transactions (user_id,type,amount,balance_after,description) VALUES (?,'deposit',?,?,?)")
            ->execute([$user['id'], $amount, $newBalance, 'Deposit via ' . $method]);
        $message = '<div class="toast success" style="position:static">✅ $' . number_format($amount, 2) . ' added to your balance!</div>';
        $user = currentUser();
    }
}
$pageTitle = 'Add Funds — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card">
    <h2>Add Funds</h2>
    <p class="sub">Top up your account balance</p>
    <?= $message ?>
    <div class="stats">
        <div class="stat"><div class="label">Current Balance</div><div class="value grad">$<?= number_format($user['balance'], 2) ?></div></div>
    </div>
    <form method="POST">
        <div class="field"><label>Amount (USD)</label><input type="number" name="amount" min="5" step="0.01" placeholder="10.00" required></div>
        <div class="field">
            <label>Payment Method</label>
            <select name="method" required>
                <option value="">Select method</option>
                <option value="PayPal">PayPal</option>
                <option value="Stripe">Credit/Debit Card (Stripe)</option>
                <option value="Paytm">Paytm</option>
                <option value="JazzCash">JazzCash</option>
                <option value="Easypaisa">Easypaisa</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="Crypto">Cryptocurrency</option>
            </select>
        </div>
        <button class="btn-primary">Add Funds</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
