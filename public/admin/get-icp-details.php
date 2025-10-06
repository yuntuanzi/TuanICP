<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => '未授权访问']);
    exit;
}

require('../../app/config/db.php');
require_once '../../app/config/function.php';

if (!isset($_GET['uid'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => '缺少必要参数']);
    exit;
}

$uid = (int)$_GET['uid'];

try {
    $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE uid = ?");
    $stmt->execute([$uid]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$record) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => '未找到指定的备案记录']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'record' => $record]);
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => '数据库查询失败: ' . $e->getMessage()]);
}