<?php
// Start the session
session_start();

// Tạo CSRF token nếu chưa có
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once 'models/UserModel.php';
$userModel = new UserModel();

$params = [];
if (!empty($_GET['keyword'])) {
    // Lưu ý: hãy sanitize trước khi truyền xuống DB hoặc sử dụng prepared statement trong model
    $params['keyword'] = $_GET['keyword'];
}

$users = $userModel->getUsers($params);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if (!empty($users)) { ?>
            <div class="alert alert-warning" role="alert">
                List of users! <br>
                Hacker: http://php.local/list_users.php?keyword=ASDF%25%22%3BTRUNCATE+banks%3B%23%23
            </div>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Username</th>
                        <th scope="col">Fullname</th>
                        <th scope="col">Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user) { ?>
                        <tr>
                            <th scope="row"><?= (int)$user['id'] ?></th>
                            <td><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['fullname'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($user['type'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a href="form_user.php?id=<?= (int)$user['id'] ?>">
                                    <i class="fa fa-pencil-square-o" aria-hidden="true" title="Update"></i>
                                </a>
                                <a href="view_user.php?id=<?= (int)$user['id'] ?>">
                                    <i class="fa fa-eye" aria-hidden="true" title="View"></i>
                                </a>

                                <!-- XÓA: dùng POST với CSRF token -->
                                <form action="delete_user.php" method="POST" style="display:inline" onsubmit="return confirm('Bạn có chắc muốn xóa user #<?= (int)$user['id'] ?>?')">
                                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                    <button type="submit" style="border:none;background:none;padding:0;margin-left:6px;cursor:pointer" title="Delete">
                                        <i class="fa fa-eraser" aria-hidden="true" style="color:#d9534f"></i>
                                    </button>
                                </form>

                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <div class="alert alert-dark" role="alert">
                This is a dark alert—check it out!
            </div>
        <?php } ?>
    </div>
</body>
</html>
