<?php
require_once('../app/config/db.php');
require_once '../vendor/autoload.php';
require_once '../app/config/function.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('非法请求');
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception('邮箱格式不正确');
    }

    $redis = new Redis();
    $redis->connect(REDIS_HOST, REDIS_PORT);
    if (REDIS_PASS) {
        $redis->auth(REDIS_PASS);
    }

    $lastSendTime = $redis->get(REDIS_PREFIX . 'email_time_' . $email);
    if ($lastSendTime && time() - $lastSendTime < 60) {
        throw new Exception('操作过于频繁，请稍后再试');
    }

    $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

    $smtpSettings = getSmtpSettings();
    if (!$smtpSettings) {
        throw new Exception('邮件服务配置错误');
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $smtpSettings['smtp_host'];
    $mail->Port = $smtpSettings['smtp_port'];
    $mail->SMTPAuth = true;
    $mail->Username = $smtpSettings['smtp_user'];
    $mail->Password = $smtpSettings['smtp_pass'];
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    $mail->setFrom($smtpSettings['smtp_user'], '二次元虚拟ICP备案系统');
    $mail->addAddress($email);
    $mail->Subject = '您的二次元虚拟ICP备案验证码';
    
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>您的二次元虚拟ICP备案验证码</title>
    </head>
    <body style="margin: 0; padding: 0; font-family: \'Helvetica Neue\', Helvetica, Arial, sans-serif; line-height: 1.6; color: #2d3748; background-color: #f8fafc;">
        <div class="email-container" style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0;">
            <div class="email-header" style="background: linear-gradient(135deg, #4f46e5, #7c3aed); padding: 35px 20px; text-align: center; color: white;">
                <h1 style="margin: 0; font-size: 26px; letter-spacing: 0.5px;">二次元虚拟ICP备案系统</h1>
                <p style="margin: 12px 0 0; opacity: 0.9; font-weight: 300;">备案安全验证码</p>
            </div>
            
            <div class="email-body" style="padding: 35px;">
                <p style="margin: 0 0 18px; font-size: 15px;">尊敬的会员，您好！</p>
                <p style="margin: 0 0 28px; font-size: 15px;">您正在进行账户安全操作，请使用以下验证码完成验证：</p>
                
                <div class="verification-code" style="font-size: 32px; font-weight: bold; letter-spacing: 8px; text-align: center; margin: 35px 0; padding: 25px; background: linear-gradient(to right, #f0f4ff, #ffffff); border-radius: 8px; border: 1px solid #e0e7ff; color: #4f46e5; text-shadow: 0 2px 4px rgba(79, 70, 229, 0.1); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">'.$code.'</div>
                
                <div class="divider" style="height: 1px; background: linear-gradient(to right, transparent, #e2e8f0, transparent); margin: 30px 0;"></div>
                
                <p class="note" style="font-size: 14px; color: #64748b; margin: 20px 0; line-height: 1.7; padding: 0 10px;">
                    <strong style="color: #4f46e5;">安全提示：</strong>此验证码有效期为5分钟，请勿向任何人泄露。
                </p>
                
                <p class="note" style="font-size: 14px; color: #64748b; margin: 20px 0; line-height: 1.7; padding: 0 10px;">
                    系统不会通过任何方式向您索要验证码，请警惕诈骗信息。<br><br><hr>
                    管理员邮箱：'.$adminemail.'
                </p>
            </div>
            
            <div class="footer" style="padding: 25px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #f1f5f9; background: #f8fafc;">
                <p style="margin: 5px 0;">© '.date('Y').' 二次元虚拟ICP备案系统 | 版权所有</p>
                <p style="margin: 5px 0; font-size: 11px; color: #94a3b8;">此为系统自动发送邮件，请勿直接回复</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    $mail->AltBody = "您的验证码是：{$code}，有效期5分钟。请勿将此验证码告知他人。";

    if (!$mail->send()) {
        throw new Exception('邮件发送失败');
    }

    $redis->setex(REDIS_PREFIX . 'email_code_' . $email, VERIFY_CODE_EXPIRE, $code);
    $redis->setex(REDIS_PREFIX . 'email_time_' . $email, 60, time());

    echo json_encode(['success' => true, 'message' => '验证码已发送']);
} catch (Exception $e) {
    error_log('[' . date('Y-m-d H:i:s') . '] ' . $e->getMessage() . "\n", 3, '../app/logs/email_error.log');
    
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}