<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>Управление новостями</title>
</head>

<body>
    <?php include __DIR__ . '/../../partials/header.php'; ?>
    <div class="main-background">
        <main class="content" style="max-width: 1200px; margin: 0 auto;">
            <br>
            <br>
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; color:#fffdfd">
                <a href="/admin/comments" class="form-submit-btn" style="text-decoration: none; color:#fffdfd">📝
                    Комментарии</a>
                <h1>Управление новостями</h1>
                <div style="display: flex; gap: 20px;">
                    <a href="/admin/parse" class="form-submit-btn" style="text-decoration: none; color:#fffdfd;">RSS</a>
                    <a href="/" class="form-submit-btn" style="text-decoration: none; color:#fffdfd">← На главную</a>
                    <a href="/admin/news/create" class="form-submit-btn" style="text-decoration: none; color:#fffdfd">+
                        Новая новость</a>
                </div>
            </div>

            <table
                style="width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.2); border-radius: 10px; overflow: hidden;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.3); color:#6d6d6d">
                        <th style="padding: 12px; text-align: left;">ID</th>
                        <th style="padding: 12px; text-align: left;">Заголовок</th>
                        <th style="padding: 12px; text-align: left;">Категория</th>
                        <th style="padding: 12px; text-align: left;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($news as $item): ?>
                        <tr style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 12px; color:#6d6d6d"><?= $item['id'] ?></td>
                            <td style="padding: 12px;">
                                <a href="/news/<?= $item['id'] ?>"
                                    style="color: #fffdfd; text-decoration: none; border-bottom: 1px dashed #efb2b2;">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </td>
                            <td style="padding: 12px; color:#fffdfd">
                                <?= htmlspecialchars($item['category_name'] ?? 'Без категории') ?>
                            </td>
                            <td style="padding: 12px;">
                                <a href="/admin/news/edit/<?= $item['id'] ?>"
                                    style="color: #efb2b2; margin-right: 10px;">Редактировать</a>
                                <a href="/admin/news/delete/<?= $item['id'] ?>" onclick="return confirm('Удалить новость?')"
                                    style="color: #ff8888;">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>
</body>

</html>