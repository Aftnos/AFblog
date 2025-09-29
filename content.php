<?php
session_start();

// 获取文章ID
$post_id = $_GET['id'] ?? null;
if (!$post_id) {
    die("没有指定文章ID");
}

// 连接数据库
include('config/db.php');

// 获取文章内容
try {
    // 获取文章
    $stmt = $pdo->prepare("SELECT * FROM wz WHERE id = :id");
    $stmt->bindParam(':id', $post_id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$post) {
        die("文章不存在");
    }

    // 生成页面描述：从文章内容中提取纯文本，并截取前150个字符
    $rawContent = strip_tags($post['content']);
    $decodedContent = html_entity_decode($rawContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $description = mb_substr($decodedContent, 0, 150, 'UTF-8');
    if(mb_strlen($decodedContent, 'UTF-8') > 150) {
        $description .= '...';
    }
    $description = htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

} catch (PDOException $e) {
    die("数据库错误: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($post['name'] ?? '文章详情', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>

    <!-- SEO相关 meta 标签 -->
    <meta name="description" content="<?= $description ?>">
    <meta name="keywords" content="艾方笔记, 博客, 文章, 关键字">
    <link rel="canonical" href="https://bk.fkdk.ink/content.php?id=<?= urlencode($post['id']) ?>">

    <!-- 页面样式 -->
    <link rel="stylesheet" href="css/content.css">
    <!-- Prism.js 样式 -->
    <link href="css/Prism.css" rel="stylesheet" />

    <!-- 可引入其他需要的CSS（例如行号插件的CSS） -->
</head>
<body>

    <?php include('tou.php'); ?> <!-- 顶部导航 -->

    <main class="post-content-container">
        <article class="post-detail">
            <!-- 使用 H1 标签作为页面主要标题 -->
            <h1 class="post-title">
                <?= htmlspecialchars($post['name'] ?? '未命名文章', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </h1>

            <!-- 文章发布时间 -->
            <p class="post-time">
                发布时间：<?= htmlspecialchars($post['time'] ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
            </p>

            <!-- 文章内容 -->
            <div class="post-body">
                <?php
                // 直接输出数据库中存储的 HTML 内容
                echo $post['content'];
                ?>
            </div>

            <div class="post-footer">
                <p>评论数：<?= htmlspecialchars($post['pl'] ?? 0, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            </div>
        </article>
    </main>

    <?php include('lizi.php'); ?> <!-- 侧边栏或其他区域 -->
    <?php include('wei.php'); ?> <!-- 页尾 -->

    <!-- Prism.js 核心脚本 -->
    <script src="js/Prism.js"></script>

    <!-- 如果需要行号、复制按钮等插件，可在此引入相应JS -->
    <!-- 例如：<script src="js/prism-line-numbers.min.js"></script> -->
</body>
</html>
