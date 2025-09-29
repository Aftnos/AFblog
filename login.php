<?php
// 简单的后台登录验证代码（这里只做示范，实际需要加密处理）
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 假设用户名和密码为 tnos 和 tnos，实际情况应该从数据库验证
    if ($username === 'tnos' && $password === 'tnos') {
        $_SESSION['logged_in'] = true;
        header('Location: bj.php');
        exit();
    } else {
        $error_message = "用户名或密码错误";
    }
}
?>

<?php include('tou.php'); ?>
<link rel="stylesheet" href="./css/login.css"> <!-- 引入后台登录样式 -->
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>编辑登录</h2>
            <?php if (isset($error_message)): ?>
                <div class="error-message"><?= $error_message ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="form-group">
                    <label for="username">用户名</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">密码</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="login-button">登录</button>
            </form>
        </div>
    </div>
<?php include('lizi.php'); ?>
<?php include('wei.php'); ?>