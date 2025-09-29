<?php
$host = 'localhost';
$dbname = 'root';
$username = 'root';
$password = 'root';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // 错误模式
} catch (PDOException $e) {
    echo "连接失败: " . $e->getMessage();
}
?>