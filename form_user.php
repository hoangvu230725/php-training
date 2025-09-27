<?php
// Start the session
session_start();
require_once 'models/UserModel.php';
require_once __DIR__ . '/security_helpers.php';

$userModel = new UserModel();

$user = NULL; // Add new user
$_id = NULL;

if (!empty($_GET['id'])) {
    $_id = (int)$_GET['id']; // cast để tránh injection
    $user = $userModel->findUserById($_id); // Update existing user
}

if (!empty($_POST['submit'])) {
    // lấy dữ liệu từ POST
    $data = [
        'id'       => isset($_POST['id']) ? (int)$_POST['id'] : null,
        'name'     => $_POST['name'] ?? '',
        'password' => $_POST['password'] ?? '',
    ];

    if (!empty($data['id'])) {
        $userModel->updateUser($data);
    } else {
        $userModel->insertUser($data);
    }
    header('Location: list_users.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>User form</title>
    <?php include 'views/meta.php' ?>
</head>
<body>
    <?php include 'views/header.php'?>
    <div class="container">
        <?php if ($user || !isset($_id)) { ?>
            <div class="alert alert-warning" role="alert">
                User form
            </div>
            <form method="POST">
                <input type="hidden" name="id" value="<?php echo (int)$_id ?>">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input 
                        class="form-control" 
                        name="name" 
                        placeholder="Name" 
                        value="<?php echo !empty($user[0]['name']) ? e($user[0]['name']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Password">
                </div>
                <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
            </form>
        <?php } else { ?>
            <div class="alert alert-success" role="alert">
                User not found!
            </div>
        <?php } ?>
    </div>
</body>
</html>
