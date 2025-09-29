<?php
// search.php
session_start();

// 引入数据库连接文件
include('config/db.php');

// 获取搜索查询和搜索范围
$query = $_GET['query'] ?? '';
$query = trim($query);

// 获取搜索范围，默认为空数组
$search_fields = $_GET['search_fields'] ?? [];
if (!is_array($search_fields)) {
    $search_fields = [$search_fields];
}
// 过滤允许的搜索字段
$allowed_fields = ['title', 'content', 'time'];
$search_fields = array_intersect($search_fields, $allowed_fields);

// 获取日期范围
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

// 获取分页参数
$limit = 10; // 每页显示的结果数
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 处理搜索逻辑
$results = [];
$total_results = 0;
$total_pages = 0;

// 构建 WHERE 子句
$where_clauses = [];
$params = [];

// 如果用户选择了搜索字段且查询不为空
if (!empty($search_fields) && $query !== '') {
    $search_sub_clauses = [];
    foreach ($search_fields as $field) {
        if ($field === 'title') {
            $search_sub_clauses[] = "name LIKE :query_title";
            $params[':query_title'] = "%" . $query . "%";
        }
        if ($field === 'content') {
            $search_sub_clauses[] = "content LIKE :query_content";
            $params[':query_content'] = "%" . $query . "%";
        }
    }
    if (!empty($search_sub_clauses)) {
        $where_clauses[] = "(" . implode(" OR ", $search_sub_clauses) . ")";
    }
}

// 如果用户选择了发布时间并指定了日期范围
if (in_array('time', $search_fields)) {
    if ($start_date !== '' && $end_date !== '') {
        $where_clauses[] = "DATE(time) BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    } elseif ($start_date !== '') {
        $where_clauses[] = "DATE(time) >= :start_date";
        $params[':start_date'] = $start_date;
    } elseif ($end_date !== '') {
        $where_clauses[] = "DATE(time) <= :end_date";
        $params[':end_date'] = $end_date;
    }
}

// Combine all WHERE clauses
$where_clause = "";
if (!empty($where_clauses)) {
    $where_clause = "WHERE " . implode(" AND ", $where_clauses);
}

try {
    // 统计总结果数
    $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM wz " . $where_clause);
    foreach ($params as $key => $value) {
        $stmt_count->bindValue($key, $value);
    }
    $stmt_count->execute();
    $total_results = $stmt_count->fetchColumn();
    
    // 计算总页数
    $total_pages = ceil($total_results / $limit);
    
    // 获取当前页的结果
    $stmt = $pdo->prepare("SELECT * FROM wz " . $where_clause . " ORDER BY time DESC LIMIT :limit OFFSET :offset");
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("数据库错误: " . htmlspecialchars($e->getMessage()));
}

// 函数：高亮显示搜索关键词，仅高亮第一个匹配并限制摘要长度
function highlightKeyword($text, $keyword, $search_field = 'title') {
    $escaped_keyword = preg_quote($keyword, '/');
    if ($search_field === 'content') {
        // 查找第一个匹配的位置
        $pos = stripos($text, $keyword);
        if ($pos !== false) {
            // 定义上下文长度
            $context_length = 50;
            $start = max(0, $pos - $context_length);
            $length = strlen($keyword);
            $snippet = substr($text, $start, $context_length + $length + 50); // 前后各50字符
            // 高亮第一个匹配
            $snippet = preg_replace('/(' . $escaped_keyword . ')/i', '<mark>$1</mark>', $snippet, 1);
            return '...' . htmlspecialchars($snippet, ENT_QUOTES, 'UTF-8') . '...';
        } else {
            // 如果未找到关键词，则返回前100个字符
            return substr(strip_tags($text), 0, 100) . '...';
        }
    } else {
        // 对标题或其他字段进行高亮，全部匹配
        return preg_replace('/(' . $escaped_keyword . ')/i', '<mark>$1</mark>', htmlspecialchars($text, ENT_QUOTES, 'UTF-8'));
    }
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>搜索 - 艾方笔记</title>
    <link rel="stylesheet" href="css/content.css">
    <!-- 引入 Prism.js 的 CSS -->
    <link href="css/Prism.css" rel="stylesheet" />
    <!-- 引入独立的搜索样式 -->
    <link rel="stylesheet" href="css/search.css">
    <!-- 引入 Font Awesome (用于搜索按钮图标) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include('tou.php'); ?>

    <main class="post-content-container">
        <article class="post-detail">
            <h2 class="post-title">搜索</h2>
            <!-- 搜索表单 -->
            <form action="search.php" method="GET" class="search-form">
                <input type="text" name="query" placeholder="请输入搜索内容..." value="<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" required>
                
                <!-- 搜索范围选择 -->
                <div class="search-options">
                    <label>
                        <input type="checkbox" name="search_fields[]" value="title" <?= in_array('title', $search_fields) ? 'checked' : '' ?>>
                        标题
                    </label>
                    <label>
                        <input type="checkbox" name="search_fields[]" value="content" <?= in_array('content', $search_fields) ? 'checked' : '' ?>>
                        内容
                    </label>
                    <label>
                        <input type="checkbox" name="search_fields[]" value="time" <?= in_array('time', $search_fields) ? 'checked' : '' ?>>
                        发布时间
                    </label>
                </div>
                
                <!-- 发布日期范围选择 -->
                <div class="date-range" id="date-range" style="<?= in_array('time', $search_fields) ? 'display: flex;' : 'display: none;' ?>">
                    <label>
                        发布开始日期：
                        <input type="date" name="start_date" value="<?= htmlspecialchars($start_date, ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                    <label>
                        发布结束日期：
                        <input type="date" name="end_date" value="<?= htmlspecialchars($end_date, ENT_QUOTES, 'UTF-8') ?>">
                    </label>
                </div>
                
                <button type="submit"><i class="fas fa-search"></i> 搜索</button>
            </form>

            <?php if ($query !== ''): ?>
                <h3 class="search-summary">搜索结果: <?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?> (共 <?= $total_results ?> 条结果)</h3>

                <?php if (count($results) > 0): ?>
                    <ul class="search-results">
                        <?php foreach ($results as $post): ?>
                            <li class="search-result-item">
                                <a href="content.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= highlightKeyword($post['name'], $query, 'title') ?>
                                </a>
                                <p class="post-time">发布时间：<?= htmlspecialchars($post['time'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if (in_array('content', $search_fields)): ?>
                                    <p><?= highlightKeyword($post['content'], $query, 'content') ?></p>
                                <?php else: ?>
                                    <p><?= highlightKeyword(substr(strip_tags($post['content']), 0, 200), $query, 'title') ?>...</p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- 分页 -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php 
                                    // 保留搜索条件
                                    $params = [
                                        'query' => $query,
                                        'page' => $page - 1
                                    ];
                                    foreach ($search_fields as $field) {
                                        $params['search_fields'][] = $field;
                                    }
                                    if ($start_date !== '') {
                                        $params['start_date'] = $start_date;
                                    }
                                    if ($end_date !== '') {
                                        $params['end_date'] = $end_date;
                                    }
                                    echo http_build_query($params, '', '&amp;'); 
                                ?>">&laquo; 上一页</a>
                            <?php endif; ?>

                            <?php
                            // 显示分页链接
                            // 为了避免过多页码显示，可以只显示当前页的前后几页
                            $visible_pages = 5;
                            $start_page = max(1, $page - floor($visible_pages / 2));
                            $end_page = min($total_pages, $start_page + $visible_pages - 1);
                            $start_page = max(1, $end_page - $visible_pages + 1);

                            for ($i = $start_page; $i <= $end_page; $i++) {
                                // 构建 URL 参数，保留搜索条件
                                $url_params = [
                                    'query' => $query,
                                    'page' => $i
                                ];
                                foreach ($search_fields as $field) {
                                    $url_params['search_fields'][] = $field;
                                }
                                if ($start_date !== '') {
                                    $url_params['start_date'] = $start_date;
                                }
                                if ($end_date !== '') {
                                    $url_params['end_date'] = $end_date;
                                }
                                $url = 'search.php?' . http_build_query($url_params);
                                
                                if ($i == $page) {
                                    echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="active">' . $i . '</a>';
                                } else {
                                    echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . $i . '</a>';
                                }
                            }
                            ?>

                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php 
                                    // 保留搜索条件
                                    $params = [
                                        'query' => $query,
                                        'page' => $page + 1
                                    ];
                                    foreach ($search_fields as $field) {
                                        $params['search_fields'][] = $field;
                                    }
                                    if ($start_date !== '') {
                                        $params['start_date'] = $start_date;
                                    }
                                    if ($end_date !== '') {
                                        $params['end_date'] = $end_date;
                                    }
                                    echo http_build_query($params, '', '&amp;'); 
                                ?>">下一页 &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                <?php else: ?>
                    <p class="no-results">没有找到与 "<?= htmlspecialchars($query, ENT_QUOTES, 'UTF-8') ?>" 相关的文章。</p>
                <?php endif; ?>
            <?php endif; ?>
        </article>
    </main>

    <?php include('lizi.php'); ?>
    <?php include('wei.php'); ?>

    <!-- 引入 Prism.js 的 JavaScript -->
    <script src="js/Prism.js"></script>

    <!-- 初始化 Prism.js -->
    <script>
        // 当页面加载完成后，手动触发 Prism.js 的高亮
        document.addEventListener('DOMContentLoaded', (event) => {
            Prism.highlightAll();
        });
    </script>
    
    <!-- 添加 JavaScript 控制日期范围选择的显示与隐藏 -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeCheckbox = document.querySelector('input[type="checkbox"][value="time"]');
            const dateRange = document.getElementById('date-range');
            
            timeCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    dateRange.style.display = 'flex';
                } else {
                    dateRange.style.display = 'none';
                    // 清空日期输入框的值
                    document.querySelector('input[name="start_date"]').value = '';
                    document.querySelector('input[name="end_date"]').value = '';
                }
            });
        });
    </script>
</body>
</html>
