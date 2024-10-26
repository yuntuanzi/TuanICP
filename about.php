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
    <meta name="keywords" content="关于<?php echo htmlspecialchars($site_abbr); ?>备, 关于<?php echo htmlspecialchars($site_abbr); ?>ICP备, 关于<?php echo htmlspecialchars($site_name); ?>ICP备案中心">
    <meta name="description" content="<?php echo htmlspecialchars($admin_nickname); ?>去野餐时爱(i)吃(c)苹(p)果派,需要提前准备,俗称“<?php echo htmlspecialchars($site_abbr); ?>icp备”,简称“<?php echo htmlspecialchars($site_abbr); ?>备">
    <title>关于<?php echo htmlspecialchars($site_name); ?>ICP备案</title>
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
<h2>什么是<?php echo htmlspecialchars($site_name); ?>ICP备？</h2>

<p><?php echo htmlspecialchars($admin_nickname); ?>去野餐时爱(<span class="colored-text">i</span>)吃(<span class="colored-text">c</span>)苹(<span class="colored-text">p</span>)果派,需要提前准<span class="colored-text">备</span>,<br>俗称“<span class="colored-text"><?php echo htmlspecialchars($site_abbr); ?>icp备</span>”,简称“<?php echo htmlspecialchars($site_abbr); ?>备"</p>


<h2>为什么加入<?php echo htmlspecialchars($site_abbr); ?>备</h2>
                    <p><?php echo htmlspecialchars($site_abbr); ?>ICP备，是众多<?php echo htmlspecialchars($site_abbr); ?>友的网站都聚在一起可可爱爱！</p>
                    <p>是一种异次元中的爱好，可爱和个性的展现</p>
                    <p>将<?php echo htmlspecialchars($site_abbr); ?>备放在博客页脚，是很好看的装饰喔</p>
                    
                    
<h2><?php echo htmlspecialchars($admin_nickname); ?>有话说</div></h2>
                    <p>欢迎各大喜爱<?php echo htmlspecialchars($site_name); ?>的站长加入鸭~<br>快来给自己的网站加上个可爱的<?php echo htmlspecialchars($site_abbr); ?>ICP号”</p>
                    
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
