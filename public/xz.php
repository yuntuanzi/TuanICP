<?php
require_once '../app/config/db.php';
require_once '../app/config/function.php';
include('header.php');

?>


<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shortname); ?>ICP备案加入须知</title>
    <meta name="keywords" content="<?php echo htmlspecialchars($shortname); ?>备加入须知, <?php echo htmlspecialchars($shortname); ?>ICP备加入须知, <?php echo htmlspecialchars($maintitle); ?>">
    <meta name="description" content="哇，是谁家的小可爱？请仔细阅读以下条款，确保您的网站符合要求。成功提交申请后，您需要按照页面引导将备案信息代码悬挂至您的网站底部。">
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/xz.css">
    <?php echo ($headerhtml); ?>
    <style>
    <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    <div class="container">

        <header class="header">
            <h1>📋<?php echo htmlspecialchars($shortname); ?>ICP备案加入须知</h1>
            <p>请仔细阅读以下条款，确保您的网站符合要求</p>
        </header>

        <div class="notice-container">
            
            <table class="notice-table">
                <tbody>
                    <tr>
                        <td class="section-title">第一条</td>
                        <td>
                            我们会通过邮箱、QQ等方式通知您备案信息的状态，请确保您提供的联系方式<b class="highlight">真实有效</b>。
                        </td>
                    </tr>
                    <tr>
                        <td class="section-title">第二条</td>
                        <td>
                            <p>成功提交申请后，您需要按照页面引导将备案信息代码悬挂至您的网站底部。</p>
                        </td>
                    </tr>
                    <tr>
                        <td class="section-title">第三条</td>
                        <td>
                            <p>不被允许与我们对接的站点类型：</p>
                            <ul>
                                <li>使用<b class="highlight">免费域名</b>或<b class="highlight">建站服务</b>的站点</li>
                                <li>含有<b class="highlight">色情暴力</b>、<b class="highlight">政治敏感</b>等违禁内容的</li>
                                <li>违反<b class="highlight">中华人民共和国法律法规</b>的</li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <td class="section-title">第四条</td>
                        <td>
                            <p>您的站点需满足以下条件：</p>
                            <ul>
                                <li>开启<b class="highlight">HTTPS安全访问</b></li>
                                <li>非<b class="highlight">空壳网站</b>，能长期存活和更新</li>
                                <li>按要求完成与<b class="highlight"><?php echo htmlspecialchars($shortname); ?>备</b>的正确对接</li>
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="contact-info">
                站长邮箱：<a href="mailto:<?php echo htmlspecialchars($adminemail); ?>" style="color: var(--primary-color); text-decoration: none;"><?php echo htmlspecialchars($adminemail); ?></a>
            </div>

            <div class="action-area">
                <button class="agree-btn" onclick="window.location.href='/xh.php'">
                    我已经认真阅读并承诺遵守
                </button>
            </div>
        </div>

        <p class="luck-message">
            若你在万千网站中遇到<?php echo htmlspecialchars($shortname); ?>备<br>甚有缘分! ✧(≖ ◡ ≖✿)
        </p>
    </div>

        <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>