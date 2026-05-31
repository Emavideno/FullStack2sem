<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>Управление комментариями</title>
</head>

<body>
    <?php include __DIR__ . '/../../partials/header.php'; ?>
    <div class="main-background">
        <main class="content" style="max-width: 1200px; margin: 0 auto;">
            <br><br>
            <div
                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; color:#fffdfd">
                <a href="/admin/news" class="form-submit-btn" style="text-decoration: none; color:#fffdfd">← Назад к
                    новостям</a>
                <h1>Управление комментариями</h1>
                <div style="display: flex; gap: 20px;">
                    <a href="/" class="form-submit-btn" style="text-decoration: none; color:#fffdfd">← На главную</a>
                </div>
            </div>

            <table
                style="width: 100%; border-collapse: collapse; background: rgba(0,0,0,0.2); border-radius: 10px; overflow: hidden;">
                <thead>
                    <tr style="background: rgba(0,0,0,0.3); color:#6d6d6d">
                        <th style="padding: 12px; text-align: left;">ID</th>
                        <th style="padding: 12px; text-align: left;">Новость</th>
                        <th style="padding: 12px; text-align: left;">Автор</th>
                        <th style="padding: 12px; text-align: left;">Текст</th>
                        <th style="padding: 12px; text-align: left;">Дата</th>
                        <th style="padding: 12px; text-align: left;">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment): ?>
                        <tr style="border-top: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 12px; color:#6d6d6d"><?= $comment['id'] ?></td>
                            <td style="padding: 12px;">
                                <a href="/news/<?= $comment['news_id'] ?>"
                                    style="color: #fffdfd; text-decoration: none; border-bottom: 1px dashed #efb2b2;">
                                    <?= htmlspecialchars(mb_substr($comment['news_title'], 0, 50)) ?>
                                </a>
                            </td>
                            <td style="padding: 12px; color:#fffdfd"><?= htmlspecialchars($comment['author'] ?? 'Аноним') ?>
                            </td>
                            <td
                                style="padding: 12px; color:#fffdfd; word-wrap: break-word; max-width: 400px; vertical-align: top;">
                                <?= nl2br(htmlspecialchars($comment['text'])) ?>
                            </td>
                            <td style="padding: 12px; color:#fffdfd">
                                <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></td>
                            <td style="padding: 12px;">
                                <a href="/admin/comments/delete/<?= $comment['id'] ?>"
                                    onclick="return confirm('Удалить комментарий?')" style="color: #ff8888;">Удалить</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php if (empty($comments)): ?>
                <p style="color: #c1c0c0; text-align: center; padding: 40px;">Нет комментариев</p>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>