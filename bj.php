<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// 连接数据库
include('config/db.php');

// 获取学习历程分类（来自 lc 表）
try {
    // 按层级排序示例：可根据需要微调
    $stmt_categories = $pdo->prepare("SELECT * FROM lc ORDER BY parent_id ASC, id ASC");
    $stmt_categories->execute();
    $categories = $stmt_categories->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("数据库错误: " . $e->getMessage());
}

// 处理表单提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取表单数据，并进行基本验证
    $title       = trim($_POST['title'] ?? '');
    $content     = trim($_POST['content'] ?? '');
    $category_id = $_POST['category_id'] ?? null;   // 用户选择的分类 ID
    $is_essence  = isset($_POST['is_essence']) ? 1 : 0; // 默认不精华，选中时为精华

    // 验证标题和内容是否为空
    if (empty($title) || empty($content)) {
        echo "<script>alert('标题和内容不能为空！');history.back();</script>";
        exit();
    }

    try {
        // 事务开始
        $pdo->beginTransaction();

        // 1. 插入文章数据到 wz 表
        $stmt_insert_wz = $pdo->prepare("
            INSERT INTO wz (name, content, time, jh)
            VALUES (:name, :content, NOW(), :jh)
        ");
        $stmt_insert_wz->bindParam(':name', $title, PDO::PARAM_STR);
        $stmt_insert_wz->bindParam(':content', $content, PDO::PARAM_STR);
        $stmt_insert_wz->bindParam(':jh', $is_essence, PDO::PARAM_INT);
        $stmt_insert_wz->execute();
        $post_id = $pdo->lastInsertId(); // 获取刚插入的文章 ID

        // 2. 如果选择了学习历程分类，插入关联到 lcwz 表
        if (!empty($category_id)) {
            // 只支持单分类时，插入一条关联记录
            $stmt_insert_lcwz = $pdo->prepare("
                INSERT INTO lcwz (lc_id, wz_id)
                VALUES (:lc_id, :wz_id)
            ");
            $stmt_insert_lcwz->bindParam(':lc_id', $category_id, PDO::PARAM_INT);
            $stmt_insert_lcwz->bindParam(':wz_id', $post_id, PDO::PARAM_INT);
            $stmt_insert_lcwz->execute();
        }

        // 提交事务
        $pdo->commit();

        // 提交成功后，跳转到首页并显示成功消息
        header('Location: index.php?message=article_created');
        exit();

    } catch (PDOException $e) {
        // 回滚事务并显示错误信息
        $pdo->rollBack();
        die("数据库错误: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>编写文章</title>
    <!-- 引入 wangEditor 的 CSS -->
    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="css/bj.css">
    <style>
      #editor-wrapper {
        border: 1px solid #ccc;
        z-index: 100;
      }
      #toolbar-container {
        border-bottom: 1px solid #ccc;
      }
      #editor-container {
        height: 500px;
      }
    </style>
</head>
<body>
    <?php include('tou.php'); ?>

    <div class="bj-container">
        <h1 class="bj-title">编写文章</h1>

        <form action="bj.php" method="POST" class="bj-form">
            <div class="bj-form-group">
                <label for="title" class="bj-label">文章标题：</label>
                <input type="text" name="title" id="title" required class="bj-input">
            </div>

            <div class="bj-form-group">
                <label for="content" class="bj-label">文章内容：</label>
                <div id="editor-wrapper" class="bj-textarea">
                    <div id="toolbar-container"></div>
                    <div id="editor-container"></div>
                </div>
                <!-- 隐藏的 textarea，用于提交编辑器内容 -->
                <textarea name="content" id="content" style="display: none;"></textarea>
            </div>

            <!-- 学习历程分类 (来自 lc 表) -->
            <div class="bj-form-group">
                <label for="category_id" class="bj-label">选择学习历程分类：</label>
                <select name="category_id" id="category_id" class="bj-select">
                    <option value="">选择分类（可选）</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= htmlspecialchars($category['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                            <?= htmlspecialchars($category['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 精华选项 -->
            <div class="bj-form-group bj-checkbox-group">
                <input type="checkbox" name="is_essence" id="is_essence" class="bj-checkbox">
                <label for="is_essence" class="bj-checkbox-label">勾选为精华文章</label>
            </div>

            <div class="bj-form-group">
                <button type="submit" class="bj-button">提交文章</button>
            </div>
        </form>
    </div>

    <!-- 引入 wangEditor 的 JavaScript -->
    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <script>
    const { createEditor, createToolbar } = window.wangEditor

    // 配置编辑器
    const editorConfig = {
        placeholder: '在此输入文章内容...',
        onChange(editor) {
            // 将内容同步到隐藏的 textarea
            const html = editor.getHtml()
            document.getElementById('content').value = html
        },
        MENU_CONF: {
            // 图片上传配置
            uploadImage: {
                server: '/upload_image.php',  // 上传图片的服务器接口
                fieldName: 'wangeditor-uploaded-image',
                maxFileSize: 5 * 1024 * 1024, // 5MB
                maxNumberOfFiles: 10,
                allowedFileTypes: ['image/*'],
                meta: { token: 'your-auth-token' },
                metaWithUrl: false,
                headers: {
                    // 可以添加自定义 HTTP 头
                },
                withCredentials: false,
                timeout: 5000,
                onBeforeUpload(file) {
                    return file
                },
                onProgress(progress) {
                    console.log('图片上传进度', progress)
                },
                onSuccess(file, res) {
                    console.log(`${file.name} 上传成功`, res)
                },
                onFailed(file, res) {
                    console.error(`${file.name} 上传失败`, res)
                },
                onError(file, err, res) {
                    console.error(`${file.name} 上传出错`, err, res)
                },
                customInsert(res, insertFn) {
                    // 假设服务端返回的数据格式符合 { errno: 0, data: { url, alt, href } }
                    insertFn(res.data.url, res.data.alt, res.data.href)
                }
            },
            // 视频上传配置
            uploadVideo: {
                server: '/upload_video.php',
                fieldName: 'wangeditor-uploaded-video',
                maxFileSize: 50 * 1024 * 1024, // 50MB
                maxNumberOfFiles: 5,
                allowedFileTypes: ['video/*'],
                meta: { token: 'your-auth-token' },
                metaWithUrl: false,
                headers: {
                    // 可以添加自定义 HTTP 头
                },
                withCredentials: false,
                timeout: 15000,
                onBeforeUpload(file) {
                    return file
                },
                onProgress(progress) {
                    console.log('视频上传进度', progress)
                },
                onSuccess(file, res) {
                    console.log(`${file.name} 上传成功`, res)
                },
                onFailed(file, res) {
                    console.error(`${file.name} 上传失败`, res)
                },
                onError(file, err, res) {
                    console.error(`${file.name} 上传出错`, err, res)
                },
                customInsert(res, insertFn) {
                    // 假设服务端返回的数据格式符合 { errno: 0, data: { url, poster } }
                    insertFn(res.data.url, res.data.poster)
                }
            }
        }
    }

    // 创建编辑器实例
    const editor = createEditor({
        selector: '#editor-container',
        html: '<p><br></p>',   // 初始化内容
        config: editorConfig,
        mode: 'default',
    })

    // 创建工具栏
    const toolbar = createToolbar({
        editor,
        selector: '#toolbar-container',
        config: {
            // 可根据需要自定义工具栏菜单
            // 例如：
            // excludeKeys: ['bold', 'italic'],
            // toolbarKeys: [ 'bold', 'italic', 'underline', 'head', 'fontSize', 'fontFamily', ... ],
        },
        mode: 'default',
    })

    // 在表单提交前，确保同步内容
    document.querySelector('.bj-form').addEventListener('submit', function(e) {
        const html = editor.getHtml()
        document.getElementById('content').value = html
    })
    </script>

    <?php include('wei.php'); ?>
</body>
</html>
