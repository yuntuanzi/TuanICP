<?php
ob_start();
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: login.php');
    exit;
}

require('../../app/config/db.php');
require_once '../../app/config/function.php';

$stmt = $pdo->query("SELECT COUNT(*) as total FROM icp_records");
$totalIcp = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as pending FROM icp_records WHERE status = 'pending'");
$pendingIcp = $stmt->fetch()['pending'];

$stmt = $pdo->query("SELECT COUNT(*) as approved FROM icp_records WHERE status = 'approved'");
$approvedIcp = $stmt->fetch()['approved'];

$stmt = $pdo->query("SELECT COUNT(*) as rejected FROM icp_records WHERE status = 'rejected'");
$rejectedIcp = $stmt->fetch()['rejected'];

$stmt = $pdo->query("SELECT COUNT(*) as reports FROM icp_reports");
$totalReports = $stmt->fetch()['reports'];

$stmt = $pdo->query("SELECT COUNT(*) as changes FROM icp_changes");
$totalChanges = $stmt->fetch()['changes'];

?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ICP备案管理系统 - <?php echo $title ?? '控制面板'; ?></title>
    <link href="./assets/css2?family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div class="admin-main">
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle">
                        <i class="mdi mdi-menu"></i>
                    </button>
                    <h1><?php echo $title ?? '控制面板'; ?></h1>
                </div>
                <div class="header-right">
                    <div class="user-dropdown">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['admin_nickname']); ?>&background=7e22ce&color=fff" alt="用户头像">
                        <span><?php echo htmlspecialchars($_SESSION['admin_nickname']); ?></span>
                        <i class="mdi mdi-chevron-down"></i>
                        <div class="dropdown-menu">
                            <a href="admin-accounts.php"><i class="mdi mdi-account"></i> 个人资料</a>
                            <a href="site-settings.php"><i class="mdi mdi-cog"></i> 站点设置</a>
                            <a href="logout.php"><i class="mdi mdi-logout"></i> 退出登录</a>
                        </div>
                    </div>
                </div>
            </header>