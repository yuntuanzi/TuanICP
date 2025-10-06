<?php
require_once '../app/config/db.php';
require_once '../app/config/function.php';
$page = 'about';
include('header.php');
try {
    $stmt = $pdo->query("SELECT site_name, short_name FROM web_settings LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);
        $site_name = $settings['site_name'] ?? $site_name;
        $short_name = $settings['short_name'] ?? $short_name;
    }
    
    $stmt = $pdo->query("SELECT nickname FROM admin_accounts ORDER BY id ASC LIMIT 1");
    if ($stmt->rowCount() > 0) {
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);
        $admin_nickname = $admin['nickname'] ?? $admin_nickname;
    }
} catch (PDOException $e) {
    error_log('数据库查询错误: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>关于<?php echo htmlspecialchars($maintitle); ?></title>
    <meta name="keywords" content="关于<?php echo htmlspecialchars($sitename); ?>, 关于<?php echo htmlspecialchars($sitename); ?>备, 关于<?php echo htmlspecialchars($maintitle); ?>, 关于<?php echo htmlspecialchars($shortname); ?>ICP备">
    <meta name="description" content="关于<?= htmlspecialchars($sitename) ?>，二次元虚拟备案系统">
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/about.css">
    <?php echo ($headerhtml); ?>
    <style>
    <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    <div class="container">
        <div class="about-container">
            <div class="about-header">
                <h1><?php echo htmlspecialchars($maintitle); ?></h1>
                <p><?php echo htmlspecialchars($subtitle); ?></p>
            </div>
            
            <div class="about-section floating">
                <div class="about-content">
                    <h2>什么是<?= htmlspecialchars($short_name) ?>ICP备？</h2>
                    <p><span class="highlight"><?= htmlspecialchars($admin_nickname) ?></span>去野餐时爱(I)吃(C)苹果派(P),需要提前准备。
                    俗称"<span class="highlight"><?= htmlspecialchars($short_name) ?>ICP备</span>",简称"<span class="highlight"><?= htmlspecialchars($short_name) ?>备</span>"</p>
                </div>
            </div>
            
            <div class="about-section">
                <div class="about-content">
                    <h2>为什么加入<?= htmlspecialchars($short_name) ?>备</h2>
                    <p><span class="highlight"><?= htmlspecialchars($short_name) ?>ICP备</span>，是众多<span class="highlight"><?= htmlspecialchars($short_name) ?>友</span>的网站都聚在一起可可爱爱！</p>
                    <p>是一种异次元中的爱好，可爱和个性的展现</p>
                    <p>将<span class="highlight"><?= htmlspecialchars($short_name) ?>备</span>放在博客页脚，是很好看的装饰喔</p>
                    
                    <div class="quote-box">
                        <p class="quote-text">欢迎各大喜爱<?= htmlspecialchars($short_name) ?>的站长加入鸭~<br>快来给自己的网站加上个可爱的<?= htmlspecialchars($short_name) ?>ICP号</p>
                        <p class="quote-author">—— <?= htmlspecialchars($admin_nickname) ?></p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
    <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>