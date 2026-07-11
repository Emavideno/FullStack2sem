<?php
$title = 'Админ-панель';
ob_start();
?>

<div class="admin-header">
    <h1>👑 Админ-панель</h1>
    <p class="subtitle">Управление викториной</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">👤</div>
        <div class="stat-content">
            <div class="stat-number"><?= $user_count ?></div>
            <div class="stat-label">Пользователей</div>
        </div>
        <a href="/admin/users" class="stat-link">Управление →</a>
    </div>

    <div class="stat-card">
        <div class="stat-icon">🌍</div>
        <div class="stat-content">
            <div class="stat-number"><?= $country_count ?></div>
            <div class="stat-label">Стран в базе</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon">❓</div>
        <div class="stat-content">
            <div class="stat-number"><?= $question_count ?></div>
            <div class="stat-label">Вопросов</div>
        </div>
    </div>

    <div class="stat-card api-card">
        <div class="stat-icon">📡</div>
        <div class="stat-content">
            <div class="stat-label">API обновление</div>
            <?php if ($last_update): ?>
                <?php
                $isSuccess = $last_update['status'] === 'success' || ($last_update['countries_imported'] ?? 0) > 249;
                $statusText = $isSuccess ? '✅ Успешно' : '❌ Ошибка';
                $statusClass = $isSuccess ? 'status-success' : 'status-error';
                ?>
                <div class="api-status <?= $statusClass ?>">
                    <?= $statusText ?>
                </div>
                <div class="api-info">
                    <span>Стран: <?= $last_update['countries_imported'] ?? 0 ?></span>
                    <span>Дата: <?= date('d.m.Y H:i', strtotime($last_update['created_at'])) ?></span>
                </div>
                <?php if (($last_update['error_message'] ?? '') && !$isSuccess): ?>
                    <div class="api-error-detail"><?= htmlspecialchars($last_update['error_message']) ?></div>
                <?php endif; ?>
            <?php else: ?>
                <div class="api-status status-warning">⚠️ Данные не импортированы</div>
            <?php endif; ?>

            <?php if ($needs_update): ?>
                <div class="api-warning">⚠️ Данные устарели (более 24 часов)</div>
                <button onclick="updateData()" class="btn btn-primary btn-api" id="update-btn">🔄 Обновить данные</button>
            <?php else: ?>
                <div class="api-success">✅ Данные актуальны (менее 24 часов)</div>
                <button class="btn btn-secondary btn-api" id="update-btn" disabled
                    style="opacity: 0.6; cursor: not-allowed;">
                    🔒 Обновление недоступно
                </button>
                <?php if ($last_update): ?>
                    <div class="api-hint">Обновление доступно через
                        <?= round(24 - (time() - strtotime($last_update['created_at'])) / 3600, 1) ?> часов
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function updateData() {
        const btn = document.getElementById('update-btn');
        const originalText = btn.textContent;
        btn.textContent = '⏳ Обновление...';
        btn.disabled = true;

        fetch('/admin/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                csrf_token: '<?= $_SESSION['csrf_token'] ?? '' ?>'
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Данные обновлены!\nИмпортировано: ' + data.imported + ' стран\nСгенерировано вопросов: ' + data.questions_generated);
                    location.reload();
                } else {
                    alert('❌ ' + data.error);
                    btn.textContent = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                alert('❌ Ошибка: ' + error);
                btn.textContent = originalText;
                btn.disabled = false;
            });
    }
</script>

<style>
    .admin-header {
        text-align: center;
        padding: 20px 0 30px;
    }

    .admin-header h1 {
        font-size: 32px;
        margin-bottom: 8px;
        color: #2c3e50;
    }

    .admin-header .subtitle {
        color: #7f8c8d;
        font-size: 16px;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        display: flex;
        flex-direction: column;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-icon {
        font-size: 32px;
        margin-bottom: 12px;
    }

    .stat-content {
        flex: 1;
    }

    .stat-number {
        font-size: 36px;
        font-weight: 700;
        color: #2c3e50;
    }

    .stat-label {
        color: #7f8c8d;
        font-size: 14px;
        margin-top: 4px;
    }

    .stat-link {
        color: #3498db;
        text-decoration: none;
        font-weight: 500;
        margin-top: 12px;
        display: inline-block;
    }

    .stat-link:hover {
        text-decoration: underline;
    }

    .api-card {
        background: #f8f9fa;
        border: 1px solid #e8ecf1;
    }

    .api-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin: 8px 0;
    }

    .status-success {
        background: #d4edda;
        color: #155724;
    }

    .status-error {
        background: #f8d7da;
        color: #721c24;
    }

    .status-warning {
        background: #fff3cd;
        color: #856404;
    }

    .api-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
        font-size: 13px;
        color: #6c757d;
        margin: 8px 0;
    }

    .api-warning {
        color: #856404;
        background: #fff3cd;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin: 8px 0;
    }

    .api-success {
        color: #155724;
        background: #d4edda;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 13px;
        margin: 8px 0;
    }

    .api-hint {
        font-size: 12px;
        color: #6c757d;
        margin-top: 6px;
        font-style: italic;
    }

    .api-error-detail {
        color: #721c24;
        background: #f8d7da;
        padding: 6px 10px;
        border-radius: 4px;
        font-size: 12px;
        margin: 4px 0;
    }

    .btn-api {
        margin-top: 12px;
        width: 100%;
    }

    .btn-primary {
        background: #3498db;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: #2980b9;
        transform: translateY(-1px);
    }

    .btn-primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-size: 14px;
        cursor: not-allowed;
        transition: all 0.3s;
        opacity: 0.6;
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';