<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = SITE_NAME . ' — #1 Cheapest SMM Panel';
include 'includes/header.php';
?>

<div style="position:fixed;top:80px;right:20px;z-index:99">
    <a href="<?= SITE_URL ?>/admin-login.php" 
       style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;background:linear-gradient(135deg,#f59e0b,#ef4444);color:#fff;text-decoration:none;font-size:13px;font-weight:700;box-shadow:0 6px 20px rgba(245,158,11,.45);transition:all .25s">
        🛡️ Admin
    </a>
</div>

<section class="hero">
    <h1>Grow Your Social Media<br>With <span>Nima SMM</span></h1>
    <p>The cheapest and most reliable SMM panel for Instagram, TikTok, YouTube, Facebook, Twitter and more. Instant delivery, 24/7 support.</p>
    <div class="hero-btns">
        <a href="<?= SITE_URL ?>/register.php" class="btn-primary">Get Started Free →</a>
        <a href="<?= SITE_URL ?>/services.php" class="btn-outline">View Services</a>
    </div>
</section>

<div class="features">
    <div class="feature"><div class="icon">⚡</div><h3>Instant Delivery</h3><p>Orders start automatically within seconds. No waiting, no delays.</p></div>
    <div class="feature"><div class="icon">💰</div><h3>Lowest Prices</h3><p>Starting from just $0.001 per 1000. Cheapest rates in the market.</p></div>
    <div class="feature"><div class="icon">🔒</div><h3>100% Secure</h3><p>Your data and funds are protected with industry-standard security.</p></div>
    <div class="feature"><div class="icon">🌍</div><h3>All Platforms</h3><p>Instagram, TikTok, YouTube, Facebook, Twitter, Spotify, and more.</p></div>
    <div class="feature"><div class="icon">🔁</div><h3>Refill Guarantee</h3><p>Free refills on eligible services if your engagement drops.</p></div>
    <div class="feature"><div class="icon">🎧</div><h3>24/7 Support</h3><p>Our team is always online to help you with any issue.</p></div>
</div>

<?php include 'includes/footer.php'; ?>
