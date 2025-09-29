<?php
// 设置 header 为 XML 格式
header("Content-Type: application/xml; charset=utf-8");

// 数据库连接文件
include('config/db.php');

// 网站根 URL（请根据实际情况修改）
$base_url = "https://bk.fkdk.ink/";

// 输出 XML 声明和 urlset 开始标签
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <!-- 首页 -->
    <url>
        <loc><?php echo $base_url; ?></loc>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>

    <?php
    // ---------------------------
    // 文章列表（从 wz 表获取）
    // ---------------------------
    try {
        $stmt = $pdo->prepare("SELECT id, time FROM wz ORDER BY time DESC");
        $stmt->execute();
        $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // 出错处理，可记录日志后继续
        die("数据库错误：" . $e->getMessage());
    }

    foreach ($articles as $article) {
        // 构造文章详情页链接，例如 content.php?id=文章ID
        $articleUrl = $base_url . "content.php?id=" . urlencode($article['id']);
        // 格式化最后修改时间（例如：2025-01-07）
        $lastmod = date("Y-m-d", strtotime($article['time']));
        ?>
        <url>
            <loc><?php echo htmlspecialchars($articleUrl, ENT_QUOTES, 'UTF-8'); ?></loc>
            <lastmod><?php echo $lastmod; ?></lastmod>
            <changefreq>weekly</changefreq>
            <priority>0.8</priority>
        </url>
        <?php
    }

    // ---------------------------
    // 分类列表（从 lc 表获取）
    // ---------------------------
    try {
        $stmt = $pdo->prepare("SELECT id FROM lc ORDER BY id ASC");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("数据库错误：" . $e->getMessage());
    }

    foreach ($categories as $cat) {
        // 构造分类页链接，例如 category.php?id=分类ID
        $catUrl = $base_url . "category.php?id=" . urlencode($cat['id']);
        ?>
        <url>
            <loc><?php echo htmlspecialchars($catUrl, ENT_QUOTES, 'UTF-8'); ?></loc>
            <changefreq>weekly</changefreq>
            <priority>0.7</priority>
        </url>
        <?php
    }
    ?>
</urlset>
