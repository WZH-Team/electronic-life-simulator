# Electronic Life Simulator (电子生命模拟器)

中文 | [English](README_en.md)

Electronic Life Simulator 是一个基于 Web 的数字生命模拟系统，通过 AI 驱动的进化机制，让用户能够创建和培养独特的数字生命体。每个生命体都有其独特的特征和发展轨迹，通过用户的引导和 AI 的响应来经历不同的人生阶段。

🌐 在线体验：[https://wbot.ecylt.top/new/electronic-life-simulator/](https://wbot.ecylt.top/new/electronic-life-simulator/)

## 主要特性

- 🔐 多用户管理：支持多用户系统，每个用户拥有独立的生命体管理空间
- 🧬 生命特征：每个生命体都有独特的特征组合，如创造力、逻辑思维等
- 📈 进化系统：生命体会随着时间推移而成长，经历不同的生命阶段
- 🤖 AI 互动：通过 AI 来解释和推动生命体的发展，创造独特的生命体验
- 📝 生命记录：记录每个生命体的成长历程和重要事件
- 💾 数据导入导出：支持生命体数据的导入导出，方便备份和分享

## 技术栈

- 后端：PHP 7.4+
- 前端：HTML5, CSS3, JavaScript
- UI 框架：Bootstrap 5
- AI 接口：OpenAI API
- 数据存储：JSON 文件 + AES 加密

## AI 模型推荐

本项目推荐使用 `wbot-4-preview-low-mini` 模型，这是一个高性价比的 AI 模型选择：

- 🎯 完美适配：专门针对电子生命模拟场景优化
- 💰 超低成本：1M Tokens 仅需 0.1 RMB
- ⚡ 快速响应：生命体进化反应迅速
- 🎨 创意丰富：能生成有趣而多样的进化事件

要使用此模型，只需在配置文件中设置：
```php
define('OPENAI_MODEL', 'wbot-4-preview-low-mini');
```

## 安装说明

1. 克隆仓库：
```bash
git clone https://github.com/WZH-Team/electronic-life-simulator.git
cd electronic-life-simulator
```

2. 配置环境：
- 确保安装了 PHP 7.4 或更高版本
- 确保 PHP 开启了 OpenSSL 扩展

3. 配置文件：
- 复制 `config.example.php` 为 `config.php`
- 修改 `config.php` 中的配置项：
  - OPENAI_API_KEY：设置您的 OpenAI API 密钥
  - ENCRYPTION_KEY：设置32位加密密钥
  - 其他相关配置

4. 设置权限：
```bash
chmod 755 -R data/
```

## 使用说明

1. 用户注册/登录：
   - 首次使用时输入您想要使用的 ID 和密码即可注册
   - 后续使用相同的 ID 和密码登录

2. 创建生命体：
   - 点击"创建新的电子生命"
   - 可以指定名称，或使用系统生成的随机名称

3. 生命体培养：
   - 通过"引导进化"功能来影响生命体的发展
   - 观察生命体的特征变化和事件记录
   - 可以随时导出生命体数据进行备份

4. 数据管理：
   - 支持生命体数据的导入导出
   - 可以删除不需要的生命体
   - 每个用户只能管理自己创建的生命体

## 贡献指南

欢迎提交 Issue 和 Pull Request！在提交 PR 之前，请确保：

1. 代码遵循项目的编码规范
2. 新功能添加了相应的测试用例
3. 所有测试都能通过
4. 更新了相关文档

## 开源协议

本项目采用 Apache License 2.0 许可证。详情请见 [LICENSE](LICENSE) 文件。