<?php
session_start();

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

function redirect($url)
{
    header("Location: $url");
    exit;
}

if (!isset($_SESSION['user_token']) && $path != '/register') {
    $_SESSION['msg'] = "Сначала зарегистрируйтесь";
    redirect('/register');
}

if (!isset($_SESSION['tasks'])) {
    $_SESSION['tasks'] = [];
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(16));
}

$method = $_SERVER['REQUEST_METHOD'];

function sendMessage() {
    if (!empty($_SESSION['msg'])) {
        echo "<p>" . $_SESSION['msg'] . "</p>";
        unset($_SESSION['msg']);
    }
}

$get = [

    '/' => function () {
        echo "<h1>Главная</h1>";
        echo '<a href="/tasks">Список задач</a><br>';
        echo '<a href="/create">Создать задачу</a>';
    },

    '/register' => function () {
            ?>
            <h1>Регистрация</h1>

            <?php sendMessage(); ?>

            <form method="POST" action="/register">
                <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

                <input name="name" placeholder="Ваше имя"><br><br>

                <button>Зарегистрироваться</button>
            </form>
            <?php
        },

    '/tasks' => function () {
        echo "<h1>Список задач</h1>";
        sendMessage();

        foreach ($_SESSION['tasks'] as $id => $task) {
            echo "<div>";
            echo "<h3>{$task['title']}</h3>";
            echo "<p>{$task['text']}</p>";

            if (!empty($task['image'])) {
                echo "<img src='{$task['image']}' width='100'><br>";
            }

            echo "<a href='/edit?id=$id'>Редактировать</a>";
            echo "</div><br>";

            echo "
            <form method='POST' action='/delete' style='display:inline'>
                <input type='hidden' name='id' value='$id'>
                <input type='hidden' name='token' value='{$_SESSION['token']}'>
                <button>Удалить</button>
            </form>
            <hr>
            ";
        }

        echo '<br><br><a href="/create">Добавить</a>';
    },

    '/create' => function () {
        ?>
    <h1>Создать задачу</h1>
    <?php sendMessage(); ?>

    <form method="POST" enctype="multipart/form-data" action="/create">
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

        <input name="title" placeholder="Заголовок"><br><br>
        <textarea name="text" placeholder="Текст"></textarea><br><br>
        <input type="file" name="image"><br><br>

        <button>Создать</button>
    </form>
    <?php
    },

    '/edit' => function () {
        $id = $_GET['id'] ?? null;

        if (!isset($_SESSION['tasks'][$id])) {
            echo "Задача не найдена";
            return;
        }

        $task = $_SESSION['tasks'][$id];
        ?>

    <h1>Редактировать</h1>
    <?php sendMessage(); ?>

    <form method="POST" enctype="multipart/form-data" action="/edit?id=<?= $id ?>">
        <input type="hidden" name="token" value="<?= $_SESSION['token'] ?>">

        <input name="title" value="<?= $task['title'] ?>"><br><br>
        <textarea name="text"><?= $task['text'] ?></textarea><br><br>
        <input type="file" name="image"><br><br>

        <button>Сохранить</button>
    </form>

    <?php
    }
];

$post = [
    '/register' => function () {

        $name = htmlspecialchars($_POST['name'] ?? '');

        if (empty($name) || strlen($name) < 2) {
            $_SESSION['msg'] = "Введите имя";
            redirect('/register');
        }

        $_SESSION['user_token'] = bin2hex(random_bytes(16));
        $_SESSION['user_name'] = $name;

        $_SESSION['msg'] = "Вы зарегистрированы";
        redirect('/tasks');
    },

    '/create' => function () {

        if ($_POST['token'] !== $_SESSION['token']) {
            $_SESSION['msg'] = "Ошибка токена";
            redirect('/create');
        }

        $title = htmlspecialchars($_POST['title'] ?? '');
        $text = htmlspecialchars($_POST['text'] ?? '');

        if (empty($title) || strlen($title) < 3) {
            $_SESSION['msg'] = "Ошибка: короткий заголовок";
            redirect('/create');
        }

        $imagePath = '';

        if (!empty($_FILES['image']['name'])) {
            $dir = 'images/';
            if (!is_dir($dir))
                mkdir($dir);

            $imagePath = $dir . time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);
        }

        $_SESSION['tasks'][] = [
            'title' => $title,
            'text' => $text,
            'image' => $imagePath
        ];

        $_SESSION['msg'] = "Задача создана";
        redirect('/tasks');
    },

    '/delete' => function () {
            if ($_POST['token'] !== $_SESSION['token']) {
                $_SESSION['msg'] = "Ошибка токена";
                redirect('/tasks');
            }

            $id = $_POST['id'] ?? null;

            if (!isset($_SESSION['tasks'][$id])) {
                $_SESSION['msg'] = "Задача не найдена";
                redirect('/tasks');
            }

            if (!empty($_SESSION['tasks'][$id]['image']) && file_exists($_SESSION['tasks'][$id]['image'])) {
                unlink($_SESSION['tasks'][$id]['image']);
            }

            unset($_SESSION['tasks'][$id]);

            $_SESSION['msg'] = "Задача удалена";
            redirect('/tasks');
        },

    '/edit' => function () {

        $id = $_GET['id'] ?? null;

        if (!isset($_SESSION['tasks'][$id])) {
            echo "Ошибка";
            return;
        }

        if ($_POST['token'] !== $_SESSION['token']) {
            $_SESSION['msg'] = "Ошибка токена";
            redirect("/edit?id=$id");
        }

        $title = htmlspecialchars($_POST['title']);
        $text = htmlspecialchars($_POST['text']);

        if (empty($title)) {
            $_SESSION['msg'] = "Введите заголовок";
            redirect("/edit?id=$id");
        }

        if (!empty($_FILES['image']['name'])) {
            $dir = 'images/';
            if (!is_dir($dir))
                mkdir($dir);

            $imagePath = $dir . time() . '_' . $_FILES['image']['name'];
            move_uploaded_file($_FILES['image']['tmp_name'], $imagePath);

            $_SESSION['tasks'][$id]['image'] = $imagePath;
        }

        $_SESSION['tasks'][$id]['title'] = $title;
        $_SESSION['tasks'][$id]['text'] = $text;

        $_SESSION['msg'] = "Сохранено";
        redirect('/tasks');
    }
];

$routes = ($method === 'POST') ? $post : $get;

if (isset($routes[$path])) {
    $routes[$path]();
} else {
    http_response_code(404);
    echo "404";
}
