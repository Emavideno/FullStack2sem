<?php
$title = 'Географическая викторина';
ob_start();
?>

<div class="hero">
    <h1>🌍 Географическая викторина</h1>
    <p class="subtitle">Проверьте свои знания о странах мира в 6 режимах игры</p>
</div>

<div class="quiz-modes-grid">
    <div class="mode-card" onclick="startGame('flag_to_country')">
        <div class="mode-icon">🗺️</div>
        <h3>Угадай по флагу</h3>
        <p>Определите страну по её флагу</p>
        <span class="mode-badge">10 вопросов</span>
    </div>
    
    <div class="mode-card" onclick="startGame('country_to_flag')">
        <div class="mode-icon">🏁</div>
        <h3>Угадай флаг</h3>
        <p>Найдите правильный флаг для страны</p>
        <span class="mode-badge">10 вопросов</span>
    </div>
    
    <div class="mode-card" onclick="startGame('capital_to_country')">
        <div class="mode-icon">🏛️</div>
        <h3>Страна по столице</h3>
        <p>Угадайте страну по её столице</p>
        <span class="mode-badge">10 вопросов</span>
    </div>
    
    <div class="mode-card" onclick="startGame('country_to_capital')">
        <div class="mode-icon">🌆</div>
        <h3>Столица по стране</h3>
        <p>Назовите столицу страны</p>
        <span class="mode-badge">10 вопросов</span>
    </div>
    
    <div class="mode-card" onclick="startGame('population')">
        <div class="mode-icon">👥</div>
        <h3>По населению</h3>
        <p>Угадайте страну по численности населения</p>
        <span class="mode-badge">10 вопросов</span>
    </div>
    
    <div class="mode-card" onclick="startGame('area')">
        <div class="mode-icon">📐</div>
        <h3>По площади</h3>
        <p>Угадайте страну по её территории</p>
        <span class="mode-badge">10 вопросов</span>
    </div>
</div>

<div class="action-buttons">
    <button onclick="randomGame()" class="btn btn-random">🎲 Случайный режим</button>
    <a href="/stats" class="btn btn-stats">📊 Моя статистика</a>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <a href="/admin" class="btn btn-admin">👑 Админ</a>
    <?php endif; ?>
</div>

<script>
function startGame(type) {
    window.location.href = `/quiz/play?type=${type}`;
}

function randomGame() {
    const types = [
        'flag_to_country',
        'country_to_flag',
        'capital_to_country',
        'country_to_capital',
        'population',
        'area'
    ];
    const randomType = types[Math.floor(Math.random() * types.length)];
    window.location.href = `/quiz/play?type=${randomType}`;
}
</script>

<style>
.hero {
    text-align: center;
    padding: 30px 0 20px;
}

.hero h1 {
    font-size: 36px;
    margin-bottom: 10px;
    color: #2c3e50;
}

.subtitle {
    font-size: 18px;
    color: #7f8c8d;
}

.quiz-modes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.mode-card {
    background: white;
    border: 2px solid #e8ecf1;
    border-radius: 12px;
    padding: 25px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    position: relative;
    user-select: none;
}

.mode-card:hover {
    transform: translateY(-4px);
    border-color: #3498db;
    box-shadow: 0 8px 25px rgba(52, 152, 219, 0.15);
}

.mode-card:active {
    transform: scale(0.97);
}

.mode-icon {
    font-size: 40px;
    display: block;
    margin-bottom: 12px;
}

.mode-card h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #2c3e50;
}

.mode-card p {
    margin: 0 0 12px 0;
    font-size: 14px;
    color: #7f8c8d;
    line-height: 1.4;
}

.mode-badge {
    display: inline-block;
    background: #e8f4fd;
    color: #3498db;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 20px;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin: 10px 0 20px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 32px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    min-width: 180px;
    min-height: 52px;
    box-sizing: border-box;
}

.btn-random {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-random:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-stats {
    background: #2c3e50;
    color: white;
}

.btn-stats:hover {
    background: #34495e;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
}

.btn-admin {
    background: #e67e22;
    color: white;
}

.btn-admin:hover {
    background: #d35400;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .hero h1 {
        font-size: 28px;
    }
    
    .quiz-modes-grid {
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    
    .mode-card {
        padding: 18px 15px;
    }
    
    .mode-icon {
        font-size: 30px;
    }
    
    .mode-card h3 {
        font-size: 15px;
    }
    
    .mode-card p {
        font-size: 12px;
    }
    
    .btn {
        min-width: 140px;
        padding: 12px 20px;
        font-size: 14px;
        min-height: 44px;
    }
}

@media (max-width: 480px) {
    .quiz-modes-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 280px;
        min-width: unset;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/main.php';