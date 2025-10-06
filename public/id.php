<?php
require_once('../app/config/db.php');
require_once '../app/config/function.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

$icpInfo = null;
if (!empty($keyword)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE icp_number = :keyword OR site_domain = :keyword LIMIT 1");
        $stmt->execute([':keyword' => $keyword]);
        $icpInfo = $stmt->fetch();
        
        if ($icpInfo && ($icpInfo['status'] === 'pending' || $icpInfo['status'] === 'rejected')) {
            header("Location: xg.php?keyword=" . urlencode($icpInfo['icp_number']));
            exit;
        }
    } catch (PDOException $e) {

        $icpInfo = null;
    }
}

$defaultTitle = '备案信息查询';
$defaultIcpNumber = '未找到备案';
$defaultSiteTitle = '备案跑丢啦';
$defaultDescription = '未找到相关备案信息';

include('header.php');

if (isset($_POST['generate_certificate']) && $icpInfo) {

    $siteTitle = htmlspecialchars($icpInfo['site_title'] ?? $defaultSiteTitle);
    $siteDomain = htmlspecialchars(str_replace("\n", ", ", $icpInfo['site_domain'] ?? ''));
    $icpNumber = htmlspecialchars($icpInfo['icp_number'] ?? $defaultIcpNumber);
    $owner = htmlspecialchars($icpInfo['owner'] ?? '未知');
    $updateTime = htmlspecialchars($icpInfo['update_time'] ?? '未知');
    
    $statusText = '未知状态';
    $statusClass = '';
    if (isset($icpInfo['status'])) {
        switch ($icpInfo['status']) {
            case 'approved':
                $statusText = '审核通过';
                $statusClass = 'status-approved';
                break;
            case 'pending':
                $statusText = '待审核';
                break;
            case 'rejected':
                $statusText = '审核驳回';
                break;
        }
    }
    
    $currentDate = date('Y年m月d日');
    
    $certificateHTML = <<<HTML
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$sitename}虚拟备案证书</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .certificate-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .certificate-wrapper {
            position: relative;
            width: 90%;
            max-width: 1200px;
        }
        .certificate {
            width: 100%;
            aspect-ratio: 16/9;
            background: linear-gradient(to right, #f9f2e8, #faf6f0, #f9f2e8);
            border: 20px solid transparent;
            border-image: linear-gradient(135deg, #d4af37, #f9f2e8, #d4af37);
            border-image-slice: 1;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }
        .certificate::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><text x="10" y="50" font-family="Arial" font-size="15" fill="rgba(0,0,0,0.05)" transform="rotate(-30)">{$shortname}ICP</text></svg>');
            opacity: 0.1;
            z-index: 0;
        }
        .certificate-content {
            position: relative;
            z-index: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .certificate-header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #d4af37;
            padding-bottom: 15px;
        }
        .certificate-title {
            font-size: 36px;
            color: #8b4513;
            margin: 0;
            font-weight: bold;
            letter-spacing: 5px;
        }
        .certificate-subtitle {
            font-size: 18px;
            color: #a67c52;
            margin-top: 8px;
            font-style: italic;
        }
        .certificate-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .certificate-table {
            width: 80%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 18px;
        }
        .certificate-table th, .certificate-table td {
            padding: 15px 20px;
            border-bottom: 1px solid #e0d6c2;
            text-align: left;
            vertical-align: top;
        }
        .certificate-table th {
            width: 25%;
            color: #8b4513;
            font-weight: normal;
            padding-left: 50px;
        }
        .certificate-table td {
            color: #333;
            font-weight: bold;
            padding-left: 50px;
            padding-right: 50px;
        }
        .status-approved {
            color: #28a745;
            font-weight: bold;
        }
        .certificate-remark {
            margin-top: 25px;
            text-align: center;
            font-size: 18px;
            color: #8b4513;
            font-style: italic;
        }
        .certificate-footer {
            margin-top: auto;
            text-align: right;
            padding-top: 15px;
            border-top: 1px solid #d4af37;
        }
        .certificate-issuer {
            font-size: 20px;
            color: #8b4513;
            margin-bottom: 5px;
        }
        .certificate-date {
            font-size: 16px;
            color: #a67c52;
        }
        .certificate-actions {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .download-btn {
            padding: 14px 32px;
            background: linear-gradient(135deg, #d4af37 0%, #b8972e 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 17px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(212, 175, 55, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .download-btn:hover {
            background: linear-gradient(135deg, #b8972e 0%, #9e8025 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(212, 175, 55, 0.5);
        }
        
        .download-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(212, 175, 55, 0.3);
        }
    
        .close-btn {
            padding: 14px 32px;
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 17px;
            font-weight: bold;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(108, 117, 125, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .close-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #495057 100%);
            transform: translateY(-3px);
            box-shadow: 0 6px 16px rgba(108, 117, 125, 0.5);
        }
        
        .close-btn:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(108, 117, 125, 0.3);
        }
        
        .close-modal {
            position: absolute;
            top: 15px;
            right: 15px;
            background: none;
            border: none;
            font-size: 28px;
            color: #fff;
            cursor: pointer;
            z-index: 1001;
            transition: transform 0.2s;
        }
        
        .close-modal:hover {
            transform: scale(1.1);
            color: #d4af37;
        }
    </style>
</head>
<body>
    <div class="certificate-modal">
        <button class="close-modal" onclick="window.history.back()">×</button>
        <div class="certificate-wrapper">
            <div class="certificate">
                <div class="certificate-content">
                    <div class="certificate-header">
                        <h1 class="certificate-title">{$shortname}ICP虚拟备案证书</h1>
                        <p class="certificate-subtitle">Virtual ICP Filing Certificate</p>
                    </div>
                    <div class="certificate-body">
                        <table class="certificate-table">
                            <tr>
                                <th>网站名称</th>
                                <td>{$siteTitle}</td>
                            </tr>
                            <tr>
                                <th>网站域名</th>
                                <td>{$siteDomain}</td>
                            </tr>
                            <tr>
                                <th>备案号码</th>
                                <td>{$shortname}ICP备{$icpNumber}号</td>
                            </tr>
                            <tr>
                                <th>所有者</th>
                                <td>{$owner}</td>
                            </tr>
                            <tr>
                                <th>更新时间</th>
                                <td>{$updateTime}</td>
                            </tr>
                            <tr>
                                <th>审核状态</th>
                                <td><span class="{$statusClass}">{$statusText}</span></td>
                            </tr>
                        </table>
                        <p class="certificate-remark">经审核，该网站符合{$shortname}ICP备案要求，特发此证</p>
                    </div>
                    <div class="certificate-footer">
                        <div class="certificate-issuer">{$shortname}ICP备案中心</div>
                        <div class="certificate-date">{$currentDate}</div>
                    </div>
                </div>
            </div>
            <div class="certificate-actions">
                <button class="download-btn" onclick="downloadCertificate()">下载证书</button>
                <button class="close-btn" onclick="window.history.back()">关闭</button>
            </div>
        </div>
    </div>
    <script>
        function downloadCertificate() {
            const certificate = document.querySelector('.certificate');
            html2canvas(certificate, {
                scale: 2,
                logging: false,
                useCORS: true,
                allowTaint: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = '{$shortname}ICP备案证书_{$icpNumber}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            });
        }
        
        // 阻止背景滚动
        document.body.style.overflow = 'hidden';
    </script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
</body>
</html>
HTML;

    echo $certificateHTML;
    exit;
}

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shortname ?? ''); ?>ICP备<?php echo htmlspecialchars($icpInfo['icp_number'] ?? $defaultIcpNumber); ?>号—<?php echo htmlspecialchars($icpInfo['site_title'] ?? $defaultSiteTitle); ?> <?php echo htmlspecialchars($icpInfo['site_description'] ?? $defaultDescription); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($shortname ?? ''); ?>备, <?php echo htmlspecialchars($shortname ?? ''); ?>ICP备, <?php echo htmlspecialchars($maintitle ?? ''); ?>">
    
    <link rel="icon" href="<?php echo htmlspecialchars($logourl ?? ''); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl ?? ''); ?>" type="image/x-icon">
    <meta name="description" content="">
    <link rel="stylesheet" href="assets/css/id.css">
    <?php echo ($headerhtml); ?>
    <style>
    <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    
    <div class="container">
        <header class="header">
            <h1><?php echo htmlspecialchars($shortname ?? ''); ?>ICP备案信息查询系统</h1>
            <p></p>
        </header>

        <?php if ($icpInfo): ?>
        <div class="icp-card">
            <h2 class="icp-title">备案信息公示</h2>
            
            <table class="icp-table">
                <tr>
                    <th>网站名称</th>
                    <td><?php echo htmlspecialchars($icpInfo['site_title'] ?? $defaultSiteTitle); ?></td>
                </tr>
                <tr>
                    <th style="vertical-align: middle;">网站域名</th>
                    <td>
                        <?php if (!empty($icpInfo['site_domain'])): ?>
                            <?php 
                            $domains = explode("\n", $icpInfo['site_domain']);
                            foreach ($domains as $domain): 
                                $domain = trim($domain);
                                if (!empty($domain)): ?>
                                    <a href="https://<?php echo htmlspecialchars($domain); ?>" class="domain-link" target="_blank">
                                        <?php echo htmlspecialchars($domain); ?>
                                    </a><br>
                                <?php endif; 
                            endforeach; ?>
                        <?php else: ?>
                            未提供域名
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>网站描述</th>
                    <td><?php echo htmlspecialchars(!empty($icpInfo['site_description']) ? $icpInfo['site_description'] : '暂无描述'); ?></td>
                </tr>
                <tr>
                    <th>备案号码</th>
                    <td>
                        <?php 
                        $icpNumber = $icpInfo['icp_number'] ?? $defaultIcpNumber;
                        if (strpos($icpNumber, '-') !== false) {
                            list($mainNum, $subNum) = explode('-', $icpNumber, 2);
                            echo htmlspecialchars($shortname ?? '') . 'ICP备' . htmlspecialchars($mainNum) . '号-' . htmlspecialchars($subNum);
                        } else {
                            echo htmlspecialchars($shortname ?? '') . 'ICP备' . htmlspecialchars($icpNumber) . '号';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th>所有者</th>
                    <td><?php echo htmlspecialchars($icpInfo['owner'] ?? '未知'); ?></td>
                </tr>
                <tr>
                    <th>更新时间</th>
                    <td><?php echo htmlspecialchars($icpInfo['update_time'] ?? '未知'); ?></td>
                </tr>
                <tr>
                    <th>状态</th>
                    <td>
                        <?php 
                        $statusClass = '';
                        $statusText = '未知状态';
                        if (isset($icpInfo['status'])) {
                            switch ($icpInfo['status']) {
                                case 'approved':
                                    $statusClass = 'approved';
                                    $statusText = '审核通过';
                                    break;
                                case 'pending':
                                    $statusClass = 'pending';
                                    $statusText = '待审核';
                                    break;
                                case 'rejected':
                                    $statusClass = 'rejected';
                                    $statusText = '审核驳回';
                                    break;
                            }
                        }
                        ?>
                        <span class="status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                        <?php if (!empty($adminemail)): ?>
                        <a href="mailto:<?php echo htmlspecialchars($adminemail); ?>" class="feedback-link">反馈</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <div class="button-container">
                <form method="post" style="display: inline;" id="certificate-form">
                    <input type="hidden" name="generate_certificate" value="1">
                    <button type="button" class="action-btn action-btn-primary certificate-icon" onclick="checkDevice()">
                        证书生成
                    </button>
                </form>
                <a href="xg.php?keyword=<?php echo urlencode($icpInfo['icp_number'] ?? ''); ?>" class="action-btn action-btn-secondary code-icon" target="_blank">
                    对接代码
                </a>
            </div>
            
            <div class="mascot">
                哇，是谁家的小可爱？
            </div>
        </div>
        <?php else: ?>
        <div class="icp-card">
            <h2 class="icp-title">备案信息详情</h2>
            <p class="no-result"><?php echo empty($keyword) ? '请输入备案号或域名进行查询' : '备案跑丢啦，未找到相关备案信息'; ?></p>
        </div>
        <?php endif; ?>
    </div>
    <script>
    function checkDevice() {
        if (window.innerWidth < 768) {
            alert('对不起，请在电脑端访问并生成证书');
        } else {
            document.getElementById('certificate-form').submit();
        }
    }
    </script>
    <?php include('footer.php'); ?>
</body>
</html>