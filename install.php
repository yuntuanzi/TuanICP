<?php
// install.php

// 检查是否已经提交了表单
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 获取用户输入的数据库连接信息
    $dbHost =$_POST['dbHost'] ?? '127.0.0.1';
    $dbName =$_POST['dbName'];
    $dbUser =$_POST['dbUser'];
    $dbPass =$_POST['dbPass'];

    // 尝试连接到数据库
    $mysqli = new mysqli($dbHost, $dbUser,$dbPass, $dbName);

    // 检查连接是否成功
    if ($mysqli->connect_error) {
        die('Connect Error (' . $mysqli->connect_errno . ') ' .$mysqli->connect_error);
    }

    // 读取 SQL 文件内容
    $sqlContent = file_get_contents('install.sql');

    // 执行 SQL 语句
    if ($mysqli->multi_query($sqlContent)) {
        // 等待所有查询执行完成
        do {
            // 无需进一步处理结果
        } while ($mysqli->next_result());

        // 创建 config.php 文件
        $configFile = "<?php\n// config.php\nreturn [\n    'db' => [\n        'host' => '{$dbHost}',\n        'dbname' => '{$dbName}',\n        'user' => '{$dbUser}',\n        'pass' => '{$dbPass}'\n    ]\n];";
        file_put_contents('config.php', $configFile);

        echo "安装成功，请删除安装文件！请前往Myphpadmin进入数据库修改网站配置信息！";
        exit;
    } else {
        // 输出错误信息
        echo "安装失败: " . $mysqli->error;
    }

    // 关闭数据库连接
    $mysqli->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>数据库安装</title>
</head>
<body>
    <h1>数据库安装</h1>
    <form action="install.php" method="post">
        <label for="dbHost">数据库地址:</label>
        <input type="text" id="dbHost" name="dbHost" value="127.0.0.1"><br><br>
        <label for="dbName">数据库名:</label>
        <input type="text" id="dbName" name="dbName" required><br><br>
        <label for="dbUser">数据库用户名:</label>
        <input type="text" id="dbUser" name="dbUser" required><br><br>
        <label for="dbPass">数据库密码:</label>
        <input type="password" id="dbPass" name="dbPass" required><br><br>
        <input type="submit" value="安装">
    </form>
</body>
</html>
