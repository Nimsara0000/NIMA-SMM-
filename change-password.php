<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
requireLogin();
$user = currentUser();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current'] ?? '';
    $new = $_POST['new'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    
    if (!password_verify($current, $user['password'])) {
        $message = '<div class="toast error" style="position:static">Current password is incorrect.</div>';
    } elseif (strlen($new) < 6) {
        $message = '<div class="toast error" style="position:static">New password must be at least 6 characters.</div>';
    } elseif ($new !== $confirm) {
        $message = '<div class="toast error" style="position:static">New passwords do not match.</div>';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
        $message = '<div class="toast success" style="position:static">✅ Password changed successfully!</div>';
    }
}
$pageTitle = 'Change Password — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="card" style="max-width:500px;margin:30px auto">
    <h2>Change Password</h2>
    <p class="sub">Update your account password</p>
    <?= $message ?>
    <form method="POST">
        <div class="field"><label>Current Password</label><input type="password" name="current" required></div>
        <div class="field"><label>New Password</label><input type="password" name="new" required></div>
        <div class="field"><label>Confirm New Password</label><input type="password" name="confirm" required></div>
        <button class="btn-primary">Change Password</button>
    </form>
</div>
<?php include 'includes/footer.php'; ?>
