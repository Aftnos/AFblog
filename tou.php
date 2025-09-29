<!-- tou.php -->
<header class="header-container">
    <div class="header-content">
        <div class="header-logo">
            <a href="index.php">艾方笔记</a>
            <!-- 首页一言打字效果 -->
            <span class="dz"></span>
        </div>
        <nav class="header-navbar">
            <ul>
                <li><a href="index.php">首页</a></li>
                <li><a href="bj.php">编写文章</a></li>
                <li><a href="xx.php">学习历程</a></li>
                <li><a href="admin/login.php">登录后台</a></li>
                <!-- 添加搜索图标链接 -->
                <li class="header-search-icon">
                    <a href="search.php"><i class="fas fa-search"></i></a>
                </li>
                <li>
                    <img class="img-tou" alt="header_user_avatar" src="https://weavatar.com/avatar/a327c43efa0ac37a1ad7c3c67a92ae03?s=96&amp;d=mm&amp;r=g" width="30" height="30">
                </li>
            </ul>
        </nav>
    </div>
</header>

<!-- 引入头部样式 -->
<link rel="stylesheet" href="css/tou.css">
<!-- 引入 JavaScript 文件 -->
<script src="js/typed.js"></script>
<!-- 引入 Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var typed = new Typed('.dz', {
            strings: ["给时光以生命，给岁月以文明^1000", "奉献我此生一切！^1000"],
            typeSpeed: 140,
            backSpeed: 50,
            loop: true,
            showCursor: true
        });
    });
</script>
