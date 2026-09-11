<?php
require_once __DIR__ . '/includes/config.php';
require_once 'includes/auth.php';
if (isLoggedIn()) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if (strlen($username) < 3) $error = 'Username must be at least 3 characters.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Invalid email address.';
    elseif (strlen($password) < 6) $error = 'Password must be at least 6 characters.';
    elseif ($password !== $confirm) $error = 'Passwords do not match.';
    else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) $error = 'Username or email already exists.';
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $apiKey = bin2hex(random_bytes(32));
            $bonus = (float)($pdo->query("SELECT setting_value FROM settings WHERE setting_key='signup_bonus'")->fetchColumn() ?: 0);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, api_key, balance) VALUES (?,?,?,?,?)");
            $stmt->execute([$username, $email, $hash, $apiKey, $bonus]);
            $userId = $pdo->lastInsertId();
            if ($bonus > 0) {
                $pdo->prepare("INSERT INTO transactions (user_id,type,amount,balance_after,description) VALUES (?,'deposit',?,?,?)")
                    ->execute([$userId, $bonus, $bonus, 'Signup bonus']);
            }
            $user = ['id'=>$userId,'username'=>$username,'role'=>'user'];
            loginUser($user);
            header('Location: ' . SITE_URL . '/dashboard.php'); exit;
        }
    }
}
$pageTitle = 'Sign Up — ' . SITE_NAME;
include 'includes/header.php';
?>
<div class="auth-wrap">
    <div class="auth-card">
        <h2>Create Account</h2>
        <p class="sub">Join Nima SMM and start growing today</p>
        <?php if ($error): ?><div class="toast error" style="position:static;margin-bottom:18px"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="POST">
            <div class="field"><label>Username</label><input name="username" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"></div>
            <div class="field"><label>Email</label><input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"></div>
            <div class="field"><label>Password</label><input type="password" name="password" required></div>
            <div class="field"><label>Confirm Password</label><input type="password" name="confirm" required></div>
            <button class="btn-primary" style="width:100%;justify-content:center">Sign Up</button>
        </form>
        <p style="text-align:center;margin-top:18px;color:var(--muted);font-size:14px">Already have an account? <a href="<?= SITE_URL ?>/login.php" style="color:var(--primary)">Login</a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
