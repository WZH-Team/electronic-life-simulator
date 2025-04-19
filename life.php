<?php
require_once 'config.php';
require_once 'classes/Life.php';
require_once 'classes/JsonStorage.php';

$storage = new JsonStorage();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $life = new Life($_POST['name'] ?? '');
        $storage->saveLife($life);
        header("Location: index.php?action=view&id={$life->getId()}");
        exit;
        
    case 'evolve':
        $life = $storage->getLife($_POST['life_id'] ?? '');
        if ($life) {
            $life->evolve($_POST['prompt'] ?? '');
            $storage->saveLife($life);
            header("Location: index.php?action=view&id={$life->getId()}");
            exit;
        }
        break;
        
    case 'delete':
        if ($storage->deleteLife($_GET['id'] ?? '')) {
            header("Location: index.php");
            exit;
        }
        break;
}

// 如果操作无效或失败，返回首页
header("Location: index.php");
exit;
?>
