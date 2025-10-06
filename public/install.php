<?php
define('INSTALLING', true);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 检查是否已安装
$configExists = file_exists('config/db.php');
$lockExists = file_exists('config/installed.lock');

// 存在锁定文件或配置文件，显示提示信息
if ($configExists || $lockExists) {
    die('
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>ICP 安装向导 - 检测到已安装</title>
        <style>
            body {
                font-family: \'Segoe UI\', Tahoma, Geneva, Verdana, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f5f5f5;
            }
            .container {
                background-color: white;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                padding: 30px;
                margin-top: 20px;
                text-align: center;
            }
            h1 {
                color: #e74c3c;
            }
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid transparent;
                border-radius: 4px;
                background-color: #f2dede;
                border-color: #ebccd1;
                color: #a94442;
            }
            .btn {
                background-color: #3498db;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                text-decoration: none;
                display: inline-block;
                margin-top: 20px;
            }
            .btn:hover {
                background-color: #2980b9;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>系统已安装成功</h1>
            <div class="alert">
                <strong>系统检测到您已经安装过ICP系统！</strong>
                <p>如需重新安装，请先删除以下文件：</p>
                    '.($configExists ? 'app/config/db.php' : '').'
                    '.($lockExists ? 'app/config/installed.lock' : '').'
            </div>
            <a href="javascript:location.reload()" class="btn">我已删除，重新安装</a>
            <a href="/admin" class="btn">进入管理后台</a>
        </div>
    </body>
    </html>
    ');
}
// 错误报告设置
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 安装步骤
$step = isset($_GET['step']) ? intval($_GET['step']) : 1;
$mode = isset($_GET['mode']) ? $_GET['mode'] : ($configExists ? 'reconfig' : 'install');

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($step) {
        case 1:
            // 环境检查通过
            header('Location: install.php?step=2&mode=' . $mode);
            exit;
            
        case 2:
            // 保存数据库配置到session
            $_SESSION['db_host'] = $_POST['db_host'];
            $_SESSION['db_name'] = $_POST['db_name'];
            $_SESSION['db_user'] = $_POST['db_user'];
            $_SESSION['db_pass'] = $_POST['db_pass'];
            
            if ($mode === 'reconfig') {
                // 仅重新生成配置文件
                if (generateConfigFile()) {
                    header('Location: install.php?step=4&mode=reconfig');
                    exit;
                } else {
                    die('配置文件生成失败，请检查权限');
                }
            } else {
                header('Location: install.php?step=3');
                exit;
            }
            
        case 3:
            // 保存网站信息和管理员账户
            $_SESSION['site_name'] = $_POST['site_name'];
            $_SESSION['main_title'] = $_POST['main_title'];
            $_SESSION['sub_title'] = $_POST['sub_title'];
            $_SESSION['logo_url'] = $_POST['logo_url'];
            $_SESSION['short_name'] = $_POST['short_name'];
            $_SESSION['site_domain'] = $_POST['site_domain'];
            $_SESSION['admin_email'] = $_POST['admin_email'];
            $_SESSION['admin_qq'] = $_POST['admin_qq'];
            
            $_SESSION['admin_username'] = $_POST['admin_username'];
            $_SESSION['admin_password'] = password_hash($_POST['admin_password'], PASSWORD_DEFAULT);
            $_SESSION['admin_nickname'] = $_POST['admin_nickname'];
            $_SESSION['admin_email_account'] = $_POST['admin_email'];
            
            // 直接执行安装
            if (completeInstallation()) {
                header('Location: install.php?step=4');
                exit;
            } else {
                die('安装过程中出现错误，请检查权限或配置');
            }
    }
}

// 生成配置文件函数
function generateConfigFile() {
    // 创建配置文件
    $configContent = "<?php
// 数据库配置
define('DB_HOST', '{$_SESSION['db_host']}');
define('DB_NAME', '{$_SESSION['db_name']}');
define('DB_USER', '{$_SESSION['db_user']}');
define('DB_PASS', '{$_SESSION['db_pass']}');
define('DB_CHARSET', 'utf8mb4');

// Redis 配置
define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_PASS', '');
define('REDIS_PREFIX', 'icp_');
define('VERIFY_CODE_EXPIRE', 300); // 验证码有效期5分钟
";

    // 验证config目录存在
    if (!file_exists('config')) {
        if (!mkdir('config', 0755, true)) {
            return false;
        }
    }
    
    // 写入配置文件
    return file_put_contents('../app/config/db.php', $configContent) !== false;
}

// 完成安装的函数
function completeInstallation() {
    // 创建数据库连接
    try {
        $dsn = "mysql:host={$_SESSION['db_host']};charset=utf8mb4";
        $pdo = new PDO($dsn, $_SESSION['db_user'], $_SESSION['db_pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 创建数据库
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_SESSION['db_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
        $pdo->exec("USE `{$_SESSION['db_name']}`");
        
        // 导入SQL文件
        $sqlFile = __DIR__ . '/install/1.sql';
        if (!file_exists($sqlFile)) {
            die('SQL文件不存在: ' . $sqlFile);
        }
        
        $sql = file_get_contents($sqlFile);
        if (empty($sql)) {
            die('SQL文件为空或读取失败');
        }
        
        // 执行SQL
        $queries = explode(';', $sql);
        foreach ($queries as $query) {
            if (trim($query)) {
                $pdo->exec($query);
            }
        }
        
        // 插入网站设置
        $stmt = $pdo->prepare("UPDATE web_settings SET site_name = ?, main_title = ?, sub_title = ?, logo_url = ?, short_name = ?, admin_email = ?, admin_qq = ?, site_domain = ? WHERE id = 1");
        $stmt->execute([
            $_SESSION['site_name'],
            $_SESSION['main_title'],
            $_SESSION['sub_title'],
            $_SESSION['logo_url'],
            $_SESSION['short_name'],
            $_SESSION['admin_email'],
            $_SESSION['admin_qq'],
            $_SESSION['site_domain']
        ]);
        
        // 创建管理员账户
        $stmt = $pdo->prepare("INSERT INTO admin_accounts (username, email, password, nickname) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['admin_username'],
            $_SESSION['admin_email_account'],
            $_SESSION['admin_password'],
            $_SESSION['admin_nickname']
        ]);
        
        // 生成配置文件
        if (!generateConfigFile()) {
            return false;
        }
        
        // 创建安装锁定文件
        file_put_contents('../app/config/installed.lock', date('Y-m-d H:i:s'));
        
        return true;
    } catch (PDOException $e) {
        die('数据库错误: ' . $e->getMessage());
    }
}

// 检查函数
function checkRequirement($name, $condition, $errorMessage) {
    echo '<tr>';
    echo '<td>' . htmlspecialchars($name) . '</td>';
    echo '<td>' . ($condition ? '<span style="color: green;">✓ 支持</span>' : '<span style="color: red;">✗ 不支持</span>') . '</td>';
    echo '<td>' . ($condition ? '正常' : htmlspecialchars($errorMessage)) . '</td>';
    echo '</tr>';
    return $condition;
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICP 安装向导 - 步骤 <?php echo $step; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-top: 20px;
        }
        h1, h2, h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        h1 {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="password"],
        input[type="email"],
        input[type="url"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        button, .btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }
        button:hover, .btn:hover {
            background-color: #2980b9;
        }
        .btn-next {
            float: right;
        }
        .btn-prev {
            float: left;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        .requirements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .requirements-table th, .requirements-table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .requirements-table th {
            background-color: #f2f2f2;
        }
        .progress-bar {
            height: 20px;
            background-color: #f1f1f1;
            border-radius: 4px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .progress {
            height: 100%;
            background-color: #3498db;
            width: <?php echo ($step - 1) * 33.33; ?>%;
            transition: width 0.3s;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            background-color: #dff0d8;
            border-color: #d6e9c6;
            color: #3c763d;
        }
        .alert-info {
            background-color: #d9edf7;
            border-color: #bce8f1;
            color: #31708f;
        }
        .alert-warning {
            background-color: #fcf8e3;
            border-color: #faebcc;
            color: #8a6d3b;
        }
        .alert-danger {
            background-color: #f2dede;
            border-color: #ebccd1;
            color: #a94442;
        }
        .mode-selector {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .mode-option {
            margin-bottom: 10px;
        }
        .mode-option input {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>ICP 安装向导</h1>
        
        <?php if ($step == 1 && !$lockExists): ?>
<div class="mode-selector" style="background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%); border-radius: 8px; padding: 20px; margin-bottom: 30px; border: 1px solid rgba(149, 117, 205, 0.2); box-shadow: 0 4px 15px rgba(149, 117, 205, 0.1);">
    <h3 style="color: #6a5acd; margin-top: 0; padding-bottom: 10px; border-bottom: 1px dashed #c3b4e7; font-weight: 600;">选择安装模式</h3>
    
    <div class="mode-option" style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #6a5acd; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(106, 90, 205, 0.08);">
        <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 8px;">
            <input type="radio" id="mode-install" name="mode" value="install" <?php echo $mode === 'install' ? 'checked' : ''; ?> style="margin-right: 12px; accent-color: #6a5acd;">
            <span style="font-weight: 600; color: #6a5acd;">全新安装</span>
        </label>
        <p style="margin: 0; color: #666; font-size: 14px; padding-left: 28px;">适用于首次安装，将创建数据库并初始化所有数据。</p>
    </div>
    
    <div class="mode-option" style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #4b6cb7; transition: all 0.3s ease; box-shadow: 0 2px 8px rgba(75, 108, 183, 0.08);">
        <label style="display: flex; align-items: center; cursor: pointer; margin-bottom: 8px;">
            <input type="radio" id="mode-reconfig" name="mode" value="reconfig" <?php echo $mode === 'reconfig' ? 'checked' : ''; ?> style="margin-right: 12px; accent-color: #4b6cb7;">
            <span style="font-weight: 600; color: #4b6cb7;">仅重新生成配置文件</span>
        </label>
        <p style="margin: 0; color: #666; font-size: 14px; padding-left: 28px;">仅更新数据库配置文件，不修改数据库内容。</p>
    </div>
    
    <style>
        .mode-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 90, 205, 0.15);
        }
        
        #mode-install:checked ~ .mode-option[for="mode-install"],
        #mode-reconfig:checked ~ .mode-option[for="mode-reconfig"] {
            background-color: rgba(106, 90, 205, 0.05);
        }
    </style>
</div>
        <?php endif; ?>
        
        <div class="progress-bar">
            <div class="progress"></div>
        </div>
        
        <!-- 步骤1: 环境检查 -->
        <div class="step <?php echo $step == 1 ? 'active' : ''; ?>" id="step1">
            <h2>环境检查</h2>
            <p>在开始安装前，系统会检查您的服务器环境是否符合要求。</p>
            
            <table class="requirements-table">
                <thead>
                    <tr>
                        <th>项目</th>
                        <th>状态</th>
                        <th>说明</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $allPassed = true;
                    
                    // PHP版本检查
                    $phpVersion = phpversion();
                    $phpPassed = version_compare($phpVersion, '8.1.0', '>=');
                    $allPassed = $allPassed && checkRequirement(
                        'PHP 版本 (当前: ' . $phpVersion . ')',
                        $phpPassed,
                        '需要 PHP 8.1.0 或更高版本'
                    );
                    
                    // PDO MySQL 扩展检查
                    $pdoMysqlPassed = extension_loaded('pdo_mysql');
                    $allPassed = $allPassed && checkRequirement(
                        'PDO MySQL 扩展',
                        $pdoMysqlPassed,
                        '需要安装 PDO MySQL 扩展'
                    );
                    
                    // 文件权限检查
                    $configWritable = is_writable('config') || (!file_exists('config') && is_writable('..'));
                    $allPassed = $allPassed && checkRequirement(
                        'config 目录可写',
                        $configWritable,
                        'config 目录需要可写权限'
                    );
                    
                    // JSON 扩展检查
                    $jsonPassed = extension_loaded('json');
                    $allPassed = $allPassed && checkRequirement(
                        'JSON 扩展',
                        $jsonPassed,
                        '需要 JSON 扩展'
                    );
                    
                    // cURL 扩展检查
                    $curlPassed = extension_loaded('curl');
                    $allPassed = $allPassed && checkRequirement(
                        'cURL 扩展',
                        $curlPassed,
                        '需要 cURL 扩展'
                    );
                    ?>
                </tbody>
            </table>
            
            <?php if (!$allPassed): ?>
                <div class="alert alert-danger">
                    <strong>您的服务器环境不满足系统要求！</strong> 请解决上述问题后再继续安装。
                </div>
            <?php endif; ?>
            
            <div class="clearfix">
                <form method="post" class="<?php echo $allPassed ? '' : 'hidden'; ?>">
                    <input type="hidden" name="mode" value="<?php echo $mode; ?>">
                    <button type="submit" class="btn btn-next" <?php echo $allPassed ? '' : 'disabled'; ?>>继续</button>
                </form>
            </div>
        </div>
        
        <!-- 步骤2: 数据库配置 -->
        <div class="step <?php echo $step == 2 ? 'active' : ''; ?>" id="step2">
            <h2>数据库配置</h2>
            <p>请输入您的 MySQL 数据库信息。如果您不确定这些信息，请联系您的服务器管理员或主机提供商。</p>
            
            <form method="post">
                <div class="form-group">
                    <label for="db_host">数据库主机</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <small>通常是 localhost 或 127.0.0.1</small>
                </div>
                
                <div class="form-group">
                    <label for="db_name">数据库名称</label>
                    <input type="text" id="db_name" name="db_name" required>
                </div>
                
                <div class="form-group">
                    <label for="db_user">数据库用户名</label>
                    <input type="text" id="db_user" name="db_user" required>
                </div>
                
                <div class="form-group">
                    <label for="db_pass">数据库密码</label>
                    <input type="password" id="db_pass" name="db_pass">
                </div>
                
                <div class="clearfix">
                    <a href="install.php?step=1" class="btn btn-prev">上一步</a>
                    <button type="submit" class="btn btn-next"><?php echo $mode === 'reconfig' ? '生成配置文件' : '继续'; ?></button>
                </div>
            </form>
        </div>
        
        <!-- 步骤3: 网站设置 -->
        <div class="step <?php echo $step == 3 ? 'active' : ''; ?>" id="step3">
            <h2>网站设置</h2>
            <p>请填写您的网站基本信息和管理员账户信息。</p>
            
            <form method="post">
                <h3>网站信息</h3>
                
                <div class="form-group">
                    <label for="site_name">站点名称</label>
                    <input type="text" id="site_name" name="site_name" value="TuanICP" required>
                </div>
                
                <div class="form-group">
                    <label for="main_title">主标题</label>
                    <input type="text" id="main_title" name="main_title" value="云团子ICP备案中心" required>
                </div>
                
                <div class="form-group">
                    <label for="sub_title">副标题</label>
                    <input type="text" id="sub_title" name="sub_title" value="安全 • 可爱 • 高效的二次元虚拟备案">
                </div>
                
                <div class="form-group">
                    <label for="logo_url">LOGO URL</label>
                    <input type="text" id="logo_url" name="logo_url" value="favicon.ico">
                </div>
                
                <div class="form-group">
                    <label for="short_name">单字简称</label>
                    <input type="text" id="short_name" name="short_name" maxlength="1" value="团">
                    
                </div>
                
                <div class="form-group">
                    <label for="site_domain">网站域名</label>
                    <input type="text" id="site_domain" name="site_domain" value="icp.星.fun" required>
                    <small>例如: example.com (不包含http(s)://协议)</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_email">管理员邮箱</label>
                    <input type="email" id="admin_email" name="admin_email" value="ccssna@qq.com" required>
                </div>
                
                <div class="form-group">
                    <label for="admin_qq">管理员QQ</label>
                    <input type="text" id="admin_qq" name="admin_qq" required>
                </div>
                
                <h3>管理员账户</h3>
                
                <div class="form-group">
                    <label for="admin_username">登录用户名</label>
                    <input type="text" id="admin_username" name="admin_username" required>
                    <small>用于登录管理后台</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_password">登录密码</label>
                    <input type="password" id="admin_password" name="admin_password" required>
                    <small>请使用强密码</small>
                </div>
                
                <div class="form-group">
                    <label for="admin_nickname">显示昵称</label>
                    <input type="text" id="admin_nickname" name="admin_nickname" required>
                    <small>在系统中显示的名称</small>
                </div>
                
                <div class="clearfix">
                    <a href="install.php?step=2" class="btn btn-prev">上一步</a>
                    <button type="submit" class="btn btn-next">完成安装</button>
                </div>
            </form>
        </div>
        
        <!-- 步骤4: 安装完成 -->
        <div class="step <?php echo $step == 4 ? 'active' : ''; ?>" id="step4">
            <h2><?php echo $mode === 'reconfig' ? '配置文件已更新' : '安装完成'; ?></h2>
            
            <div class="alert alert-success">
                <strong>成功！</strong> <?php echo $mode === 'reconfig' ? '数据库配置文件已重新生成。' : 'ICP 已成功安装。'; ?>
            </div>
            
            <h3>下一步</h3>
            <ul>
                <li><a href="/admin/" target="_blank">访问管理后台</a></li>
                <li><a href="/" target="_blank">访问网站首页</a></li>
            </ul>
            
            <?php if ($mode === 'install'): ?>
            <div class="alert alert-warning">
                <strong>安全提示：</strong> 为了安全起见，请删除 install 目录或设置访问权限限制。
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($step == 1 && !$lockExists): ?>
    <script>
        // 处理安装模式选择
        document.querySelectorAll('input[name="mode"]').forEach(radio => {
            radio.addEventListener('change', function() {
                window.location.href = 'install.php?step=1&mode=' + this.value;
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>