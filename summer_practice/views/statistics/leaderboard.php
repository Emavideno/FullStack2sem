<?php
$title = 'Лидерборд';
ob_start();
?>

<h1>🏆 Лидерборд</h1>

<div class="filter-bar">
    <a href="/stats/leaderboard" class="btn <?= $current_type === null ? 'btn-active' : 'btn-secondary' ?>">Все</a>
    <?php foreach ($type_labels as $key => $label): ?>
        <a href="/stats/leaderboard?type=<?= $key ?>" class="btn <?= $current_type === $key ? 'btn-active' : 'btn-secondary' ?>">
            <?= $label ?>
        </a>
    <?php endforeach; ?>
</div>

<table class="leaderboard-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Пользователь</th>
            <th>Правильных</th>
            <th>Всего</th>
            <th>Процент</th>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($leaderboard)): ?>
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">Нет данных для отображения</td>
            </tr>
        <?php else: ?>
            <?php $rank = 1; foreach ($leaderboard as $player): ?>
                <tr class="<?= $rank <= 3 ? 'top-' . $rank : '' ?>">
                    <td><?= $rank ?></td>
                    <td><?= htmlspecialchars($player['login']) ?></td>
                    <td><?= $player['correct_answers'] ?></td>
                    <td><?= $player['total_answers'] ?></td>
                    <td>
                        <span class="<?= $player['success_rate'] >= 70 ? 'text-success' : ($player['success_rate'] >= 40 ? 'text-warning' : 'text-danger') ?>">
                            <?= $player['success_rate'] ?>%
                        </span>
                    </td>
                </tr>
            <?php $rank++; endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 30px;">
    <a href="/stats" class="btn">⬅️ Назад к статистике</a>
</div>

<style>
.filter-bar {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin: 20px 0;
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

.btn-active {
    background: #2c3e50;
    color: white;
}

.btn-active:hover {
    background: #34495e;
    box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
}

.btn-secondary {
    background: #e8ecf1;
    color: #2c3e50;
}

.btn-secondary:hover {
    background: #d5d9e0;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.leaderboard-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.leaderboard-table th {
    background: #2c3e50;
    color: white;
    padding: 12px 15px;
    text-align: left;
}

.leaderboard-table td {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
}

.leaderboard-table tr:hover {
    background: #f8f9fa;
}

.top-1 td {
    background: #fff9e6;
}

.top-1 td:first-child {
    font-size: 20px;
    font-weight: bold;
    color: #f1c40f;
}

.top-2 td {
    background: #f5f5f5;
}

.top-2 td:first-child {
    font-size: 18px;
    font-weight: bold;
    color: #95a5a6;
}

.top-3 td {
    background: #faf0e6;
}

.top-3 td:first-child {
    font-size: 16px;
    font-weight: bold;
    color: #cd7f32;
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
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';