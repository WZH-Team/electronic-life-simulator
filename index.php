<?php
require_once 'config.php';
require_once 'classes/JsonStorage.php';
require_once 'classes/Life.php';

// 处理用户登录
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['password'])) {
    $userId = trim($_POST['user_id']);
    $password = $_POST['password'];
    
    // 检查是否是新用户注册
    if (!file_exists(USERS_FILE) || !file_get_contents(USERS_FILE)) {
        // 首次使用，保存用户
        if (saveUser($userId, $password)) {
            $_SESSION['user_id'] = $userId;
            header('Location: index.php');
            exit;
        }
    } else if (validateUser($userId, $password)) {
        // 已存在用户，验证密码
        $_SESSION['user_id'] = $userId;
        header('Location: index.php');
        exit;
    } else {
        $error = '用户ID或密码错误';
    }
}

// 处理登出
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// 如果用户未登录，显示登录页面
if (!isLoggedIn()) {
    ?>
    <!DOCTYPE html>
    <html lang="zh-CN">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>登录 - Electronic Life Simulator</title>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <nav class="navbar navbar-expand-lg mb-4" style="border-bottom: 2px solid #0d6efd;">
            <div class="container">
                <a class="navbar-brand text-primary" href="index.php">Electronic Life Simulator</a>
            </div>
        </nav>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="card-title text-center mb-4">请输入您的ID和密码</h2>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                            <?php endif; ?>
                            <form method="post" action="index.php" class="auth-form">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">ID:</label>
                                    <input type="text" class="form-control" id="user_id" name="user_id" required 
                                           value="<?= htmlspecialchars($_POST['user_id'] ?? '') ?>"
                                           placeholder="输入您的ID">
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">密码:</label>
                                    <input type="password" class="form-control" id="password" name="password" required 
                                           placeholder="输入您的密码">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">登录 / 注册</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="footer mt-0">
            <div class="container">
                <a href="https://github.com/WZH-Team/electronic-life-simulator" target="_blank">开源项目</a>
            </div>
        </footer>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/script.js"></script>
    </body>
    </html>
    <?php
    exit;
}

$storage = new JsonStorage();
$action = $_GET['action'] ?? 'list';
$message = '';
$currentUserId = getCurrentUserId();

// 处理不同操作
switch ($action) {
    case 'create':
        try {
            $life = new Life($_POST['name'] ?? '', $currentUserId);
            if ($storage->saveLife($life)) {
                header("Location: ?action=view&id={$life->id}");
                exit;
            } else {
                $message = "保存生命体失败";
            }
        } catch (RuntimeException $e) {
            $message = "创建生命体时出错: " . $e->getMessage();
        }
        break;
        
    case 'evolve':
        $life = $storage->getLife($_GET['id'], $currentUserId);
        if ($life) {
            $life->evolve($_POST['prompt'] ?? '');
            $storage->saveLife($life);
            header("Location: ?action=view&id={$life->id}");
            exit;
        }
        break;
        
    case 'export':
        $life = $storage->getLife($_GET['id'], $currentUserId);
        if ($life) {
            // 导出时不加密
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . $life->name . '.json"');
            echo $life->toJson();
            exit;
        }
        break;
        
    case 'import':
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['life_file'])) {
            $json = file_get_contents($_FILES['life_file']['tmp_name']);
            $data = json_decode($json, true);
            
            if ($data) {
                $life = Life::fromArray($data);
                $storage->saveLife($life);
                header("Location: ?action=view&id={$life->id}");
                exit;
            } else {
                $message = "Invalid file format";
            }
        }
        break;
        
    case 'delete':
        if ($storage->deleteLife($_GET['id'], $currentUserId)) {
            header("Location: index.php");
            exit;
        }
        break;
}

// 显示当前视图
if (isset($_GET['id'])) {
    $life = $storage->getLife($_GET['id'], $currentUserId);
}

$lives = $storage->getAllLives($currentUserId);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" width="device-width, initial-scale=1.0">
    <title>Electronic Life Simulator</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg mb-4" style="border-bottom: 2px solid #0d6efd;">
        <div class="container">
            <a class="navbar-brand text-primary" href="index.php">Electronic Life Simulator</a>
            <?php if (isLoggedIn()): ?>
            <div class="d-flex align-items-center">
                <span class="text-dark me-3">当前用户ID: <?= htmlspecialchars($currentUserId) ?></span>
                <a href="?action=logout" class="btn btn-outline-primary btn-sm">登出</a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <?php if ($action === 'list' || $action === 'import'): ?>
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="card-title">创建新的电子生命</h2>
                            <form method="post" action="?action=create" class="mb-3">
                                <div class="input-group">
                                    <input type="text" name="name" class="form-control" placeholder="生命体名称（可选）">
                                    <button type="submit" class="btn btn-primary">创建</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h2 class="card-title">导入生命体</h2>
                            <form method="post" action="?action=import" enctype="multipart/form-data">
                                <div class="input-group">
                                    <input type="file" name="life_file" class="form-control" accept=".json">
                                    <button type="submit" class="btn btn-primary">导入</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">您的电子生命</h2>
                    <div class="list-group">
                        <?php foreach ($lives as $life): ?>
                            <a href="?action=view&id=<?= $life->id ?>" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($life->name) ?></h5>
                                    <p class="mb-1">年龄: <?= $life->age ?> | 阶段: <?= $life->stage ?></p>
                                </div>
                                <span class="badge bg-primary rounded-pill">查看详情</span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($action === 'view' && $life): ?>
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= htmlspecialchars($life->name) ?></h2>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h3 class="card-title h5">基本信息</h3>
                                    <p class="mb-2">年龄: <?= $life->age ?></p>
                                    <p class="mb-2">阶段: <?= $life->stage ?></p>
                                    
                                    <h4 class="h5 mt-4">特征</h4>
                                    <div class="list-group">
                                        <?php foreach ($life->traits as $trait): ?>
                                            <div class="list-group-item"><?= htmlspecialchars($trait) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-body">
                                    <h3 class="card-title h5">引导进化</h3>
                                    <form method="post" action="?action=evolve&id=<?= $life->id ?>" class="evolution-form">
                                        <div class="mb-3">
                                            <textarea name="prompt" class="form-control evolution-prompt" 
                                                      placeholder="输入进化引导（可选）" rows="4"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">进化</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-body">
                            <h3 class="card-title h5">生命事件</h3>
                            <div class="list-group">
                                <?php foreach (array_reverse($life->memory) as $event): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1">年龄 <?= $event['age'] ?></h5>
                                            <small><?= $event['timestamp'] ?></small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars($event['event']) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="?action=export&id=<?= $life->id ?>" class="btn btn-success">导出</a>
                        <a href="?action=delete&id=<?= $life->id ?>" class="btn btn-danger" 
                           onclick="return confirm('确定要删除这个生命体吗？')">删除</a>
                        <a href="index.php" class="btn btn-secondary">返回列表</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <p>未找到生命体。</p>
                <a href="index.php" class="btn btn-primary">返回列表</a>
            </div>
        <?php endif; ?>
    </div>
    
    <footer class="footer mt-0">
        <div class="container">
            <a href="https://github.com/WZH-Team/electronic-life-simulator" target="_blank">开源项目</a>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>