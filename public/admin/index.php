<?php
$title = '控制面板';
require('includes/header.php');

$today = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) as today_count FROM icp_records WHERE DATE(update_time) = :today");
$stmt->execute([':today' => $today]);
$todayCount = $stmt->fetch()['today_count'];

$serverStatus = '正常';
$dbStatus = '正常';

$mailStatus = '未配置';
$mailConfigured = false;
$mailConfigDetails = 'SMTP 未配置';

try {
    $stmt = $pdo->query("SELECT smtp_host, smtp_user, smtp_pass, smtp_port FROM system_settings LIMIT 1");
    $mailSettings = $stmt->fetch();
    
    if ($mailSettings) {
        $mailConfigured = !empty($mailSettings['smtp_host']) && 
                          !empty($mailSettings['smtp_user']) && 
                          !empty($mailSettings['smtp_pass']) && 
                          !empty($mailSettings['smtp_port']);
        
        if ($mailConfigured) {
            $mailStatus = '已配置';
            $mailConfigDetails = sprintf(
                'SMTP: %s@%s:%d', 
                $mailSettings['smtp_user'], 
                $mailSettings['smtp_host'], 
                $mailSettings['smtp_port']
            );
        } else {
            $missing = [];
            if (empty($mailSettings['smtp_host'])) $missing[] = '服务器地址';
            if (empty($mailSettings['smtp_user'])) $missing[] = '用户名';
            if (empty($mailSettings['smtp_pass'])) $missing[] = '密码';
            if (empty($mailSettings['smtp_port'])) $missing[] = '端口';
            
            $mailConfigDetails = '配置不完整: ' . implode(', ', $missing);
        }
    }
} catch (PDOException $e) {
    $mailConfigDetails = '获取配置失败: ' . $e->getMessage();
}
?>

<main class="admin-content">
    <div class="page-header">
        <h2>总览</h2>
        <p>欢迎回来，<?php echo htmlspecialchars($_SESSION['admin_nickname']); ?>！今天是 <?php echo date('Y年m月d日'); ?></p>
    </div>
    
    <div class="dashboard-cards">
        <div class="card card-primary">
            <div class="card-icon">
                <i class="mdi mdi-plus-circle-outline"></i>
            </div>
            <div class="card-content">
                <h3>今日新增</h3>
                <p><?php echo $todayCount; ?></p>
            </div>
            <div class="card-footer">
                <a href="all-icp.php">查看详情 <i class="mdi mdi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="card card-warning">
            <div class="card-icon">
                <i class="mdi mdi-clock-outline"></i>
            </div>
            <div class="card-content">
                <h3>待审核</h3>
                <p><?php echo $pendingIcp; ?></p>
            </div>
            <div class="card-footer">
                <a href="all-icp.php?status=pending">立即处理 <i class="mdi mdi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="card card-success">
            <div class="card-icon">
                <i class="mdi mdi-check-circle-outline"></i>
            </div>
            <div class="card-content">
                <h3>审核通过</h3>
                <p><?php echo $approvedIcp; ?></p>
            </div>
            <div class="card-footer">
                <a href="all-icp.php?status=approved">查看详情 <i class="mdi mdi-arrow-right"></i></a>
            </div>
        </div>
        
        <div class="card" style="
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        ">
                <div class="card-content" style="
                    padding: 0.8rem;
                    flex-grow: 1;
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    text-align: center;
                    width: 100%;
                    box-sizing: border-box;
                    margin: 0;
                ">
                <div id="live-clock" style="
                    font-size: 1.6rem;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                    margin: 0.2rem 0;
                    line-height: 1.3;
                "><?php echo date('H:i:s'); ?></div>
                <div id="live-date" style="
                    font-size: 0.78rem;
                    opacity: 0.9;
                    margin-bottom: 0.8rem;
                "><?php echo date('Y年m月d日 星期') . ['日','一','二','三','四','五','六'][date('w')]; ?></div>
                
                <div id="day-progress" style="
                    width: 100%;
                    height: 3px;
                    background: rgba(255,255,255,0.2);
                    border-radius: 2px;
                    overflow: hidden;
                ">
                    <div style="
                        height: 100%;
                        background: linear-gradient(90deg, rgba(255,255,255,0.8), white);
                        width: <?php echo ((time() - strtotime('today')) / 86400) * 100; ?>%;
                        transition: width 0.5s ease;
                    "></div>
                </div>
            </div>
        
            <div class="card-footer" style="
                padding: 0.6rem 0.8rem;
                background: rgba(0,0,0,0.1);
                font-size: 0.7rem;
            ">
                <div id="seconds-counter" style="
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <span>今日进度 <?php echo number_format(((time() - strtotime('today')) / 86400) * 100, 1); ?>%</span>
                    <span><?php echo date('A') == 'AM' ? '上午' : '下午'; ?>好</span>
                </div>
            </div>
        </div>
        
        <script>
        function updateClock() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const mins = now.getMinutes().toString().padStart(2, '0');
            const secs = now.getSeconds().toString().padStart(2, '0');
            
            document.getElementById('live-clock').textContent = `${hours}:${mins}:${secs}`;
            
            const weekdays = ['日','一','二','三','四','五','六'];
            document.getElementById('live-date').textContent = 
                `${now.getFullYear()}年${(now.getMonth()+1).toString().padStart(2, '0')}月${now.getDate().toString().padStart(2, '0')}日 星期${weekdays[now.getDay()]}`;
            
            const start = new Date();
            start.setHours(0,0,0,0);
            const progress = ((now - start) / 86400000) * 100;
            document.querySelector('#day-progress > div').style.width = `${progress}%`;
            document.querySelector('#seconds-counter > span:first-child').textContent = 
                `今日进度 ${progress.toFixed(1)}%`;
            
            const greeting = hours < 12 ? '上午' : hours < 18 ? '下午' : '晚上';
            document.querySelector('#seconds-counter > span:last-child').textContent = `${greeting}好`;
        }
        
        updateClock();
        setInterval(updateClock, 1000);
        </script>
    </div>
    
    <div class="dashboard-row">
        <div class="card">
            <div class="card-header">
                <h3>最近备案</h3>
                <a href="all-icp.php" class="btn btn-sm btn-outline">查看全部</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>备案号</th>
                                <th>网站标题</th>
                                <th>域名</th>
                                <th>状态</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM icp_records ORDER BY update_time DESC LIMIT 5");
                            while ($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['icp_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['site_title']); ?></td>
                                <td><?php echo htmlspecialchars($row['site_domain']); ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $row['status'] === 'pending' ? 'warning' : 
                                             ($row['status'] === 'approved' ? 'success' : 'danger'); 
                                    ?>">
                                        <?php 
                                        echo $row['status'] === 'pending' ? '待审核' : 
                                             ($row['status'] === 'approved' ? '已通过' : '已驳回'); 
                                        ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>系统状态</h3>
            </div>
            <div class="card-body">
                <div class="status-item">
                    <i class="mdi mdi-server"></i>
                    <div>
                        <h4>服务器状态</h4>
                        <p>PHP <?php echo phpversion(); ?> | <?php echo $_SERVER['SERVER_SOFTWARE'] ?? '未知'; ?></p>
                    </div>
                    <span class="badge badge-success"><?php echo $serverStatus; ?></span>
                </div>
                <div class="status-item">
                    <i class="mdi mdi-database"></i>
                    <div>
                        <h4>数据库状态</h4>
                        <p>MySQL <?php echo $pdo->getAttribute(PDO::ATTR_SERVER_VERSION); ?></p>
                    </div>
                    <span class="badge badge-success"><?php echo $dbStatus; ?></span>
                </div>
                <div class="status-item">
                    <i class="mdi mdi-email"></i>
                    <div>
                        <h4>邮件服务</h4>
                        <p><?php echo $mailConfigured ? 'SMTP 已配置' : 'SMTP 未配置'; ?></p>
                    </div>
                    <span class="badge badge-<?php echo $mailConfigured ? 'success' : 'danger'; ?>"><?php echo $mailStatus; ?></span>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require('includes/footer.php'); ?>