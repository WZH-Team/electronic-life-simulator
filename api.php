<?php
require_once 'config.php';
require_once 'classes/Life.php';
require_once 'classes/OpenAIClient.php';

header('Content-Type: application/json');

// 检查用户是否已登录
if (!isLoggedIn()) {
    echo json_encode(['error' => '未授权访问']);
    exit;
}

$action = htmlspecialchars($_GET['action'] ?? '');
$lifeId = htmlspecialchars($_GET['life_id'] ?? '');
$currentUserId = getCurrentUserId();

if (empty($action) || empty($lifeId)) {
    echo json_encode(['error' => '缺少必要参数']);
    exit;
}

$storage = new JsonStorage();
$life = $storage->getLife($lifeId, $currentUserId); // 添加用户ID参数

if (!$life) {
    echo json_encode(['error' => '未找到生命体']);
    exit;
}

switch ($action) {
    case 'evolve':
        $prompt = htmlspecialchars(file_get_contents('php://input'));
        $life->evolve($prompt);
        $storage->saveLife($life);
        echo json_encode($life->toArray());
        break;
        
    case 'get_info':
        echo json_encode($life->toArray());
        break;
        
    default:
        echo json_encode(['error' => '无效的操作']);
        break;
}
?>
