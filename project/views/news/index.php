<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>IT'sNews</title>
</head>

<body>
    <?php include __DIR__ . '/../partials/header.php'; ?>
    <aside class="sidebar">
        <h1>Категории</h1>
        <br>
        <ul id="categories-list">
            <?php foreach ($categories as $cat): ?>
                <li>
                    <label class="checkbox-container">
                        <span class="label-text"><?= htmlspecialchars($cat['name']) ?></span>
                        <input type="checkbox" class="category-checkbox" value="<?= $cat['id'] ?>">
                        <svg viewBox="0 0 64 64">
                            <path
                                d="M 0 16 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 16 L 32 48 L 64 16 V 8 A 8 8 90 0 0 56 0 H 8 A 8 8 90 0 0 0 8 V 56 A 8 8 90 0 0 8 64 H 56 A 8 8 90 0 0 64 56 V 16"
                                class="path"></path>
                        </svg>
                    </label>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>
    <main class="content">
        <div class="news-feed">
            <?php include __DIR__ . '/_news_list.php'; ?>
        </div>
    </main>
    <?php include __DIR__ . '/../partials/footer.php'; ?>