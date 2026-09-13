<?php
require_once __DIR__ . '/db.php';

function isLoggedIn() { return isset($_SESSION['user_id']); }

function isAdmin() {
    return isset($_SESSION['admin_session']) && $_SESSION['admin_session'] === true;
}

function requireLogin() {
    if (!isLoggedIn() && !isAdmin()) { 
        header('Location: ' . SITE_URL . '/login.php'); 
        exit; 
    }
}

function requireAdmin() {
    if (!isAdmin()) { 
        header('Location: ' . SITE_URL . '/admin-login.php'); 
        exit; 
    }
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
