<?php
session_start();

// 设置页面特定的 CSS 文件
$page_css = 'css/index.css';

// 连接数据库
include('config/db.php');

/**
 * 从 HTML 内容中提取第一张图片的 src 属性
 *
 * @param string $html_content 文章的 HTML 内容
 * @return string|null 第一张图片的 URL 或 null（如果没有图片）
 */
function extractFirstImage($html_content) {
    $doc = new DOMDocument();
    // 为避免无效HTML警告，使用 @ 符号
    @$doc->loadHTML($html_content);
    $images = $doc->getElementsByTagName('img');
    if ($images->length > 0) {
        return $images->item(0)->getAttribute('src');
    }
    return null;
}

// 获取所有精华文章，按发布时间倒序排列
$stmt_all_essence = $pdo->prepare("SELECT * FROM wz WHERE jh = 1 ORDER BY time DESC");
$stmt_all_essence->execute();
$allEssencePosts = $stmt_all_essence->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('tou.php'); ?>
<div class="beijin">
    <link rel="stylesheet" href="css/index.css">
    
    <div class="index-container">
        <section class="index-essence-posts">
            <!-- 使用 <h1> 标记显示页面主要标题 -->
            <h1>所有精华文章</h1>
            <?php if (is_array($allEssencePosts) && count($allEssencePosts) > 0): ?>
                <div class="cards-container">
                    <?php 
                        $counter = 0; // 初始化计数器
                        foreach ($allEssencePosts as $post): 
                            $counter++;
                            $is_even = ($counter % 2 === 0);
                            
                            // 如果文章记录存在 image 字段则使用，否则尝试从内容中提取第一张图片
                            if (!empty($post['image'])) {
                                $image = htmlspecialchars($post['image'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            } else {
                                $firstImage = extractFirstImage($post['content']);
                                if ($firstImage) {
                                    $image = htmlspecialchars($firstImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                } else {
                                    // 使用 Unsplash 或其他随机图片API
                                    $image = 'https://api.maho.cc/random-img/pc.php?rand=' . uniqid();
                                }
                            }
                    ?>
                        <div class="card <?= $is_even ? 'card-right' : 'card-left'; ?>">
                            <div class="card-image">
                                <img src="<?= $image ?>" alt="<?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" loading="lazy">
                            </div>
                            <div class="card-content">
                                <h3 class="card-title">
                                    <a href="content.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                        <?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                                    </a>
                                </h3>
                                <?php
                                    if (isset($post['is_code']) && $post['is_code']) {
                                        // 提取代码内容（前100字符）
                                        $codeContent = mb_substr(strip_tags($post['content']), 0, 100);
                                        echo "<pre class=\"card-code\"><code class=\"language-php\">" . htmlspecialchars($codeContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "...</code></pre>";
                                    } else {
                                        // 提取普通文本内容（前100字符）
                                        $textContent = mb_substr(strip_tags($post['content']), 0, 100);
                                        echo "<p class=\"card-text\">" . htmlspecialchars($textContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "...</p>";
                                    }
                                ?>
                                <p class="card-time">
                                    <small>发布时间：<?= htmlspecialchars($post['time'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>没有精华文章。</p>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php include('lizi.php'); ?>
<?php include('wei.php'); ?>
