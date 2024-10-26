<?php
// 引入配置文件
$config = require 'config.php';

// 从配置文件中获取数据库连接参数
$host =$config['db']['host'];
$dbname =$config['db']['dbname'];
$user =$config['db']['user'];
$pass =$config['db']['pass'];


// 开启输出缓冲
ob_start();
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

// 检查是否有POST请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 获取用户输入的备案号并清理
    $icp_number = isset($_POST['icp_number']) ? trim($_POST['icp_number']) : '';

        // 查询数据库以检查备案号是否已被占用
        $sql = "SELECT COUNT(*) FROM icp_records WHERE icp_number = :icp_number";
        $stmt =$pdo->prepare($sql);
        $stmt->execute(['icp_number' =>$icp_number]);
        $count =$stmt->fetchColumn();

        if ($count > 0) {
            // 如果备案号已被占用，则使用JavaScript弹窗提示用户
            echo '<script type="text/javascript">alert("备案号已被占用，请重新填写。");</script>';
        } else {
            // 如果备案号未被占用
            echo "<script type='text/javascript'>";
            echo "alert('恭喜，该备案号可以注册！');";
            echo "window.location.href = 'reg.php?number=" . urlencode($icp_number) . "';";
            echo "</script>";
            exit(); // 确保脚本在这里终止

        }
    }

ob_end_flush(); // 发送输出缓冲并关闭输出缓冲
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>加入<?php echo htmlspecialchars($site_name); ?>ICP备案</title>
    <link rel="icon" href="https://www.yuncheng.fun/static/webAvatar/11727945933180571.png" type="image/png">
    <meta name="keywords" content="加入<?php echo htmlspecialchars($site_abbr); ?>备, 加入<?php echo htmlspecialchars($site_abbr); ?>ICP备, <?php echo htmlspecialchars($site_name); ?>ICP备案中心">
    <meta name="description" content="加入<?php echo htmlspecialchars($site_abbr); ?>备！快来申请一个可爱又好玩的<?php echo htmlspecialchars($site_abbr); ?>ICP备号叭~">
    <link rel="stylesheet" type="text/css" href="css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <style>
        p {
            color: white;
            text-shadow: 2px 2px 5px black;
        }
    </style>
    <script>
        // 当文档加载完毕时执行
        document.addEventListener('DOMContentLoaded', function() {
            // 绑定表单提交事件
            document.querySelector('form').addEventListener('submit', function(event) {
                // 获取用户输入的备案号
                var icpNumber = document.querySelector('input[name="icp_number"]').value.trim();

                // 使用正则表达式验证备案号
                if (!/^\d{8}$/.test(icpNumber)) {
                    // 如果验证失败，阻止表单提交并弹出警告
                    event.preventDefault(); // 阻止表单提交
                    alert("备案号必须是8位纯数字。");
                }
                // 如果验证通过，表单将正常提交
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>加入<?php echo htmlspecialchars($site_name); ?>ICP备案</h1>
        </div>
        <div class="search-box">
            <form action="join.php" method="post">
                <input type="text" name="icp_number" class="search-input" placeholder="请输入你想拥有的<?php echo htmlspecialchars($site_abbr); ?>号(8位纯数字)" required>
                <button type="submit" class="search-button">加入</button>
            </form>
        </div>
        <p>提交申请后，会在<?php echo htmlspecialchars($audit_duration); ?>个休息日审核<br>
           若超过7天仍未回复，请联系<?php echo htmlspecialchars($site_name); ?>ICP备案中心<br>
           邮箱：<?php echo htmlspecialchars($admin_email); ?></p>
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
</body>
</html>
