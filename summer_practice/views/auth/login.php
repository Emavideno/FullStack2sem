<?php
$title = 'Вход';
ob_start();
?>

<div class="auth-container">
    <div class="auth-box">
        <h1>🔑 Вход</h1>
        
        <?php if (isset($_SESSION['blocked_message'])): ?>
            <div class="blocked-message">
                ❌ <?= htmlspecialchars($_SESSION['blocked_message']) ?>
            </div>
            <?php unset($_SESSION['blocked_message']); ?>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" action="/login">
            <?= \App\Middleware\CsrfMiddleware::getTokenField() ?>
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" placeholder="Введите логин" required>
            </div>
            
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" placeholder="Введите пароль" required>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">Войти</button>
        </form>
        
        <p class="auth-link">
            Нет аккаунта? <a href="/register">Зарегистрироваться</a>
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

.btn-full {
    width: 100%;
    padding: 14px;
    font-size: 16px;
}

.btn-primary {
    background: #3498db;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary:hover {
    background: #2980b9;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
}

.error-message {
    background: #fde8e8;
    color: #c0392b;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #c0392b;
}

.blocked-message {
    background: #fff3cd;
    color: #856404;
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #ffc107;
    font-weight: 500;
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