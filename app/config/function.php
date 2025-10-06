<?
try {
    $dsn = "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=".DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage());
}

function getSiteSettings() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM web_settings LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getCustomContents() {
    global $pdo;
    $stmt = $pdo->query("SELECT * FROM custom_contents LIMIT 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$siteSettings = getSiteSettings();

$customcontents = getCustomContents();

if(!$siteSettings) {
    die("无法获取站点设置，请检查数据库或文件权限配置");
}

function getSmtpSettings() {
    global $pdo;
    
    try {
        $stmt = $pdo->query("SELECT smtp_user, smtp_host, smtp_port, smtp_pass FROM system_settings LIMIT 1");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('[' . date('Y-m-d H:i:s') . '] PDO Error: ' . $e->getMessage() . "\n", 3, '../app/logs/db_error.log');
        return false;
    }
}

$sitename = $siteSettings['site_name'];
$maintitle = $siteSettings['main_title'];
$subtitle = $siteSettings['sub_title'];
$logourl = $siteSettings['logo_url'];
$shortname = $siteSettings['short_name'];
$sitedomain = $siteSettings['site_domain'];
$adminemail = $siteSettings['admin_email'];
$globalcss = $customcontents['global_css'];
$globaljs = $customcontents['global_js'];
$headerhtml = $customcontents['header_html'];