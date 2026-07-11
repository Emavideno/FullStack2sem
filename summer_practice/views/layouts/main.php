<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Географическая викторина' ?></title>
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <nav class="navbar">
        <div class="container">
            <a href="/" class="navbar-brand">🌍 Викторина</a>
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/" class="active">🏠 Главная</a></li>
                    <li><a href="/stats">📊 Статистика</a></li>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="/admin">👑 Админ</a></li>
                    <?php endif; ?>
                    <li><a href="/random" class="btn-random-nav">🎲 Случайный режим</a></li>
                    <li><a href="/logout" class="btn-logout">🚪 Выйти</a></li>
                <?php else: ?>
                    <li><a href="/login">🔑 Вход</a></li>
                    <li><a href="/register">📝 Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <main class="container">
        <?= $content ?? '' ?>
    </main>
</body>

</html>