<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>IT's News</title>
</head>
<body class="page-auth">
    <div class="main-background">
        <main class="content">
            <div class="form-container">
                <div class="logo-container">Log in</div>
                <?php if (isset($error)): ?>
                    <div class="error-message" style="color: #efb2b2; text-align: center;"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form class="form" method="post">
                    <div class="form-group">
                        <label for="login">Login:</label>
                        <input required type="login" id="login" name="login" placeholder="Enter your login">
                        <label for="password">Password</label>
                        <input required placeholder="Enter your password" name="password" id="password" type="password">
                    </div>
                    <button type="submit" class="form-submit-btn">Login</button>
                </form>
            </div>
        </main>
    </div>
    <script src="/js/app.js"></script>
</body>
</html>