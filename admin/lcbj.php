<?php
// admin/lcbj.php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// 连接数据库
include('../config/db.php');

// 处理表单提交（创建或编辑分类）
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $parent_id = $_POST['parent_id'] ?? NULL;
    $id = $_POST['id'] ?? NULL;

    if (empty($name)) {
        $error = "分类名称不能为空！";
    } else {
        try {
            if ($id) {
                // 编辑分类
                $stmt = $pdo->prepare("UPDATE lc SET name = :name, parent_id = :parent_id WHERE id = :id");
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            } else {
                // 创建分类
                $stmt = $pdo->prepare("INSERT INTO lc (name, parent_id) VALUES (:name, :parent_id)");
            }
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            if ($parent_id === '') {
                $parent_id = NULL;
            } else {
                // 确保 parent_id 为整数
                $parent_id = (int)$parent_id;
            }
            $stmt->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: lcbj.php?message=success');
            exit();
        } catch (PDOException $e) {
            $error = "数据库错误: " . htmlspecialchars($e->getMessage());
        }
    }
}

// 处理删除分类
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    if (filter_var($delete_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
        try {
            // 删除分类及其子分类（假设设置了 ON DELETE CASCADE）
            $stmt = $pdo->prepare("DELETE FROM lc WHERE id = :id");
            $stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
            $stmt->execute();
            header('Location: lcbj.php?message=deleted');
            exit();
        } catch (PDOException $e) {
            $error = "删除失败: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $error = "无效的分类 ID！";
    }
}

// 获取所有分类用于显示和选择父分类
function getAllCategories($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM lc ORDER BY parent_id, id");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 获取分类树状结构
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
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <title>管理分类</title>
    <link rel="stylesheet" href="css/lcbj.css">
   
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>管理分类</h1>
            <div>
                <a href="index.php" class="admin-btn-edit" style="margin-right: 10px;">返回首页</a>
                <a href="logout.php" class="admin-logout-btn">退出登录</a>
            </div>
        </header>

        <?php
            if (isset($_GET['message'])) {
                if ($_GET['message'] == 'success') {
                    echo '<div class="alert alert-success">分类已成功保存。</div>';
                } elseif ($_GET['message'] == 'deleted') {
                    echo '<div class="alert alert-success">分类已成功删除。</div>';
                }
            }
            if (isset($error)) {
                echo '<div class="alert alert-error">' . $error . '</div>';
            }
        ?>

        <!-- 分类列表 -->
        <div class="tree">
            <?php
                function displayCategoryTree($categories) {
                    echo '<ul>';
                    foreach ($categories as $category) {
                        echo '<li>';
                        echo htmlspecialchars($category['name']);
                        echo ' <span class="actions">';
                        echo '<a href="lcbj.php?edit_id=' . $category['id'] . '">编辑</a>';
                        echo '<a href="lcbj.php?delete_id=' . $category['id'] . '" onclick="return confirm(\'确定要删除这个分类及其所有子分类吗？\');">删除</a>';
                        echo '</span>';
                        if (isset($category['children'])) {
                            displayCategoryTree($category['children']);
                        }
                        echo '</li>';
                    }
                    echo '</ul>';
                }

                displayCategoryTree($categoryTree);
            ?>
        </div>

        <!-- 分类创建/编辑表单 -->
        <?php
            // 如果编辑某个分类，预填充表单
            if (isset($_GET['edit_id'])) {
                $edit_id = $_GET['edit_id'];
                if (filter_var($edit_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
                    $stmt_edit = $pdo->prepare("SELECT * FROM lc WHERE id = :id");
                    $stmt_edit->bindParam(':id', $edit_id, PDO::PARAM_INT);
                    $stmt_edit->execute();
                    $edit_category = $stmt_edit->fetch(PDO::FETCH_ASSOC);
                    if ($edit_category) {
                        $form_name = htmlspecialchars($edit_category['name'], ENT_QUOTES, 'UTF-8');
                        $form_parent_id = $edit_category['parent_id'];
                        $form_id = $edit_category['id'];
                    } else {
                        echo '<div class="alert alert-error">找不到要编辑的分类。</div>';
                    }
                } else {
                    echo '<div class="alert alert-error">无效的分类 ID！</div>';
                }
            } else {
                // 默认创建新分类
                $form_name = '';
                $form_parent_id = NULL;
                $form_id = NULL;
            }
        ?>

        <h2><?php echo isset($form_id) ? '编辑分类' : '创建新分类'; ?></h2>
        <form action="lcbj.php" method="post">
            <input type="hidden" name="id" value="<?= isset($form_id) ? $form_id : '' ?>">
            <div class="form-group">
                <label for="name">分类名称：</label>
                <input type="text" id="name" name="name" value="<?= $form_name ?>" required>
            </div>
            <div class="form-group">
                <label for="parent_id">父分类：</label>
                <select name="parent_id" id="parent_id">
                    <option value="">无（根目录）</option>
                    <?php
                        function renderCategoryOptions($categories, $prefix = '', $current_id = NULL, $edit_id = NULL) {
                            foreach ($categories as $category) {
                                // 防止分类被选为自己的父分类
                                if ($edit_id && $category['id'] == $edit_id) {
                                    continue;
                                }
                                $selected = ($current_id == $category['id']) ? 'selected' : '';
                                echo '<option value="' . $category['id'] . '" ' . $selected . '>' . $prefix . htmlspecialchars($category['name']) . '</option>';
                                if (isset($category['children'])) {
                                    renderCategoryOptions($category['children'], $prefix . '--', $current_id, $edit_id);
                                }
                            }
                        }

                        $edit_id = isset($form_id) ? $form_id : NULL;
                        renderCategoryOptions($categoryTree, '', $form_parent_id, $edit_id);
                    ?>
                </select>
            </div>
            <button type="submit" class="admin-submit-btn"><?php echo isset($form_id) ? '更新分类' : '创建分类'; ?></button>
            <?php if (isset($form_id)): ?>
                <a href="lcbj.php" class="admin-submit-btn" style="background-color: #5bc0de; margin-left: 10px;">取消</a>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
