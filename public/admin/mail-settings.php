<?php
$title = "系统设置 - SMTP邮件服务";
require('includes/header.php');

$stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings) {
    $settings = [
        'smtp_user' => '',
        'smtp_host' => '',
        'smtp_port' => '',
        'smtp_pass' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $smtp_user = $_POST['smtp_user'] ?? '';
        $smtp_host = $_POST['smtp_host'] ?? '';
        $smtp_port = $_POST['smtp_port'] ?? '';
        $smtp_pass = $_POST['smtp_pass'] ?? '';

        $stmt = $pdo->query("SELECT COUNT(*) FROM system_settings");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $stmt = $pdo->prepare("UPDATE system_settings SET 
                smtp_user = ?, 
                smtp_host = ?, 
                smtp_port = ?, 
                smtp_pass = ?");
            
            $stmt->execute([
                $smtp_user,
                $smtp_host,
                $smtp_port,
                $smtp_pass
            ]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO system_settings 
                (smtp_user, smtp_host, smtp_port, smtp_pass) 
                VALUES (?, ?, ?, ?)");
            
            $stmt->execute([
                $smtp_user,
                $smtp_host,
                $smtp_port,
                $smtp_pass
            ]);
        }

        $_SESSION['success_message'] = "SMTP设置已成功更新！";
        
        header("Location: mail-settings.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "更新失败: " . $e->getMessage();
    }
}
?>
<style>
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    
    .form-control:focus {
        border-color: #4285f4;
        outline: none;
        box-shadow: 0 0 0 2px rgba(66, 133, 244, 0.2);
    }
    
    label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-text {
        display: block;
        margin-top: 6px;
        color: #6c757d;
        font-size: 12px;
    }
    
    .form-submit {
        text-align: right;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    @media (max-width: 1200px) {
        .form-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        }
    }
    
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
    
    .password-toggle {
        position: relative;
    }
    
    .password-toggle .toggle-icon {
        position: absolute;
        right: 10px;
        top: 35px;
        cursor: pointer;
        color: #6c757d;
    }
</style>

<div class="admin-content">
    <div class="content-header" style="
        background: linear-gradient(135deg, #e2c2ff 0%, #ffd6f4 100%);
        padding: 1.8rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(194, 128, 255, 0.15);
        color: #5a3d7a;
        margin-bottom: 2rem;
        border-left: 5px solid #c38fff;
        backdrop-filter: blur(2px);
    ">
        <h2 style="
            margin: 0 0 0.6rem 0;
            font-size: 1.7rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            color: #6a3a8a;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px; color: #9a5fcc;">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            SMTP邮件服务设置
        </h2>
        <p style="
            margin: 0;
            opacity: 0.85;
            font-size: 0.95rem;
            color: #7a5a9a;
        ">
            配置系统发送邮件使用的SMTP服务参数<br>
            强烈建议使用阿里云企业邮，一些邮箱的发件会导致源站IP泄露(如QQ、163等)
        </p>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" style="
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        ">
            <i class="mdi mdi-check-circle" style="margin-right: 10px; font-size: 20px;"></i>
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="smtp_host">SMTP服务器</label>
                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                               value="<?php echo htmlspecialchars($settings['smtp_host'] ?? ''); ?>" required>
                        <small class="form-text text-muted">例如：smtp.example.com</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_port">SMTP端口</label>
                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                               value="<?php echo htmlspecialchars($settings['smtp_port'] ?? ''); ?>" required>
                        <small class="form-text text-muted">465(SSL)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="smtp_user">SMTP用户名</label>
                        <input type="text" class="form-control" id="smtp_user" name="smtp_user" 
                               value="<?php echo htmlspecialchars($settings['smtp_user'] ?? ''); ?>" required>
                        <small class="form-text text-muted">完整的邮箱地址</small>
                    </div>
                    
                    <div class="form-group password-toggle">
                        <label for="smtp_pass">SMTP密码</label>
                        <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" 
                               value="<?php echo htmlspecialchars($settings['smtp_pass'] ?? ''); ?>" required>
                        <i class="mdi mdi-eye-off toggle-icon" id="togglePassword"></i>
                        <small class="form-text text-muted">SMTP服务密码</small>
                    </div>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 16px;">
                        <i class="mdi mdi-content-save" style="margin-right: 8px;"></i>保存设置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('smtp_pass');
        const icon = this;
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('mdi-eye-off');
            icon.classList.add('mdi-eye');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('mdi-eye');
            icon.classList.add('mdi-eye-off');
        }
    });
</script>

<?php require('includes/footer.php'); ?>