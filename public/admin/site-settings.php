<?php
$title = "站点信息设置";
require('includes/header.php');

$stmt = $pdo->query("SELECT * FROM web_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $site_name = $_POST['site_name'] ?? '';
        $main_title = $_POST['main_title'] ?? '';
        $sub_title = $_POST['sub_title'] ?? '';
        $logo_url = $_POST['logo_url'] ?? '';
        $short_name = $_POST['short_name'] ?? '';
        $admin_email = $_POST['admin_email'] ?? '';
        $admin_qq = $_POST['admin_qq'] ?? '';
        $site_domain = $_POST['site_domain'] ?? '';

        $stmt = $pdo->prepare("UPDATE web_settings SET 
            site_name = ?, 
            main_title = ?, 
            sub_title = ?, 
            logo_url = ?, 
            short_name = ?, 
            admin_email = ?, 
            admin_qq = ?, 
            site_domain = ? 
            WHERE id = ?");
        
        $stmt->execute([
            $site_name,
            $main_title,
            $sub_title,
            $logo_url,
            $short_name,
            $admin_email,
            $admin_qq,
            $site_domain,
            $settings['id']
        ]);

        $_SESSION['success_message'] = "站点信息已成功更新！";
        
        header("Location: site-settings.php");
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
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
            <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
        站点信息设置
    </h2>
    <p style="
        margin: 0;
        opacity: 0.85;
        font-size: 0.95rem;
        color: #7a5a9a;
    ">
        在此修改网站的基本信息和显示设置
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
                        <label for="site_name">站点名称</label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                        <small class="form-text text-muted">站点名称</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="main_title">主标题</label>
                        <input type="text" class="form-control" id="main_title" name="main_title" 
                               value="<?php echo htmlspecialchars($settings['main_title'] ?? ''); ?>" required>
                        <small class="form-text text-muted">网站大标题</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="sub_title">副标题</label>
                        <input type="text" class="form-control" id="sub_title" name="sub_title" 
                               value="<?php echo htmlspecialchars($settings['sub_title'] ?? ''); ?>">
                        <small class="form-text text-muted">显示在主标题下方</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="logo_url">LOGO URL</label>
                        <input type="text" class="form-control" id="logo_url" name="logo_url" 
                               value="<?php echo htmlspecialchars($settings['logo_url'] ?? ''); ?>">
                        <small class="form-text text-muted">网站LOGO的完整URL地址</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="short_name">简称</label>
                        <input type="text" class="form-control" id="short_name" name="short_name" maxlength="5"
                               value="<?php echo htmlspecialchars($settings['short_name'] ?? ''); ?>">
                        <small class="form-text text-muted">ICP备案简称</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">管理员邮箱</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" 
                               value="<?php echo htmlspecialchars($settings['admin_email'] ?? ''); ?>" required>
                        <small class="form-text text-muted">用于接收系统通知的邮箱地址</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_qq">管理员QQ</label>
                        <input type="text" class="form-control" id="admin_qq" name="admin_qq" 
                               value="<?php echo htmlspecialchars($settings['admin_qq'] ?? ''); ?>">
                        <small class="form-text text-muted">QQ号码</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_domain">网站域名</label>
                        <input type="text" class="form-control" id="site_domain" name="site_domain" 
                               value="<?php echo htmlspecialchars($settings['site_domain'] ?? ''); ?>">
                        <small class="form-text text-muted">网站的主域名（不带http://或https://）</small>
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

<?php require('includes/footer.php'); ?>