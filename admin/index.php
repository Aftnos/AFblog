<?php
// index.php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// 连接数据库
include('../config/db.php');

/**
 * 递归计算目录大小
 *
 * @param string $dir 目录路径
 * @return int 目录大小，单位字节
 */
function getDirectorySize($dir) {
    $size = 0;
    if (is_dir($dir)) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }
    }
    return $size;
}

// 获取监控数据
try {
    // 1. 获取总文章数量
    $stmt_count_posts = $pdo->prepare("SELECT COUNT(*) AS total_posts FROM wz");
    $stmt_count_posts->execute();
    $total_posts = $stmt_count_posts->fetch(PDO::FETCH_ASSOC)['total_posts'];

    // 2. 获取总分类数量
    $stmt_count_categories = $pdo->prepare("SELECT COUNT(*) AS total_categories FROM lc");
    $stmt_count_categories->execute();
    $total_categories = $stmt_count_categories->fetch(PDO::FETCH_ASSOC)['total_categories'];

    // 3. 获取上传目录大小（假设上传目录为 '../uploads'）
    $upload_dir = '../uploads';
    $upload_size_bytes = getDirectorySize($upload_dir);

    /**
     * 将字节数转换为更友好的格式
     *
     * @param int $bytes 字节数
     * @return string 转换后的字符串
     */
    function formatBytes($bytes) {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    $upload_size_formatted = formatBytes($upload_size_bytes);
} catch (PDOException $e) {
    // 处理数据库错误
    $monitor_error = "监控数据获取失败: " . htmlspecialchars($e->getMessage());
}

// 获取文章列表，包含精华状态和学习历程分类
$stmt = $pdo->prepare("
    SELECT wz.*, GROUP_CONCAT(lc.name SEPARATOR ', ') AS category_names
    FROM wz
    LEFT JOIN lcwz ON wz.id = lcwz.wz_id
    LEFT JOIN lc ON lcwz.lc_id = lc.id
    GROUP BY wz.id
    ORDER BY wz.time DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 如果没有文章数据
if (!$posts) {
    $posts = [];
}

/**
 * 从 HTML 内容中提取纯文本。
 *
 * @param string $html_content 文章的 HTML 内容。
 * @param int $limit 限制返回的字符数。
 * @return string 提取的纯文本内容。
 */
function extractPlainText($html_content, $limit = 150) {
    // 去除所有HTML标签
    $text = strip_tags($html_content);
    // 转换HTML实体
    $text = html_entity_decode($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    // 去除多余的空白字符
    $text = preg_replace('/\s+/', ' ', $text);
    // 限制字符数
    if (mb_strlen($text, 'UTF-8') > $limit) {
        $text = mb_substr($text, 0, $limit, 'UTF-8') . '...';
    }
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台管理</title>
    <link rel="stylesheet" href="css/index.css">  <!-- 引入新的 CSS -->
    <style>
        /* 监控数据样式 */
        .admin-monitor {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .monitor-card {
            flex: 1;
            background-color: #f1f1f1;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
        }
        .monitor-card h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
            color: #333;
        }
        .monitor-card p {
            margin: 0;
            font-size: 16px;
            color: #555;
        }
        /* 响应式监控数据布局 */
        @media (max-width: 768px) {
            .admin-monitor {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- 顶部导航栏 -->
        <header class="admin-header">
            <h1>文章管理</h1>
            <div>
                <a href="lcbj.php" class="admin-btn-edit" style="margin-right: 10px;">管理分类</a>
                <a href="logout.php" class="admin-logout-btn">退出登录</a>
            </div>
        </header>

        <!-- 监控数据 -->
        <div class="admin-monitor">
            <div class="monitor-card">
                <h2><?= htmlspecialchars($total_posts, ENT_QUOTES, 'UTF-8') ?></h2>
                <p>总文章数</p>
            </div>
            <div class="monitor-card">
                <h2><?= htmlspecialchars($total_categories, ENT_QUOTES, 'UTF-8') ?></h2>
                <p>总分类数</p>
            </div>
            <div class="monitor-card">
                <h2><?= htmlspecialchars($upload_size_formatted, ENT_QUOTES, 'UTF-8') ?></h2>
                <p>存储占用</p>
            </div>
        </div>
        <?php
            if (isset($monitor_error)) {
                echo '<div class="alert alert-error">' . $monitor_error . '</div>';
            }
        ?>

        <!-- 文章列表 -->
        <div class="admin-post-list">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="admin-post-card">
                        <h3 class="admin-post-title">
                            <a href="xg.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                                <?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                            </a>
                        </h3>
                        <p class="admin-post-time">发布时间：<?= htmlspecialchars($post['time'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        <p class="admin-post-content"><?= extractPlainText($post['content'], 150) ?></p>
                        <p class="admin-post-info">
                            精华文章：<?= $post['jh'] == 1 ? '是' : '否' ?><br>
                            学习历程分类：<?= htmlspecialchars($post['category_names'] ?? '未分类', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </p>
                        <div class="admin-post-actions">
                            <a href="xg.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="admin-btn-edit">编辑</a>
                            <a href="delete.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="admin-btn-delete" onclick="return confirm('确定要删除这篇文章吗？');">删除</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>没有找到文章。</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
