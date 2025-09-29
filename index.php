<?php
// index.php

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
    // 为了避免因无效的 HTML 导致错误，使用 @ 来抑制警告
    @$doc->loadHTML($html_content);
    $images = $doc->getElementsByTagName('img');
    if ($images->length > 0) {
        return $images->item(0)->getAttribute('src');
    }
    return null;
}

// 获取最新文章
$stmt_latest_posts = $pdo->prepare("SELECT * FROM wz ORDER BY time DESC LIMIT 5");
$stmt_latest_posts->execute();
$latestPosts = $stmt_latest_posts->fetchAll(PDO::FETCH_ASSOC);

// 获取精华文章总数
$stmt_count_essence = $pdo->prepare("SELECT COUNT(*) as total FROM wz WHERE jh = 1");
$stmt_count_essence->execute();
$count_essence = $stmt_count_essence->fetch(PDO::FETCH_ASSOC)['total'];

// 设置要显示的精华文章数量
$display_essence_limit = 5;

// 获取要显示的精华文章
$stmt_essence_posts = $pdo->prepare("SELECT * FROM wz WHERE jh = 1 ORDER BY time DESC LIMIT :limit");
$stmt_essence_posts->bindValue(':limit', $display_essence_limit, PDO::PARAM_INT);
$stmt_essence_posts->execute();
$essencePosts = $stmt_essence_posts->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('tou.php'); ?>
<div class="beijin">
    <link rel="stylesheet" href="css/index.css">
    <title>艾方笔记-主页</title>
    
    <!-- 新增的个人信息和社交联系方式部分 -->
    <div class="personal-top filter-nothing" id="123">
        <div id="banner_wave_1"></div>
        <div id="banner_wave_2"></div>
        <div id="personal_bg" class="personal-bg">
            <div class="personal-focusinfo">
                <div class="personal-avatar">
                    <a href="https://blog.fkdk.ink">
                        <img alt="avatar" loading="lazy" src="http://blog.fkdk.ink/wp-content/uploads/2023/04/JD2MQY_V3M4OB250AX_YX_tmb.jpg">
                    </a>
                </div>
                <div class="personal-container">
                    <div class="personal-info">
                        <!-- 首页一言打字效果 -->
                        <span class="personal-element">给时光以生命，给岁月以文明</span>
                    </div>
                    <p>找到我</p>
                </div>
                <ul class="personal-social">
                    <li id="bg-pre"><a><img src="https://s.nmxc.ltd/sakurairo_vision/@2.6/display_icon/sakura/pre.png" loading="lazy" alt="上一篇"></a></li>
                    <li>
                        <a href="https://space.bilibili.com/391516272" target="_blank" class="social-bili" title="bilibili">
                            <img alt="bilibili" loading="lazy" src="https://s.nmxc.ltd/sakurairo_vision/@2.6/display_icon/sakura/bilibili.png">
                        </a>
                    </li>
                    <li>
                        <a href="https://github.com/Aftnos" target="_blank" class="social-github" title="github">
                            <img alt="github" loading="lazy" src="https://s.nmxc.ltd/sakurairo_vision/@2.6/display_icon/sakura/github.png">
                        </a>
                    </li>
                    <li>
                        <a onclick="mail_me()" class="social-email" title="E-mail">
                            <img loading="lazy" alt="E-mail" src="https://s.nmxc.ltd/sakurairo_vision/@2.6/display_icon/sakura/mail.png">
                        </a>
                    </li>
                    <li id="bg-next"><a><img loading="lazy" src="https://s.nmxc.ltd/sakurairo_vision/@2.6/display_icon/sakura/next.png" alt="下一篇"></a></li>
                </ul>
            </div>
        </div>
        <!-- 首页下拉箭头 -->
        <div class="personal-down-arrow" onclick="personal_down()">
            <span>
                <svg t="1682342753354" class="homepage-downicon" viewBox="0 0 1843 1024" xmlns="http://www.w3.org/2000/svg" width="80px" height="80px">
                    <path d="M1221.06136021 284.43250057a100.69380037 100.69380037 0 0 1 130.90169466 153.0543795l-352.4275638 302.08090944a100.69380037 100.69380037 0 0 1-130.90169467 0L516.20574044 437.48688007A100.69380037 100.69380037 0 0 1 647.10792676 284.43250057L934.08439763 530.52766665l286.97696258-246.09516608z" fill="rgba(255,255,255,0.8)"></path>
                </svg>
            </span>
        </div>
    </div>
    
    <div class="index-container">
        <!-- 精华文章板块 -->
        <section class="index-essence-posts">
            <h2>精华文章</h2>
            <?php if (is_array($essencePosts) && count($essencePosts) > 0): ?>
                <div class="cards-container">
                    <?php 
                        $counter = 0; // 初始化计数器
                        foreach ($essencePosts as $post): 
                            $counter++;
                            $is_even = $counter % 2 == 0;
                            
                            // 提取文章内容中的第一张图片
                            $firstImage = extractFirstImage($post['content']);
                            if ($firstImage) {
                                $image = htmlspecialchars($firstImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            } else {
                                // 使用随机图片API
                                $image = 'https://api.maho.cc/random-img/pc.php?rand='.uniqid();
                            }
                    ?>
                        <div class="card <?php echo $is_even ? 'card-right' : 'card-left'; ?>">
                            <div class="card-image">
                                <img src="<?= $image ?>" alt="<?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" loading="lazy">
                            </div>
                            <div class="card-content">
                                <h3 class="card-title">
                                    <a href="content.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                                </h3>
                                <?php
                                    if (isset($post['is_code']) && $post['is_code']) {
                                        // 提取代码内容的前100个字符
                                        $codeContent = mb_substr(strip_tags($post['content']), 0, 100);
                                        $codeContent = htmlspecialchars($codeContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                        echo "<pre class=\"card-code\"><code class=\"language-php\">{$codeContent}...</code></pre>";
                                    } else {
                                        // 提取文本内容的前100个字符并处理
                                        $rawContent = strip_tags($post['content']); // 移除所有 HTML 标签
                                        $decodedContent = html_entity_decode($rawContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // 解码 HTML 实体
                                        $trimmedContent = mb_substr($decodedContent, 0, 100); // 截取前100个字符
                                        $finalContent = htmlspecialchars($trimmedContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // 转义特殊字符
                                        echo "<p class=\"card-text\">{$finalContent}...</p>";
                                    }
                                ?>
                                <p class="card-time"><small>发布时间：<?= htmlspecialchars($post['time'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <!-- 如果精华文章总数超过显示数量，则显示“其他精华文章”按钮 -->
                <?php if ($count_essence > $display_essence_limit): ?>
                    <div class="see-more">
                        <a href="other_essence.php" class="see-more-button">其他精华文章</a>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <p>没有精华文章。</p>
            <?php endif; ?>
        </section>

        <!-- 最新文章板块 -->
        <section class="index-latest-posts">
            <h2>最新文章</h2>
            <?php if (is_array($latestPosts) && count($latestPosts) > 0): ?>
                <div class="cards-container">
                    <?php 
                        $counter = 0; // 初始化计数器
                        foreach ($latestPosts as $post): 
                            $counter++;
                            $is_even = $counter % 2 == 0;
                            
                            // 提取文章内容中的第一张图片
                            $firstImage = extractFirstImage($post['content']);
                            if ($firstImage) {
                                $image = htmlspecialchars($firstImage, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                            } else {
                                // 使用随机图片API
                                $image = 'https://api.maho.cc/random-img/pc.php?rand='.uniqid();
                            }
                    ?>
                        <div class="card <?php echo $is_even ? 'card-right' : 'card-left'; ?>">
                            <div class="card-image">
                                <img src="<?= $image ?>" alt="<?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" loading="lazy">
                            </div>
                            <div class="card-content">
                                <h3 class="card-title">
                                    <a href="content.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                                </h3>
                                <?php
                                    if (isset($post['is_code']) && $post['is_code']) {
                                        // 提取代码内容的前100个字符
                                        $codeContent = mb_substr(strip_tags($post['content']), 0, 100);
                                        $codeContent = htmlspecialchars($codeContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                                        echo "<pre class=\"card-code\"><code class=\"language-php\">{$codeContent}...</code></pre>";
                                    } else {
                                        // 提取文本内容的前100个字符并处理
                                        $rawContent = strip_tags($post['content']); // 移除所有 HTML 标签
                                        $decodedContent = html_entity_decode($rawContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // 解码 HTML 实体
                                        $trimmedContent = mb_substr($decodedContent, 0, 100); // 截取前100个字符
                                        $finalContent = htmlspecialchars($trimmedContent, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); // 转义特殊字符
                                        echo "<p class=\"card-text\">{$finalContent}...</p>";
                                    }
                                ?>
                                <p class="card-time"><small>发布时间：<?= htmlspecialchars($post['time'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></small></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>没有最新文章。</p>
            <?php endif; ?>
        </section>
    </div>
</div>
<?php include('an.php'); ?>
<?php include('lizi.php'); ?>
<?php include('wei.php'); ?>
