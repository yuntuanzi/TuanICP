<?php
require_once('../app/config/db.php');
require_once '../app/config/function.php';
$page = 'reg';
include('header.php');

session_start();

$currentYear = date('Y');

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$siteSettings = getSiteSettings();

if(!$siteSettings) {
    die("无法获取站点设置，请检查数据库或文件权限配置");
}

$sitename = $siteSettings['site_name'];
$maintitle = $siteSettings['main_title'];
$subtitle = $siteSettings['sub_title'];
$logourl = $siteSettings['logo_url'];
$shortname = $siteSettings['short_name'];

if (!isset($_GET['number'])) {
    header("Location: xh.php");
    exit;
}

$icpNumber = $_GET['number'];

if (substr($icpNumber, 0, 4) != $currentYear) {
    header("Location: xh.php");
    exit;
}

$errorMessage = '';
$count = 0;
$currentTime = date('Y-m-d H:i:s');
$hackerDetected = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('/^\d{8}$/', $icpNumber)) {
        $errorMessage = "备案号格式不正确，请勿恶意修改参数，否则将被永久拉黑";
        $hackerDetected = true;
    } else {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM icp_records WHERE icp_number = ?");
            $stmt->execute([$icpNumber]);
            $count = $stmt->fetchColumn();
            
            if ($count > 0) {
                $errorMessage = "该备案号已被注册，请重新选号";
            } else {
                if (!empty($_POST['site-domain'])) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM icp_records WHERE site_domain = ?");
                    $stmt->execute([$_POST['site-domain']]);
                    $domainCount = $stmt->fetchColumn();
                    
                    if ($domainCount > 0) {
                        $errorMessage = "该域名已经登记备案，请更换域名。若存在异议，请联系$adminemail";
                    }
                }
                
                if (empty($errorMessage)) {
                    $requiredFields = [
                        'site-name' => '网站名称',
                        'site-domain' => '网站域名',
                        'site-desc' => '网站描述',
                        'owner' => '所有者',
                        'email' => '邮箱'
                    ];
                    
                    $errors = [];
                    foreach ($requiredFields as $field => $name) {
                        if (empty($_POST[$field])) {
                            $errors[] = "$name 不能为空";
                        }
                    }
                    
                    if (isset($_POST['site-name']) && mb_strlen($_POST['site-name']) < 2) {
                        $errors[] = "网站名称至少需要2个字符";
                        $hackerDetected = true;
                    }
                    
                    if (empty($_POST['site-desc'])) {
                        $errors[] = "网站描述不能为空";
                        $hackerDetected = true;
                    }
                    
                    if (isset($_POST['site-domain'])) {
                        if (preg_match('/^https?:\/\//i', $_POST['site-domain'])) {
                            $errors[] = "域名不能包含http://或https://";
                            $hackerDetected = true;
                        }
                        if (strpos($_POST['site-domain'], '.') === false) {
                            $errors[] = "域名必须包含点(.)";
                            $hackerDetected = true;
                        }
                    }
                    
                    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "邮箱格式不正确";
                        $hackerDetected = true;
                    }
                    
                    if (!empty($_POST['qq']) && !preg_match('/^\d{5,12}$/', $_POST['qq'])) {
                        $errors[] = "QQ号码格式不正确";
                        $hackerDetected = true;
                    }
                    
                    if (!empty($_POST['site-avatar'])) {
                        if (!preg_match('/^https?:\/\//i', $_POST['site-avatar'])) {
                            $errors[] = "头像URL必须以http://或https://开头";
                            $hackerDetected = true;
                        }
                        if (strpos($_POST['site-avatar'], '.') === false) {
                            $errors[] = "头像URL必须包含点(.)";
                            $hackerDetected = true;
                        }
                    }
                    
                    if (empty($_POST['agree'])) {
                        $errors[] = "必须同意用户协议和对接须知";
                        $hackerDetected = true;
                    }
                    
                    if (!isset($_POST['email_code']) || empty($_POST['email_code'])) {
                        $errors[] = "验证码不能为空";
                        $hackerDetected = true;
                    } else {
                        try {
                            $redis = new Redis();
                            $redis->connect(REDIS_HOST, REDIS_PORT);
                            if (REDIS_PASS) {
                                $redis->auth(REDIS_PASS);
                            }
                            
                            $storedCode = $redis->get(REDIS_PREFIX . 'email_code_' . $_POST['email']);
                            if (!$storedCode || $storedCode !== $_POST['email_code']) {
                                $errors[] = "验证码错误或已过期";
                                $hackerDetected = true;
                            }
                        } catch (Exception $e) {
                            $errors[] = "验证码验证服务暂时不可用";
                            error_log('[' . date('Y-m-d H:i:s') . '] Redis Error: ' . $e->getMessage() . "\n", 3, '../app/logs/redis_error.log');
                        }
                    }
                    
                    if (empty($errors)) {
                        $data = [
                            'icp_number' => $icpNumber,
                            'site_title' => $_POST['site-name'],
                            'site_description' => $_POST['site-desc'] ?? '',
                            'site_domain' => $_POST['site-domain'],
                            'site_avatar' => $_POST['site-avatar'] ?? '',
                            'owner' => $_POST['owner'],
                            'update_time' => $currentTime,
                            'email' => $_POST['email'],
                            'qq' => $_POST['qq'] ?? null,
                            'status' => 'pending',
                            'remark' => '',
                            'inspection_status' => 'normal',
                            'ping_delay' => null,
                            'submit_ip' => $_SERVER['REMOTE_ADDR']
                        ];
                        
                        $columns = implode(', ', array_keys($data));
                        $placeholders = ':' . implode(', :', array_keys($data));
                        
                        $stmt = $pdo->prepare("INSERT INTO icp_records ($columns) VALUES ($placeholders)");
                        $stmt->execute($data);

                        $adminemail = 'yun@yuncheng.fun';
                        
                        $mailSubject = "新的ICP备案申请通知 - " . htmlspecialchars($icpNumber);
                        $mailContent = "
                        <h2>新的ICP备案申请通知</h2>
                        <p>您收到一条新的ICP备案申请，请尽快审核：</p>
                        
                        <table border='1' cellpadding='5' cellspacing='0'>
                            <tr>
                                <th>备案号</th>
                                <td>{$icpNumber}</td>
                            </tr>
                            <tr>
                                <th>网站名称</th>
                                <td>" . htmlspecialchars($_POST['site-name']) . "</td>
                            </tr>
                            <tr>
                                <th>网站域名</th>
                                <td>" . htmlspecialchars($_POST['site-domain']) . "</td>
                            </tr>
                            <tr>
                                <th>网站描述</th>
                                <td>" . htmlspecialchars($_POST['site-desc']) . "</td>
                            </tr>
                            <tr>
                                <th>所有者</th>
                                <td>" . htmlspecialchars($_POST['owner']) . "</td>
                            </tr>
                            <tr>
                                <th>联系方式</th>
                                <td>
                                    邮箱: " . htmlspecialchars($_POST['email']) . "<br>
                                    QQ: " . (isset($_POST['qq']) ? htmlspecialchars($_POST['qq']) : '未提供') . "
                                </td>
                            </tr>
                            <tr>
                                <th>提交时间</th>
                                <td>{$currentTime}</td>
                            </tr>
                            <tr>
                                <th>提交IP</th>
                                <td>{$_SERVER['REMOTE_ADDR']}</td>
                            </tr>
                        </table>
                        
                        <p>请登录管理后台处理此申请：<a href='".(isset($_SERVER['HTTPS']) ? 'https://' : 'http://')."{$_SERVER['HTTP_HOST']}/admin/'>管理后台</a></p>
                        ";
                        
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type: text/html; charset=utf-8\r\n";
                        $headers .= "From: ICP备案系统 <noreply@{$_SERVER['HTTP_HOST']}>\r\n";
                        $headers .= "X-Mailer: PHP/" . phpversion();
                        
                        @mail($adminemail, $mailSubject, $mailContent, $headers);

                        header("Location: xg.php?keyword=" . urlencode($icpNumber));
                        exit;
                    } else {
                        $errorMessage = implode("<br>", $errors);
                    }
                }
            }
        } catch (PDOException $e) {
            $errorMessage = "数据库操作失败，请稍后再试";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>备案登记 — <?php echo htmlspecialchars($maintitle); ?></title>
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/reg.css">
    <style>
        .error-message {
            color: #ff4444;
            background-color: #ffeeee;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            border-left: 4px solid #ff4444;
        }
        .notice-error {
            color: #ff4444;
            margin: 10px 0;
        }
        .form-hint.error {
            color: #ff4444;
        }
        #captcha {
            display: inline-block;
            margin-bottom: 15px;
        }
        .btn {
            display: inline-block;
            box-sizing: border-box;
            border: 1px solid #cccccc;
            border-radius: 2px;
            width: 100px;
            height: 40px;
            line-height: 40px;
            font-size: 16px;
            color: #666;
            cursor: pointer;
            background: white linear-gradient(180deg, #ffffff 0%, #f3f3f3 100%);
        }
        .btn:hover {
            background: white linear-gradient(0deg, #ffffff 0%, #f3f3f3 100%)
        }
    </style>
    <?php echo ($headerhtml); ?>
    <style>
    <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?php echo htmlspecialchars($maintitle); ?></h1>
            <p><?php echo htmlspecialchars($subtitle); ?></p>
        </header>

        <div class="form-container">
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <?= $errorMessage ?>
                </div>
            <?php endif; ?>
            
            <form id="icp-form" method="post">
                <table class="form-table">
                    <tr class="hidden-field">
                        <th>ICP备案号</th>
                        <td>
                            <input type="text" class="form-input" id="icp-number" name="icp-number" value="<?= htmlspecialchars($icpNumber) ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="site-name">网站名称</label></th>
                        <td>
                            <input type="text" class="form-input" id="site-name" name="site-name" placeholder="请输入网站名称" required value="<?= htmlspecialchars($_POST['site-name'] ?? '') ?>">
                            <span class="form-hint">请填写您的网站名称</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="site-domain">网站域名</label></th>
                        <td>
                            <input type="text" class="form-input" id="site-domain" name="site-domain" placeholder="example.com" required value="<?= htmlspecialchars($_POST['site-domain'] ?? '') ?>">
                            <span class="form-hint">不需要包含http://或https://</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="site-desc">网站描述</label></th>
                        <td>
                            <textarea class="form-input" id="site-desc" name="site-desc" placeholder="请简要描述您的网站内容"><?= htmlspecialchars($_POST['site-desc'] ?? '') ?></textarea>
                            <span class="form-hint">不建议字数过多，20字以内最佳</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="site-avatar">网站头像URL</label></th>
                        <td>
                            <input type="url" class="form-input" id="site-avatar" name="site-avatar" placeholder="https://example.com/avatar.png" value="<?= htmlspecialchars($_POST['site-avatar'] ?? '') ?>">
                            <span class="form-hint">请输入完整的图片URL地址，必须包含http://或https://</span>
                            <span class="form-hint">推荐好用的图床：https://imgse.koxiuqiu.cc/</span>
                            <span class="form-hint">如果一定要用自己的URL的话，请务必关闭防盗链！</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="owner">所有者</label></th>
                        <td>
                            <input type="text" class="form-input" id="owner" name="owner" placeholder="请输入所有者名称" required value="<?= htmlspecialchars($_POST['owner'] ?? '') ?>">
                        </td>
                    </tr>
                    <tr class="hidden-field">
                        <th>注册时间</th>
                        <td>
                            <input type="text" class="form-input" id="register-time" name="register-time" value="<?= htmlspecialchars($currentTime) ?>" readonly>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="email">邮箱</label></th>
                        <td>
                            <div class="email-verify-group">
                                <input type="email" class="form-input verify-input" id="email" name="email" placeholder="your@email.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                <button type="button" class="send-verify-btn" id="send-verify-btn">发送验证码</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="email_code">验证码</label></th>
                        <td>
                            <input type="text" class="form-input" id="email_code" name="email_code" placeholder="请输入6位验证码" required>
                            <span class="form-hint">验证码已发送至您的邮箱，有效期5分钟</span>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="qq">QQ</label></th>
                        <td>
                            <input type="text" class="form-input" id="qq" name="qq" placeholder="请输入QQ号码" value="<?= htmlspecialchars($_POST['qq'] ?? '') ?>">
                            <span class="form-hint">非必填，但是我们希望在发现备案信息异常时，能够及时与您取得联系，以免造成备案信息驳回。</span>
                        </td>
                    </tr>
                </table>
                <div class="agreement-container">
                    <input type="checkbox" id="agree-checkbox" name="agree" class="agree-checkbox" <?= !empty($_POST['agree']) ? 'checked' : '' ?>>
                    <label for="agree-checkbox" class="agree-label">
                        我已经认真阅读并且承诺遵守
                        <a href="xy.php" target="_blank" class="agreement-link">《用户协议》</a>和
                        <a href="xz.php" target="_blank" class="agreement-link">《对接须知》</a>
                    </label>
                </div>
                <div class="agreement-error" id="agreement-error"></div>
                
                <div class="form-notice">
                    <?php if (isset($count) && $count > 0): ?>
                        <p class="notice-error">该备案号已被注册，请<a href="xh.php">重新选号</a></p>
                    <?php endif; ?>
                    
                    <?php if (!preg_match('/^\d{8}$/', $icpNumber)): ?>
                        <p class="notice-error">备案号格式不正确，请勿恶意修改参数，否则将被永久拉黑</p>
                    <?php endif; ?>
                </div>
            
                <button type="submit" class="submit-btn">提交备案申请</button>
            </form>
        </div>
    </div>

    <script>
    // Enhanced Toast Notification
    function showToast(message, type = 'info', duration = 3000) {
        // Remove existing toast if any
        const existingToast = document.getElementById('custom-toast');
        if (existingToast) {
            existingToast.remove();
        }

        // Create toast element
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
        const sendBtn = document.getElementById('send-verify-btn');
        const emailInput = document.getElementById('email');
        
        if (sendBtn && emailInput) {
            sendBtn.addEventListener('click', function() {
                const email = emailInput.value.trim();
                
                if (!email) {
                    showToast('请输入邮箱地址', 'error');
                    return;
                }
                
                if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    showToast('邮箱格式不正确', 'error');
                    return;
                }
                
                const originalText = sendBtn.textContent;
                
                sendBtn.disabled = true;
                sendBtn.textContent = '发送中...';
                
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
                        sendBtn.textContent = `重新发送(${countdown})`;
                        
                        const timer = setInterval(() => {
                            countdown--;
                            sendBtn.textContent = `重新发送(${countdown})`;
                            
                            if (countdown <= 0) {
                                clearInterval(timer);
                                sendBtn.disabled = false;
                                sendBtn.textContent = originalText;
                            }
                        }, 1000);
                    } else {
                        showToast(data.message || '发送失败', 'error');
                        sendBtn.disabled = false;
                        sendBtn.textContent = originalText;
                    }
                })
                .catch(error => {
                    showToast('请求失败，请稍后重试', 'error');
                    sendBtn.disabled = false;
                    sendBtn.textContent = originalText;
                });
            });
        }
    });
    </script>

    <noscript>
        <meta http-equiv="refresh" content="0; url=xz.php" />
    </noscript>
    <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>