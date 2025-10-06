<?php
if (!file_exists('../app/config/db.php')) {
    die('
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TuanICP系统尚未安装</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0ff 100%);
            color: #333;
        }
        .install-container {
            text-align: center;
            padding: 40px 50px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(102, 95, 239, 0.1);
            max-width: 500px;
            width: 90%;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        .install-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(102, 95, 239, 0.15);
        }
        h2 {
            color: #5e4bff;
            margin-bottom: 20px;
            font-weight: 600;
            font-size: 28px;
        }
        p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .install-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 12px 32px;
            background: linear-gradient(135deg, #6a5acd 0%, #4b4bff 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            transition: all 0.3s ease;
            font-weight: 500;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(106, 90, 205, 0.3);
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .install-btn:hover {
            background: linear-gradient(135deg, #5e4bff 0%, #3a3aff 100%);
            box-shadow: 0 6px 20px rgba(106, 90, 205, 0.4);
            transform: translateY(-2px);
        }
        .icon {
            font-size: 48px;
            color: #6a5acd;
            margin-bottom: 20px;
        }
        .highlight {
            color: #5e4bff;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="icon">⚠️</div>
        <h2>系统尚未安装</h2>
        <p>检测到系统尚未安装，请点击下方按钮进行安装<br><span class="highlight">(config/db.php文件不存在)</span></p>
        <a href="install.php" class="install-btn">开始安装向导</a>
    </div>
</body>
</html>
    ');
}

require_once '../app/config/db.php';
require_once '../app/config/function.php';

$page = 'index';

$error = '';
$keyword = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keyword = trim($_POST['keyword']);
    
    if (!empty($keyword)) {
        try {
            $stmt = $pdo->prepare("SELECT uid FROM icp_records WHERE icp_number = :keyword OR site_domain = :keyword");
            $stmt->bindParam(':keyword', $keyword);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                header("Location: id.php?keyword=" . urlencode($keyword));
                exit();
            } else {
                $error = '喵喵：不存在这个备案记录呦~';
            }
        } catch (PDOException $e) {
            $error = '查询出错啦: ' . $e->getMessage();
        }
    } else {
        $error = '请输入备案号或域名';
    }
}

include('header.php');
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($maintitle); ?> — <?php echo htmlspecialchars($subtitle); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($sitename); ?>, <?php echo htmlspecialchars($sitename); ?>备, <?php echo htmlspecialchars($maintitle); ?>, <?php echo htmlspecialchars($shortname); ?>ICP备">
    <meta name="description" content="哇，是谁家的小可爱？二次元虚拟备案系统，快来申请一个可爱的备案号挂在网站页脚叭~">
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/index.css">
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

        <div class="search-container">
            <form method="POST" action="index.php">
                <div class="search-box">
                    <input 
                        type="text" 
                        name="keyword"
                        class="search-input" 
                        placeholder="请输入备案号 or 域名"
                        value="<?= htmlspecialchars($keyword) ?>"
                        required
                    >
                    <button type="submit" class="search-btn">立即查询</button>
                </div>
                <?php if (!empty($error)): ?>
                    <p style="color: red; margin-top: 10px; text-align: center;"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <script>
console.log(`
 _________   ___  ___   ________   ________    ________   ___     
|\\___   ___\\|\\  \\|\\  \\ |\\   __  \\ |\\   ___  \\ |\\_____  \\ |\\  \\    
\\|___ \\  \\_|\\ \\  \\\\\\  \\\\ \\  \\|\\  \\\\ \\  \\\\ \\  \\ \\|___/  /|\\ \\  \\   
     \\ \\  \\  \\ \\  \\\\\\  \\\\ \\   __  \\\\ \\  \\\\ \\  \\    /  / / \\ \\  \\  
      \\ \\  \\  \\ \\  \\\\\\  \\\\ \\  \\ \\  \\\\ \\  \\\\ \\  \\  /  /_/__ \\ \\  \\ 
       \\ \\__\\  \\ \\_______\\\\ \\__\\ \\__\\\\ \\__\\\\ \\__\\|\\________\\\\ \\__\\
        \\|__|   \\|_______| \\|__|\\|__| \\|__| \\|__| \\|_______| \\|__|
本程序由团子开发
TuanICP系统需要正版授权
（您当前使用的为免费版，若付费购买，请退款并举报！）
未经许可不得二开/商用/转让/倒卖
https://github.com/yuntuanzi/TuanICP/`,
);
</script>
    <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>