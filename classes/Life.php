<?php
class Life {
    public $id;
    private $userId; // 添加用户ID字段
    private $name;
    private $age = 0;
    private $stage = '胚胎'; // 修改默认值为中文
    private $traits = [];
    private $memory = [];
    public $created_at;
    public $last_updated;
    
    // 生命阶段定义
    private static $stages = [
        '胚胎', 
        '婴儿', 
        '儿童', 
        '青少年', 
        '成年', 
        '老年', 
        '超越'
    ];
    
    // 可能的特征列表
    private static $possibleTraits = [
        '适应性强', '好奇心重', '富有攻击性', '随和', 
        '创造力强', '逻辑思维', '感性', '社交能力强', 
        '独处者', '探索者', '居家', '思想家'
    ];
    
    public function __construct($name = '', $userId = '') {
        $this->name = $name ?: $this->generateRandomName();
        $this->userId = $userId;
        $this->created_at = date('Y-m-d H:i:s');
        $this->last_updated = $this->created_at;
        $this->initializeTraits();
    }
    
    private function generateRandomName() {
        $prefixes = ['量子', '数字', '神经', '生物', '赛博', '纳米'];
        $suffixes = ['火花', '之光', '核心', '脉冲', '微粒', '节点'];
        return $prefixes[array_rand($prefixes)] . $suffixes[array_rand($suffixes)];
    }
    
    private function initializeTraits() {
        // 初始随机特征
        $numTraits = rand(1, 3);
        $this->traits = array_rand(array_flip(self::$possibleTraits), $numTraits);
        if (!is_array($this->traits)) {
            $this->traits = [$this->traits];
        }
    }
    
    public function evolve($prompt = '') {
        $this->age++;
        $this->last_updated = date('Y-m-d H:i:s');
        
        // 调用OpenAI API决定进化路径
        $response = $this->callOpenAI($prompt);
        $this->processEvolution($response);
        
        return $this;
    }
    
    private function callOpenAI($prompt) {
        $api = new OpenAIClient();
        return $api->getEvolutionSuggestion($this, $prompt);
    }
    
    private function processEvolution($response) {
        // 解析API响应并更新生命状态
        if (isset($response['stage'])) {
            $this->stage = $response['stage'];
        }
        
        if (isset($response['new_traits']) && is_array($response['new_traits'])) {
            $this->traits = array_merge($this->traits, $response['new_traits']);
            $this->traits = array_unique($this->traits);
        }
        
        // 限制特征数量
        if (count($this->traits) > 5) {
            shuffle($this->traits);
            $this->traits = array_slice($this->traits, 0, 5);
        }
        
        // 添加到记忆
        $this->memory[] = [
            'age' => $this->age,
            'event' => $response['event_description'] ?? '未知的进化变化',
            'timestamp' => $this->last_updated
        ];
        
        // 限制记忆长度
        if (count($this->memory) > 20) {
            $this->memory = array_slice($this->memory, -20);
        }
    }
    
    public function __get($name) {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        return null;
    }
    
    public function __set($name, $value) {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }
    
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->userId;
    }
    
    public function getName() {
        return $this->name;
    }
    
    public function getAge() {
        return $this->age;
    }
    
    public function getStage() {
        return $this->stage;
    }
    
    public function getTraits() {
        return $this->traits;
    }
    
    public function getMemory() {
        return $this->memory;
    }
    
    public function toArray() {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'name' => $this->name,
            'age' => $this->age,
            'stage' => $this->stage,
            'traits' => $this->traits,
            'memory' => $this->memory,
            'created_at' => $this->created_at,
            'last_updated' => $this->last_updated
        ];
    }
    
    public function toJson() {
        return json_encode($this->toArray(), JSON_PRETTY_PRINT);
    }
    
    public static function fromArray($data) {
        $life = new self($data['name'] ?? '', $data['userId'] ?? '');
        $life->id = $data['id'] ?? null;
        $life->age = $data['age'] ?? 0;
        $life->stage = $data['stage'] ?? '胚胎';
        $life->traits = $data['traits'] ?? [];
        $life->memory = $data['memory'] ?? [];
        $life->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $life->last_updated = $data['last_updated'] ?? $life->created_at;
        return $life;
    }
}
?>
