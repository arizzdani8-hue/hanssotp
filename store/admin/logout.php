<?php
define('BASE_PATH', dirname(__DIR__));
require_once BASE_PATH . '/includes/config.php';
session_start();
unset($_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_name']);
header('Location: ' . SITE_URL . '/admin/login.php');
exit;
