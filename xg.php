<?php
// 引入配置文件
$config = require 'config.php';

// 从配置文件中获取数据库连接参数
$host =$config['db']['host'];
$dbname =$config['db']['dbname'];
$user =$config['db']['user'];
$pass =$config['db']['pass'];

// 获取URL参数 keyword
$keyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';

// 创建PDO实例并设置错误模式
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8",$user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}

// 防止数据库注入：使用预处理语句
$sql = "SELECT * FROM icp_records WHERE icp_number = :keyword OR website_url = :keyword";$stmt = $pdo->prepare($sql);
$stmt->execute(['keyword' =>$keyword]);
$icp_record =$stmt->fetch(PDO::FETCH_ASSOC);

// 检查备案信息是否存在
if ($icp_record) {
    // 备案信息存在，正常访问页面
} else {
    // 备案信息不存在，弹窗提示并重定向
    echo '<script type="text/javascript">';
    echo 'alert("备案号不存在");';
    echo 'window.location.href="index.html";';
    echo '</script>';
    exit; // 确保脚本停止执行
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
    <title>备案信息待审核</title>
    <link rel="stylesheet" type="text/css" href="css/xg.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>云团子ICP备案信息审核ing~</h1>
            <hr>
            <h2>欢迎加入云团子ICP备，请站长在贵站页脚悬挂备案信息</h2>
            <xmp id="icp-info"><a href="https://icp.yuncheng.fun/id.php?keyword=<?php echo htmlspecialchars($keyword); ?>" target="_blank">团ICP备<?php echo htmlspecialchars($icp_record['icp_number']); ?>号</a></xmp>
            <button class="copy-btn" onclick="copyToClipboard()">复制备案信息</button>
            <p><b>您的备案申请状态：</b><?php echo htmlspecialchars($icp_record['STATUS']); ?>，请尽快按照上述要求与团ICP备对接！</p>
            <p><b>待审核</b>的备案，我们会在2~4个休息日审核完成</p>
            <p><b>备案被驳回</b>，请检查您的网站是否含有不适宜展示的内容，<br>
            并确认您已经正确悬挂了备案信息，我们会在收到您的申诉邮件后重新审核</p>
            <p>云团子ICP备案中心邮箱：yun@yuncheng.fun</p>
        </div>
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
    <script>
        function copyToClipboard() {
            var text = document.getElementById('icp-info').innerText;
            navigator.clipboard.writeText(text).then(function() {
                alert('备案信息已复制到剪贴板！');
            }, function(err) {
                alert('复制失败，请手动复制。');
            });
        }
    </script>
</body>
</html>
