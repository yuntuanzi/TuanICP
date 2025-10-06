<?php
require_once('../app/config/db.php');
require_once '../app/config/function.php';

function getApprovedSites() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT site_title, site_domain FROM icp_records WHERE status = 'approved'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$siteSettings = getSiteSettings();
$sitename = $siteSettings['site_name'];

$approvedSites = getApprovedSites();

if (empty($approvedSites)) {
    $targetSite = "TuanICP";
    $targetUrl = "https://icp.星.fun";
} else {
    $randomSite = $approvedSites[array_rand($approvedSites)];
    $targetSite = $randomSite['site_title'];
    $targetUrl = "https://" . ltrim($randomSite['site_domain'], 'https://');
}

function generateRandomCoords() {
    $x = rand(0, 9999);
    $y = rand(0, 9999);
    $z = rand(0, 9999);
    return "X-{$x}.Y-{$y}.Z-{$z}";
}

$targetCoords = generateRandomCoords();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sitename); ?> - 星际迁跃</title>
    <style>
        :root {
            --primary: #ff7eb9;
            --secondary: #ff65a3;
            --accent: #ff9eb5;
            --light: #fff0f5;
            --dark: #2a0a1a;
            --text: #5a2d40;
        }
        
        html, body {
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #fff0f5 0%, #ffe6ee 100%);
            overflow: hidden;
            font-family: 'Comic Sans MS', 'Arial Rounded MT Bold', 'Segoe UI', sans-serif;
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .jump-container {
            position: relative;
            width: 90%;
            max-width: 500px;
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(255, 126, 185, 0.2);
            text-align: center;
            z-index: 10;
            overflow: hidden;
            border: 3px solid var(--primary);
        }
        
        .jump-container::before {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            background: linear-gradient(45deg, 
                          var(--primary) 0%, 
                          var(--accent) 50%, 
                          var(--secondary) 100%);
            z-index: -1;
            border-radius: 25px;
            opacity: 0.3;
            animation: rotate 10s linear infinite;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            color: var(--primary);
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            text-shadow: 0 2px 5px rgba(255, 126, 185, 0.2);
            position: relative;
            display: inline-block;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--accent));
            border-radius: 3px;
        }
        
        .jump-info {
            margin: 20px 0;
        }
        
        .info-line {
            margin: 15px 0;
            font-size: 1.1rem;
            opacity: 0;
            transform: translateY(10px);
            animation: fadeInUp 0.5s forwards;
        }
        
        .info-line:nth-child(1) { animation-delay: 0.3s; }
        .info-line:nth-child(2) { animation-delay: 0.6s; }
        .info-line:nth-child(3) { animation-delay: 0.9s; }
        
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        
        .highlight {
            color: var(--secondary);
            font-weight: bold;
            background: linear-gradient(transparent 70%, rgba(255, 101, 163, 0.2) 0);
            padding: 0 3px;
            cursor: pointer;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .highlight:hover {
            background: linear-gradient(transparent 70%, rgba(255, 101, 163, 0.4) 0);
            transform: scale(1.05);
        }
        
        .countdown {
            margin-top: 30px;
            font-size: 0.9rem;
            color: var(--text);
            opacity: 0.8;
        }
        
        .time-remaining {
            font-weight: bold;
            color: var(--primary);
            display: inline-block;
            min-width: 40px;
        }
        
        .progress-container {
            width: 100%;
            height: 10px;
            background: #ffe6ee;
            border-radius: 5px;
            margin-top: 15px;
            overflow: hidden;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .progress-bar {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #ffb6c1, #d8bfd8, #add8e6);
            border-radius: 5px;
            animation: progress 3.5s linear forwards;
            position: relative;
            overflow: hidden;
        }
        
        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(90deg, 
                          rgba(255,255,255,0.8) 0%, 
                          rgba(255,255,255,0) 50%);
            animation: shine 1.5s infinite;
        }
        
        @keyframes progress {
            0% { width: 0; }
            100% { width: 100%; }
        }
        
        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .star {
            position: absolute;
            background-color: var(--primary);
            border-radius: 50%;
            animation: twinkle var(--duration) infinite ease-in-out;
            opacity: 0.6;
        }
        
        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.2); }
        }
        
        .emoticon {
            position: fixed;
            font-size: 2.2rem;
            color: hsl(330, 100%, calc(60% + var(--hue-offset, 0%)));
            z-index: 1;
            animation: floatEmoticon 6s ease-in-out infinite;
        }
        
        .decoration {
            position: fixed;
            font-size: 2.5rem;
            color: hsl(var(--decor-hue, 330), 100%, calc(60% + var(--hue-offset, 0%)));
            z-index: 1;
            animation: rotateDecoration 10s linear infinite;
        }
        
        @keyframes floatEmoticon {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.8; }
            25% { transform: translateY(-20px) rotate(8deg); opacity: 1; }
            50% { transform: translateY(0) rotate(0deg); opacity: 0.8; }
            75% { transform: translateY(20px) rotate(-8deg); opacity: 1; }
        }
        
        @keyframes rotateDecoration {
            0% { transform: rotate(0deg) scale(0.8); opacity: 0.7; }
            50% { transform: rotate(180deg) scale(1.2); opacity: 1; }
            100% { transform: rotate(360deg) scale(0.8); opacity: 0.7; }
        }
        
        @media (max-width: 600px) {
            h1 { font-size: 1.5rem; }
            .info-line { font-size: 1rem; }
            .jump-container { padding: 20px; }
            .emoticon { font-size: 1.8rem; }
            .decoration { font-size: 2rem; }
        }
    </style>
</head>
<body>
    <div class="stars" id="stars"></div>
    
    <div class="emoticon" style="--hue-offset: -15%; top: 15%; left: 10%; animation-delay: 0s;">(✧ω✧)</div>
    <div class="emoticon" style="--hue-offset: 10%; top: 25%; right: 15%; animation-delay: 0.5s;">(≧∇≦)ﾉ</div>
    <div class="emoticon" style="--hue-offset: -5%; top: 70%; left: 15%; animation-delay: 1s;">(๑•̀ㅂ•́)و✧</div>
    <div class="emoticon" style="--hue-offset: 20%; top: 60%; right: 12%; animation-delay: 1.5s;">(✿◠‿◠)</div>
    <div class="emoticon" style="--hue-offset: -10%; top: 40%; left: 5%; animation-delay: 2s;">(๑>ᴗ<๑)</div>
    <div class="emoticon" style="--hue-offset: 15%; top: 30%; right: 8%; animation-delay: 2.5s;">(≧∀≦)ﾉ</div>
    
    <div class="decoration" style="--decor-hue: 330; --hue-offset: -10%; top: 10%; right: 25%; animation-delay: 0s;">✧</div>
    <div class="decoration" style="--decor-hue: 270; --hue-offset: 15%; top: 30%; left: 20%; animation-delay: 1s;">☼</div>
    <div class="decoration" style="--decor-hue: 195; --hue-offset: -5%; top: 75%; right: 25%; animation-delay: 2s;">♡</div>
    <div class="decoration" style="--decor-hue: 330; --hue-offset: 20%; top: 50%; left: 15%; animation-delay: 3s;">✦</div>
    <div class="decoration" style="--decor-hue: 270; --hue-offset: -15%; top: 20%; left: 30%; animation-delay: 4s;">✧</div>
    <div class="decoration" style="--decor-hue: 195; --hue-offset: 10%; top: 60%; right: 30%; animation-delay: 5s;">★</div>
    <div class="decoration" style="--decor-hue: 330; --hue-offset: -5%; top: 40%; right: 25%; animation-delay: 6s;">✩</div>
    <div class="decoration" style="--decor-hue: 270; --hue-offset: 15%; top: 80%; left: 20%; animation-delay: 7s;">❀</div>
    <div class="decoration" style="--decor-hue: 330; --hue-offset: 20%; top: 50%; right: 18%; animation-delay: 9s;">✫</div>
    
    <div class="jump-container">
        <h1>✨ 星际迁跃准备中 ✨</h1>
        
        <div class="jump-info">
            <div class="info-line">
                亲爱的旅行者，欢迎乘坐 <span class="highlight"><?php echo htmlspecialchars($sitename); ?></span> 航班
            </div>
            <div class="info-line">
                目的地: <span class="highlight" id="target-site" onclick="jumpNow()"><?php echo htmlspecialchars($targetSite); ?></span>
            </div>
            <div class="info-line">
                坐标: <span class="highlight" id="target-coords"><?php echo htmlspecialchars($targetCoords); ?></span>
            </div>
        </div>
        
        <div class="countdown">
            正在启动超空间引擎... 预计还有 <span class="time-remaining" id="time-remaining">3.5</span> 秒到达
        </div>
        <div class="progress-container">
            <div class="progress-bar"></div>
        </div>
        
    </div>
    
    <script>
        function createStars() {
            const starsContainer = document.getElementById('stars');
            const starCount = 100;
            
            for (let i = 0; i < starCount; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                
                const size = Math.random() * 4 + 2;
                star.style.width = `${size}px`;
                star.style.height = `${size}px`;
                
                star.style.left = `${Math.random() * 100}%`;
                star.style.top = `${Math.random() * 100}%`;
                
                const duration = Math.random() * 3 + 1;
                star.style.setProperty('--duration', `${duration}s`);
                
                const hueOffset = Math.random() * 40 - 20;
                star.style.backgroundColor = `hsl(330, 100%, ${50 + hueOffset}%)`;
                
                starsContainer.appendChild(star);
            }
        }
        
        function jumpNow() {
            window.location.href = "<?php echo htmlspecialchars($targetUrl); ?>";
        }
        
        document.addEventListener('DOMContentLoaded', () => {
            createStars();
            
            const timeRemaining = document.getElementById('time-remaining');
            const startTime = 3.5;
            let remaining = startTime;
            
            const timer = setInterval(() => {
                remaining -= 0.1;
                if (remaining <= 0) {
                    remaining = 0;
                    clearInterval(timer);
                    jumpNow();
                }
                timeRemaining.textContent = remaining.toFixed(1);
            }, 100);
            
            const container = document.querySelector('.jump-container');
            container.addEventListener('mouseover', () => {
                container.style.transform = 'scale(1.02)';
            });
            container.addEventListener('mouseout', () => {
                container.style.transform = 'scale(1)';
            });
            
            const targetSite = document.getElementById('target-site');
            targetSite.style.cursor = 'pointer';
            targetSite.addEventListener('click', jumpNow);
        });
    </script>
</body>
</html>