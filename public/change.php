<?php
require_once('../app/config/db.php');
require_once '../app/config/function.php';
$page = 'change';
include('header.php');

session_start();

$siteSettings = getSiteSettings();
if(!$siteSettings) {
    die("无法获取站点设置，请检查数据库或文件权限配置");
}

$currentIP = $_SERVER['REMOTE_ADDR'];

$errorMessage = '';
$searchResults = [];
$showVerificationForm = false;
$showChangeForm = false;
$selectedRecord = null;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $keyword = trim($_POST['keyword']);
    
    if (empty($keyword)) {
        $errorMessage = "请输入搜索关键词";
    } else {
        try {
            $sql = "SELECT * FROM icp_records WHERE 
                    site_domain LIKE :keyword OR 
                    icp_number LIKE :keyword OR 
                    email LIKE :keyword OR 
                    qq LIKE :keyword OR 
                    owner LIKE :keyword 
                    LIMIT 10";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':keyword' => "%$keyword%"]);
            $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($searchResults)) {
                $errorMessage = "没有找到匹配的备案信息";
            }
        } catch (PDOException $e) {
            $errorMessage = "数据库查询失败: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_record'])) {
    $icpNumber = $_POST['icp_number'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE icp_number = ?");
        $stmt->execute([$icpNumber]);
        $selectedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($selectedRecord) {
            $email = $selectedRecord['email'];
            $showVerificationForm = true;
        } else {
            $errorMessage = "备案记录不存在";
        }
    } catch (PDOException $e) {
        $errorMessage = "数据库查询失败: " . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_code'])) {
    $email = $_POST['email'];
    $verificationCode = $_POST['verification_code'];
    $icpNumber = $_POST['icp_number'];
    
    try {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);
        if (REDIS_PASS) {
            $redis->auth(REDIS_PASS);
        }
        
        $storedCode = $redis->get(REDIS_PREFIX . 'email_code_' . $email);
        
        if (!$storedCode || $storedCode !== $verificationCode) {
            $errorMessage = "验证码错误或已过期";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE icp_number = ?");
            $stmt->execute([$icpNumber]);
            $selectedRecord = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($selectedRecord) {
                $showChangeForm = true;
                $showVerificationForm = false;
            } else {
                $errorMessage = "备案记录不存在";
            }
        }
    } catch (Exception $e) {
        $errorMessage = "验证服务暂时不可用";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_record'])) {
    $icpNumber = $_POST['icp_number'];
    $action = $_POST['update_record'];
    $currentIP = $_SERVER['REMOTE_ADDR'];
    $currentTime = date('Y-m-d H:i:s');

    try {
        if ($action === 'update') {
            $requiredFields = [
                'site_title' => '网站名称',
                'site_domain' => '网站域名',
                'owner' => '所有者',
                'email' => '邮箱'
            ];
            
            $errors = [];
            foreach ($requiredFields as $field => $name) {
                if (empty($_POST[$field])) {
                    $errors[] = "$name 不能为空";
                }
            }
            
            if (isset($_POST['site_title']) && mb_strlen($_POST['site_title']) < 2) {
                $errors[] = "网站名称至少需要2个字符";
            }
            
            if (isset($_POST['site_domain'])) {
                if (preg_match('/^https?:\/\//i', $_POST['site_domain'])) {
                    $errors[] = "域名不能包含http://或https://";
                }
                if (strpos($_POST['site_domain'], '.') === false) {
                    $errors[] = "域名必须包含点(.)";
                }
            }
            
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "邮箱格式不正确";
            }
            
            if (!empty($_POST['qq']) && !preg_match('/^\d{5,12}$/', $_POST['qq'])) {
                $errors[] = "QQ号码格式不正确";
            }
            
            if (!empty($_POST['site_avatar'])) {
                if (!preg_match('/^https?:\/\//i', $_POST['site_avatar'])) {
                    $errors[] = "头像URL必须以http://或https://开头";
                }
                if (strpos($_POST['site_avatar'], '.') === false) {
                    $errors[] = "头像URL必须包含点(.)";
                }
            }
            
            if (empty($errors)) {
                $data = [
                    'site_title' => $_POST['site_title'],
                    'site_description' => $_POST['site_description'] ?? '',
                    'site_domain' => $_POST['site_domain'],
                    'site_avatar' => $_POST['site_avatar'] ?? '',
                    'owner' => $_POST['owner'],
                    'email' => $_POST['email'],
                    'qq' => $_POST['qq'] ?? null,
                    'status' => 'pending',
                    'update_time' => $currentTime,
                    'submit_ip' => $currentIP,
                    'icp_number' => $icpNumber
                ];
                
                $changeData = [
                    'icp_number' => $icpNumber,
                    'site_title' => $data['site_title'],
                    'site_description' => $data['site_description'],
                    'site_domain' => $data['site_domain'],
                    'site_avatar' => $data['site_avatar'],
                    'owner' => $data['owner'],
                    'update_time' => $currentTime,
                    'email' => $data['email'],
                    'qq' => $data['qq'],
                    'remark' => '信息变更',
                    'submit_ip' => $currentIP
                ];
                
                $pdo->beginTransaction();
                
                try {
                    $stmt = $pdo->prepare("UPDATE icp_records SET 
                        site_title = :site_title,
                        site_description = :site_description,
                        site_domain = :site_domain,
                        site_avatar = :site_avatar,
                        owner = :owner,
                        email = :email,
                        qq = :qq,
                        status = :status,
                        update_time = :update_time,
                        submit_ip = :submit_ip
                        WHERE icp_number = :icp_number");
                    
                    $stmt->execute($data);
                    
                    $stmt = $pdo->prepare("INSERT INTO icp_changes 
                        (icp_number, site_title, site_description, site_domain, site_avatar, owner, update_time, email, qq, remark, submit_ip)
                        VALUES 
                        (:icp_number, :site_title, :site_description, :site_domain, :site_avatar, :owner, :update_time, :email, :qq, :remark, :submit_ip)");
                    
                    $stmt->execute($changeData);
                    
                    $pdo->commit();
                    
                    header("Location: xg.php?keyword=" . urlencode($icpNumber));
                    exit;
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    throw $e;
                }
            } else {
                $errorMessage = implode("<br>", $errors);
                $selectedRecord = $_POST;
                $showChangeForm = true;
            }
        } elseif ($action === 'delete') {
            $pdo->beginTransaction();
            
            try {
                $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE icp_number = ?");
                $stmt->execute([$icpNumber]);
                $record = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($record) {
                    $stmt = $pdo->prepare("INSERT INTO icp_changes 
                        (icp_number, site_title, site_description, site_domain, site_avatar, owner, update_time, email, qq, remark, submit_ip)
                        VALUES 
                        (:icp_number, :site_title, :site_description, :site_domain, :site_avatar, :owner, :update_time, :email, :qq, :remark, :submit_ip)");
                    
                    $changeData = [
                        'icp_number' => $record['icp_number'],
                        'site_title' => $record['site_title'],
                        'site_description' => $record['site_description'],
                        'site_domain' => $record['site_domain'],
                        'site_avatar' => $record['site_avatar'],
                        'owner' => $record['owner'],
                        'update_time' => $currentTime,
                        'email' => $record['email'],
                        'qq' => $record['qq'],
                        'remark' => '备案注销',
                        'submit_ip' => $currentIP
                    ];
                    
                    $stmt->execute($changeData);
                    
                    $stmt = $pdo->prepare("DELETE FROM icp_records WHERE icp_number = ?");
                    $stmt->execute([$icpNumber]);
                    
                    $pdo->commit();
                    
                    header("Location: xg.php?keyword=" . urlencode($icpNumber) . "&action=delete");
                    exit;
                } else {
                    $errorMessage = "备案记录不存在";
                    $pdo->rollBack();
                }
            } catch (PDOException $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    } catch (PDOException $e) {
        $errorMessage = "数据库操作失败: " . $e->getMessage();
        error_log('[' . date('Y-m-d H:i:s') . '] Database Error: ' . $e->getMessage() . "\n", 3, '../app/logs/db_error.log');
    } catch (Exception $e) {
        $errorMessage = "系统错误: " . $e->getMessage();
        error_log('[' . date('Y-m-d H:i:s') . '] System Error: ' . $e->getMessage() . "\n", 3, '../app/logs/system_error.log');
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>备案变更 — <?php echo htmlspecialchars($maintitle); ?></title>
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/change.css">
    <style>
        <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>备案信息变更</h1>
            <p>修改或注销您的备案信息</p>
        </header>

        <?php if (!empty($errorMessage)): ?>
            <div class="error-message">
                <?= $errorMessage ?>
            </div>
        <?php endif; ?>

        <?php if (!$showVerificationForm && !$showChangeForm): ?>
            <div class="search-container">
                <form method="post">
                    <div class="search-box">
                        <input type="text" class="search-input" name="keyword" placeholder="输入域名、备案号、QQ、邮箱或所有者" required>
                        <button type="submit" name="search" class="search-btn">搜索</button>
                    </div>
                </form>
            </div>

            <?php if (!empty($searchResults)): ?>
                <div class="results-container">
                    <h2 class="results-title">搜索结果</h2>
                    <div class="results-grid">
                        <?php foreach ($searchResults as $record): ?>
                            <div class="record-card">
                                <div class="card-header">
                                    <h3><?= htmlspecialchars($record['site_title']) ?></h3>
                                    <span class="icp-number"><?= htmlspecialchars($record['icp_number']) ?></span>
                                </div>
                                <div class="card-body">
                                    <p><strong>域名:</strong> <?= htmlspecialchars($record['site_domain']) ?></p>
                                    <p><strong>所有者:</strong> <?= htmlspecialchars($record['owner']) ?></p>
                                    <p><strong>状态:</strong> 
                                        <span class="status-badge <?= $record['status'] ?>">
                                            <?= $record['status'] === 'approved' ? '已通过' : ($record['status'] === 'pending' ? '待审核' : '已驳回') ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="card-footer">
                                    <form method="post">
                                        <input type="hidden" name="icp_number" value="<?= htmlspecialchars($record['icp_number']) ?>">
                                        <button type="submit" name="select_record" class="select-btn">选择此备案</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($showVerificationForm && !$showChangeForm): ?>
            <div class="verification-container">
                <h2>验证邮箱</h2>
                <p class="verification-instruction">请向 <strong><?= htmlspecialchars($email) ?></strong> 发送验证码</p>
                
                <form method="post" class="verification-form">
                    <input type="hidden" name="icp_number" value="<?= htmlspecialchars($selectedRecord['icp_number']) ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">
                    
                    <div class="form-group">
                        <label for="verification_code">验证码</label>
                        <div class="code-input-group">
                            <input type="text" id="verification_code" name="verification_code" placeholder="输入6位验证码" required>
                            <button type="button" id="send-verify-btn" class="resend-btn">发送验证码</button>
                        </div>
                        <p class="form-hint">点击"发送验证码"按钮获取验证码</p>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="verify_code" class="verify-btn">验证</button>
                        <button type="button" id="cancel-verify" class="cancel-btn">取消</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <?php if ($showChangeForm && $selectedRecord): ?>
            <div class="change-form-container">
                <h2>修改备案信息</h2>
                <p class="form-instruction">您可以修改以下信息或选择注销备案</p>
                
                <form method="post" id="change-form">
                    <input type="hidden" name="icp_number" value="<?= htmlspecialchars($selectedRecord['icp_number']) ?>">
                    
                    <div class="form-section">
                        <h3 class="section-title">基本信息</h3>
                        <div class="form-group">
                            <label for="site_title">网站名称</label>
                            <input type="text" id="site_title" name="site_title" value="<?= htmlspecialchars($selectedRecord['site_title']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_domain">网站域名</label>
                            <input type="text" id="site_domain" name="site_domain" value="<?= htmlspecialchars($selectedRecord['site_domain']) ?>" required>
                            <p class="form-hint">不需要包含http://或https://</p>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_description">网站描述</label>
                            <textarea id="site_description" name="site_description"><?= htmlspecialchars($selectedRecord['site_description']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="site_avatar">网站头像URL</label>
                            <input type="url" id="site_avatar" name="site_avatar" value="<?= htmlspecialchars($selectedRecord['site_avatar']) ?>">
                            <p class="form-hint">请输入完整的图片URL地址，必须包含http://或https://</p>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3 class="section-title">所有者信息</h3>
                        <div class="form-group">
                            <label for="owner">所有者</label>
                            <input type="text" id="owner" name="owner" value="<?= htmlspecialchars($selectedRecord['owner']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">邮箱</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($selectedRecord['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="qq">QQ</label>
                            <input type="text" id="qq" name="qq" value="<?= htmlspecialchars($selectedRecord['qq']) ?>">
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="update_record" value="update" class="update-btn">提交变更</button>
                        <button type="submit" name="update_record" value="delete" class="delete-btn" onclick="return confirm('确定要注销此备案吗？此操作不可撤销！')">注销备案</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>

<script>
function showToast(message, type = 'info', duration = 3000) {
    const existingToast = document.getElementById('custom-toast');
    if (existingToast) {
        existingToast.remove();
    }

    const toast = document.createElement('div');
    toast.id = 'custom-toast';
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%)';
    toast.style.padding = '16px 24px';
    toast.style.borderRadius = '8px';
    toast.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
    toast.style.zIndex = '9999';
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.justifyContent = 'center';
    toast.style.fontFamily = 'system-ui, -apple-system, sans-serif';
    toast.style.fontSize = '14px';
    toast.style.fontWeight = '500';
    toast.style.lineHeight = '1.5';
    toast.style.transition = 'all 0.3s ease';
    toast.style.opacity = '0';
    toast.style.maxWidth = '90%';
    toast.style.textAlign = 'center';
    toast.style.wordBreak = 'break-word';

    const colors = {
        success: {
            bg: 'linear-gradient(135deg, #4CAF50 0%, #81C784 100%)',
            icon: 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z'
        },
        error: {
            bg: 'linear-gradient(135deg, #F44336 0%, #E57373 100%)',
            icon: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z'
        },
        info: {
            bg: 'linear-gradient(135deg, #2196F3 0%, #64B5F6 100%)',
            icon: 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z'
        },
        warning: {
            bg: 'linear-gradient(135deg, #FF9800 0%, #FFB74D 100%)',
            icon: 'M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z'
        }
    };

    const selectedColor = colors[type] || colors.info;
    toast.style.background = selectedColor.bg;
    toast.style.color = 'white';

    const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    iconSvg.setAttribute('viewBox', '0 0 24 24');
    iconSvg.setAttribute('width', '20');
    iconSvg.setAttribute('height', '20');
    iconSvg.style.marginRight = '8px';
    iconSvg.style.flexShrink = '0';

    const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    iconPath.setAttribute('d', selectedColor.icon);
    iconPath.setAttribute('fill', 'currentColor');
    iconSvg.appendChild(iconPath);

    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;

    toast.appendChild(iconSvg);
    toast.appendChild(messageSpan);
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(-50%) translateY(0)';
    }, 10);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(-50%) translateY(-20px)';
        setTimeout(() => toast.remove(), 300);
    }, duration);
}

(function() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes toastSlideIn {
            0% {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            100% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }
        
        @keyframes toastSlideOut {
            0% {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
            100% {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
        }
        
        #security-toast {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #b2a1ff 0%, #89cff0 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            font-family: 'Poppins', 'Microsoft YaHei', sans-serif;
            text-align: center;
            opacity: 0;
            animation: toastSlideIn 0.3s ease forwards;
            display: flex;
            align-items: center;
            line-height: 1.6;
            transition: opacity 0.3s ease;
            border: 1px solid rgba(255,255,255,0.3);
        }
        
        #security-toast.hide {
            animation: toastSlideOut 0.3s ease forwards !important;
        }
        
        #devtools-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(30, 41, 59, 0.95);
            z-index: 10000;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            color: #f8fafc;
            font-family: 'Poppins', 'Microsoft YaHei', sans-serif;
            animation: fadeIn 0.3s ease-out;
            padding: 20px;
            box-sizing: border-box;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        #devtools-modal .modal-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 
                        0 4px 6px -2px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        #devtools-modal h2 {
            background: linear-gradient(135deg, #b2a1ff 0%, #89cff0 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 1.8em;
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        #devtools-modal .icon {
            font-size: 3em;
            margin-bottom: 20px;
            color: #b2a1ff;
        }
        
        #devtools-modal p {
            color: #64748b;
            font-size: 1.1em;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        
        #devtools-modal button {
            background: linear-gradient(135deg, #b2a1ff 0%, #89cff0 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 1em;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        #devtools-modal button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 
                        0 4px 6px -2px rgba(0, 0, 0, 0.05);
            opacity: 0.9;
        }
    `;
    document.head.appendChild(style);

    let originalBodyContent = null;

    function showToast(message) {
        let toast = document.getElementById('security-toast');
        
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'security-toast';
            document.body.appendChild(toast);
        } else {
            toast.classList.remove('hide');
        }
        
        toast.innerHTML = `
            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            ${message}
        `;
        
        toast.style.animation = 'none';
        void toast.offsetHeight;
        toast.style.animation = 'toastSlideIn 0.3s ease forwards';
        
        setTimeout(() => {
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }

    function showDevToolsWarning() {
        if (!originalBodyContent) {
            originalBodyContent = document.body.innerHTML;
        }
        
        document.body.innerHTML = `
            <div id="devtools-modal">
                <div class="modal-card">
                    <div class="icon">
                        <svg viewBox="0 0 24 24" width="60" height="60" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <h2>安全警告</h2>
                    <p>检测到开发者工具已打开<br>请关闭开发者工具后刷新页面以继续浏览</p>
                    <button onclick="window.location.reload()">我已关闭，刷新页面</button>
                </div>
            </div>
        `;
        
        document.addEventListener('keydown', blockAllKeys, true);
    }

    function blockAllKeys(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
        return false;
    }

    document.addEventListener('contextmenu', function(e) {
        if (!['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) {
            e.preventDefault();
            showToast('右键菜单已禁用');
        }
    }, true);

    const blockedKeys = {
        'F12': 123,
        'I': 73,
        'J': 74,
        'C': 67,
        'U': 85
    };
    
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName === 'BODY' || e.target === document) {
            const isDevToolKey = (e.ctrlKey && e.shiftKey && blockedKeys[e.keyCode]) || 
                               (e.keyCode === blockedKeys.F12);
            
            if (isDevToolKey) {
                e.preventDefault();
                showToast('开发者工具快捷键已禁用');
                return false;
            }
            
            if (e.ctrlKey && !e.shiftKey && e.keyCode === blockedKeys.U) {
                e.preventDefault();
                showToast('查看源代码功能已禁用');
                return false;
            }
        }
    }, true);

    console.log('%c⚠️ 安全警告 ⚠️', 'color:red;font-size:20px;font-weight:bold;');
    console.log('%c如果您不了解这些内容，请勿在此处执行任何操作！', 'color:black;font-size:14px;');
    console.log('%c此浏览器功能专为开发人员设计。', 'color:red;font-size:14px;');
})();

document.addEventListener('DOMContentLoaded', function() {
    const sendVerifyBtn = document.getElementById('send-verify-btn');
    
    if (sendVerifyBtn) {
        sendVerifyBtn.addEventListener('click', function() {
            const email = document.querySelector('input[name="email"]').value;
            
            if (!email) {
                showToast('邮箱地址无效', 'error');
                return;
            }
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showToast('邮箱格式不正确', 'error');
                return;
            }
            
            const originalText = sendVerifyBtn.textContent;
            
            sendVerifyBtn.disabled = true;
            sendVerifyBtn.textContent = '发送中...';
            
            fetch('evc.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `email=${encodeURIComponent(email)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('验证码已发送到您的邮箱', 'success');
                    
                    let countdown = 60;
                    sendVerifyBtn.textContent = `重新发送(${countdown})`;
                    
                    const timer = setInterval(() => {
                        countdown--;
                        sendVerifyBtn.textContent = `重新发送(${countdown})`;
                        
                        if (countdown <= 0) {
                            clearInterval(timer);
                            sendVerifyBtn.disabled = false;
                            sendVerifyBtn.textContent = originalText;
                        }
                    }, 1000);
                } else {
                    showToast(data.message || '发送失败', 'error');
                    sendVerifyBtn.disabled = false;
                    sendVerifyBtn.textContent = originalText;
                }
            })
            .catch(error => {
                showToast('请求失败，请稍后重试', 'error');
                sendVerifyBtn.disabled = false;
                sendVerifyBtn.textContent = originalText;
            });
        });
    }
    
    const cancelBtn = document.getElementById('cancel-verify');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function() {
            window.location.href = 'change.php';
        });
    }
    
    const deleteBtn = document.querySelector('button[value="delete"]');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            if (!confirm('确定要注销此备案吗？此操作不可撤销！')) {
                e.preventDefault();
            }
        });
    }
    
    const changeForm = document.getElementById('change-form');
    if (changeForm) {
        changeForm.addEventListener('submit', function(e) {
            
            const siteName = document.getElementById('site_title');
            if (siteName && siteName.value.length < 2) {
                showToast('网站名称至少需要2个字符', 'error');
                e.preventDefault();
                return;
            }
            
            
            const siteDomain = document.getElementById('site_domain');
            if (siteDomain) {
                if (siteDomain.value.includes('http://') || siteDomain.value.includes('https://')) {
                    showToast('域名不能包含http://或https://', 'error');
                    e.preventDefault();
                    return;
                }
                
                if (!siteDomain.value.includes('.')) {
                    showToast('域名必须包含点(.)', 'error');
                    e.preventDefault();
                    return;
                }
            }
        });
    }
});
</script>
    
    <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>