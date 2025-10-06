<?php
require('includes/header.php');

$pageTitle = "ICP备案管理 - " . $sitename;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['uid'])) {
        $uid = (int)$_POST['uid'];
        $action = $_POST['action'];
        
        try {
            if ($action === 'approve') {
                $stmt = $pdo->prepare("UPDATE icp_records SET status = 'approved', update_time = NOW() WHERE uid = ?");
                $stmt->execute([$uid]);
            } elseif ($action === 'reject') {
                $stmt = $pdo->prepare("UPDATE icp_records SET status = 'rejected', update_time = NOW() WHERE uid = ?");
                $stmt->execute([$uid]);
            } elseif ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM icp_records WHERE uid = ?");
                $stmt->execute([$uid]);
            } elseif ($action === 'update') {
                $icp_number = trim($_POST['icp_number']);
                $site_title = trim($_POST['site_title']);
                $site_domain = trim($_POST['site_domain']);
                $owner = trim($_POST['owner']);
                $email = trim($_POST['email']);
                $qq = trim($_POST['qq']);
                $status = $_POST['status'];
                $inspection_status = $_POST['inspection_status'];
                $remark = trim($_POST['remark']);
                
                if (empty($icp_number) || empty($site_title) || empty($site_domain) || empty($owner) || empty($email)) {
                    throw new Exception("必填字段不能为空");
                }
                
                $stmt = $pdo->prepare("UPDATE icp_records SET 
                    icp_number = ?, 
                    site_title = ?, 
                    site_domain = ?, 
                    owner = ?, 
                    email = ?, 
                    qq = ?, 
                    status = ?, 
                    inspection_status = ?, 
                    remark = ?, 
                    update_time = NOW() 
                    WHERE uid = ?");
                
                $stmt->execute([
                    $icp_number,
                    $site_title,
                    $site_domain,
                    $owner,
                    $email,
                    $qq,
                    $status,
                    $inspection_status,
                    $remark,
                    $uid
                ]);
            }
            
            header("Location: all-icp.php");
            exit;
        } catch (PDOException $e) {
            $errorMessage = "数据库操作失败: " . $e->getMessage();
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
        }
    }
}

$statusFilter = isset($_GET['status']) ? $_GET['status'] : null;

try {
    if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
        $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE status = ? ORDER BY update_time DESC");
        $stmt->execute([$statusFilter]);
    } else {
        $stmt = $pdo->query("SELECT * FROM icp_records ORDER BY update_time DESC");
    }
    $icpRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("获取ICP记录失败: " . $e->getMessage());
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="./assets/css2?family=Noto+Sans+SC:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./assets/npm/@mdi/font@6.5.95/css/materialdesignicons.min.css">
    <style>
        :root {
            --primary-color: #4a6bdf;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --border-color: #dee2e6;
            --font-family: 'Noto Sans SC', sans-serif;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: var(--font-family);
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .admin-container {
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
        
        .admin-title {
            font-size: 24px;
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .admin-nav {
            display: flex;
            gap: 15px;
        }
        
        .admin-nav a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: 500;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover {
            background-color: rgba(74, 107, 223, 0.1);
        }
        
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 15px 20px;
            background-color: var(--light-color);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }
        
        .table th {
            background-color: var(--light-color);
            font-weight: 500;
            color: var(--dark-color);
        }
        
        .table tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            margin-bottom: 2px;
        }
        
        .status-pending {
            background-color: var(--warning-color);
            color: #212529;
        }
        
        .status-approved {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }
        
        .inspection-normal {
            color: var(--success-color);
        }
        
        .inspection-abnormal {
            color: var(--danger-color);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            text-decoration: none;
            white-space: nowrap;
        }
        
        .btn-sm {
            padding: 4px 8px;
            font-size: 12px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #3a5bd9;
        }
        
        .btn-success {
            background-color: var(--success-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            color: #212529;
        }
        
        .btn-warning:hover {
            background-color: #e0a800;
        }
        
        .btn-info {
            background-color: var(--info-color);
            color: white;
        }
        
        .btn-info:hover {
            background-color: #138496;
        }
        
        .btn-light {
            background-color: var(--light-color);
            color: #212529;
        }
        
        .btn-light:hover {
            background-color: #e2e6ea;
        }
        
        .btn-group {
            display: flex;
            gap: 6px;
        }
        
        .avatar {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 8px;
        }
        
        .text-truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-dialog {
            background-color: white;
            border-radius: 8px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-title {
            font-size: 18px;
            font-weight: 500;
            margin: 0;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            padding: 15px 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6c757d;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
        }
        
        .select-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='https://www.w3.org/2000/svg' width='16' height='16' fill='%23333' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px;
        }
        
        .alert {
            padding: 10px 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(220, 53, 69, 0.2);
        }
        
        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .text-center {
            text-align: center;
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: #6c757d;
        }
        
        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #adb5bd;
        }
        
        .filter-tabs {
            display: flex;
            gap: 8px;
            background: transparent;
            padding: 4px;
            margin-right: auto;
        }
        
        .filter-tab {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            background: rgba(255,255,255,0.8);
            color: #555;
            white-space: nowrap;
            border: 1px solid var(--border-color);
        }
        
        .filter-tab.active {
            color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-color: transparent;
        }
        
        .filter-tab[data-status="all"] {
            background-color: rgba(74, 107, 223, 0.15);
            color: var(--primary-color);
        }
        
        .filter-tab[data-status="pending"] {
            background-color: rgba(255, 193, 7, 0.15);
            color: #d39e00;
        }
        
        .filter-tab[data-status="approved"] {
            background-color: rgba(40, 167, 69, 0.15);
            color: var(--success-color);
        }
        
        .filter-tab[data-status="rejected"] {
            background-color: rgba(220, 53, 69, 0.15);
            color: var(--danger-color);
        }
        
        .filter-tab.active[data-status="all"] {
            background-color: var(--primary-color);
        }
        
        .filter-tab.active[data-status="pending"] {
            background-color: var(--warning-color);
        }
        
        .filter-tab.active[data-status="approved"] {
            background-color: var(--success-color);
        }
        
        .filter-tab.active[data-status="rejected"] {
            background-color: var(--danger-color);
        }
        
        .filter-tab:hover:not(.active) {
            opacity: 0.8;
        }
        .filter-tab.active,
        .filter-tab.active[data-status="all"],
        .filter-tab.active[data-status="pending"],
        .filter-tab.active[data-status="approved"],
        .filter-tab.active[data-status="rejected"] {
            color: white !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-color: transparent;
        }


        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .admin-nav {
                width: 100%;
                overflow-x: auto;
                padding-bottom: 10px;
            }
            
            .table th, .table td {
                padding: 8px 10px;
            }
            
            .btn-group {
                flex-wrap: wrap;
            }
            
            .btn {
                margin-bottom: 5px;
            }
        }
            @media (max-width: 992px) {
                .table th, .table td {
                    padding: 10px 12px;
                    font-size: 14px;
                }
            }
            
            @media (max-width: 576px) {
                .btn-group {
                    flex-direction: column;
                }
                .table-responsive {
                    border: 1px solid var(--border-color);
                    border-radius: 8px;
                }
            }
    </style>
</head>
<body>
    <div class="admin-container">
        
        <?php if (isset($errorMessage)): ?>
        <div class="alert alert-danger">
            <i class="mdi mdi-alert-circle"></i> <?php echo htmlspecialchars($errorMessage); ?>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="mdi mdi-check-circle"></i> 操作成功完成！
        </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">备案记录列表</h2>
                <div class="d-flex align-items-center gap-3">
                    <div class="filter-tabs">
                        <a href="all-icp.php" class="filter-tab <?php echo !isset($_GET['status']) ? 'active' : ''; ?>" data-status="all">全部备案</a>
                        <a href="all-icp.php?status=pending" class="filter-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'pending' ? 'active' : ''; ?>" data-status="pending">待审核</a>
                        <a href="all-icp.php?status=approved" class="filter-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'approved' ? 'active' : ''; ?>" data-status="approved">审核通过</a>
                        <a href="all-icp.php?status=rejected" class="filter-tab <?php echo isset($_GET['status']) && $_GET['status'] === 'rejected' ? 'active' : ''; ?>" data-status="rejected">审核驳回</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($icpRecords)): ?>
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="mdi mdi-database-remove"></i>
                    </div>
                    <h3>暂无备案记录</h3>
                    <p>还没有任何ICP备案记录，快前往前台添加一个</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>备案号</th>
                                <th>网站标题</th>
                                <th>域名</th>
                                <th>所有者</th>
                                <th>状态</th>
                                <th>更新时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($icpRecords as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['icp_number']); ?></td>
<td>
    <?php if ($record['site_avatar']): ?>
        <?php
        $avatarUrl = str_replace('http://', 'https://', $record['site_avatar']);
        ?>
        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" 
             class="avatar" 
             alt="网站头像" 
             onerror="this.src='data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'40\' height=\'40\' viewBox=\'0 0 40 40\'%3E%3Crect width=\'40\' height=\'40\' fill=\'%23eee\'/%3E%3Ctext x=\'50%\' y=\'50%\' font-size=\'14\' fill=\'%23666\' text-anchor=\'middle\' dominant-baseline=\'middle\'%3E无头像%3C/text%3E%3C/svg%3E'">
    <?php else: ?>
        <img src="data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40' viewBox='0 0 40 40'%3E%3Crect width='40' height='40' fill='%23eee'/%3E%3Ctext x='50%' y='50%' font-size='14' fill='%23666' text-anchor='middle' dominant-baseline='middle'%3E无头像%3C/text%3E%3C/svg%3E" 
             class="avatar" 
             alt="网站头像">
    <?php endif; ?>
    <span class="text-truncate"><?php echo htmlspecialchars($record['site_title']); ?></span>
</td>
                                <td>
                                    <a href="https://<?php echo htmlspecialchars($record['site_domain']); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="text-truncate d-block" 
                                       style="color: var(--primary-color); text-decoration: none;">
                                        <?php echo htmlspecialchars($record['site_domain']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($record['owner']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $record['status']; ?>">
                                        <?php 
                                            switch($record['status']) {
                                                case 'pending': echo '待审核'; break;
                                                case 'approved': echo '已通过'; break;
                                                case 'rejected': echo '已驳回'; break;
                                            }
                                        ?>
                                    </span>
                                    <br>
                                    <small class="inspection-<?php echo $record['inspection_status']; ?>">
                                        <?php echo $record['inspection_status'] === 'normal' ? '正常' : '异常'; ?>
                                    </small>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($record['update_time'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                    <a href="https://<?php echo htmlspecialchars($record['site_domain']); ?>" 
                                       target="_blank" 
                                       rel="noopener noreferrer"
                                       class="btn btn-light btn-sm">
                                        <i class="mdi mdi-open-in-new"></i> 访问
                                    </a>
                                        <button class="btn btn-info btn-sm view-btn" data-uid="<?php echo $record['uid']; ?>">
                                            <i class="mdi mdi-eye"></i> 查看
                                        </button>
                                        <button class="btn btn-primary btn-sm edit-btn" data-uid="<?php echo $record['uid']; ?>">
                                            <i class="mdi mdi-pencil"></i> 修改
                                        </button>
                                        <?php if ($record['status'] !== 'approved'): ?>
                                        <button class="btn btn-success btn-sm approve-btn" data-uid="<?php echo $record['uid']; ?>">
                                            <i class="mdi mdi-check"></i> 通过
                                        </button>
                                        <?php endif; ?>
                                        <?php if ($record['status'] !== 'rejected'): ?>
                                        <button class="btn btn-warning btn-sm reject-btn" data-uid="<?php echo $record['uid']; ?>">
                                            <i class="mdi mdi-close"></i> 驳回
                                        </button>
                                        <?php endif; ?>
                                        <button class="btn btn-danger btn-sm delete-btn" data-uid="<?php echo $record['uid']; ?>">
                                            <i class="mdi mdi-delete"></i> 删除
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="modal" id="viewModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">备案详情</h3>
                <button class="close">&times;</button>
            </div>
            <div class="modal-body" id="viewModalBody">
            </div>
            <div class="modal-footer">
                <button class="btn btn-light close-btn">关闭</button>
            </div>
        </div>
    </div>
    
    <div class="modal" id="editModal">
        <div class="modal-dialog">
            <form id="editForm" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="uid" id="editUid">
                <div class="modal-header">
                    <h3 class="modal-title">修改备案信息</h3>
                    <button type="button" class="close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="editIcpNumber">备案号</label>
                        <input type="text" class="form-control" id="editIcpNumber" name="icp_number" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editSiteTitle">网站标题</label>
                        <input type="text" class="form-control" id="editSiteTitle" name="site_title" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editSiteDomain">网站域名</label>
                        <input type="text" class="form-control" id="editSiteDomain" name="site_domain" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editOwner">所有者</label>
                        <input type="text" class="form-control" id="editOwner" name="owner" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editEmail">联系邮箱</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editQq">联系QQ</label>
                        <input type="text" class="form-control" id="editQq" name="qq">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editStatus">状态</label>
                        <select class="form-control select-control" id="editStatus" name="status" required>
                            <option value="pending">待审核</option>
                            <option value="approved">已通过</option>
                            <option value="rejected">已驳回</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editInspectionStatus">巡查状态</label>
                        <select class="form-control select-control" id="editInspectionStatus" name="inspection_status" required>
                            <option value="normal">正常</option>
                            <option value="abnormal">异常</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="editRemark">备注</label>
                        <textarea class="form-control" id="editRemark" name="remark" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light close-btn">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="modal" id="confirmModal">
        <div class="modal-dialog">
            <form id="confirmForm" method="post">
                <input type="hidden" name="action" id="confirmAction">
                <input type="hidden" name="uid" id="confirmUid">
                <div class="modal-header">
                    <h3 class="modal-title" id="confirmTitle">确认操作</h3>
                    <button type="button" class="close">&times;</button>
                </div>
                <div class="modal-body" id="confirmMessage">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light close-btn">取消</button>
                    <button type="submit" class="btn" id="confirmBtn">确认</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const statusParam = urlParams.get('status');
    
    if (statusParam && statusParam !== 'all') {
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.querySelector('.status-badge').classList.contains(`status-${statusParam}`) 
                ? '' 
                : 'none';
        });
    }
    
    document.querySelectorAll('.filter-tab').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            const status = this.getAttribute('data-status');
            let url = 'all-icp.php';
            
            if (status !== 'all') {
                url += `?status=${status}`;
            }
            
            window.location.href = url;
        });
    });
            const viewModal = document.getElementById('viewModal');
            const editModal = document.getElementById('editModal');
            const confirmModal = document.getElementById('confirmModal');
            
            const closeButtons = document.querySelectorAll('.close, .close-btn');
            const viewButtons = document.querySelectorAll('.view-btn');
            const editButtons = document.querySelectorAll('.edit-btn');
            const approveButtons = document.querySelectorAll('.approve-btn');
            const rejectButtons = document.querySelectorAll('.reject-btn');
            const deleteButtons = document.querySelectorAll('.delete-btn');
            
            function closeModal(modal) {
                modal.classList.remove('show');
            }
            
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.modal');
                    closeModal(modal);
                });
            });
            
            [viewModal, editModal, confirmModal].forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeModal(this);
                    }
                });
            });
            
            viewButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const uid = this.getAttribute('data-uid');
                    
                    fetch(`get-icp-details.php?uid=${uid}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const record = data.record;
                                const html = `
                                    <div class="form-group">
                                        <label class="form-label">备案号</label>
                                        <p>${record.icp_number}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">网站标题</label>
                                        <p>${record.site_title}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">网站描述</label>
                                        <p>${record.site_description || '无'}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">网站域名</label>
                                        <p>${record.site_domain}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">网站头像</label>
                                        <div>
                                            ${record.site_avatar ? `<img src="${record.site_avatar}" style="max-width: 100px; max-height: 100px; border-radius: 4px;" onerror="this.src='https://via.placeholder.com/100'">` : '无'}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">所有者</label>
                                        <p>${record.owner}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">更新时间</label>
                                        <p>${new Date(record.update_time).toLocaleString()}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">联系邮箱</label>
                                        <p>${record.email}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">联系QQ</label>
                                        <p>${record.qq || '无'}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">状态</label>
                                        <p>
                                            <span class="status-badge status-${record.status}">
                                                ${record.status === 'pending' ? '待审核' : record.status === 'approved' ? '已通过' : '已驳回'}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">巡查状态</label>
                                        <p class="inspection-${record.inspection_status}">
                                            ${record.inspection_status === 'normal' ? '正常' : '异常'}
                                        </p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">备注</label>
                                        <p>${record.remark || '无'}</p>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">提交IP</label>
                                        <p>${record.submit_ip || '未知'}</p>
                                    </div>
                                `;
                                
                                document.getElementById('viewModalBody').innerHTML = html;
                                viewModal.classList.add('show');
                            } else {
                                alert('获取详情失败: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('获取详情时发生错误');
                        });
                });
            });
            
            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const uid = this.getAttribute('data-uid');
                    
                    fetch(`get-icp-details.php?uid=${uid}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const record = data.record;
                                
                                document.getElementById('editUid').value = record.uid;
                                document.getElementById('editIcpNumber').value = record.icp_number;
                                document.getElementById('editSiteTitle').value = record.site_title;
                                document.getElementById('editSiteDomain').value = record.site_domain;
                                document.getElementById('editOwner').value = record.owner;
                                document.getElementById('editEmail').value = record.email;
                                document.getElementById('editQq').value = record.qq || '';
                                document.getElementById('editStatus').value = record.status;
                                document.getElementById('editInspectionStatus').value = record.inspection_status;
                                document.getElementById('editRemark').value = record.remark || '';
                                
                                editModal.classList.add('show');
                            } else {
                                alert('获取详情失败: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('获取详情时发生错误');
                        });
                });
            });
            
            approveButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const uid = this.getAttribute('data-uid');
                    
                    document.getElementById('confirmAction').value = 'approve';
                    document.getElementById('confirmUid').value = uid;
                    document.getElementById('confirmTitle').textContent = '确认通过备案';
                    document.getElementById('confirmMessage').textContent = '您确定要通过此备案申请吗？';
                    document.getElementById('confirmBtn').className = 'btn btn-success';
                    document.getElementById('confirmBtn').textContent = '确认通过';
                    
                    confirmModal.classList.add('show');
                });
            });
            
            rejectButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const uid = this.getAttribute('data-uid');
                    
                    document.getElementById('confirmAction').value = 'reject';
                    document.getElementById('confirmUid').value = uid;
                    document.getElementById('confirmTitle').textContent = '确认驳回备案';
                    document.getElementById('confirmMessage').textContent = '您确定要驳回此备案申请吗？';
                    document.getElementById('confirmBtn').className = 'btn btn-warning';
                    document.getElementById('confirmBtn').textContent = '确认驳回';
                    
                    confirmModal.classList.add('show');
                });
            });
            
            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const uid = this.getAttribute('data-uid');
                    
                    document.getElementById('confirmAction').value = 'delete';
                    document.getElementById('confirmUid').value = uid;
                    document.getElementById('confirmTitle').textContent = '确认删除备案';
                    document.getElementById('confirmMessage').textContent = '您确定要删除此备案记录吗？此操作不可撤销！';
                    document.getElementById('confirmBtn').className = 'btn btn-danger';
                    document.getElementById('confirmBtn').textContent = '确认删除';
                    
                    confirmModal.classList.add('show');
                });
            });
            
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const status = this.getAttribute('data-status');
        document.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = status === 'all' || row.querySelector('.status-badge').classList.contains(`status-${status}`) 
                ? '' 
                : 'none';
        });
    });
});
        });
    </script>
</body>
</html>