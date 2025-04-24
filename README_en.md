# Electronic Life Simulator

[中文](README.md) | English

Electronic Life Simulator is a web-based digital life simulation system that enables users to create and nurture unique digital life forms through an AI-driven evolution mechanism. Each life form has its own unique characteristics and development trajectory, experiencing different life stages through user guidance and AI responses.

🌐 Live Demo: [https://wbot.ecylt.top/new/electronic-life-simulator/](https://wbot.ecylt.top/new/electronic-life-simulator/)

## Key Features

- 🔐 Multi-user Management: Supports multiple users with independent life form management spaces
- 🧬 Life Characteristics: Each life form has unique trait combinations, such as creativity and logical thinking
- 📈 Evolution System: Life forms grow over time, experiencing different life stages
- 🤖 AI Interaction: Uses AI to interpret and drive life form development, creating unique life experiences
- 📝 Life Records: Records the growth journey and important events of each life form
- 💾 Data Import/Export: Supports life form data import/export for backup and sharing

## Tech Stack

- Backend: PHP 7.4+
- Frontend: HTML5, CSS3, JavaScript
- UI Framework: Bootstrap 5
- AI Interface: OpenAI API
- Data Storage: JSON files + AES encryption

## AI Model Recommendation

We recommend using the `wbot-4-preview-low-mini` model, which offers excellent value:

- 🎯 Perfect Fit: Specifically optimized for digital life simulation scenarios
- 💰 Low Cost: Only 0.1 RMB per 1M Tokens
- ⚡ Fast Response: Quick evolution reaction time
- 🎨 Creative Rich: Generates interesting and diverse evolution events

To use this model, simply set in your config file:
```php
define('OPENAI_MODEL', 'wbot-4-preview-low-mini');
```

## Installation Guide

1. Clone the repository:
```bash
git clone https://github.com/WZH-Team/electronic-life-simulator.git
cd electronic-life-simulator
```

2. Configure environment:
- Ensure PHP 7.4 or higher is installed
- Make sure PHP OpenSSL extension is enabled

3. Configuration file:
- Copy `config.example.php` to `config.php`
- Modify settings in `config.php`:
  - OPENAI_API_KEY: Set your OpenAI API key
  - ENCRYPTION_KEY: Set a 32-character encryption key
  - Other related configurations

4. Set permissions:
```bash
chmod 755 -R data/
```

## Usage Guide

1. User Registration/Login:
   - Enter your desired ID and password for first-time registration
   - Use the same ID and password for subsequent logins

2. Create Life Forms:
   - Click "Create New Electronic Life"
   - Specify a name or use system-generated random names

3. Life Form Cultivation:
   - Use the "Guide Evolution" feature to influence life form development
   - Observe trait changes and event records
   - Export life form data for backup anytime

4. Data Management:
   - Support for life form data import/export
   - Delete unwanted life forms
   - Users can only manage their own created life forms

## Contribution Guidelines

Issues and Pull Requests are welcome! Before submitting a PR, please ensure:

1. Code follows project coding standards
2. New features include appropriate test cases
3. All tests pass
4. Related documentation is updated

## License

This project is licensed under the Apache License 2.0. See the [LICENSE](LICENSE) file for details.