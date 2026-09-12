<?php
// ============================================
// PAYHERE WEBHOOK / NOTIFICATION HANDLER
// ============================================
require_once __DIR__ . '/includes/config.php';

// PayHere merchant secret
$merchantSecret = 'YOUR_MERCHANT_SECRET'; // ඔබගේ secret එක දාන්න

// Verify PayHere signature
$localMd5Sig = strtoupper(md5(
    $_POST['merchant_id'] .
    $_POST['order_id'] .
    $_POST['payhere_amount'] .
    $_POST['payhere_currency'] .
    $_POST['status_code'] .
    strtoupper(md5($merchantSecret))
));

if ($localMd5Sig !== $_POST['md5sig']) {
    // Signature mismatch - reject
    http_response_code(400);
    exit('Invalid signature');
}

$orderId = $_POST['order_id'];
$statusCode = $_POST['status_code'];
$amount = (float)$_POST['payhere_amount'];
$paymentId = $_POST['payment_id'] ?? '';

// Status codes:
// 2 = Success
// 0 = Pending
// -1 = Canceled
// -2 = Failed
// -3 = Chargedback

if ($statusCode == 2) {
    // Payment successful
    $stmt = $pdo->prepare("SELECT * FROM deposits WHERE note = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$orderId]);
    $dep = $stmt->fetch();

    if ($dep && $dep['status'] === 'pending') {
        $pdo->beginTransaction();
        try {
            // Add balance to user
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
                ->execute([$amount, $dep['user_id']]);

            // Get new balance
            $balStmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $balStmt->execute([$dep['user_id']]);
            $newBal = $balStmt->fetchColumn();

            // Transaction log
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, balance_after, description) VALUES (?,'deposit',?,?,?)")
                ->execute([$dep['user_id'], $amount, $newBal, 'PayHere payment #' . $paymentId]);

            // Update deposit
            $pdo->prepare("UPDATE deposits SET status='approved', admin_note=?, reviewed_at=CURRENT_TIMESTAMP WHERE id=?")
                ->execute(['PayHere Payment ID: ' . $paymentId, $dep['id']]);

            $pdo->commit();
            http_response_code(200);
            echo 'OK';
        } catch (Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo 'Error: ' . $e->getMessage();
        }
    } else {
        http_response_code(200);
        echo 'Already processed';
    }
} else {
    // Payment failed/canceled
    $pdo->prepare("UPDATE deposits SET status='rejected', admin_note=? WHERE note=?")
        ->execute(['PayHere status code: ' . $statusCode, $orderId]);
    http_response_code(200);
    echo 'OK';
}
