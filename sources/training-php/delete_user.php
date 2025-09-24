<?php
// delete_user.php - secure minimal for testing
session_start();

require_once 'models/UserModel.php';
$userModel = new UserModel();

// 1) Kiểm tra login (bắt buộc)
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// 2) Chỉ chấp nhận POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo "Phương thức không hợp lệ";
    exit;
}

// 3) Kiểm tra CSRF token
$posted_token = $_POST['csrf_token'] ?? '';
if (empty($posted_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $posted_token)) {
    http_response_code(400);
    echo "Yêu cầu không hợp lệ (CSRF token).";
    exit;
}

// 4) Validate id
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    header('Location: list_users.php?msg=invalid_id');
    exit;
}

// 5) Thực hiện xóa bằng model
try {
    // Nếu model bạn có method deleteUserById, dùng luôn
    // Nếu chưa, bạn cần thêm method này trong models/UserModel.php (mình sẽ hướng dẫn dưới)
    $deleted = $userModel->deleteUserById($id);

    // Nếu model trả false => lỗi
    if ($deleted === false) {
        header('Location: list_users.php?msg=delete_failed');
        exit;
    }

    // Success
    header('Location: list_users.php?msg=deleted');
    exit;
} catch (Exception $e) {
    error_log("Delete user error: " . $e->getMessage());
    header('Location: list_users.php?msg=error');
    exit;
}
