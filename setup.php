<?php
$host = 'dpg-dahuva3m8hqs73dg2bl0-a';
$port = '5432';
$dbname = 'nima_smm';
$user = 'nima_user';
$pass = 'D22bn3THPRHNE8fFXmnxnBnpGxMfGO';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<h2>✅ Database Connected!</h2>";

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        balance DECIMAL(15,4) DEFAULT 0.0000,
        api_key VARCHAR(64) UNIQUE,
        role VARCHAR(20) DEFAULT 'user',
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS services (
        id SERIAL PRIMARY KEY,
        provider_service_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        category VARCHAR(100),
        type VARCHAR(50) DEFAULT 'Default',
        rate DECIMAL(15,4) NOT NULL,
        min INT DEFAULT 1,
        max INT DEFAULT 100000,
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS orders (
        id SERIAL PRIMARY KEY,
        user_id INT NOT NULL REFERENCES users(id),
        order_id VARCHAR(50),
        service_id INT NOT NULL REFERENCES services(id),
        link TEXT NOT NULL,
        quantity INT NOT NULL,
        charge DECIMAL(15,4) NOT NULL,
        start_count INT DEFAULT 0,
        remains INT DEFAULT 0,
        status VARCHAR(50) DEFAULT 'Pending',
        provider VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS transactions (
        id SERIAL PRIMARY KEY,
        user_id INT NOT NULL REFERENCES users(id),
        type VARCHAR(20) NOT NULL,
        amount DECIMAL(15,4) NOT NULL,
        balance_after DECIMAL(15,4) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS settings (
        id SERIAL PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT
    );
    ";

    $pdo->exec($sql);
    echo "<p>✅ Tables created successfully!</p>";

    $pdo->exec("INSERT INTO settings (setting_key, setting_value) VALUES
        ('site_name', 'Nima SMM'),
        ('site_url', 'https://nima-smm.onrender.com'),
        ('currency', 'USD'),
        ('provider_api_url', 'https://smmpakpanel.com/api/v2'),
        ('provider_api_key', '924d90d325b79930a235cc83af4311fe'),
        ('admin_email', 'admin@nimasmm.com'),
        ('min_deposit', '5'),
        ('signup_bonus', '0.50')
        ON CONFLICT (setting_key) DO NOTHING");
    echo "<p>✅ Settings inserted!</p>";

    $pdo->exec("INSERT INTO users (username, email, password, role, balance, api_key) VALUES
        ('admin', 'admin@nimasmm.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 0.00, 'ADMIN_API_KEY_001')
        ON CONFLICT (username) DO NOTHING");
    echo "<p>✅ Admin user created!</p>";

    echo "<h3 style='color:green'>🎉 Database setup complete!</h3>";
    echo "<p><b>⚠️ IMPORTANT: Delete this setup.php file now!</b></p>";

} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>
