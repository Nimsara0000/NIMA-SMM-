<?php
// ============================================
// PAYHERE WEBHOOK - Auto balance add
// ============================================
define('PAYHERE_MERCHANT_SECRET', 'MzU0MDAzNDM0MDI3NTAyMTA5MTE5NTUzMTg4MDMzMjYzOTE0OTAw');

require_once __DIR__ . '/includes/config.php';

// Verify signature (CORRECT FORMULA)
$localMd5Sig = strtoupper(md5(
    ($_POST['merchant_id'] ?? '') .
    ($_POST['order_id'] ?? '') .
    ($_POST['payhere_amount'] ?? '') .
    ($_POST['payhere_currency'] ?? '') .
    ($_POST['status_code'] ?? '') .
    strtoupper(md5(PAYHERE_MERCHANT_SECRET))
));

if ($localMd5Sig !== ($_POST['md5sig'] ?? '')) {
    http_response_code(400);
    exit('Invalid signature');
}

$orderId = $_POST['order_id'] ?? '';
$statusCode = $_POST['status_code'] ?? '';
$amount = (float)($_POST['payhere_amount'] ?? 0);
$paymentId = $_POST['payment_id'] ?? '';

// status_code: 2 = Success
if ($statusCode == 2) {
    $stmt = $pdo->prepare("SELECT * FROM deposits WHERE note = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$orderId]);
    $dep = $stmt->fetch();

    if ($dep && $dep['status'] === 'pending') {
        $pdo->beginTransaction();
        try {
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
                ->execute([$amount, $dep['user_id']]);

            $balStmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
            $balStmt->execute([$dep['user_id']]);
            $newBal = $balStmt->fetchColumn();

            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, balance_after, description) VALUES (?,'deposit',?,?,?)")
                ->execute([$dep['user_id'], $amount, $newBal, 'PayHere card payment #' . $paymentId]);

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
    $pdo->prepare("UPDATE deposits SET status='rejected', admin_note=? WHERE note=?")
        ->execute(['PayHere status: ' . $statusCode, $orderId]);
    http_response_code(200);
    echo 'OK';
}
