<?php
// xx.php

// 连接数据库
include('config/db.php');

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
                echo '<li class="xx-article"><a href="content.php?id=' . htmlspecialchars($article['id'], ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($article['name']) . '</a></li>';
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
    <title>学习历程</title>
    <!-- 引入 Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- 引入学习历程页面的样式 -->
    <link rel="stylesheet" href="css/xx.css">
    <style>
        /* 如果有特定样式需要覆盖，可以在这里添加 */
    </style>
</head>
<body>
    <?php include('tou.php'); ?>  <!-- 引入头部 -->

    <div class="xx-container">
        <div class="xx-content">
            <h1>学习历程</h1>
            <?php 
                if (!empty($categoryTree)) {
                    displayCategory($pdo, $categoryTree); // 显示学习历程目录树
                } else {
                    echo '<p>暂无分类和文章。</p>';
                }
            ?>
        </div>
    </div>
    <?php include('lizi.php'); ?>
    <?php include('wei.php'); ?>  <!-- 引入页尾 -->

    <!-- 引入 JavaScript -->
    <script src="js/xx.js"></script>
</body>
</html>
