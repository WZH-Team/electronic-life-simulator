<?php
class JsonStorage {
    private $encryptionKey;
    private $encryptionMethod;
    
    public function __construct() {
        $this->encryptionKey = ENCRYPTION_KEY;
        $this->encryptionMethod = ENCRYPTION_METHOD;
        
        // 确保数据目录存在且可写
        if (!file_exists(DATA_DIR)) {
            if (!mkdir(DATA_DIR, 0755, true)) {
                throw new RuntimeException('无法创建数据目录');
            }
        } elseif (!is_writable(DATA_DIR)) {
            throw new RuntimeException('数据目录不可写');
        }
    }
    
    public function encrypt($data) {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->encryptionMethod));
        $encrypted = openssl_encrypt(
            $data, 
            $this->encryptionMethod, 
            $this->encryptionKey, 
            0, 
            $iv
        );
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt($data) {
        $data = base64_decode($data);
        $ivLength = openssl_cipher_iv_length($this->encryptionMethod);
        $iv = substr($data, 0, $ivLength);
        $encrypted = substr($data, $ivLength);
        return openssl_decrypt(
            $encrypted, 
            $this->encryptionMethod, 
            $this->encryptionKey, 
            0, 
            $iv
        );
    }
    
    public function saveLife(Life $life) {
        if (!isset($life->id)) {
            $life->id = 'life_' . uniqid();
        }
        $filename = DATA_DIR . $life->id . '.json';
        $data = $life->toJson();
        $encryptedData = $this->encrypt($data);
        
        if (file_put_contents($filename, $encryptedData) !== false) {
            return true;
        }
        return false;
    }
    
    public function getLife($id, $userId = null) {
        // 确保ID包含'life_'前缀
        if (strpos($id, 'life_') !== 0) {
            $id = 'life_' . $id;
        }
        $filename = DATA_DIR . $id . '.json';
        if (file_exists($filename)) {
            $encryptedData = file_get_contents($filename);
            $data = $this->decrypt($encryptedData);
            $arrayData = json_decode($data, true);
            
            if ($arrayData) {
                $life = Life::fromArray($arrayData);
                $life->id = $id;
                
                // 如果指定了用户ID，验证权限
                if ($userId === null || $life->getUserId() === $userId) {
                    return $life;
                }
            }
        }
        return null;
    }
    
    public function getAllLives($userId = null) {
        $lives = [];
        $files = glob(DATA_DIR . 'life_*.json');
        
        foreach ($files as $file) {
            $encryptedData = file_get_contents($file);
            $data = $this->decrypt($encryptedData);
            $arrayData = json_decode($data, true);
            
            if ($arrayData) {
                $life = Life::fromArray($arrayData);
                $life->id = basename($file, '.json');
                
                // 如果指定了用户ID，只返回该用户的生命体
                if ($userId === null || $life->getUserId() === $userId) {
                    $lives[] = $life;
                }
            }
        }
        
        // 按最后更新时间排序
        usort($lives, function($a, $b) {
            return strtotime($b->last_updated) - strtotime($a->last_updated);
        });
        
        return $lives;
    }
    
    public function deleteLife($id, $userId = null) {
        // 验证用户权限
        $life = $this->getLife($id);
        if ($life && ($userId === null || $life->getUserId() === $userId)) {
            $filename = DATA_DIR . $id . '.json';
            if (file_exists($filename)) {
                return unlink($filename);
            }
        }
        return false;
    }
}
?>
