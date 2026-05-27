<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>ITISnews</title>
</head>
<body>
    <div class="main-background">
        <?php include __DIR__ . '/../partials/header.php'; ?>
        <main class="content">
            <?= $content ?? '' ?>
        </main>
    </div>
    <script src="/js/app.js"></script>
</body>
</html>