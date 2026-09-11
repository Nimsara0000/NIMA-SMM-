<?php
require_once __DIR__ . '/db.php';

function isLoggedIn() { return isset($_SESSION['user_id']); }
function isAdmin() { return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin'; }

function requireLogin() {
    if (!isLoggedIn()) { header('Location: ' . SITE_URL . '/login.php'); exit; }
}
function requireAdmin() {
    if (!isAdmin()) { header('Location: ' . SITE_URL . '/dashboard.php'); exit; }
}

function currentUser() {
    global $pdo;
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

function loginUser($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
}
