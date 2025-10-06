<div class="admin-sidebar">
    <div class="sidebar-brand">
        <h2><?php echo htmlspecialchars($shortname); ?>ICP备案管理系统</h2>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <i class="mdi mdi-view-dashboard"></i>
                    <span>总览</span>
                </a>
            </li>
            <li>
                <a href="all-icp.php">
                    <i class="mdi mdi-file-document-multiple"></i>
                    <span>全部备案</span>
                    <span class="badge"><?php echo $totalIcp; ?></span>
                </a>
            </li>
            <li>
                <a href="all-icp.php?status=pending">
                    <i class="mdi mdi-clock-outline"></i>
                    <span>待审核</span>
                    <span class="badge badge-warning"><?php echo $pendingIcp; ?></span>
                </a>
            </li>
            <li>
                <a href="all-icp.php?status=approved">
                    <i class="mdi mdi-check-circle-outline"></i>
                    <span>审核通过</span>
                    <span class="badge badge-success"><?php echo $approvedIcp; ?></span>
                </a>
            </li>
            <li>
                <a href="all-icp.php?status=rejected">
                    <i class="mdi mdi-close-circle-outline"></i>
                    <span>审核驳回</span>
                    <span class="badge badge-danger"><?php echo $rejectedIcp; ?></span>
                </a>
            </li>
            <li class="sidebar-divider">系统设置</li>
            <li>
                <a href="site-settings.php">
                    <i class="mdi mdi-web"></i>
                    <span>站点信息</span>
                </a>
            </li>
            <li>
                <a href="mail-settings.php">
                    <i class="mdi mdi-cog"></i>
                    <span>邮箱设置</span>
                </a>
            </li>
            <li>
                <a href="style-settings.php">
                    <i class="mdi mdi-cog"></i>
                    <span>自定义样式</span>
                </a>
            </li>
            <li>
                <a href="admin-accounts.php">
                    <i class="mdi mdi-account-group"></i>
                    <span>管理员账户</span>
                </a>
            </li>
        </ul>

    </nav>
    
    <div class="sidebar-footer">
        <p>版本 v2.0.∞</p>
    </div>
</div>