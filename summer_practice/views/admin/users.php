<?php
$title = 'Управление пользователями';
ob_start();
?>

<div class="users-header">
    <h1>👤 Управление пользователями</h1>
    <a href="/admin" class="btn btn-secondary">⬅️ Назад</a>
</div>

<div class="table-responsive">
    <table class="users-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Логин</th>
                <th>Роль</th>
                <th>Статус</th>
                <th>Всего игр</th>
                <th>Правильных</th>
                <th>Процент</th>
                <th>По типам</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <?php 
                    $stats = $userStats[$user['id']] ?? [
                        'total_answers' => 0, 
                        'correct_answers' => 0,
                        'success_rate' => 0,
                        'types' => [],
                        'history' => []
                    ]; 
                ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td>
                        <strong><?= htmlspecialchars($user['login']) ?></strong>
                        <?php if ($user['role'] === 'admin'): ?>
                            <span class="role-badge">👑</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $user['role'] === 'admin' ? 'Админ' : 'Пользователь' ?></td>
                    <td>
                        <span class="status <?= $user['is_blocked'] ? 'status-blocked' : 'status-active' ?>">
                            <?= $user['is_blocked'] ? '🔒 Заблокирован' : '✅ Активен' ?>
                        </span>
                    </td>
                    <td><?= $stats['total_answers'] ?></td>
                    <td><?= $stats['correct_answers'] ?></td>
                    <td>
                        <span class="<?= $stats['success_rate'] >= 70 ? 'text-success' : ($stats['success_rate'] >= 40 ? 'text-warning' : 'text-danger') ?>">
                            <?= $stats['success_rate'] ?>%
                        </span>
                    </td>
                    <td>
                        <div class="types-grid">
                            <?php 
                            foreach ($type_labels as $key => $label): 
                                $typeStat = null;
                                foreach ($stats['types'] as $t) {
                                    if ($t['type'] === $key) {
                                        $typeStat = $t;
                                        break;
                                    }
                                }
                                $total = $typeStat ? $typeStat['total'] : 0;
                                $correct = $typeStat ? $typeStat['correct'] : 0;
                            ?>
                                <span class="type-badge <?= $total > 0 ? 'has-answers' : '' ?>">
                                    <?= $label ?>: <?= $correct ?><?= $total > 0 ? "({$total})" : '' ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <?php if ($user['id'] !== $_SESSION['user_id'] && $user['role'] !== 'admin'): ?>
                            <?php if ($user['is_blocked']): ?>
                                <button onclick="toggleBlock(<?= $user['id'] ?>, false)" class="btn btn-success btn-sm">Разблокировать</button>
                            <?php else: ?>
                                <button onclick="toggleBlock(<?= $user['id'] ?>, true)" class="btn btn-danger btn-sm">Заблокировать</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
function toggleBlock(userId, block) {
    const action = block ? 'заблокировать' : 'разблокировать';
    if (!confirm(`Вы уверены, что хотите ${action} этого пользователя?`)) {
        return;
    }
    
    const url = block ? '/admin/users/block' : '/admin/users/unblock';
    
    const formData = new FormData();
    formData.append('user_id', userId);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    
    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Сервер вернул HTML вместо JSON. Возможно, ошибка CSRF.');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            alert('✅ ' + data.message);
            location.reload();
        } else {
            alert('❌ ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ Ошибка: ' + error.message);
    });
}
</script>

<style>
.users-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0 30px;
    flex-wrap: wrap;
    gap: 15px;
}

.users-header h1 {
    font-size: 28px;
    margin: 0;
    color: #2c3e50;
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.users-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    min-width: 900px;
}

.users-table th {
    background: #2c3e50;
    color: white;
    padding: 12px 14px;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    white-space: nowrap;
}

.users-table td {
    padding: 10px 14px;
    border-bottom: 1px solid #f0f2f5;
    font-size: 13px;
    vertical-align: middle;
}

.users-table tbody tr:hover {
    background: #f8f9fa;
}

.users-table tbody tr:last-child td {
    border-bottom: none;
}

.status {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-block;
    white-space: nowrap;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-blocked {
    background: #f8d7da;
    color: #721c24;
}

.role-badge {
    font-size: 14px;
}

.types-grid {
    display: flex;
    flex-direction: column;
    gap: 2px;
    min-width: 140px;
}

.type-badge {
    display: inline-block;
    background: #f0f0f0;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 12px;
    color: #999;
    white-space: nowrap;
}

.type-badge.has-answers {
    background: #e8f4fd;
    color: #2c3e50;
}

.text-success { color: #27ae60; font-weight: bold; }
.text-warning { color: #f39c12; font-weight: bold; }
.text-danger { color: #e74c3c; font-weight: bold; }
.text-muted { color: #adb5bd; }

.btn-sm {
    padding: 4px 12px;
    font-size: 12px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.btn-sm:hover {
    transform: translateY(-1px);
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #218838;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #c82333;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    padding: 8px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-1px);
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';