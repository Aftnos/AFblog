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

    // 获取文章数据
    $stmt = $pdo->prepare("SELECT * FROM wz WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    // 如果找不到文章
    if (!$post) {
        echo "文章不存在！";
        exit();
    }
} else {
    echo "无效的文章 ID！";
    exit();
}

// 4. 获取所有分类用于显示和选择
function getAllCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM lc ORDER BY parent_id, id");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 5. 获取分类树状结构
function buildCategoryTree(array $categories, $parent_id = NULL) {
    $branch = array();
    foreach ($categories as $category) {
        if ($category['parent_id'] == $parent_id) {
            $children = buildCategoryTree($categories, $category['id']);
            if ($children) {
                $category['children'] = $children;
            }
            $branch[] = $category;
        }
    }
    return $branch;
}

$categories = getAllCategories($pdo);
$categoryTree = buildCategoryTree($categories);

// 6. 获取当前文章所属的分类（假设每篇文章只能属于一个分类）
$stmt_current_category = $pdo->prepare("SELECT lc_id FROM lcwz WHERE wz_id = :id LIMIT 1");
$stmt_current_category->bindParam(':id', $id, PDO::PARAM_INT);
$stmt_current_category->execute();
$current_category = $stmt_current_category->fetch(PDO::FETCH_ASSOC);
$current_category_id = $current_category ? $current_category['lc_id'] : NULL;

// 7. 处理表单提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $is_essence = isset($_POST['is_essence']) ? 1 : 0; // 精华文章
    $category_id = isset($_POST['category_id']) ? $_POST['category_id'] : NULL;

    // 验证标题和内容是否为空
    if (empty($name) || empty($content)) {
        echo "<script>alert('标题和内容不能为空！');history.back();</script>";
        exit();
    }

    try {
        // 开始事务
        $pdo->beginTransaction();

        // 更新文章
        $updateStmt = $pdo->prepare("UPDATE wz SET name = :name, content = :content, jh = :jh WHERE id = :id");
        $updateStmt->bindParam(':name', $name, PDO::PARAM_STR);
        $updateStmt->bindParam(':content', $content, PDO::PARAM_STR);
        $updateStmt->bindParam(':jh', $is_essence, PDO::PARAM_INT);
        $updateStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $updateStmt->execute();

        // 删除旧的分类关联
        $stmt_delete = $pdo->prepare("DELETE FROM lcwz WHERE wz_id = :id");
        $stmt_delete->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt_delete->execute();

        // 插入新的分类关联（如果选择了分类）
        if ($category_id) {
            $stmt_insert = $pdo->prepare("INSERT INTO lcwz (lc_id, wz_id) VALUES (:lc_id, :wz_id)");
            $stmt_insert->bindParam(':lc_id', $category_id, PDO::PARAM_INT);
            $stmt_insert->bindParam(':wz_id', $id, PDO::PARAM_INT);
            $stmt_insert->execute();
        }

        // 提交事务
        $pdo->commit();

        // 重定向到文章列表，并显示成功消息
        header('Location: index.php?message=article_updated');
        exit();
    } catch (PDOException $e) {
        // 回滚事务并显示错误信息
        $pdo->rollBack();
        echo "更新过程中发生错误: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改文章</title>
    <!-- 引入 wangEditor 的 CSS -->
    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <!-- 引入 admin 目录下的 css/xg.css 样式 -->
    <link rel="stylesheet" href="css/xg.css">  
    <style>
        /* 确保编辑器容器有适当的高度和边框 */
        #editor-wrapper {
            border: 1px solid #ccc;
            z-index: 100;
            margin-bottom: 20px; /* 添加下边距，避免与其他元素重叠 */
        }
        #toolbar-container { border-bottom: 1px solid #ccc; }
        #editor-container { height: 500px; overflow-y: auto; }
        /* 响应式设计优化 */
        @media (max-width: 768px) {
            #editor-container { height: 300px; }
        }
        /* 管理后台的基础样式 */
        .admin-container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .admin-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .admin-logout-btn {
            text-decoration: none;
            color: #fff;
            background-color: #d9534f;
            padding: 8px 16px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .admin-logout-btn:hover {
            background-color: #c9302c;
        }
        .admin-edit-form .admin-form-group {
            margin-bottom: 15px;
        }
        .admin-edit-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .admin-edit-form input[type="text"],
        .admin-edit-form select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .admin-submit-btn {
            padding: 10px 20px;
            background-color: #5cb85c;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .admin-submit-btn:hover {
            background-color: #4cae4c;
        }
        /* 响应式设计 */
        @media (max-width: 768px) {
            .admin-container {
                width: 95%;
                padding: 10px;
            }
            .admin-header h1 {
                font-size: 20px;
            }
            .admin-submit-btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- 顶部导航栏 -->
        <header class="admin-header">
            <h1>修改文章</h1>
            <a href="logout.php" class="admin-logout-btn">退出登录</a>
        </header>

        <form action="xg.php?id=<?= htmlspecialchars($post['id'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" method="post" class="admin-edit-form">
            <div class="admin-form-group">
                <label for="name">文章标题：</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($post['name'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required>
            </div>
            <div class="admin-form-group">
                <label for="content">文章内容：</label>
                <div id="editor-wrapper" class="admin-textarea">
                    <div id="toolbar-container"></div>
                    <div id="editor-container"></div> <!-- 保持空白 -->
                </div>
                <!-- 隐藏的 textarea，用于提交编辑器内容 -->
                <textarea name="content" id="content"></textarea>
            </div>
            <!-- 精华文章选项 -->
            <div class="admin-form-group admin-checkbox-group">
                <input type="checkbox" id="is_essence" name="is_essence" <?= $post['jh'] == 1 ? 'checked' : '' ?>>
                <label for="is_essence">设为精华文章</label>
            </div>
            <!-- 学习历程分类 -->
            <div class="admin-form-group">
                <label for="category_id">选择学习历程分类：</label>
                <select name="category_id" id="category_id" class="admin-select">
                    <option value="">选择分类（可选）</option>
                    <?php
                        function renderCategoryOptions($categories, $prefix = '', $selected_id = NULL) {
                            foreach ($categories as $category) {
                                $selected = ($selected_id == $category['id']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($category['id'], ENT_QUOTES, 'UTF-8') . '" ' . $selected . '>' . $prefix . htmlspecialchars($category['name']) . '</option>';
                                if (isset($category['children'])) {
                                    renderCategoryOptions($category['children'], $prefix . '--', $selected_id);
                                }
                            }
                        }

                        renderCategoryOptions($categoryTree, '', $current_category_id);
                    ?>
                </select>
            </div>
            <div class="admin-form-group">
                <button type="submit" class="admin-submit-btn">更新文章</button>
            </div>
        </form>
    </div>

    <!-- 引入 wangEditor 的 JavaScript -->
    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <script>
    const { createEditor, createToolbar } = window.wangEditor

    // 获取 PHP 中的文章内容，并通过 JSON 安全地传递给 JavaScript
    const initialContent = <?= json_encode($post['content']) ?>;

    // 配置编辑器
    const editorConfig = {
        placeholder: '在此输入文章内容...',
        onChange(editor) {
            const html = editor.getHtml()
            // 将内容同步到隐藏的 textarea
            document.getElementById('content').value = html
        },
        MENU_CONF: {
            // 图片上传配置
            uploadImage: {
                server: '/upload_image.php', // 上传图片的服务器接口
                fieldName: 'wangeditor-uploaded-image', // form-data 中文件的字段名
                maxFileSize: 5 * 1024 * 1024, // 5MB
                maxNumberOfFiles: 10,
                allowedFileTypes: ['image/*'],
                meta: {
                    // 可以添加自定义参数
                    token: 'your-auth-token', // 示例
                },
                metaWithUrl: false,
                headers: {
                    // 可以添加自定义 HTTP 头
                    // 'Authorization': 'Bearer your-token',
                },
                withCredentials: false, // 是否跨域传递 cookie
                timeout: 5000, // 5秒
                onBeforeUpload(file) {
                    // 可在上传前进行文件处理
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
                    // 如果服务端返回的数据格式不符合要求，可以自定义插入
                    // 这里假设服务端已经按要求返回
                    insertFn(res.data.url, res.data.alt, res.data.href)
                }
            },
            // 视频上传配置
            uploadVideo: {
                server: '/upload_video.php', // 上传视频的服务器接口
                fieldName: 'wangeditor-uploaded-video', // form-data 中文件的字段名
                maxFileSize: 50 * 1024 * 1024, // 50MB
                maxNumberOfFiles: 5,
                allowedFileTypes: ['video/*'],
                meta: {
                    // 可以添加自定义参数
                    token: 'your-auth-token', // 示例
                },
                metaWithUrl: false,
                headers: {
                    // 可以添加自定义 HTTP 头
                    // 'Authorization': 'Bearer your-token',
                },
                withCredentials: false, // 是否跨域传递 cookie
                timeout: 15000, // 15秒
                onBeforeUpload(file) {
                    // 可在上传前进行文件处理
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
                    // 如果服务端返回的数据格式不符合要求，可以自定义插入
                    // 这里假设服务端已经按要求返回
                    insertFn(res.data.url, res.data.poster)
                }
            },
            // 可根据需要配置其他菜单项
            // 例如颜色、字体、表情等
        }
    }

    // 创建编辑器实例
    const editor = createEditor({
        selector: '#editor-container',
        html: initialContent, // 使用 JSON 安全传递的内容初始化编辑器
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
            // excludeKeys: ['bold', 'italic'], // 排除某些菜单
            // toolbarKeys: [ 'bold', 'italic', 'underline', 'head', 'fontSize', 'fontFamily', 'color', 'list', 'justify', 'image', 'video', 'link', 'quote', 'code', 'undo', 'redo' ],
        },
        mode: 'default',
    })

    // 在表单提交前，确保同步内容
    document.querySelector('.admin-edit-form').addEventListener('submit', function(e) {
        const html = editor.getHtml()
        document.getElementById('content').value = html
    })
    </script>
</body>
</html>
