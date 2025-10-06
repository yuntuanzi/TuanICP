<?php
require_once '../app/config/db.php';
require_once '../app/config/function.php';
$page = 'gs';
include('header.php');
try {
    $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE status = 'approved' ORDER BY RAND() LIMIT 20");
    $stmt->execute();
    $icpRecords = $stmt->fetchAll();
} catch (PDOException $e) {
    die("获取备案记录失败: " . $e->getMessage());
}

$pastelColors = [
    'rgba(173, 216, 230, 0.7)',
    'rgba(255, 182, 193, 0.7)',
    'rgba(216, 191, 216, 0.7)',
    'rgba(255, 255, 153, 0.7)',
    'rgba(144, 238, 144, 0.7)',
    'rgba(255, 218, 185, 0.7)',
    'rgba(176, 224, 230, 0.7)',
];
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($maintitle); ?>—备案信息公示</title>
    <meta name="keywords" content="<?php echo htmlspecialchars($shortname); ?>备, <?php echo htmlspecialchars($shortname); ?>ICP备, <?php echo htmlspecialchars($maintitle); ?>">
    <meta name="description" content="哇，是谁家的小可爱？二次元虚拟备案信息公示，开始冒险叭~">
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/gs.css">
    <style>
        .record-avatar {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-image: url('https://file.xn--kiv.fun/jpg/loading.jpg');
        }
        
        .record-avatar.loaded {
            background-image: none !important;
        }
        
        .record-avatar.fallback-color {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-weight: bold;
            background-image: none !important;
        }
    </style>
    <?php echo ($headerhtml); ?>
    <style>
    <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><?php echo htmlspecialchars($maintitle); ?>—备案信息公示</h1>
            <p><?php echo htmlspecialchars($subtitle); ?></p>
        </header>

        <div class="records-container">
            <?php foreach ($icpRecords as $record):
                $randomColor = $pastelColors[array_rand($pastelColors)];
                $firstChar = mb_substr(htmlspecialchars($record['site_title']), 0, 1);
            ?>
                <a href="/id.php?keyword=<?php echo urlencode($record['icp_number']); ?>" class="record-card">
                    <img 
                        src="<?php echo htmlspecialchars($record['site_avatar']); ?>" 
                        class="record-avatar" 
                        loading="lazy"
                        onload="this.classList.add('loaded')"
                        onerror="this.onerror=null; this.src=''; this.classList.add('fallback-color'); this.style.backgroundColor='<?php echo $randomColor; ?>'; this.innerHTML='<?php echo $firstChar; ?>'"
                    >
                    <div class="record-info">
                        <h3 class="record-title"><?php echo htmlspecialchars($record['site_title']); ?></h3>
                        <span class="record-number"><?php echo htmlspecialchars($record['icp_number']); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
        <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>