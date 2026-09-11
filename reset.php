<?php
$host = 'dpg-dahuva3m8hqs73dg2bl0-a';
$port = '5432';
$dbname = 'nima_smm';
$user = 'nima_user';
$pass = 'D22bn3THPFRHNEbfFMnxnnBnpGxpMIGO';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create correct bcrypt hash for "admin123"
    $newHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Update admin password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
    $stmt->execute([$newHash]);
    
    echo "<h2>✅ Admin password reset!</h2>";
    echo "<p>Username: <b>admin</b></p>";
    echo "<p>Password: <b>admin123</b></p>";
    echo "<p><b>⚠️ Delete this reset.php file now!</b></p>";
    echo "<p><a href='/login.php'>→ Go to Login</a></p>";
    
} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>
