<?php
$title = "网站样式设置";
require('includes/header.php');

$stmt = $pdo->query("SELECT * FROM custom_contents LIMIT 1");
$customContents = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nav_html = $_POST['nav_html'] ?? '';
        $global_css = $_POST['global_css'] ?? '';
        $global_js = $_POST['global_js'] ?? '';
        $footer_html = $_POST['footer_html'] ?? '';
        $header_html = $_POST['header_html'] ?? '';

        $stmt = $pdo->prepare("UPDATE custom_contents SET 
            nav_html = ?, 
            global_css = ?, 
            global_js = ?, 
            footer_html = ?, 
            header_html = ? 
            WHERE id = ?");
        
        $stmt->execute([
            $nav_html,
            $global_css,
            $global_js,
            $footer_html,
            $header_html,
            $customContents['id']
        ]);

        $_SESSION['success_message'] = "网站样式已成功更新！";
        
        header("Location: style-settings.php");
        exit();
    } catch (PDOException $e) {
        $error_message = "更新失败: " . $e->getMessage();
    }
}
?>

<style>
    .code-editor {
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .editor-header {
        background-color: #f5f5f5;
        padding: 8px 15px;
        border-bottom: 1px solid #ddd;
        font-family: monospace;
        font-weight: bold;
        color: #555;
    }
    
    .editor-body {
        position: relative;
    }
    
    textarea.code-input {
        width: 100%;
        min-height: 200px;
        padding: 15px;
        border: none;
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        line-height: 1.5;
        background-color: #fafafa;
        resize: vertical;
    }
    
    .section-label {
        display: block;
        margin: 25px 0 10px;
        font-weight: 600;
        color: #5a3d7a;
        font-size: 1.1rem;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }
    
    .form-hint {
        display: block;
        margin: -10px 0 15px;
        color: #6c757d;
        font-size: 13px;
    }
    
    .form-submit {
        text-align: right;
        padding-top: 20px;
        border-top: 1px solid #eee;
        margin-top: 30px;
    }
    
    @media (max-width: 768px) {
        .code-editor {
            margin-left: -15px;
            margin-right: -15px;
            border-radius: 0;
        }
    }
    
    .tab-container {
        margin-bottom: 20px;
    }
    
    .tab-buttons {
        display: flex;
        border-bottom: 1px solid #ddd;
    }
    
    .tab-button {
        padding: 10px 20px;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        color: #666;
        border-bottom: 2px solid transparent;
        transition: all 0.3s;
    }
    
    .tab-button.active {
        color: #6a3a8a;
        border-bottom-color: #9a5fcc;
        font-weight: 500;
    }
    
    .tab-content {
        display: none;
        padding: 20px 0;
    }
    
    .tab-content.active {
        display: block;
    }
</style>

<div class="admin-content">
    <div class="content-header" style="
        background: linear-gradient(135deg, #e2c2ff 0%, #ffd6f4 100%);
        padding: 1.8rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(194, 128, 255, 0.15);
        color: #5a3d7a;
        margin-bottom: 2rem;
        border-left: 5px solid #c38fff;
        backdrop-filter: blur(2px);
    ">
        <h2 style="
            margin: 0 0 0.6rem 0;
            font-size: 1.7rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            display: flex;
            align-items: center;
            color: #6a3a8a;
        ">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 10px; color: #9a5fcc;">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path>
            </svg>
            网站样式设置
        </h2>
        <p style="
            margin: 0;
            opacity: 0.85;
            font-size: 0.95rem;
            color: #7a5a9a;
        ">
            在此自定义网站的样式和布局
        </p>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success" style="
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 4px;
            border: 1px solid #c3e6cb;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        ">
            <i class="mdi mdi-check-circle" style="margin-right: 10px; font-size: 20px;"></i>
            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <form method="POST">
                <div class="tab-container">
                    <div class="tab-buttons">
                        <button type="button" class="tab-button active" data-tab="css">CSS样式</button>
                        <button type="button" class="tab-button" data-tab="js">JavaScript</button>
                        <button type="button" class="tab-button" data-tab="html">HTML内容</button>
                    </div>
                    
                    <div id="css-tab" class="tab-content active">
                        <label class="section-label">全局CSS样式</label>
                        <p class="form-hint">在此添加自定义CSS样式，这些样式将应用于整个网站</p>
                        
                        <div class="code-editor">
                            <div class="editor-header">global.css</div>
                            <div class="editor-body">
                                <textarea class="code-input" name="global_css" id="global_css" spellcheck="false"><?php echo htmlspecialchars($customContents['global_css'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div id="js-tab" class="tab-content">
                        <label class="section-label">全局JavaScript</label>
                        <p class="form-hint">在此添加自定义JavaScript代码，这些代码将在每个页面加载时执行</p>
                        
                        <div class="code-editor">
                            <div class="editor-header">global.js</div>
                            <div class="editor-body">
                                <textarea class="code-input" name="global_js" id="global_js" spellcheck="false"><?php echo htmlspecialchars($customContents['global_js'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div id="html-tab" class="tab-content">
                        <label class="section-label">导航栏HTML</label>
                        <p class="form-hint">请前往网站public目录下的header.php修改，此处修改无效</p>
                        
                        <div class="code-editor">
                            <div class="editor-header">header</div>
                            <div class="editor-body">
                                <textarea class="code-input" name="nav_html" id="nav_html" spellcheck="false">请前往网站public目录下的header.php修改，此处修改无效</textarea>
                            </div>
                        </div>
                        
                        <label class="section-label">head标签内</label>
                        <p class="form-hint">自定义页面head标签内的HTML内容</p>
                        
                        <div class="code-editor">
                            <div class="editor-header">head</div>
                            <div class="editor-body">
                                <textarea class="code-input" name="header_html" id="header_html" spellcheck="false"><?php echo htmlspecialchars($customContents['header_html'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        
                        <label class="section-label">页脚HTML</label>
                        <p class="form-hint">请前往网站public目录下的footer.php修改，此处修改无效</p>
                        
                        <div class="code-editor">
                            <div class="editor-header">footer</div>
                            <div class="editor-body">
                                <textarea class="code-input" name="footer_html" id="footer_html" spellcheck="false">请前往网站public目录下的footer.php修改，此处修改无效</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="form-submit">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-size: 16px;">
                        <i class="mdi mdi-content-save" style="margin-right: 8px;"></i>保存设置
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                button.classList.add('active');
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId + '-tab').classList.add('active');
            });
        });
        
        const textareas = document.querySelectorAll('.code-input');
        textareas.forEach(textarea => {
            textarea.style.height = textarea.scrollHeight + 'px';
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
            });
        });
    });
</script>

<?php require('includes/footer.php'); ?>