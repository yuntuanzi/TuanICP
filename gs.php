<?php
// 引入配置文件
$config = require 'config.php';

// 从配置文件中获取数据库连接参数
$host =$config['db']['host'];
$dbname =$config['db']['dbname'];
$user =$config['db']['user'];
$pass =$config['db']['pass'];

// 创建PDO实例并设置错误模式
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// 查询数据库获取网站信息
$query = "SELECT site_name, site_keywords, site_description, footer_code FROM website_info LIMIT 1";$stmt = $pdo->query($query);
$websiteInfo =$stmt->fetch(PDO::FETCH_ASSOC);

// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值

// 查询最新的8个备案信息（审核通过）
$queryNewRecords = "SELECT icp_number, website_name FROM icp_records WHERE STATUS = '审核通过' ORDER BY id DESC LIMIT 8";$stmtNewRecords = $pdo->query($queryNewRecords);
$newRecords =$stmtNewRecords->fetchAll(PDO::FETCH_ASSOC);

// 查询历史备案信息（除了最新的8个，且审核通过）
$queryOldRecords = "SELECT icp_number, website_name FROM icp_records WHERE STATUS = '审核通过' ORDER BY id DESC LIMIT 8, 9999";$stmtOldRecords = $pdo->query($queryOldRecords);
$oldRecords =$stmtOldRecords->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($site_name); ?>ICP备案信息公示</title>
    <link rel="icon" href="https://www.yuncheng.fun/static/webAvatar/11727945933180571.png" type="image/png">
    <meta name="keywords" content="<?php echo htmlspecialchars($site_keywords); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <link rel="stylesheet" type="text/css" href="css/gs.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($site_name); ?>ICP备案信息公示</h1>
        </div>
        
<h2>最新备案</h2>
<div class="records">
    <?php foreach ($newRecords as$record): ?>
        <div class="record" onclick="location.href='id.php?keyword=<?php echo urlencode(htmlspecialchars($record['icp_number'])); ?>'">
            <div class="website-name"><?php echo htmlspecialchars($record['website_name']); ?></div>
            <div class="icp-number"><?php echo htmlspecialchars($record['icp_number']); ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- 历史备案 -->
<h2>历史备案</h2>
<div class="records">
    <?php foreach ($oldRecords as$record): ?>
        <div class="record" onclick="location.href='id.php?keyword=<?php echo urlencode(htmlspecialchars($record['icp_number'])); ?>'">
            <div class="website-name"><?php echo htmlspecialchars($record['website_name']); ?></div>
            <div class="icp-number"><?php echo htmlspecialchars($record['icp_number']); ?></div>
        </div>
    <?php endforeach; ?>
</div>

        
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
</body>
</html>
