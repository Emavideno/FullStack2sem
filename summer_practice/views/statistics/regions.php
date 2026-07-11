<?php
$title = 'Статистика по регионам';
ob_start();
?>

<h1>🌍 Статистика по регионам</h1>

<div class="stats-grid">
    <?php foreach ($region_stats as $stat): ?>
        <div class="stat-card">
            <h3><?= $stat['region'] ?></h3>
            <div class="stat-item">
                <span>Попыток:</span>
                <span class="stat-value"><?= $stat['total_attempts'] ?></span>
            </div>
            <div class="stat-item">
                <span>Правильных:</span>
                <span class="stat-value text-success"><?= $stat['correct_attempts'] ?></span>
            </div>
            <div class="stat-item">
                <span>Процент:</span>
                <span
                    class="stat-value <?= $stat['success_rate'] >= 70 ? 'text-success' : ($stat['success_rate'] >= 40 ? 'text-warning' : 'text-danger') ?>">
                    <?= $stat['success_rate'] ?>%
                </span>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($region_stats)): ?>
        <p class="text-muted">Нет данных по регионам</p>
    <?php endif; ?>
</div>

<div style="margin-top: 30px;">
    <a href="/stats" class="btn">⬅️ Назад к статистике</a>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 20px;
        margin: 20px 0;
    }

    .stat-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .stat-card h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: #2c3e50;
        border-bottom: 2px solid #f0f0f0;
        padding-bottom: 10px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .stat-item:last-child {
        border-bottom: none;
    }

    .stat-value {
        font-weight: bold;
    }

    .text-success {
        color: #27ae60;
    }

    .text-warning {
        color: #f39c12;
    }

    .text-danger {
        color: #e74c3c;
    }

    .text-muted {
        color: #999;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';