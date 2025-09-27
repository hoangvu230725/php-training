<?php
// security_helpers.php
// Hàm escape HTML (text + attribute)
function e($s){
    if ($s === null) return '';
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Sinh JS literal an toàn để nhúng vào <script>
function js_literal($v){
    return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Helper PDO (ví dụ). Nếu bạn có kết nối khác, hãy sử dụng kết nối hiện tại.
function getPdo($host='127.0.0.1', $db='yourdb', $user='root', $pass=''){
    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";
    $opt = [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $user, $pass, $opt);
}
