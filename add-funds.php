<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = (float)($_POST['amount'] ?? 0);
    $method = $_POST['method'] ?? '';
    $note = trim($_POST['note'] ?? '');

    if ($amount < 5) {
        $message = '<div class="toast error" style="position:static">Minimum deposit is $5.</div>';
    } elseif (!$method) {
        $message = '<div class="toast error" style="position:static">Select a payment method.</div>';
    } else {
        $receiptPath = null;

        if (!empty($_FILES['receipt']['name'])) {
            $allowed = ['jpg','jpeg','png','gif','pdf','webp'];
            $ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowed)) {
                $message = '<div class="toast error" style="position:static">Invalid file type. Only JPG, PNG, GIF, WEBP, PDF allowed.</div>';
            } elseif ($_FILES['receipt']['size'] > 5 * 1024 * 1024) {
                $message = '<div class="toast error" style="position:static">File too large. Max 5MB.</div>';
            } else {
                $uploadDir = __DIR__ . '/uploads/receipts/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $filename = 'receipt_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $fullPath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $fullPath)) {
                    $receiptPath = 'uploads/receipts/' . $filename;
                } else {
                    $message = '<div class="toast error" style="position:static">File upload failed. Try again.</div>';
                }
            }
        } elseif ($method === 'Bank Transfer') {
            $message = '<div class="toast error" style="position:static">Please upload your bank receipt.</div>';
        }

        if (!$message) {
            $stmt = $pdo->prepare("INSERT INTO deposits (user_id, amount, method, receipt_path, note) VALUES (?,?,?,?,?)");
            $stmt->execute([$user['id'], $amount, $method, $receiptPath, $note]);
            $message = '<div class="toast success" style="position:static">
                ✅ Deposit request submitted! Admin will review it shortly.<br>
                <small>You will be notified once approved.</small>
            </div>';
        }
    }
}

$deposits = $pdo->prepare("SELECT * FROM deposits WHERE user_id = ? ORDER BY id DESC LIMIT 10");
$deposits->execute([$user['id']]);
$deposits = $deposits->fetchAll();

$pageTitle = 'Add Funds — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card">
    <h2>💰 Add Funds</h2>
    <p class="sub">Top up your account balance via Bank Transfer</p>
    <?= $message ?>

    <div class="stats">
        <div class="stat"><div class="label">Current Balance</div><div class="value grad">$<?= number_format($user['balance'], 2) ?></div></div>
    </div>

    <!-- BANK DETAILS -->
    <div style="background:linear-gradient(135deg,rgba(168,85,247,.08),rgba(236,72,153,.08));border:1px solid rgba(168,85,247,.3);border-radius:16px;padding:22px;margin-bottom:24px">
        <h3 style="margin-bottom:16px;font-size:18px">🏦 Bank Transfer Details</h3>
        <div style="display:grid;gap:12px">
            <div style="display:flex;justify-content:space-between;padding:12px;background:rgba(0,0,0,.2);border-radius:10px">
                <span style="color:var(--muted)">Bank</span>
                <strong>Bank of Ceylon</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px;background:rgba(0,0,0,.2);border-radius:10px">
                <span style="color:var(--muted)">Account Number</span>
                <strong style="font-family:monospace;letter-spacing:1px">0096651034</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:12px;background:rgba(0,0,0,.2);border-radius:10px">
                <span style="color:var(--muted)">Account Holder</span>
                <strong>MASTER P V D N SANDARUWAN</strong>
            </div>
        </div>
        <p style="margin-top:14px;font-size:13px;color:var(--muted)">
            ⚠️ ඔබ deposit කරන ලද මුදල <b>Bank slip / Receipt</b> එකක් upload කරන්න. Admin approve කළ පසු balance එකට add වේ.
        </p>
    </div>

    <!-- DEPOSIT FORM -->
    <form method="POST" enctype="multipart/form-data">
        <div class="field">
            <label>Amount (USD)</label>
            <input type="number" name="amount" min="5" step="0.01" placeholder="10.00" required>
        </div>

        <input type="hidden" name="method" value="Bank Transfer">

        <div class="field">
            <label>Upload Receipt / Bank Slip <span style="color:var(--danger)">*</span></label>
            <input type="file" name="receipt" accept="image/*,.pdf" required>
            <small style="color:var(--muted);font-size:12px;display:block;margin-top:6px">
                JPG, PNG, WEBP, PDF (max 5MB)
            </small>
        </div>

        <div class="field">
            <label>Note (Optional)</label>
            <textarea name="note" placeholder="Reference number, deposit date, etc." style="min-height:80px"></textarea>
        </div>

        <button class="btn-primary" style="width:100%;justify-content:center">
            📤 Submit Deposit Request
        </button>
    </form>
</div>

<?php if ($deposits): ?>
<div class="card">
    <h2>📋 My Deposit History</h2>
    <p class="sub">Your recent deposit requests</p>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($deposits as $d):
            $statusClass = $d['status']==='approved' ? 'completed' : ($d['status']==='rejected' ? 'canceled' : 'pending');
        ?>
            <tr>
                <td>#<?= $d['id'] ?></td>
                <td>$<?= number_format($d['amount'], 2) ?></td>
                <td><?= htmlspecialchars($d['method']) ?></td>
                <td><span class="badge badge-<?= $statusClass ?>"><?= ucfirst($d['status']) ?></span></td>
                <td><?= date('M d, H:i', strtotime($d['created_at'])) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
