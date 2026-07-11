<?php
$title = 'Моя статистика';
ob_start();
?>

<h1>📊 Моя статистика</h1>

<div class="stats-grid">
    <div class="stat-card">
        <h3>Общая статистика</h3>
        <div class="stat-item">
            <span>Всего вопросов отвечено:</span>
            <span class="stat-value"><?= $overall['total_answers'] ?? 0 ?></span>
        </div>
        <div class="stat-item">
            <span>Правильных ответов:</span>
            <span class="stat-value text-success"><?= $overall['correct_answers'] ?? 0 ?></span>
        </div>
        <div class="stat-item">
            <span>Процент правильных:</span>
            <span class="stat-value <?= ($overall['overall_rate'] ?? 0) >= 70 ? 'text-success' : 'text-warning' ?>">
                <?= $overall['overall_rate'] ?? 0 ?>%
            </span>
        </div>
        <div class="stat-item">
            <span>Изучено типов вопросов:</span>
            <span class="stat-value"><?= $overall['types_played'] ?? 0 ?> из 6</span>
        </div>
    </div>

    <div class="stat-card">
        <h3>По типам вопросов</h3>
        <?php foreach ($stats_by_type as $stat): ?>
            <div class="stat-item">
                <span><?= $type_labels[$stat['type']] ?? $stat['type'] ?>:</span>
                <span class="stat-value <?= ($stat['rate'] ?? 0) >= 70 ? 'text-success' : 'text-warning' ?>">
                    <?= $stat['rate'] ?? 0 ?>% (<?= $stat['correct'] ?? 0 ?>/<?= $stat['total'] ?? 0 ?>)
                </span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($stats_by_type)): ?>
            <p class="text-muted">Вы ещё не отвечали на вопросы</p>
        <?php endif; ?>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <h3>✅ Сильные регионы</h3>
        <?php foreach ($strong_regions as $region): ?>
            <div class="stat-item">
                <span><?= $region['region'] ?>:</span>
                <span class="stat-value text-success"><?= $region['success_rate'] ?>%</span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($strong_regions)): ?>
            <p class="text-muted">Пока нет сильных регионов</p>
        <?php endif; ?>
    </div>

    <div class="stat-card">
        <h3>❌ Слабые регионы</h3>
        <?php foreach ($weak_regions as $region): ?>
            <div class="stat-item">
                <span><?= $region['region'] ?>:</span>
                <span class="stat-value text-danger"><?= $region['success_rate'] ?>%</span>
                <span class="stat-hint">(<?= $region['total_attempts'] ?> попыток)</span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($weak_regions)): ?>
            <p class="text-muted">Пока нет слабых регионов</p>
        <?php endif; ?>
    </div>
</div>

<!-- ===== НОВЫЙ БЛОК: ИГРА ПО СЛАБЫМ РЕГИОНАМ ===== -->
<?php if (!empty($weak_regions)): ?>
    <div class="weak-regions-section">
        <h3>🎯 Пройдите игру по слабым регионам</h3>
        <p class="weak-regions-hint">Эти регионы даются вам хуже всего. Тренируйтесь!</p>
        <div class="weak-regions-buttons">
            <?php foreach ($weak_regions as $region): ?>
                <a href="/quiz/play?type=all&region=<?= urlencode($region['region']) ?>" class="btn btn-warning">
                    🌍 <?= htmlspecialchars($region['region']) ?> (<?= $region['success_rate'] ?>%)
                </a>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div style="margin-top: 30px;">
    <a href="/stats/leaderboard" class="btn">🏆 Лидерборд</a>
    <a href="/stats/regions" class="btn btn-secondary">🌍 Регионы</a>
</div>

<style>
    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
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

    .stat-hint {
        font-size: 12px;
        color: #999;
        margin-left: 10px;
    }

    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #3498db;
        color: white;
        text-decoration: none;
        border-radius: 8px;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
    }

    .btn-secondary {
        background: #6c757d;
    }

    .btn-secondary:hover {
        box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
    }

    /* ===== НОВЫЕ СТИЛИ ДЛЯ СЛАБЫХ РЕГИОНОВ ===== */
    .weak-regions-section {
        background: #fff3cd;
        padding: 20px 25px;
        border-radius: 8px;
        margin: 20px 0;
        border-left: 4px solid #ffc107;
    }

    .weak-regions-section h3 {
        margin: 0 0 8px 0;
        color: #856404;
    }

    .weak-regions-hint {
        color: #856404;
        margin: 0 0 15px 0;
    }

    .weak-regions-buttons {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-warning {
        background: #ffc107;
        color: #333;
        padding: 10px 20px;
        border-radius: 8px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.3s;
        border: none;
        cursor: pointer;
    }

    .btn-warning:hover {
        background: #e0a800;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
        
        .weak-regions-buttons {
            flex-direction: column;
        }
        
        .weak-regions-buttons .btn-warning {
            text-align: center;
        }
    }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';