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

// 获取URL参数 keyword
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// 查询备案信息
// 使用 OR 逻辑来查询备案号或域名
$sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url LIKE :urlPattern";$stmt = $pdo->prepare($sql);
$urlPattern = "%{$keyword}%"; // 用于模糊匹配URL
$stmt->execute(['keyword' =>$keyword, 'urlPattern' => $urlPattern]);$icp_record = $stmt->fetch(PDO::FETCH_ASSOC);

// 如果没有找到记录，则弹窗提示并跳转
if (!$icp_record) {
    // 使用单行注释
    echo "<script>alert('没有找到对应的ICP备案信息。');</script>";
    echo "<script>window.location.href='index.php';</script>";
    exit;
}

// 检查备案状态是否为“审核通过”
if ($icp_record['STATUS'] !== '审核通过') {
    // 如果状态不是“审核通过”，则弹窗提示用户
    echo "<script type='text/javascript'>";
    echo "alert('该备案信息未通过审核');";
    echo "window.location.href = 'xg.php?keyword=" . urlencode($keyword) . "';";
    echo "</script>";
    exit; // 终止脚本执行
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
    <link rel="icon" href="https://www.yuncheng.fun/static/webAvatar/11727945933180571.png" type="image/png">
    <meta name="keywords" content="<?php echo htmlspecialchars($icp_record['website_name']); ?>, <?php echo htmlspecialchars($site_abbr); ?>ICP备<?php echo htmlspecialchars($icp_record['icp_number']); ?>号, <?php echo htmlspecialchars($site_abbr); ?>备, <?php echo htmlspecialchars($site_abbr); ?>ICP备, <?php echo htmlspecialchars($site_name); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($icp_record['website_info']); ?>">
    <title><?php echo htmlspecialchars($site_abbr); ?>ICP备<?php echo htmlspecialchars($icp_record['icp_number']); ?>号—<?php echo htmlspecialchars($icp_record['website_name']); ?></title>
    <link rel="stylesheet" type="text/css" href="css/id.css">
    <style>
    a.fk {
    text-decoration: none; /* 取消下划线 */
    color: red; /* 文字颜色为红色 */
    font-weight: bold; /* 文字加粗 */
}

    </style>



</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($site_abbr); ?>ICP备案查询</h1>
        </div>
        <div class="table-container">
            <div class="row">
                <div class="cell header-cell">网站名称</div>
                <div class="cell"><?php echo htmlspecialchars($icp_record['website_name']); ?></div>
            </div>
            <div class="row">
                <div class="cell header-cell">网站域名</div>
                <div class="cell">
                    <a style="color:#b62df0; text-decoration:none; text-shadow: 1px 1px 1px black;" href="https://<?php echo htmlspecialchars($icp_record['website_url']); ?>" target="_blank">
                        <?php echo htmlspecialchars($icp_record['website_url']); ?>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="cell header-cell">网站信息</div>
                <div class="cell"><?php echo htmlspecialchars($icp_record['website_info']); ?></div>
            </div>
            <div class="row">
                <div class="cell header-cell"><?php echo htmlspecialchars($site_abbr); ?>备案号</div>
                <div class="cell"><?php echo htmlspecialchars($site_abbr); ?>ICP备<?php echo htmlspecialchars($icp_record['icp_number']); ?>号</div>
            </div>
            <div class="row">
                <div class="cell header-cell">所有者</div>
                <div class="cell"><?php echo htmlspecialchars($icp_record['owner']); ?></div>
            </div>
            <div class="row">
                <div class="cell header-cell">更新时间</div>
                <div class="cell"><?php echo htmlspecialchars($icp_record['update_time']); ?></div>
            </div>
            <div class="row">
                <div class="cell header-cell">状态</div>
                <div class="cell"><?php echo htmlspecialchars($icp_record['STATUS']); ?><a href="<?php echo htmlspecialchars($feedback_link); ?>" class="fk">&nbsp;&nbsp;反馈</a></div>
            </div>
        </div>
        <br>
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
</body>
</html>
