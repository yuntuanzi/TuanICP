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


$query = "SELECT site_name, site_url, site_avatar, site_abbr, site_keywords, site_description, admin_nickname, admin_email, admin_qq, footer_code, audit_duration, feedback_link, background_image FROM website_info LIMIT 1";$stmt = $pdo->query($query);
$websiteInfo =$stmt->fetch(PDO::FETCH_ASSOC);

// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <link rel="icon" href="https://www.yuncheng.fun/static/webAvatar/11727945933180571.png" type="image/png">
    <title>接入要求</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <style>
        .colored-text {
            color: #ba2eff;
            font-weight: bold;
            text-shadow: 1px 1px 0 white;
        }
                /* 覆盖index.css中的.container样式，移除垂直居中 */
        .container {
            position: static; /* 移除绝对定位 */
            top: auto; /* 移除垂直居中 */
            left: auto; /* 移除水平居中 */
            transform: none; /* 移除平移变换 */
            align-items: flex-start; /* 从顶部开始排列 */
            justify-content: flex-start; /* 从顶部开始排列 */
        }
        .join{
            padding: 10px 20px; /* 内边距 */
            border: none; /* 移除边框 */
            border-radius: 15px; /* 边框圆角 */
            background-color: #e5c8fc; /* 背景颜色 */
            cursor: pointer; /* 鼠标样式为指针 */
            outline: none; /* 移除聚焦时的轮廓线 */
            
        }
        .button-container {
            display: flex; /* 设置为Flexbox容器 */
            justify-content: center; /* 水平居中按钮 */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>接入要求</h2>
            <p>网站内容不得涉及商业/政治/色情/灰色/版权/破解/企业类<br>
            非空壳网站，能长期存活和更新<br>
            无违反道德公序良俗<br>
            已启用安全的HTTPS连接<br>
            会按要求完成与团备完成正确的对接</p>
        <div class="button-container">
            <button class="join" onclick="location.href='join.php'">加入<?php echo htmlspecialchars($site_abbr); ?>ICP备</button>
        </div>
			<p>若你在万千网站中遇到<?php echo htmlspecialchars($site_abbr); ?>备</p>
			<p>甚有缘分! ✧(≖ ◡ ≖✿)</p>
			<br><br><br>
        </div>
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
</body>
</html>
