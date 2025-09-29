<?php

session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'root' && $password === 'root') {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit();
    } else {
        $error_message = "用户名或密码错误";
    }
}
?>

<link rel="stylesheet" href="css/login.css"> <!-- 引入后台登录样式 -->
<body>
    <div class="login-container">
        <div class="login-form">
            <h2>后台登录</h2>
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