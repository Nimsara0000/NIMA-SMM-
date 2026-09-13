<?php
require_once __DIR__ . '/../includes/config.php';
unset($_SESSION['admin_session']);
header('Location: ' . SITE_URL . '/index.php');
exit;
