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

// 检查是否有GET请求，并处理备案查询
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['keyword'])) {
    $keyword =$_GET['keyword'];

    // 查询备案信息
    $sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url LIKE :urlPattern";
    $stmt =$pdo->prepare($sql);
    $urlPattern = "%{$keyword}%"; // 用于模糊匹配URL
    $stmt->execute(['keyword' =>$keyword, 'urlPattern' => $urlPattern]);
    $icp_record =$stmt->fetch(PDO::FETCH_ASSOC);

    // 如果没有找到记录，则显示提示信息
    if (!$icp_record) {
        $noRecordMessage = "喵喵：未查询到该备案记录";
    } else {
        // 如果找到记录，则跳转到id.php
        header("Location: id.php?keyword=" . urlencode($keyword));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($site_name); ?>ICP备案中心</title>
    <link rel="icon" href="https://www.yuncheng.fun/static/webAvatar/11727945933180571.png" type="image/png">
    <meta name="keywords" content="<?php echo htmlspecialchars($site_keywords); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($site_description); ?>">
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">

</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($site_name); ?>ICP备案中心</h1>
        </div>
        <div class="search-box">
            <form action="index.php" method="get">
                <input type="text" name="keyword" class="search-input" placeholder="请输入8位备案号 OR 域名" required><br>
                <p style="text-align: center;"><?php echo htmlspecialchars($site_description); ?></p>
                <?php if (isset($noRecordMessage)): ?>
                    <p style="color: #FF9999; text-align: center;"><?php echo $noRecordMessage; ?></p>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
</body>
</html>
