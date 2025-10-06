<footer style="
    position: sticky;
    top: 100vh;
    text-align: center;
    padding: 12px 0;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 -2px 15px rgba(0, 0, 0, 0.1);
    margin-top: 50px;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
">
    <div style="display: flex; justify-content: center; align-items: center; gap: 15px; margin-bottom: 8px; flex-wrap: wrap;">
        <?php
        $currentDomain = $_SERVER['HTTP_HOST'];
        
        if ($currentDomain == 'icp.we2050.com') {
            echo '<div style="display: flex; align-items: center; background: rgba(0, 123, 255, 0.1); padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(0, 123, 255, 0.3);">
                <img src="https://lanxiyun.com/logo.png" alt="蓝希云" style="height: 24px; margin-right: 12px;">
                <span style="font-weight: 600; color: #007bff; font-size: 17px; font-family: inherit;">金牌云服务器 by 蓝希云IDC</span>
            </div>
            <a href="https://www.lanxiyun.com/cart?fid=1&gid=54" target="_blank" style="background: linear-gradient(90deg, #ff6b00, #ff9500); color: white; padding: 6px 18px; border-radius: 20px; text-decoration: none; font-weight: 600; font-size: 17px; box-shadow: 0 2px 8px rgba(255, 107, 0, 0.2); transition: all 0.3s ease; font-family: inherit;">✨ 九块九服务器限时特惠 ✨</a>';
        } elseif ($currentDomain == 'icp.xn--kiv.fun') {
            echo '<div style="display: flex; align-items: center; background: rgba(0, 123, 255, 0.1); padding: 6px 16px; border-radius: 20px; border: 1px solid rgba(0, 123, 255, 0.3);">
                <img src="https://youke1.picui.cn/s1/2025/07/22/687f7d994ebed.png" alt="深海数据" style="height: 24px; margin-right: 12px;">
                <span style="font-weight: 600; color: #007bff; font-size: 17px; font-family: inherit;">金牌云服务器 by 深海数据</span>
            </div>
            <a href="https://tianyancloud.top/" target="_blank" style="background: linear-gradient(90deg, #ff6b00, #ff9500); color: white; padding: 6px 18px; border-radius: 20px; text-decoration: none; font-weight: 600; font-size: 17px; box-shadow: 0 2px 8px rgba(255, 107, 0, 0.2); transition: all 0.3s ease; font-family: inherit;">✨ 新用户注册限时特惠 ✨</a>';
        }
        ?>
    </div>
    <p style="margin: 0; font-size: 12px; color: #666; font-family: inherit;">© 2025 TuanICP System. All rights reserved.</p>
</footer>