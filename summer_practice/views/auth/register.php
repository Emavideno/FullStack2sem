<?php
$title = 'Регистрация';
ob_start();
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>📝 Регистрация</h1>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/register">
            <?= \App\Middleware\CsrfMiddleware::getTokenField() ?>
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" placeholder="Придумайте логин" required minlength="3">
                <small class="form-hint">Минимум 3 символа</small>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="Придумайте пароль" required minlength="3">
                <small class="form-hint">Минимум 3 символа</small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Зарегистрироваться</button>
        </form>
        
        <p class="auth-link">
            Уже есть аккаунт? <a href="/login">Войти</a>
        </p>
    </div>
</div>

<style>
.auth-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 70vh;
    padding: 20px;
}

.auth-box {
    background: white;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 400px;
}

.auth-box h1 {
    text-align: center;
    margin-bottom: 30px;
    color: #2c3e50;
    font-size: 28px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #2c3e50;
}

.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e8ecf1;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: #3498db;
}

.form-hint {
    display: block;
    margin-top: 4px;
    font-size: 12px;
    color: #95a5a6;
}

.btn-full {
    width: 100%;
    padding: 14px;
    font-size: 16px;
}

.error-message {
    background: #fde8e8;
    color: #c0392b;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #c0392b;
}

.auth-link {
    text-align: center;
    margin-top: 20px;
    color: #7f8c8d;
}

.auth-link a {
    color: #3498db;
    text-decoration: none;
    font-weight: 500;
}

.auth-link a:hover {
    text-decoration: underline;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/main.php';