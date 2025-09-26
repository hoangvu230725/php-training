<?php
require_once 'BaseModel.php';

class UserModel extends BaseModel
{
    // Tìm user theo id (an toàn)
    public function findUserById($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) return null;

        $sql = 'SELECT * FROM users WHERE id = ? LIMIT 1';
        $stmt = mysqli_prepare(self::$_connection, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        if ($res) mysqli_free_result($res);
        mysqli_stmt_close($stmt);
        return $user;
    }

    // Tìm user theo keyword (LIKE) - an toàn với prepared statements
    public function findUser($keyword)
    {
        $kw = '%' . trim($keyword) . '%';
        $sql = 'SELECT * FROM users WHERE user_name LIKE ? OR user_email LIKE ?';
        $stmt = mysqli_prepare(self::$_connection, $sql);
        mysqli_stmt_bind_param($stmt, 'ss', $kw, $kw);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
        if ($res) mysqli_free_result($res);
        mysqli_stmt_close($stmt);
        return $rows;
    }

    // Authentication user: dùng password_hash / password_verify
    public function auth($userName, $password)
    {
        $userName = trim($userName);
        $sql = 'SELECT * FROM users WHERE name = ? LIMIT 1';
        $stmt = mysqli_prepare(self::$_connection, $sql);
        mysqli_stmt_bind_param($stmt, 's', $userName);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $user = $res ? mysqli_fetch_assoc($res) : null;
        if ($res) mysqli_free_result($res);
        mysqli_stmt_close($stmt);

        if ($user && isset($user['password'])) {
            // Giả sử password trong DB đã dùng password_hash
            if (password_verify($password, $user['password'])) {
                // thành công
                return $user;
            }
        }
        return null;
    }

    // Delete user by id (đã chuẩn)
    public function deleteUserById($id)
    {
        $id = filter_var($id, FILTER_VALIDATE_INT);
        if ($id === false) return false;

        $sql = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare(self::$_connection, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    // Update user (POST) - sử dụng prepared, hash password
    public function updateUser($input)
    {
        if ($_SERVER["REQUEST_METHOD"] !== 'POST') {
            http_response_code(405);
            die('Method not allowed');
        }

        // CSRF token check (giữ nguyên cơ chế nếu bạn đã set token trong session)
        if (!isset($_SESSION['csrf_token_update']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token_update']) {
            http_response_code(403);
            die('CSRF token validation failed');
        }

        // Validate inputs
        $id = isset($input['id']) ? filter_var($input['id'], FILTER_VALIDATE_INT) : false;
        $name = isset($input['name']) ? trim($input['name']) : '';
        $password = isset($input['password']) ? $input['password'] : '';

        if ($id === false || $name === '' ) {
            return false;
        }

        // Nếu password rỗng => không update password
        if ($password === '') {
            $sql = 'UPDATE users SET name = ? WHERE id = ?';
            $stmt = mysqli_prepare(self::$_connection, $sql);
            mysqli_stmt_bind_param($stmt, 'si', $name, $id);
        } else {
            // hash mật khẩu an toàn
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $sql = 'UPDATE users SET name = ?, password = ? WHERE id = ?';
            $stmt = mysqli_prepare(self::$_connection, $sql);
            mysqli_stmt_bind_param($stmt, 'ssi', $name, $hashedPassword, $id);
        }

        if (!$stmt) {
            return false;
        }

        $result = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        return $result;
    }

    // Insert user (sử dụng prepared statement + password_hash)
    public function insertUser($input) {
        $name = isset($input['name']) ? trim($input['name']) : '';
        $fullname = isset($input['fullname']) ? trim($input['fullname']) : '';
        $email = isset($input['email']) ? trim($input['email']) : '';
        $type = isset($input['type']) ? trim($input['type']) : 'user';
        $password = isset($input['password']) ? $input['password'] : '';

        if ($name === '' || $password === '') {
            return false; // required fields
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (name, fullname, email, type, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare(self::$_connection, $sql);
        mysqli_stmt_bind_param($stmt, 'sssss', $name, $fullname, $email, $type, $hashed);
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            $insert_id = mysqli_insert_id(self::$_connection);
        } else {
            $insert_id = false;
        }
        mysqli_stmt_close($stmt);
        return $insert_id;
    }

    // Get users (with optional keyword) - fixed (no multi_query)
    public function getUsers($params = [])
    {
        if (!empty($params['keyword'])) {
            $kw = '%' . trim($params['keyword']) . '%';
            $sql = 'SELECT * FROM users WHERE name LIKE ?';
            $stmt = mysqli_prepare(self::$_connection, $sql);
            mysqli_stmt_bind_param($stmt, 's', $kw);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $rows = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
            if ($res) mysqli_free_result($res);
            mysqli_stmt_close($stmt);
            return $rows;
        } else {
            $sql = 'SELECT * FROM users';
            // reuse BaseModel select method (assume it returns array)
            return $this->select($sql);
        }
    }
}
