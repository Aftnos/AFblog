<?php
session_start();

// 1. 检查用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// 2. 连接数据库
include('../config/db.php');

// 3. 获取文章 ID 并验证
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // 验证 'id' 是否为正整数
    if (!filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        echo "无效的文章 ID！";
        exit();
    }

    try {
        // 开始事务
        $pdo->beginTransaction();

        // 4. 检查文章是否存在
        $stmt_check = $pdo->prepare("SELECT * FROM wz WHERE id = :id");
        $stmt_check->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_check->execute();
        $post = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if (!$post) {
            echo "文章不存在！";
            $pdo->rollBack();
            exit();
        }

        // 5. 删除媒体文件（可选）
        deleteMediaFiles($post['content']);

        // 6. 删除文章记录
        $stmt_delete_wz = $pdo->prepare("DELETE FROM wz WHERE id = :id");
        $stmt_delete_wz->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete_wz->execute();

        // 7. 删除分类关联
        $stmt_delete_category = $pdo->prepare("DELETE FROM lcwz WHERE wz_id = :id");
        $stmt_delete_category->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete_category->execute();

        // 提交事务
        $pdo->commit();

        // 8. 重定向到文章列表，并显示成功消息
        header('Location: index.php?message=article_deleted');
        exit();

    } catch (PDOException $e) {
        // 回滚事务并显示错误信息
        $pdo->rollBack();
        echo "删除过程中发生错误: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit();
    }

} else {
    echo "无效的请求！";
    exit();
}

// 可选功能：删除文章内容中的媒体文件
function deleteMediaFiles($content) {
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // 忽略HTML5标签警告
    $dom->loadHTML($content, LIBXML_NOERROR | LIBXML_NOWARNING);
    libxml_clear_errors();

    // 删除图片文件
    $images = $dom->getElementsByTagName('img');
    foreach ($images as $img) {
        $src = $img->getAttribute('src');
        // 解析文件路径
        $file_path = parse_url($src, PHP_URL_PATH);
        $absolute_path = $_SERVER['DOCUMENT_ROOT'] . $file_path;
        if (file_exists($absolute_path)) {
            unlink($absolute_path);
        }
    }

    // 删除视频文件
    $videos = $dom->getElementsByTagName('video');
    foreach ($videos as $video) {
        $src = $video->getAttribute('src');
        // 解析文件路径
        $file_path = parse_url($src, PHP_URL_PATH);
        $absolute_path = $_SERVER['DOCUMENT_ROOT'] . $file_path;
        if (file_exists($absolute_path)) {
            unlink($absolute_path);
        }
    }
}
?>
