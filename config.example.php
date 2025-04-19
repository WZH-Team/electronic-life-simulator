<?php
session_start();

// 基本配置
define('DATA_DIR', __DIR__ . '/data/');

// OpenAI API 配置
define('OPENAI_API_HOST', 'https://api.223387.xyz'); // 可以修改为其他API代理
define('OPENAI_API_KEY', 'your-api-key-here');
define('OPENAI_MODEL', 'wbot-4-preview-low-mini'); // 推荐使用此模型，1M Tokens仅需0.1RMB

// 加密配置
define('ENCRYPTION_KEY', 'change-this-to-a-32-character-random-string'); // 32字符加密密钥
define('ENCRYPTION_METHOD', 'AES-256-CBC');
define('USER_SALT', 'change-this-to-your-user-salt'); // 用于密码加密的盐值

// 用户数据文件
define('USERS_FILE', DATA_DIR . 'users.json');

// 确保数据目录存在
if (!file_exists(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}

// 检查用户是否已登录
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// 获取当前登录用户ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// 验证用户凭据
function validateUser($userId, $password) {
    if (!file_exists(USERS_FILE)) {
        return false;
    }
    
    $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];
    $hashedPassword = md5($password . USER_SALT);
    
    return isset($users[$userId]) && $users[$userId] === $hashedPassword;
}

// 保存用户凭据
function saveUser($userId, $password) {
    $users = [];
    if (file_exists(USERS_FILE)) {
        $users = json_decode(file_get_contents(USERS_FILE), true) ?? [];
    }
    
    $hashedPassword = md5($password . USER_SALT);
    $users[$userId] = $hashedPassword;
    
    return file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT));
}

// 自动加载类
spl_autoload_register(function ($class_name) {
    include 'classes/' . $class_name . '.php';
});
?>