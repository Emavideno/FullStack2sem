<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title><?= $isEdit ? 'Редактирование' : 'Создание' ?> новости</title>
</head>

<body>
    <?php include __DIR__ . '/../../partials/header.php'; ?>
    <div class="main-background">
        <main class="content" style="max-width: 900px; margin: 0 auto;">
            <br>
            <br>
            <div class="form-container" style="width: 100%;">
                <h1 style="text-align: center; margin-bottom: 20px;"><?= $isEdit ? 'Редактирование' : 'Создание' ?>
                    новости</h1>
                <form method="post" action="<?= $isEdit ? '/admin/news/update/' . $news['id'] : '/admin/news/store' ?>">
                    <div class="form-group">
                        <label for="title">Заголовок</label>
                        <input type="text" id="title" name="title" required
                            value="<?= $news ? htmlspecialchars($news['title']) : '' ?>">
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="category_id">Категория</label>
                        <select id="category_id" name="category_id"
                            style="width: 100%; padding: 12px; border-radius: 20px;">
                            <option value="">Без категории</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($news && $news['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="excerpt">Краткое описание</label>
                        <textarea id="excerpt" name="excerpt" rows="3"
                            style="width: 100%; padding: 12px; border-radius: 20px;"><?= $news ? htmlspecialchars($news['excerpt']) : '' ?></textarea>
                    </div>
                    <br>

                    <div class="form-group">
                        <label for="content">Полный текст</label>
                        <textarea id="content" name="content" rows="10" required
                            style="width: 100%; padding: 12px; border-radius: 20px;"><?= $news ? htmlspecialchars($news['content']) : '' ?></textarea>
                    </div>
                    <br>
                    
                    <div class="form-group">
                        <label for="source_url">Источник (ссылка)</label>
                        <input type="url" id="source_url" name="source_url"
                            value="<?= $news ? htmlspecialchars($news['source_url'] ?? '') : '' ?>"
                            style="width: 100%; padding: 12px; border-radius: 20px;">
                    </div>
                    <br>

                    <button type="submit" class="form-submit-btn" style="margin-bottom: 10px;">Сохранить</button>
                    <a href="/admin/news" class="form-submit-btn"
                        style="text-decoration: none; text-align: center; display: block; background: #444; padding-top: 6px;">Отмена</a>
                </form>
            </div>
        </main>
    </div>
</body>

</html>