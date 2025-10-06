<?php
require_once '../app/config/db.php';
require_once '../app/config/function.php';
$page = 'xh';
include('header.php');
function getOccupiedNumbers(PDO $pdo, string $currentYear): array {
    $stmt = $pdo->prepare("SELECT icp_number FROM icp_records WHERE icp_number LIKE :yearPattern");
    $stmt->execute([':yearPattern' => $currentYear . '%']);
    $numbers = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    
    $mainNumbers = array_map(function($number) {
        return explode('-', $number)[0];
    }, $numbers);
    
    return array_unique($mainNumbers);
}

$currentYear = date('Y');
$occupiedNumbers = getOccupiedNumbers($pdo, $currentYear);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shortname); ?>ICP自助选号 - <?php echo htmlspecialchars($maintitle); ?></title>
    <meta name="keywords" content="<?php echo htmlspecialchars($shortname); ?>备, <?php echo htmlspecialchars($shortname); ?>ICP备, <?php echo htmlspecialchars($maintitle); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($shortname); ?>ICP自助选号 - 选择你心仪的备案号码">
    <link rel="icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="shortcut icon" href="<?php echo htmlspecialchars($logourl); ?>" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/xh.css">
    <style>
        .number-cell.occupied {
            background-color: #ffebee;
            color: #f44336;
            text-decoration: line-through;
            cursor: not-allowed;
            pointer-events: none;
        }
    </style>
    <?php echo ($headerhtml); ?>
    <style>
    <?php echo ($globalcss); ?>
    </style>
</head>
<body>
    <div class="container">

        <header class="header">
            <h1><?php echo htmlspecialchars($shortname); ?>ICP自助选号</h1>
            <p>选择你心仪的备案号码，开启二次元虚拟备案之旅</p>
        </header>

        <div class="number-container">
            <div class="prefix-selector" id="prefixSelector">
            </div>

            <table class="number-table">
                <thead>
                    <tr>
                        <th colspan="6">可选号码列表 (前缀: <span id="currentPrefix">0</span>开头)</th>
                    </tr>
                </thead>
                <tbody id="numberGrid">
                </tbody>
            </table>
        </div>
    </div>

    <script>
    const currentYear = <?= json_encode($currentYear) ?>;

    const occupiedNumbers = Object.values(<?= json_encode($occupiedNumbers) ?>);

    
    document.addEventListener('DOMContentLoaded', function() {
        const prefixSelector = document.getElementById('prefixSelector');
        const numberGrid = document.getElementById('numberGrid');
        const currentPrefixSpan = document.getElementById('currentPrefix');
        
        let currentPrefix = '0';
        
        for (let i = 0; i <= 9; i++) {
            const btn = document.createElement('button');
            btn.className = `prefix-btn ${i === 0 ? 'active' : ''}`;
            btn.textContent = `${i}开头`;
            btn.dataset.prefix = i;
            
            btn.addEventListener('click', function() {
                document.querySelectorAll('.prefix-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentPrefix = this.dataset.prefix;
                currentPrefixSpan.textContent = currentPrefix;
                generateNumberGrid();
            });
            
            prefixSelector.appendChild(btn);
        }
        
        function generateNumberGrid() {
            numberGrid.innerHTML = '';
            
            const screenWidth = window.innerWidth;
            let numbersPerRow = 6;
            if (screenWidth < 1200) numbersPerRow = 5;
            if (screenWidth < 992) numbersPerRow = 4;
            if (screenWidth < 768) numbersPerRow = 3;
            if (screenWidth < 576) numbersPerRow = 2;
            
            let row;
            
            for (let i = 0; i < 1000; i++) {
                if (i % numbersPerRow === 0) {
                    row = document.createElement('tr');
                    numberGrid.appendChild(row);
                }
                
                const num = i.toString().padStart(3, '0');
                const fullNum = `${currentPrefix}${num}`;
                const fullIcpNumber = `${currentYear}${fullNum}`;
                
                const cell = document.createElement('td');
                const numberCell = document.createElement('span');
                numberCell.className = 'number-cell';
                numberCell.textContent = fullIcpNumber;
                numberCell.dataset.number = fullNum;

                if (occupiedNumbers && occupiedNumbers.includes(fullIcpNumber)) {
                    numberCell.classList.add('occupied');
                    numberCell.title = '该备案号已被占用';
                } else {
                    numberCell.addEventListener('click', function() {
                        window.location.href = `/reg.php?number=${currentYear}${this.dataset.number}`;
                    });
                }
                
                cell.appendChild(numberCell);
                row.appendChild(cell);
            }
        }
        
        generateNumberGrid();

        window.addEventListener('resize', function() {
            generateNumberGrid();
        });
    });
</script>
        <?php include('footer.php'); ?>
    <script>
        <?php echo ($globaljs); ?>
    </script>
</body>
</html>