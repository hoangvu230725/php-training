<?php
session_start();
require_once 'models/UserModel.php';

// Kết nối Redis
$redis = new Redis();
$redis->connect('web-redis', 6379);

$userModel = new UserModel();

if (!empty($_POST['submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    $user = $userModel->auth($username, $password);
    
    if ($user) {
        $userId = $user[0]['id'];

        // Tạo sessionId ngẫu nhiên
        $sessionId = bin2hex(random_bytes(16));

        // TTL 1h, nếu Remember Me = 7 ngày
        $ttl = $remember ? 7 * 24 * 3600 : 3600;
        $redis->setex("session:$sessionId", $ttl, $userId);

        // Lưu sessionId vào PHP session
        $_SESSION['session_id'] = $sessionId;

        // Nếu remember me, lưu cookie
        if ($remember) {
            setcookie('session_id', $sessionId, time() + $ttl, "/", "", false, true);
        }

        $_SESSION['message'] = 'Login successful';
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;

        header('Location: list_users.php');
        exit;
    } else {
        $_SESSION['message'] = 'Login failed';
    }
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
    <div id="loginbox" style="margin-top:50px;" class="mainbox col-md-6 col-md-offset-3 col-sm-8 col-sm-offset-2">
        <div class="panel panel-info" >
            <div class="panel-heading">
                <div class="panel-title">Login</div>
                <div style="float:right; font-size: 80%; position: relative; top:-10px"><a href="#">Forgot password?</a></div>
            </div>

            <div style="padding-top:30px" class="panel-body" >
                <form method="post" class="form-horizontal" role="form">

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                        <input id="login-username" type="text" class="form-control" name="username" value="" placeholder="username or email">
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                        <input id="login-password" type="password" class="form-control" name="password" placeholder="password">
                    </div>

                    <div class="margin-bottom-25">
                        <input type="checkbox" tabindex="3" class="" name="remember" id="remember">
                        <label for="remember"> Remember Me</label>
                    </div>

                    <div class="margin-bottom-25 input-group">
                        <div class="col-sm-12 controls">
                            <button type="submit" name="submit" value="submit" class="btn btn-primary">Submit</button>
                            <a id="btn-fblogin" href="#" class="btn btn-primary">Login with Facebook</a>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12 control">
                                Don't have an account!
                                <a href="form_user.php">
                                    Sign Up Here
                                </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
