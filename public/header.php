<script src="assets/js/header.js" defer></script>
<link rel="stylesheet" href="assets/css/main.css">
<link rel="stylesheet" href="assets/css/header.css">
<div class="center">
    <div class="mheader">
        <div class="header_box">
            <h2 class="logo"><?php echo htmlspecialchars($sitename); ?></h2>
            <div class="bar">
                <span class="bar_btn">
                    <a class="nav-item <?php if ($page == 'index') { echo 'active'; } ?>" data-page="home" href="index.php">
                        <i class="menu-icon">🏠</i>
                        <span>主页</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'about') { echo 'active'; } ?>" href="about.php">
                        <i class="menu-icon"></i>
                        <span>关于</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'xz') { echo 'active'; } ?>" href="xz.php">
                        <i class="menu-icon"></i>
                        <span>加入</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'change') { echo 'active'; } ?>" href="change.php">
                        <i class="menu-icon"></i>
                        <span>变更</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'gs') { echo 'active'; } ?>" href="gs.php">
                        <i class="menu-icon"></i>
                        <span>公示</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'qy') { echo 'active'; } ?>" href="qy.php">
                        <i class="menu-icon"></i>
                        <span>迁跃</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'github') { echo 'active'; } ?>" href="https://github.com/yuntuanzi/TuanICP/">
                        <i class="menu-icon"></i>
                        <span>下载源码</span>
                    </a>
                    <a class="nav-item <?php if ($page == 'qq') { echo 'active'; } ?>" href="https://qm.qq.com/q/89ZUnSvQxG">
                        <i class="menu-icon"></i>
                        <span>交流Q群</span>
                    </a>
                </span>
            </div>

            <button class="mobile-menu-btn" id="mobileMenuBtn">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
    

    <div class="sidebar" id="sidebar">
        <a class="nav-item <?php if ($page == 'index') { echo 'active'; } ?>" data-page="home" href="index.php">
            <i class="menu-icon">🏠</i>
            <span>主页</span>
        </a>
        <a class="nav-item <?php if ($page == 'about') { echo 'active'; } ?>" href="about.php">
            <i class="menu-icon"></i>
            <span>关于</span>
        </a>
        <a class="nav-item <?php if ($page == 'xz') { echo 'active'; } ?>" href="xz.php">
            <i class="menu-icon"></i>
            <span>加入</span>
        </a>
        <a class="nav-item <?php if ($page == '#') { echo 'active'; } ?>" href="change.php">
            <i class="menu-icon"></i>
            <span>变更</span>
        </a>
        <a class="nav-item <?php if ($page == 'gs') { echo 'active'; } ?>" href="gs.php">
            <i class="menu-icon"></i>
            <span>公示</span>
        </a>
        <a class="nav-item <?php if ($page == 'qy') { echo 'active'; } ?>" href="qy.php">
            <i class="menu-icon"></i>
            <span>迁跃</span>
        </a>
        <a class="nav-item <?php if ($page == 'github') { echo 'active'; } ?>" href="https://github.com/yuntuanzi/TuanICP/">
            <i class="menu-icon"></i>
            <span>下载源码</span>
        </a>
        <a class="nav-item <?php if ($page == 'qq') { echo 'active'; } ?>" href="https://qm.qq.com/q/89ZUnSvQxG">
            <i class="menu-icon"></i>
            <span>交流Q群</span>
        </a>
    </div>
    

    <div class="overlay" id="overlay"></div>
</div>