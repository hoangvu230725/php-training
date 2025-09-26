<?php
session_start();
require_once 'models/UserModel.php';
$userModel = new UserModel();

// Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Method Not Allowed');
}

// Kiểm tra CSRF
if (
    !isset($_POST['csrf_token'], $_SESSION['csrf_token_delete']) ||
    $_POST['csrf_token'] !== $_SESSION['csrf_token_delete']
) {
    die('CSRF token invalid');
}

// Lấy id từ POST
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id > 0) {
    $userModel->deleteUserById($id);
}

// Redirect về danh sách
header('Location: list_users.php');
exit;