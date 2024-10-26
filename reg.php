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
    echo "<script>alert('数据库连接失败: " . addslashes($e->getMessage()) . "');</script>";
}


// 检查是否有GET参数number
$icp_number = isset($_GET['number']) ? trim($_GET['number']) : '';

// 定义当前时间
$current_time = date('Y-m-d H:i:s');

// 确保备案号只包含字母和数字
$icp_number = preg_replace('/[^a-zA-Z0-9]/', '',$icp_number);


$query = "SELECT site_name, site_url, site_avatar, site_abbr, site_keywords, site_description, admin_nickname, admin_email, admin_qq, footer_code, audit_duration, feedback_link, background_image FROM website_info LIMIT 1";$stmt = $pdo->query($query);
$websiteInfo =$stmt->fetch(PDO::FETCH_ASSOC);


// 检查是否获取到了数据
if (!$websiteInfo) {
    die("网站信息不存在");
}
extract($websiteInfo); // 将数组键名作为变量名，将数组键值作为变量值


// 如果表单被提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 清理和验证输入
    $website_name = trim($_POST['website_name']);
    $website_url = trim($_POST['website_url']);
    $website_info = trim($_POST['website_info']);
    $icp_number = trim($_POST['icp_number']);
    $owner = trim($_POST['owner']);
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $qq = trim($_POST['qq']);

    // 验证输入
    if (empty($website_name) || empty($website_url) || empty($icp_number) || empty($owner)) {
        echo "<script>alert('请填写所有必填字段');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('无效的邮箱地址');</script>";
    } else {
        // 检查备案号是否已存在
        $check_icp_number_sql = "SELECT COUNT(*) FROM icp_records WHERE icp_number = :icp_number";
        $check_icp_number_stmt =$pdo->prepare($check_icp_number_sql);
        $check_icp_number_stmt->bindParam(':icp_number',$icp_number);
        $check_icp_number_stmt->execute();
        $icp_number_exists =$check_icp_number_stmt->fetchColumn() > 0;

        // 检查域名是否已存在
        $check_website_url_sql = "SELECT COUNT(*) FROM icp_records WHERE website_url = :website_url";
        $check_website_url_stmt =$pdo->prepare($check_website_url_sql);
        $check_website_url_stmt->bindParam(':website_url',$website_url);
        $check_website_url_stmt->execute();
        $website_url_exists =$check_website_url_stmt->fetchColumn() > 0;
        
        
// 检查备案号是否为8位纯数字
if (!preg_match('/^\d{8}$/',$icp_number)) {
    echo "<script>";
    echo "alert('备案号必须是8位纯数字，早就猜到你会瞎改参数。');";
    echo "window.location.href = 'join.php';";
    echo "</script>";
    exit;
}

// 接下来是检查备案号是否存在的代码
if ($icp_number_exists) {
    echo "<script>alert('备案号被抢先注册啦，有疑问请联系云团子ICP备案中心'); window.location.href = 'join.php';</script>";
    exit;
}

// 接下来是检查域名是否存在的代码
if ($website_url_exists) {
    echo "<script>alert('域名已经登记过啦，有疑问请联系云团子ICP备案中心'); window.location.href = 'join.php';</script>";
    exit;
}
        
        else {
            // 使用预处理语句插入数据
            $sql = "INSERT INTO icp_records (website_name, website_url, website_info, icp_number, owner, update_time, email, qq) 
                    VALUES (:website_name, :website_url, :website_info, :icp_number, :owner, NOW(), :email, :qq)";
            $stmt =$pdo->prepare($sql);

            // 绑定参数
            $stmt->bindParam(':website_name',$website_name);
            $stmt->bindParam(':website_url',$website_url);
            $stmt->bindParam(':website_info',$website_info);
            $stmt->bindParam(':icp_number',$icp_number);
            $stmt->bindParam(':owner',$owner);
            $stmt->bindParam(':email',$email);
            $stmt->bindParam(':qq',$qq);

            // 执行预处理语句
            $stmt->execute();

            echo "<script>alert('您的申请已提交，请耐心等待审核！');";
            echo "window.location.href = 'xg.php?keyword=" . htmlspecialchars($icp_number) . "';</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($site_name); ?>ICP备案注册</title>
    <link rel="stylesheet" type="text/css" href="css/reg.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no">
    <script>
        // 在页面加载完毕时绑定验证函数
        window.onload = function() {
            document.getElementById("regForm").onsubmit = validateForm;
        };

        function validateForm() {
            var websiteUrl = document.getElementById("website_url").value;
            var urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
            if (!urlPattern.test(websiteUrl)) {
                alert("网址格式不正确，请输入正确的网址格式，如：http://example.com/ 或 https://example.com/");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo htmlspecialchars($site_name); ?>ICP备案注册</h1>
        </div>
        <div class="reg-box">
            <form id="regForm" action="reg.php" method="post">
                <input type="text" name="website_name" class="input" placeholder="网站名称(必填)" required><br>
                <input type="text" id="website_url" name="website_url" class="input" placeholder="网站域名(必填)" required>
                <p>不要填写http(s)协议头!</p>
                <input type="text" name="website_info" class="input" placeholder="网站描述(必填)" rows="4" required><br>
                <input type="text" name="icp_number" class="input hidden" placeholder="备案号" value="<?php echo htmlspecialchars($icp_number); ?>" required readonly>
                <input type="text" name="update_time" class="input hidden" placeholder="时间" value="<?php echo htmlspecialchars($current_time); ?>" required readonly>
                <input type="text" name="owner" class="input" placeholder="所有者(必填)" required><br>
                <input type="email" name="email" class="input" placeholder="邮箱(必填)" required><br>
                <input type="text" name="qq" class="input" placeholder="QQ(必填)">
                <button type="submit" class="tj">提交备案</button>
            </form>
        </div>
        <p>邮箱和QQ是验证备案信息所有权的重要凭证!<br>
           提交申请后，会在<?php echo htmlspecialchars($audit_duration); ?>个休息日审核<br>
           若超过7天仍未回复，请联系<?php echo htmlspecialchars($site_name); ?>ICP备案中心<br>
           邮箱：<?php echo htmlspecialchars($admin_email); ?></p>
    </div>
    <div class="footer">
        <?php echo $footer_code; ?>
    </div>
</div>
</body>
</html>
