<?php
require_once 'config.php';
require_once 'classes/Life.php';
require_once 'classes/JsonStorage.php';

$storage = new JsonStorage();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 导入处理
    if (isset($_FILES['life_file']) && $_FILES['life_file']['error'] === UPLOAD_ERR_OK) {
        $json = file_get_contents($_FILES['life_file']['tmp_name']);
        $data = json_decode($json, true);
        
        if ($data) {
            $life = Life::fromArray($data);
            if ($storage->saveLife($life)) {
                header("Location: index.php?action=view&id={$life->id}");
                exit;
            }
        }
        
        $_SESSION['error'] = "生命体导入失败";
        header("Location: index.php");
        exit;
    }
} elseif (isset($_GET['export'])) {
    // 导出处理
    $life = $storage->getLife($_GET['export']);
    if ($life) {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $life->getName() . '.json"');
        echo $life->toJson();
        exit;
    }
    
    $_SESSION['error'] = "未找到生命体";
    header("Location: index.php");
    exit;
}

// 如果直接访问，重定向回首页
header("Location: index.php");
exit;
?>
