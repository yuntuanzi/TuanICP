<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function logIcpReminder($message) {
    $logFile = dirname(__DIR__) . '/app/logs/icp_reminder.log';
    $timestamp = date('[Y-m-d H:i:s]');
    file_put_contents($logFile, $timestamp . ' ' . $message . "\n", FILE_APPEND);
}

try {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as pending_count FROM icp_records WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $pendingCount = (int)$result['pending_count'];
    
    if ($pendingCount === 0) {
        logIcpReminder("没有待审核的ICP备案记录，无需发送提醒邮件");
        if (php_sapi_name() !== 'cli') {
            echo "没有待审核的ICP备案记录";
        }
        exit(0);
    }
    
    logIcpReminder("发现 {$pendingCount} 条待审核的ICP备案记录");
    
    $smtpSettings = getSmtpSettings();
    if (!$smtpSettings || empty($smtpSettings['smtp_host']) || empty($smtpSettings['smtp_user'])) {
        logIcpReminder("错误：SMTP配置不完整或不存在");
        if (php_sapi_name() !== 'cli') {
            echo "SMTP配置不完整";
        }
        exit(1);
    }
    
    $stmt = $pdo->query("SELECT email FROM admin_accounts");
    $adminEmails = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    if (empty($adminEmails)) {
        global $adminemail;
        $adminEmails = [$adminemail];
    }
    
    $siteSettings = getSiteSettings();
    $siteName = $siteSettings['site_name'] ?? 'ICP备案系统';
    global $sitedomain;
    
    $subject = "【{$siteName}】有 {$pendingCount} 条ICP备案待审核";
    $body = "<h2>{$siteName} 待审核ICP备案提醒</h2>";
    $body .= "<p>系统检测到当前有 <strong>{$pendingCount}</strong> 条ICP备案申请等待审核。</p>";
    $body .= "<p>请及时登录管理系统处理：<a href=\"http://{$sitedomain}/admin\">http://{$sitedomain}/admin</a></p>";
    $body .= "<p>此邮件为系统自动发送，请勿直接回复。</p>";
    
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = $smtpSettings['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $smtpSettings['smtp_user'];
        $mail->Password = $smtpSettings['smtp_pass'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = $smtpSettings['smtp_port'];
        
        $mail->setFrom($smtpSettings['smtp_user'], $siteName);
        
        foreach ($adminEmails as $email) {
            $mail->addAddress($email);
        }
        
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        $mail->send();
        logIcpReminder("成功发送提醒邮件给 " . implode(', ', $adminEmails));
        
        if (php_sapi_name() !== 'cli') {
            echo "成功发送提醒邮件给 " . implode(', ', $adminEmails);
        }
    } catch (Exception $e) {
        logIcpReminder("邮件发送失败: " . $mail->ErrorInfo);
        if (php_sapi_name() !== 'cli') {
            echo "邮件发送失败";
        }
        exit(1);
    }
    
} catch (PDOException $e) {
    logIcpReminder("数据库错误: " . $e->getMessage());
    if (php_sapi_name() !== 'cli') {
        echo "数据库错误";
    }
    exit(1);
} catch (Exception $e) {
    logIcpReminder("系统错误: " . $e->getMessage());
    if (php_sapi_name() !== 'cli') {
        echo "系统错误";
    }
    exit(1);
}

exit(0);