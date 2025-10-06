<?php
date_default_timezone_set('Asia/Shanghai');

define('ROOT_DIR', dirname(__DIR__));

require_once ROOT_DIR . '/app/config/db.php';
require_once ROOT_DIR . '/app/config/function.php';

function logPluginAccess($message) {
    $logFile = ROOT_DIR . '/app/logs/plugin_access.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, $timestamp . ' ' . $message . "\n", FILE_APPEND);
}

function validatePluginRequest() {
    if (!isset($_SERVER['HTTP_HOST']) || !isset($_GET['pluginid'])) {
        die('非法访问');
    }

    $pluginId = trim($_GET['pluginid']);
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $pluginId)) {
        die('插件ID不合法');
    }

    $pluginFile = ROOT_DIR . '/plugin/' . $pluginId . '.php';
    if (!file_exists($pluginFile)) {
        die('插件不存在');
    }

    return $pluginFile;
}

try {
    $pluginFile = validatePluginRequest();

    logPluginAccess("访问插件: " . basename($pluginFile) . " IP: " . $_SERVER['REMOTE_ADDR']);

    require_once $pluginFile;
    
} catch (Exception $e) {
    logPluginAccess("插件执行错误: " . $e->getMessage());
    header('HTTP/1.1 500 Internal Server Error');
    die('插件执行出错');
}