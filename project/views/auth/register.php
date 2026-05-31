<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/styles.css">
    <title>Регистрация</title>
</head>
<body class="page-auth">
    <div class="main-background">
        <main class="content">
            <div class="form-container">
                <div class="logo-container">Register</div>
                <?php if (isset($error)): ?>
                    <div class="error-message" style="color: #efb2b2; text-align: center;"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form class="form" method="post">
                    <div class="form-group">
                        <label for="login">Login:</label>
                        <input required type="text" id="login" name="login" placeholder="Choose your login">
                        <label for="password">Password:</label>
                        <input required type="password" id="password" name="password" placeholder="Choose your password">
                    </div>
                    <button type="submit" class="form-submit-btn">Register</button>
                </form>
                <div style="text-align: center; margin-top: 15px;">
                    <a href="/login" class="link">Already have an account? Sign in</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>