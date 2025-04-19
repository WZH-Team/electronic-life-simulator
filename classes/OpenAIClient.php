<?php
class OpenAIClient {
    public function getEvolutionSuggestion(Life $life, $userPrompt = '') {
        $messages = $this->buildMessages($life, $userPrompt);
        
        $data = [
            'model' => OPENAI_MODEL,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 150
        ];
        
        $ch = curl_init(OPENAI_API_HOST . '/v1/chat/completions');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            error_log("OpenAI API curl error: " . $curlError);
            return [
                'error' => 'API连接错误',
                'stage' => $life->stage,
                'new_traits' => [],
                'event_description' => '进化过程遇到连接错误'
            ];
        }
        
        if ($httpCode !== 200) {
            error_log("OpenAI API HTTP error: " . $httpCode . ", Response: " . $response);
            return [
                'error' => 'API响应错误',
                'stage' => $life->stage,
                'new_traits' => [],
                'event_description' => '进化过程遇到API错误'
            ];
        }
        
        return $this->parseResponse($response, $life);
    }
    
    private function buildMessages(Life $life, $userPrompt) {
        $systemPrompt = "你是一个数字生命模拟器。请用中文为一个数字生命体生成一个简短的进化事件，基于其当前状态。" .
                       "你的响应必须是一个包含以下字段的有效JSON对象：\n" .
                       "{\n" .
                       "  \"stage\": \"[当前或下一个生命阶段]\",\n" .
                       "  \"new_traits\": [新获得的特征数组，使用中文描述],\n" .
                       "  \"event_description\": \"[详细的进化事件描述，使用中文]\"\n" .
                       "}";
        
        $userContent = "当前生命状态：\n" . 
                      "名称: {$life->name}\n" .
                      "年龄: {$life->age}\n" .
                      "阶段: {$life->stage}\n" .
                      "特征: " . implode(', ', $life->traits) . "\n" .
                      ($userPrompt ? "用户引导: $userPrompt\n" : "");
        
        return [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'user',
                'content' => $userContent
            ]
        ];
    }
    
    private function parseResponse($apiResponse, Life $life) {
        $response = json_decode($apiResponse, true);
        
        if (!isset($response['choices'][0]['message']['content'])) {
            error_log("OpenAI API invalid response structure: " . $apiResponse);
            return [
                'stage' => $life->stage,
                'new_traits' => [],
                'event_description' => '进化过程遇到响应错误'
            ];
        }
        
        $content = $response['choices'][0]['message']['content'];
        
        try {
            $parsed = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Failed to parse OpenAI response as JSON: " . json_last_error_msg());
                // 尝试从文本中提取信息
                return $this->extractFromText($content, $life);
            }
            
            // 验证必要字段
            if (!isset($parsed['stage']) || !isset($parsed['new_traits']) || !isset($parsed['event_description'])) {
                error_log("Missing required fields in OpenAI response: " . $content);
                return [
                    'stage' => $life->stage,
                    'new_traits' => [],
                    'event_description' => '进化过程产生了不完整的响应'
                ];
            }
            
            return $parsed;
        } catch (Exception $e) {
            error_log("Error parsing OpenAI response: " . $e->getMessage());
            return [
                'stage' => $life->stage,
                'new_traits' => [],
                'event_description' => '进化过程遇到意外错误'
            ];
        }
    }
    
    private function extractFromText($text, Life $life) {
        // 从非JSON响应中提取有用信息
        $event = trim(preg_replace('/[\r\n]+/', ' ', $text));
        return [
            'stage' => $life->stage,
            'new_traits' => [],
            'event_description' => $event ?: '发生了意外的进化变化'
        ];
    }
}
?>
