<?php
header('Content-Type: application/json; charset=utf-8');
require_once('../app/config/db.php');
require_once '../app/config/function.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$response = [
    'status' => 'error',
    'message' => '请输入备案号或域名进行查询',
    'data' => null
];

if (!empty($keyword)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM icp_records WHERE icp_number = :keyword OR site_domain = :keyword LIMIT 1");
        $stmt->execute([':keyword' => $keyword]);
        $icpInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($icpInfo) {
            // 处理域名列表
            $domains = [];
            if (!empty($icpInfo['site_domain'])) {
                $domainList = explode("\n", $icpInfo['site_domain']);
                foreach ($domainList as $domain) {
                    $domain = trim($domain);
                    if (!empty($domain)) {
                        $domains[] = $domain;
                    }
                }
            }
            
            // 构建响应数据
            $response = [
                'status' => 'success',
                'message' => '查询成功',
                'data' => [
                    'site_title' => $icpInfo['site_title'] ?? '未知',
                    'site_domain' => $domains,
                    'site_description' => $icpInfo['site_description'] ?? '暂无描述',
                    'icp_number' => $icpInfo['icp_number'] ?? '未知',
                    'icp_number_formatted' => ($shortname ?? '') . 'ICP备' . ($icpInfo['icp_number'] ?? '未知') . '号',
                    'owner' => $icpInfo['owner'] ?? '未知',
                    'update_time' => $icpInfo['update_time'] ?? '未知',
                    'status' => $icpInfo['status'] ?? 'unknown',
                    'status_text' => getStatusText($icpInfo['status'] ?? 'unknown'),
                    'timestamp' => time()
                ]
            ];
            
            // 如果是待审核或驳回状态，添加重定向提示
            if ($icpInfo['status'] === 'pending' || $icpInfo['status'] === 'rejected') {
                $response['redirect'] = 'xg.php?keyword=' . urlencode($icpInfo['icp_number']);
            }
        } else {
            $response = [
                'status' => 'error',
                'message' => '未找到相关备案信息',
                'data' => null
            ];
        }
    } catch (PDOException $e) {
        $response = [
            'status' => 'error',
            'message' => '数据库查询失败: ' . $e->getMessage(),
            'data' => null
        ];
    }
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

function getStatusText($status) {
    switch ($status) {
        case 'approved':
            return '审核通过';
        case 'pending':
            return '待审核';
        case 'rejected':
            return '审核驳回';
        default:
            return '未知状态';
    }
}
?>