<?php

session_start();

$user_id = isset($_SESSION['user_id']);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!$user_id && $path == '/lenta') {
    $_SESSION['flash'] = ['error' => 'Авторизуйтесь']; //Устанавливаем флэш-сообщения
    header('Location: /register');
    exit;
}

if ($user_id && $path == '/lenta') {
    echo $_SESSION['familia'];
    echo "<br>";
    echo $_SESSION['about'];
    echo "<br>";
    echo $_SESSION['sleepTime'];
    echo "<br>";
    unset($_SESSION['user_id']); // =============
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $familia = htmlspecialchars($_POST['familia'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $about = htmlspecialchars($_POST['about'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $sleepTime = $_POST['sleepTime'] ?? 'owl';
    
    if (empty($familia) || strlen($familia) < 3) {
        $_SESSION['error'] = 'Введите полную Фамилию';
        header('Location: /register');
        exit;
    }
    
    $_SESSION['register'][] = ['familia' => $familia, 'sleepTime' => $sleepTime];
    $_SESSION['success'] = 'Вы зарегестрировались';
    $token = random_bytes(15);
    $_SESSION['user_id'] = $token;
    $_SESSION['familia'] = $familia;
    $_SESSION['about'] = $about;
    $_SESSION['sleepTime'] = $sleepTime;
    header('Location: /lenta');
}

echo $path;
echo '<br>';
echo $_SESSION['error'] ?? '';
unset($_SESSION['error']);

?>

<form action="/register" method="POST">
    <input type="text" name="familia" required placeholder="Введите Фамилию">
    <textarea name="about">Расскажите о себе</textarea>
    <select name="sleepTime">
        <h6>Вы сова или жаворонок</h6>
        <option value="owl">Сова</option>
        <option value="zhavoronok">Жаворонок</option>
    </select>
    <button type="submit">Отправить</button>
</form>