<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_POST['uid'];
    if (isset($_POST['ban'])) $pdo->prepare("UPDATE users SET status='banned' WHERE id=?")->execute([$uid]);
    if (isset($_POST['unban'])) $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$uid]);
    if (isset($_POST['add_balance'])) {
        $amt = (float)$_POST['amount'];
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id=?")->execute([$amt, $uid]);
        $bal = $pdo->query("SELECT balance FROM users WHERE id=$uid")->fetchColumn();
        $pdo->prepare("INSERT INTO transactions (user_id,type,amount,balance_after,description) VALUES (?,'admin',?,?,?)")
            ->execute([$uid, $amt, $bal, 'Admin credit']);
    }
    header('Location: ' . SITE_URL . '/admin/users.php'); exit;
}

$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
$pageTitle = 'Manage Users — ' . SITE_NAME;
include __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h2>Manage Users</h2>
    <p class="sub"><?= count($users) ?> users registered</p>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Username</th><th>Email</th><th>Balance</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td>#<?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['username']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td>$<?= number_format($u['balance'], 4) ?></td>
                <td><span class="badge badge-<?= $u['role']==='admin'?'progress':'pending' ?>"><?= $u['role'] ?></span></td>
                <td><span class="badge badge-<?= $u['status']==='active'?'completed':'canceled' ?>"><?= $u['status'] ?></span></td>
                <td style="display:flex;gap:6px;flex-wrap:wrap">
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                        <?php if ($u['status']==='active'): ?><button name="ban" class="btn-sm btn-danger">Ban</button><?php else: ?><button name="unban" class="btn-sm">Unban</button><?php endif; ?>
                    </form>
                    <form method="POST" style="display:inline-flex;gap:4px">
                        <input type="hidden" name="uid" value="<?= $u['id'] ?>">
                        <input type="number" name="amount" step="0.01" placeholder="$" style="width:80px;padding:6px;border-radius:8px;background:rgba(255,255,255,.04);border:1px solid var(--border);color:var(--text)">
                        <button name="add_balance" class="btn-sm">+ Add</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
