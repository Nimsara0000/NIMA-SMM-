<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $did = (int)$_POST['did'];
    $action = $_POST['action'] ?? '';
    $adminNote = trim($_POST['admin_note'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM deposits WHERE id = ?");
    $stmt->execute([$did]);
    $dep = $stmt->fetch();

    if ($dep && $dep['status'] === 'pending') {
        if ($action === 'approve') {
            $pdo->beginTransaction();
            try {
                $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
                    ->execute([$dep['amount'], $dep['user_id']]);

                $balStmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
                $balStmt->execute([$dep['user_id']]);
                $newBal = $balStmt->fetchColumn();

                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, balance_after, description) VALUES (?,'deposit',?,?,?)")
                    ->execute([$dep['user_id'], $dep['amount'], $newBal, 'Deposit #' . $did . ' approved via ' . $dep['method']]);

                $pdo->prepare("UPDATE deposits SET status='approved', admin_note=?, reviewed_at=CURRENT_TIMESTAMP WHERE id=?")
                    ->execute([$adminNote, $did]);

                $pdo->commit();
                $msg = '<div class="toast success" style="position:static">✅ Deposit #' . $did . ' approved! $' . number_format($dep['amount'], 2) . ' added.</div>';
            } catch (Exception $e) {
                $pdo->rollBack();
                $msg = '<div class="toast error" style="position:static">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE deposits SET status='rejected', admin_note=?, reviewed_at=CURRENT_TIMESTAMP WHERE id=?")
                ->execute([$adminNote, $did]);
            $msg = '<div class="toast error" style="position:static">❌ Deposit #' . $did . ' rejected.</div>';
        }
    }
}

$filter = $_GET['filter'] ?? 'pending';
$where = '';
if ($filter === 'pending') $where = "WHERE d.status = 'pending'";
elseif ($filter === 'approved') $where = "WHERE d.status = 'approved'";
elseif ($filter === 'rejected') $where = "WHERE d.status = 'rejected'";

$deposits = $pdo->query("
    SELECT d.*, u.username, u.email 
    FROM deposits d 
    JOIN users u ON u.id = d.user_id 
    $where
    ORDER BY d.id DESC
")->fetchAll();

$pendingCount = $pdo->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn();

$pageTitle = 'Deposit Requests — ' . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>💳 Deposit Requests</h2>
    <p class="sub"><?= $pendingCount ?> pending request<?= $pendingCount == 1 ? '' : 's' ?> need your review</p>
    <?= $msg ?>

    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:18px">
        <a href="?filter=pending" class="btn-sm" style="<?= $filter==='pending'?'background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff':'' ?>">⏳ Pending</a>
        <a href="?filter=approved" class="btn-sm" style="<?= $filter==='approved'?'background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff':'' ?>">✅ Approved</a>
        <a href="?filter=rejected" class="btn-sm" style="<?= $filter==='rejected'?'background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff':'' ?>">❌ Rejected</a>
        <a href="?filter=all" class="btn-sm" style="<?= $filter==='all'?'background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff':'' ?>">📋 All</a>
    </div>

    <?php if ($deposits): ?>
    <div class="table-wrap"><table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Receipt</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($deposits as $d):
            $statusClass = $d['status']==='approved' ? 'completed' : ($d['status']==='rejected' ? 'canceled' : 'pending');
        ?>
            <tr>
                <td>#<?= $d['id'] ?></td>
                <td>
                    <strong><?= htmlspecialchars($d['username']) ?></strong><br>
                    <small style="color:var(--muted)"><?= htmlspecialchars($d['email']) ?></small>
                </td>
                <td><strong style="color:var(--success)">$<?= number_format($d['amount'], 2) ?></strong></td>
                <td><?= htmlspecialchars($d['method']) ?></td>
                <td>
                    <?php if ($d['receipt_path']): ?>
                        <a href="<?= SITE_URL ?>/<?= htmlspecialchars($d['receipt_path']) ?>" target="_blank" class="btn-sm">📎 View</a>
                    <?php else: ?>
                        <span style="color:var(--muted)">—</span>
                    <?php endif; ?>
                </td>
                <td><span class="badge badge-<?= $statusClass ?>"><?= ucfirst($d['status']) ?></span></td>
                <td><?= date('M d, H:i', strtotime($d['created_at'])) ?></td>
                <td>
                    <?php if ($d['status'] === 'pending'): ?>
                    <form method="POST" style="display:flex;flex-direction:column;gap:6px">
                        <input type="hidden" name="did" value="<?= $d['id'] ?>">
                        <input type="text" name="admin_note" placeholder="Note" style="width:120px;padding:6px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text);font-size:12px">
                        <div style="display:flex;gap:4px">
                            <button name="action" value="approve" class="btn-sm" style="background:rgba(16,185,129,.2);color:#34d399">✅ Approve</button>
                            <button name="action" value="reject" class="btn-sm btn-danger">❌ Reject</button>
                        </div>
                    </form>
                    <?php else: ?>
                        <small style="color:var(--muted)"><?= htmlspecialchars($d['admin_note'] ?? 'Reviewed') ?></small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php else: ?>
    <div class="empty"><div class="empty-icon">📭</div><p>No <?= $filter ?> deposits found.</p></div>
    <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
