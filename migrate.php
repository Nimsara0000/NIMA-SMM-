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
    echo "<h2>✅ Connected!</h2>";

    $pdo->exec("
    CREATE TABLE IF NOT EXISTS deposits (
        id SERIAL PRIMARY KEY,
        user_id INT NOT NULL REFERENCES users(id),
        amount DECIMAL(15,4) NOT NULL,
        method VARCHAR(50) NOT NULL,
        receipt_path VARCHAR(255),
        note TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        admin_note TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        reviewed_at TIMESTAMP
    );
    ");
    echo "<p>✅ deposits table created!</p>";

    echo "<h3 style='color:green'>🎉 Migration complete!</h3>";
    echo "<p><b>⚠️ Delete migrate.php now!</b></p>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>
