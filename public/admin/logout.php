<?php
session_start();

require('../../app/config/db.php');
require_once '../../app/config/function.php';

$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

if (!empty($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/');
    try {
        $stmt = $pdo->prepare("DELETE FROM admin_remember_tokens WHERE token = ?");
        $stmt->execute([$_COOKIE['remember_token']]);
    } catch (PDOException $e) {
        error_log("删除记住令牌错误: " . $e->getMessage());
    }
}

session_destroy();

header("Location: login.php");
exit();
?>