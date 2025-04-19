document.addEventListener('DOMContentLoaded', function() {
    // 进化表单处理
    const evolveForms = document.querySelectorAll('.evolution-form');
    evolveForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const lifeId = new URLSearchParams(window.location.search).get('id');
            const prompt = formData.get('prompt');
            
            // 显示加载状态
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="loading"></span>进化中...';
            
            fetch('api.php?action=evolve&life_id=' + lifeId, {
                method: 'POST',
                body: prompt,
                headers: {
                    'Content-Type': 'text/plain'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    showNotification(data.error, true);
                } else {
                    showNotification('进化过程完成！');
                    setTimeout(() => window.location.reload(), 1000);
                }
            })
            .catch(error => {
                console.error('错误:', error);
                showNotification('进化过程中遇到错误，请重试', true);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    });
    
    // 字符计数器
    const promptTextareas = document.querySelectorAll('.evolution-prompt');
    promptTextareas.forEach(textarea => {
        const counter = document.createElement('div');
        counter.className = 'char-counter';
        textarea.parentNode.insertBefore(counter, textarea.nextSibling);
        
        const updateCounter = () => {
            const count = textarea.value.length;
            const limit = 500;
            counter.textContent = `${count}/${limit} 字符`;
            counter.style.color = count > limit ? '#dc3545' : '#6c757d';
        };
        
        textarea.addEventListener('input', updateCounter);
        updateCounter();
    });
    
    // 删除确认
    const deleteButtons = document.querySelectorAll('a[href*="action=delete"]');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            const name = this.closest('.card').querySelector('.card-title').textContent;
            if (!confirm(`确定要删除 ${name} 吗？此操作无法撤销。`)) {
                e.preventDefault();
            }
        });
    });
    
    // 文件上传预览
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const submitBtn = this.closest('form').querySelector('button[type="submit"]');
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                submitBtn.textContent = `导入 ${fileName}`;
            } else {
                submitBtn.textContent = '导入';
            }
        });
    });
    
    // 通知显示函数
    function showNotification(message, isError = false) {
        // 移除现有通知
        const existingNotifications = document.querySelectorAll('.notification');
        existingNotifications.forEach(n => n.remove());
        
        const notification = document.createElement('div');
        notification.className = 'notification' + (isError ? ' error' : ' success');
        notification.textContent = message;
        document.body.appendChild(notification);
        
        // 动画显示
        requestAnimationFrame(() => {
            notification.style.display = 'block';
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        });
        
        // 3秒后隐藏
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
});
