<?php
session_start();
require('../../app/config/db.php');
require_once '../../app/config/function.php';

$error = '';

if (empty($_SESSION['admin_logged_in']) && !empty($_COOKIE['remember_token'])) {
    try {
        $token = $_COOKIE['remember_token'];
        $stmt = $pdo->prepare("SELECT a.id, a.username, a.nickname FROM admin_accounts a 
                              JOIN admin_remember_tokens t ON a.id = t.admin_id 
                              WHERE t.token = ? AND t.expires_at > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_nickname'] = $user['nickname'];
            
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("自动登录错误: " . $e->getMessage());
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($username) || empty($password)) {
        $error = "用户名和密码不能为空";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password, nickname FROM admin_accounts WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_nickname'] = $user['nickname'];
                
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + 30 * 24 * 60 * 60;
                    
                    $stmt = $pdo->prepare("INSERT INTO admin_remember_tokens (admin_id, token, expires_at) 
                                         VALUES (?, ?, FROM_UNIXTIME(?)) 
                                         ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = VALUES(expires_at)");
                    $stmt->execute([$user['id'], $token, $expires]);
                    
                    setcookie('remember_token', $token, $expires, '/', '', false, true);
                }
                
                header('Location: index.php');
                exit;
            } else {
                $error = "用户名或密码错误";
                sleep(1);
            }
        } catch (PDOException $e) {
            error_log("登录错误: " . $e->getMessage());
            $error = "系统错误，请稍后再试";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICP备案管理系统 - 登录</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .remember-me {
            margin: 15px 0;
            display: flex;
            align-items: center;
        }
        .remember-me input {
            margin-right: 8px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-header">
            <h1>ICP备案管理系统</h1>
            <p>欢迎回来，请登录您的账户</p>
        </div>
        
        <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">用户名</label>
                <input type="text" id="username" name="username" required placeholder="请输入用户名" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="form-group">
                <label for="password">密码</label>
                <input type="password" id="password" name="password" required placeholder="请输入密码">
            </div>
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">30天免登录</label>
            </div>
            <button type="submit" class="btn btn-primary">登录</button>
        </form>
        
        <div class="login-footer">
            <p>© <?php echo date('Y'); ?> ICP备案管理系统</p>
        </div>
    </div>
</body>
</html>