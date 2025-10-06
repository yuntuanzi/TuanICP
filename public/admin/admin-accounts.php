<?php
$title = "管理员账户管理";
require('includes/header.php');

$stmt = $pdo->query("SELECT * FROM admin_accounts ORDER BY id ASC");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_admin'])) {
            $username = trim($_POST['new_username']);
            $email = trim($_POST['new_email']);
            $nickname = trim($_POST['new_nickname']);
            $password = $_POST['new_password'];
            
            if (empty($username) || empty($email) || empty($nickname) || empty($password)) {
                throw new Exception("所有字段都必须填写");
            }
            
            if (strlen($password) < 8) {
                throw new Exception("密码长度必须至少8个字符");
            }
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM admin_accounts WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("用户名已存在");
            }
            
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO admin_accounts (username, email, password, nickname) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $passwordHash, $nickname]);
            
            $_SESSION['success_message'] = "管理员账户已成功添加";
            header("Location: admin-accounts.php");
            exit();
        }
        
        if (isset($_POST['update_admin'])) {
            $id = $_POST['admin_id'];
            $email = trim($_POST['email']);
            $nickname = trim($_POST['nickname']);
            
            if (empty($email) || empty($nickname)) {
                throw new Exception("所有字段都必须填写");
            }
            
            $stmt = $pdo->prepare("UPDATE admin_accounts SET email = ?, nickname = ? WHERE id = ?");
            $stmt->execute([$email, $nickname, $id]);
            
            if (!empty($_POST['password'])) {
                $newPassword = $_POST['password'];
                if (strlen($newPassword) < 8) {
                    throw new Exception("密码长度必须至少8个字符");
                }
                
                $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_accounts SET password = ? WHERE id = ?");
                $stmt->execute([$passwordHash, $id]);
            }
            
            $_SESSION['success_message'] = "管理员信息已更新";
            header("Location: admin-accounts.php");
            exit();
        }
        
        if (isset($_POST['delete_admin'])) {
            $id = $_POST['admin_id'];
            
            if ($id == 1) {
                throw new Exception("不能删除默认管理员账户");
            }
            
            if ($id == $_SESSION['admin_id']) {
                throw new Exception("不能删除当前登录的账户");
            }
            
            $stmt = $pdo->prepare("DELETE FROM admin_accounts WHERE id = ?");
            $stmt->execute([$id]);
            
            $_SESSION['success_message'] = "管理员账户已删除";
            header("Location: admin-accounts.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "操作失败: " . $e->getMessage();
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}
?>

<style>
    .admin-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #7e22ce;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .admin-info {
        flex: 1;
    }
    
    .admin-info h4 {
        margin: 0 0 5px 0;
        color: #7e22ce;
        display: flex;
        align-items: center;
    }
    
    .admin-info p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }
    
    .admin-actions {
        display: flex;
        gap: 10px;
    }
    
    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        border: none;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        white-space: nowrap;
    }
    
    .btn-primary {
        background-color: #7e22ce;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #6b21a8;
    }
    
    .btn-danger {
        background-color: #dc2626;
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #b91c1c;
    }
    
    .btn-secondary {
        background-color: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background-color: #4b5563;
    }
    
    .btn i {
        margin-right: 6px;
    }
    
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-content {
        background-color: #fff;
        margin: 10% auto;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        width: 90%;
        max-width: 500px;
        animation: modalFadeIn 0.3s;
    }
    
    @keyframes modalFadeIn {
        from {opacity: 0; transform: translateY(-20px);}
        to {opacity: 1; transform: translateY(0);}
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 1px solid #eee;
    }
    
    .modal-header h3 {
        margin: 0;
        color: #7e22ce;
        display: flex;
        align-items: center;
    }
    
    .modal-header .close {
        font-size: 24px;
        cursor: pointer;
        color: #6b7280;
    }
    
    .modal-body {
        margin-bottom: 20px;
    }
    
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 20px;
        border-top: 1px solid #eee;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    
    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        transition: border-color 0.3s;
    }
    
    .form-control:focus {
        border-color: #7e22ce;
        outline: none;
        box-shadow: 0 0 0 2px rgba(126, 34, 206, 0.2);
    }
    
    .form-text {
        display: block;
        margin-top: 6px;
        color: #6c757d;
        font-size: 12px;
    }
    
    @media (max-width: 768px) {
        .admin-card {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .admin-actions {
            margin-top: 15px;
            width: 100%;
            justify-content: flex-end;
        }
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
            <i class="mdi mdi-account-group" style="margin-right: 10px;"></i>
            管理员账户管理
        </h2>
        <p style="
            margin: 0;
            opacity: 0.85;
            font-size: 0.95rem;
            color: #7a5a9a;
        ">
            管理系统管理员账户，可添加、修改或删除管理员
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
        <div class="alert alert-danger" style="
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px 20px;
            border-radius: 4px;
            border: 1px solid #f5c6cb;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        ">
            <i class="mdi mdi-alert-circle" style="margin-right: 10px; font-size: 20px;"></i>
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>
    
    <div style="margin-bottom: 20px;">
        <button id="addAdminBtn" class="btn btn-primary">
            <i class="mdi mdi-account-plus"></i>添加管理员
        </button>
    </div>
    
    <div class="card">
        <div class="card-body">
            <h3 style="margin-top: 0; color: #7e22ce;">
                <i class="mdi mdi-account-multiple" style="margin-right: 8px;"></i>管理员列表
            </h3>
            
            <?php if (empty($admins)): ?>
                <p>暂无管理员账户</p>
            <?php else: ?>
                <?php foreach ($admins as $admin): ?>
                    <div class="admin-card">
                        <div class="admin-info">
                            <h4>
                                <i class="mdi mdi-account" style="margin-right: 8px;"></i>
                                <?php echo htmlspecialchars($admin['username']); ?>
                                <?php if ($admin['id'] == 1): ?>
                                    <span style="font-size: 0.8em; color: #6b7280; margin-left: 10px;">(默认管理员)</span>
                                <?php endif; ?>
                            </h4>
                            <p>
                                <i class="mdi mdi-email" style="margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($admin['email']); ?>
                                &nbsp;&nbsp;|&nbsp;&nbsp;
                                <i class="mdi mdi-account-box" style="margin-right: 5px;"></i>
                                <?php echo htmlspecialchars($admin['nickname']); ?>
                            </p>
                        </div>
                        
                        <div class="admin-actions">
                            <?php if ($admin['id'] != 1): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" name="delete_admin" class="btn btn-danger" 
                                            onclick="return confirm('确定要删除此管理员吗？此操作不可撤销！');">
                                        <i class="mdi mdi-delete"></i>删除
                                    </button>
                                </form>
                            <?php endif; ?>
                            <button class="btn btn-primary edit-admin-btn" data-id="<?php echo $admin['id']; ?>"
                                    data-username="<?php echo htmlspecialchars($admin['username']); ?>"
                                    data-email="<?php echo htmlspecialchars($admin['email']); ?>"
                                    data-nickname="<?php echo htmlspecialchars($admin['nickname']); ?>">
                                <i class="mdi mdi-pencil"></i>编辑
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="addAdminModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="mdi mdi-account-plus" style="margin-right: 8px;"></i>添加管理员</h3>
            <span class="close">&times;</span>
        </div>
        <form method="POST">
            <div class="modal-body">
                <div class="form-group">
                    <label for="new_username">用户名</label>
                    <input type="text" class="form-control" id="new_username" name="new_username" required>
                    <small class="form-text">用于登录系统的用户名</small>
                </div>
                
                <div class="form-group">
                    <label for="new_email">邮箱</label>
                    <input type="email" class="form-control" id="new_email" name="new_email" required>
                    <small class="form-text">管理员联系邮箱</small>
                </div>
                
                <div class="form-group">
                    <label for="new_nickname">昵称</label>
                    <input type="text" class="form-control" id="new_nickname" name="new_nickname" required>
                    <small class="form-text">系统中显示的名称</small>
                </div>
                
                <div class="form-group">
                    <label for="new_password">密码</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                    <small class="form-text">至少8个字符</small>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">取消</button>
                <button type="submit" name="add_admin" class="btn btn-primary">
                    <i class="mdi mdi-account-plus"></i>添加
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editAdminModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="mdi mdi-account-edit" style="margin-right: 8px;"></i>编辑管理员</h3>
            <span class="close">&times;</span>
        </div>
        <form method="POST">
            <input type="hidden" name="admin_id" id="edit_admin_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>用户名</label>
                    <input type="text" class="form-control" id="edit_username" readonly>
                    <small class="form-text">用户名不可修改</small>
                </div>
                
                <div class="form-group">
                    <label for="edit_email">邮箱</label>
                    <input type="email" class="form-control" id="edit_email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_nickname">昵称</label>
                    <input type="text" class="form-control" id="edit_nickname" name="nickname" required>
                </div>
                
                <div class="form-group">
                    <label for="edit_password">新密码</label>
                    <input type="password" class="form-control" id="edit_password" name="password" 
                           placeholder="留空则不修改密码">
                    <small class="form-text">至少8个字符</small>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal">取消</button>
                <button type="submit" name="update_admin" class="btn btn-primary">
                    <i class="mdi mdi-content-save"></i>保存
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const addAdminBtn = document.getElementById('addAdminBtn');
    const addAdminModal = document.getElementById('addAdminModal');
    const editAdminModal = document.getElementById('editAdminModal');
    const closeButtons = document.querySelectorAll('.close, .close-modal');
    
    addAdminBtn.onclick = function() {
        addAdminModal.style.display = 'block';
    }
    
    document.querySelectorAll('.edit-admin-btn').forEach(btn => {
        btn.onclick = function() {
            const id = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            const email = this.getAttribute('data-email');
            const nickname = this.getAttribute('data-nickname');
            
            document.getElementById('edit_admin_id').value = id;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_nickname').value = nickname;
            document.getElementById('edit_password').value = '';
            
            editAdminModal.style.display = 'block';
        }
    });
    
    closeButtons.forEach(btn => {
        btn.onclick = function() {
            addAdminModal.style.display = 'none';
            editAdminModal.style.display = 'none';
        }
    });
    
    window.onclick = function(event) {
        if (event.target == addAdminModal) {
            addAdminModal.style.display = 'none';
        }
        if (event.target == editAdminModal) {
            editAdminModal.style.display = 'none';
        }
    }
</script>

<?php require('includes/footer.php'); ?>