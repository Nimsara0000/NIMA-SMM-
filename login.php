<?php
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        if ($user['status'] === 'banned') $error = 'Your account has been banned.';
        else { loginUser($user); header('Location: dashboard.php'); exit; }
    } else $error = 'Invalid username/email or password.';
}
$pageTitle = 'Login — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="auth-wrap">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="sub">Login to your Nima SMM account</p>
        <?php if ($error): ?><div class="toast error" style="position:static;margin-bottom:18px"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="field"><label>Username or Email</label><input name="login" required></div>
            <div class="field"><label>Password</label><input type="password" name="password" required></div>
            <button class="btn-primary" style="width:100%;justify-content:center">Login</button>
        </form>
        <p style="text-align:center;margin-top:18px;color:var(--muted);font-size:14px">No account? <a href="register.php" style="color:var(--primary)">Sign Up</a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
