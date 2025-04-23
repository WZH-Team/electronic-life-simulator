<?php
require_once 'config.php';
require_once 'classes/JsonStorage.php';
require_once 'classes/Life.php';

// 处理用户登录
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['password'])) {
    $userId = htmlspecialchars(trim($_POST['user_id']));
    $password = htmlspecialchars($_POST['password']);
    
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
        <title>Electronic Life Simulator</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body class="bg-light d-flex flex-column min-vh-100">
        <nav class="navbar navbar-expand-lg shadow-sm bg-white">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <i class="bi bi-cpu text-primary"></i>
                    <span class="fw-bold ms-2">Electronic Life Simulator</span>
                </a>
            </div>
        </nav>

        <div class="container flex-grow-1 py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <i class="bi bi-shield-lock text-primary" style="font-size: 2.5rem;"></i>
                                <h2 class="h3 mt-3">欢迎使用</h2>
                                <p class="text-secondary mb-0">请输入您的ID和密码</p>
                            </div>
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger d-flex align-items-center" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div><?= htmlspecialchars($error) ?></div>
                                </div>
                            <?php endif; ?>
                            <form method="post" action="index.php" class="auth-form">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">ID</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                                        <input type="text" class="form-control" id="user_id" name="user_id" required 
                                               value="<?= htmlspecialchars($_POST['user_id'] ?? '') ?>"
                                               placeholder="输入您的ID">
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label">密码</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" required 
                                               placeholder="输入您的密码">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    登录 / 注册
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="footer mt-auto py-3 bg-white border-top w-100">
            <div class="container text-center">
                <a href="https://github.com/WZH-Team/electronic-life-simulator" target="_blank" class="text-decoration-none">
                    <i class="bi bi-github me-1"></i>开源项目
                </a>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/script.js"></script>
    </body>
    </html>
    <?php
    exit;
}

$storage = new JsonStorage();
$action = htmlspecialchars($_GET['action'] ?? 'list');
$message = '';
$currentUserId = getCurrentUserId();

// 处理不同操作
switch ($action) {
    case 'create':
        try {
            $life = new Life(htmlspecialchars($_POST['name'] ?? ''), $currentUserId);
            if ($storage->saveLife($life)) {
                header("Location: ?action=view&id=" . urlencode($life->id));
                exit;
            } else {
                $message = "保存生命体失败";
            }
        } catch (RuntimeException $e) {
            $message = "创建生命体时出错: " . htmlspecialchars($e->getMessage());
        }
        break;
        
    case 'evolve':
        $life = $storage->getLife(htmlspecialchars($_GET['id']), $currentUserId);
        if ($life) {
            $life->evolve(htmlspecialchars($_POST['prompt'] ?? ''));
            $storage->saveLife($life);
            header("Location: ?action=view&id=" . urlencode($life->id));
            exit;
        }
        break;
        
    case 'export':
        $life = $storage->getLife(htmlspecialchars($_GET['id']), $currentUserId);
        if ($life) {
            // 导出时不加密
            header('Content-Type: application/json');
            header('Content-Disposition: attachment; filename="' . htmlspecialchars($life->name) . '.json"');
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
                header("Location: ?action=view&id=" . urlencode($life->id));
                exit;
            } else {
                $message = "Invalid file format";
            }
        }
        break;
        
    case 'delete':
        if ($storage->deleteLife(htmlspecialchars($_GET['id']), $currentUserId)) {
            header("Location: index.php");
            exit;
        }
        break;
}

// 显示当前视图
if (isset($_GET['id'])) {
    $life = $storage->getLife(htmlspecialchars($_GET['id']), $currentUserId);
}

$lives = $storage->getAllLives($currentUserId);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronic Life Simulator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light d-flex flex-column min-vh-100">
    <nav class="navbar navbar-expand-lg shadow-sm bg-white">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-cpu text-primary"></i>
                <span class="fw-bold ms-2">Electronic Life Simulator</span>
            </a>
            <?php if (isLoggedIn()): ?>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($currentUserId) ?>
                </span>
                <a href="?action=logout" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>登出
                </a>
            </div>
            <?php endif; ?>
        </div>
    </nav>

    <div class="container flex-grow-1 py-4">
        <?php if ($message): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= htmlspecialchars($message) ?></div>
            </div>
        <?php endif; ?>
        
        <?php if ($action === 'list' || $action === 'import'): ?>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h3 class="card-title h5 mb-3">
                                <i class="bi bi-plus-circle text-primary me-2"></i>
                                创建新的电子生命
                            </h3>
                            <form method="post" action="?action=create">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-tag"></i></span>
                                    <input type="text" name="name" class="form-control" placeholder="生命体名称（可选）">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-plus-lg me-1"></i>创建
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body">
                            <h3 class="card-title h5 mb-3">
                                <i class="bi bi-upload text-primary me-2"></i>
                                导入生命体
                            </h3>
                            <form method="post" action="?action=import" enctype="multipart/form-data">
                                <div class="input-group">
                                    <input type="file" name="life_file" class="form-control" accept=".json">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cloud-arrow-up me-1"></i>导入
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="card-title h5 mb-4">
                        <i class="bi bi-collection text-primary me-2"></i>
                        您的电子生命
                    </h3>
                    <?php if (empty($lives)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-emoji-neutral text-secondary" style="font-size: 2rem;"></i>
                            <p class="text-secondary mt-3">还没有创建任何生命体</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($lives as $life): ?>
                                <a href="?action=view&id=<?= htmlspecialchars($life->id) ?>" 
                                   class="list-group-item list-group-item-action border-0 rounded mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h5 class="mb-1">
                                                <i class="bi bi-circle-fill text-primary me-2" style="font-size: 0.5rem;"></i>
                                                <?= htmlspecialchars($life->name) ?>
                                            </h5>
                                            <p class="mb-1 text-secondary">
                                                <i class="bi bi-calendar2 me-1"></i>年龄: <?= htmlspecialchars($life->age) ?> 
                                                <span class="mx-2">|</span>
                                                <i class="bi bi-stars me-1"></i>阶段: <?= htmlspecialchars($life->stage) ?>
                                            </p>
                                        </div>
                                        <span class="badge bg-primary rounded-pill">
                                            <i class="bi bi-arrow-right me-1"></i>查看详情
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif ($action === 'view' && $life): ?>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="card-title h4 mb-0">
                            <i class="bi bi-person text-primary me-2"></i>
                            <?= htmlspecialchars($life->name) ?>
                        </h2>
                        <div>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm me-2">
                                <i class="bi bi-arrow-left me-1"></i>返回列表
                            </a>
                            <div class="btn-group">
                                <a href="?action=export&id=<?= htmlspecialchars($life->id) ?>" class="btn btn-outline-success btn-sm">
                                    <i class="bi bi-download me-1"></i>导出
                                </a>
                                <a href="?action=delete&id=<?= htmlspecialchars($life->id) ?>" class="btn btn-outline-danger btn-sm"
                                   onclick="return confirm('确定要删除这个生命体吗？此操作无法撤销。')">
                                    <i class="bi bi-trash me-1"></i>删除
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <h3 class="card-title h5 mb-3">
                                        <i class="bi bi-info-circle text-primary me-2"></i>基本信息
                                    </h3>
                                    <div class="d-flex mb-3">
                                        <div class="me-4">
                                            <small class="text-secondary">年龄</small>
                                            <h4 class="mb-0"><?= htmlspecialchars($life->age) ?></h4>
                                        </div>
                                        <div>
                                            <small class="text-secondary">阶段</small>
                                            <h4 class="mb-0"><?= htmlspecialchars($life->stage) ?></h4>
                                        </div>
                                    </div>
                                    
                                    <h4 class="h6 mb-3">特征</h4>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($life->traits as $trait): ?>
                                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                                <?= htmlspecialchars($trait) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <h3 class="card-title h5 mb-3">
                                        <i class="bi bi-lightning text-primary me-2"></i>引导进化
                                    </h3>
                                    <form method="post" action="?action=evolve&id=<?= htmlspecialchars($life->id) ?>" class="evolution-form">
                                        <div class="mb-3">
                                            <textarea name="prompt" class="form-control evolution-prompt" 
                                                      placeholder="输入进化引导（可选）" rows="4"></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-arrow-clockwise me-1"></i>进化
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <h3 class="card-title h5 mb-3">
                                <i class="bi bi-clock-history text-primary me-2"></i>生命事件
                            </h3>
                            <div class="memory-timeline">
                                <?php foreach (array_reverse($life->memory) as $event): ?>
                                    <div class="list-group-item bg-transparent border-0 mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">年龄 <?= htmlspecialchars($event['age']) ?></h6>
                                                <p class="mb-1"><?= htmlspecialchars($event['event']) ?></p>
                                            </div>
                                            <small class="text-secondary"><?= htmlspecialchars($event['timestamp']) ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>未找到生命体。</div>
            </div>
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-arrow-left me-1"></i>返回列表
            </a>
        <?php endif; ?>
    </div>
    
    <footer class="footer mt-auto py-3 bg-white border-top w-100">
        <div class="container text-center">
            <a href="https://github.com/WZH-Team/electronic-life-simulator" target="_blank" class="text-decoration-none">
                <i class="bi bi-github me-1"></i>开源项目
            </a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>