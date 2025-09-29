<?php
// test_db_connection.php
// 数据库配置
$host = 'localhost';  // 数据库主机
$dbname = 'bk';       // 数据库名称
$user = 'bk';       // 数据库用户名
$password = 'bk';       // 数据库密码，根据你的配置进行调整

try {
    // 创建PDO连接
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✅ 数据库连接成功！</p>";
} catch (PDOException $e) {
    die("<p>❌ 数据库连接失败: " . $e->getMessage() . "</p>");
}

// 获取所有分类
function getCategories($pdo, $parent_id = NULL) {
    if ($parent_id === NULL) {
        $stmt = $pdo->prepare("SELECT * FROM lc WHERE parent_id IS NULL ORDER BY id ASC");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM lc WHERE parent_id = :parent_id ORDER BY id ASC");
        $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取某个分类下的文章
function getArticles($pdo, $category_id) {
    $stmt = $pdo->prepare("SELECT wz.* FROM wz 
                           JOIN lcwz ON wz.id = lcwz.wz_id
                           WHERE lcwz.lc_id = :category_id");
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 构建分类树状结构
function buildCategoryTree($pdo, $parent_id = NULL) {
    $categories = getCategories($pdo, $parent_id);
    $tree = [];
    foreach ($categories as $category) {
        $category['children'] = buildCategoryTree($pdo, $category['id']);
        $tree[] = $category;
    }
    return $tree;
}

// 显示分类和文章
function displayCategory($pdo, $categoryTree) {
    echo '<ul>';
    foreach ($categoryTree as $category) {
        echo '<li class="xx-directory">';
        $hasChildren = count($category['children']) > 0;
        $hasArticles = count(getArticles($pdo, $category['id'])) > 0;
        echo '<span class="xx-toggle-button">' . (($hasChildren || $hasArticles) ? '<i class="fas fa-plus"></i>' : '') . '</span>';
        echo '<span class="xx-directory-name">' . htmlspecialchars($category['name']) . '</span>';

        echo '<div class="xx-sub-content">';
        
        // 显示文章
        if ($hasArticles) {
            echo '<ul class="xx-articles">';
            $articles = getArticles($pdo, $category['id']);
            foreach ($articles as $article) {
                echo '<li class="xx-article"><a href="#">' . htmlspecialchars($article['name']) . '</a></li>';
            }
            echo '</ul>';
        }

        // 递归显示子分类
        if ($hasChildren) {
            displayCategory($pdo, $category['children']);
        }

        echo '</div>';
        echo '</li>';
    }
    echo '</ul>';
}

// 构建分类树状结构
$categoryTree = buildCategoryTree($pdo);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>数据库连接测试</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* 样式美化 */
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
        }

        ul {
            list-style-type: none;
        }

        .xx-directory-name {
            cursor: pointer;
            font-weight: bold;
        }

        .xx-sub-content {
            display: none;
            margin-left: 20px;
            padding-left: 10px;
            border-left: 2px solid #ccc;
        }

        .xx-toggle-button {
            cursor: pointer;
            margin-right: 10px;
        }

        .xx-article a {
            color: blue;
            text-decoration: none;
        }

        .xx-article a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>数据库连接测试</h1>
    <div class="xx-container">
        <h2>分类与文章</h2>
        <?php displayCategory($pdo, $categoryTree); ?>
    </div>

    <script>
        // JavaScript 交互逻辑
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButtons = document.querySelectorAll('.xx-toggle-button');
            toggleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const subContent = this.parentElement.querySelector('.xx-sub-content');
                    if (subContent.style.display === 'block') {
                        subContent.style.display = 'none';
                        this.innerHTML = '<i class="fas fa-plus"></i>';
                    } else {
                        subContent.style.display = 'block';
                        this.innerHTML = '<i class="fas fa-minus"></i>';
                    }
                });
            });
        });
    </script>
</body>
</html>
