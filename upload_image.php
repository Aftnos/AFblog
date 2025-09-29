<?php
session_start();

// 检查用户是否登录
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['errno' => 1, 'message' => '未授权']);
    exit();
}

// 设置响应头
header('Content-Type: application/json');

// 检查是否有文件上传
if (!isset($_FILES['wangeditor-uploaded-image'])) {
    echo json_encode(['errno' => 1, 'message' => '没有文件上传']);
    exit();
}

$file = $_FILES['wangeditor-uploaded-image'];

// 检查上传是否成功
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['errno' => 1, 'message' => '上传错误: ' . $file['error']]);
    exit();
}

// 验证文件类型（仅允许图片）
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['errno' => 1, 'message' => '不支持的图片格式']);
    exit();
}

// 验证文件大小（最大 5MB）
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    echo json_encode(['errno' => 1, 'message' => '文件大小超过限制 (5MB)']);
    exit();
}

// 生成唯一的文件名
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$unique_name = uniqid('img_', true) . '.' . $ext;

// 移动文件到目标目录
$target_path = __DIR__ . '/uploads/images/' . $unique_name;
if (!move_uploaded_file($file['tmp_name'], $target_path)) {
    echo json_encode(['errno' => 1, 'message' => '移动文件失败']);
    exit();
}

// 获取文件的 URL（假设 uploads 目录在项目根目录）
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || 
            $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$base_url = $protocol . $host . dirname($_SERVER['REQUEST_URI']) . '/uploads/images/';
$file_url = $base_url . $unique_name;

// 返回成功的 JSON
echo json_encode([
    'errno' => 0,
    'data' => [
        'url' => $file_url,
        'alt' => '',
        'href' => ''
    ]
]);
exit();
?>
