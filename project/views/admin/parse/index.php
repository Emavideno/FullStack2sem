<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>Парсинг RSS</title>
</head>

<body>
    <?php include __DIR__ . '/../../partials/header.php'; ?>
    <div class="main-background">
        <main class="content" style="max-width: 800px; margin: 0 auto;">
            <br><br>
            <h1 style="color: white; margin-bottom: 20px;">Парсинг RSS</h1>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <a href="/admin/news" class="form-submit-btn"
                    style="text-decoration: none; color:#fffdfd;">← Назад</a>
                <a href="/admin/parse" class="form-submit-btn" style="text-decoration: none; color:#fffdfd;">Запустить
                    парсинг</a>
            </div>

            <?php if (isset($results)): ?>
                <?php foreach ($results as $result): ?>
                    <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 15px; margin-bottom: 15px;">
                        <h3 style="color: #efb2b2;"><?= htmlspecialchars($result['source']) ?></h3>
                        <p style="color:#fffddd;">Добавлено новостей: <strong><?= $result['added'] ?></strong></p>
                        <?php if (!empty($result['errors'])): ?>
                            <p style="color: #ff8888;">Ошибки: <?= implode(', ', $result['errors']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="background: rgba(0,0,0,0.2); padding: 20px; border-radius: 15px; text-align: center;">
                    <p style="color: #c1c0c0;">Нажмите "Запустить парсинг" для импорта новостей из внешних RSS-лент.</p>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>