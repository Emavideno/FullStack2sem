<?php
session_start();

require_once 'Product.php';
require_once 'Cart.php';

$products = [
    1 => new Product(1, 'Телефон', 20000),
    2 => new Product(2, 'Ноутбук', 70000),
    3 => new Product(3, 'Наушники', 5000),
];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$cart = new Cart();

foreach ($_SESSION['cart'] as $id => $quantity) {
    if (isset($products[$id])) {
        $cart->add($products[$id], (int)$quantity);
    }
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/') {

    echo "<h1>Товары</h1>";
    foreach ($products as $product) {
        echo "<div>";
        echo "<b>" . htmlspecialchars($product->getTitle()) . "</b><br>";
        echo $product->getPrice() . " руб.<br>";
        echo "<a href='/add?id=" . $product->getId() . "'>Добавить в корзину</a>";
        echo "</div><hr>";
    }
    echo "<a href='/cart'>Перейти в корзину</a>";

} elseif ($path === '/add') {

    $id = $_GET['id'] ?? null;

    if (isset($products[$id])) {
        $cart->add($products[$id], 1);

        $_SESSION['cart'][$id] ??= 0;
        $_SESSION['cart'][$id] += 1;
    }

    header("Location: /");
    exit;

} elseif ($path === '/cart') {

    echo "<h1>Корзина</h1>";

    if (empty($cart->getItems())) {
        echo "<p>Корзина пуста.</p>";
    } else {
        foreach ($cart->getItems() as $item) {
            $product = $item['product'];
            $quantity = $item['quantity'];

            echo "<div>";
            echo htmlspecialchars($product->getTitle()) . "<br>";
            echo "Цена: " . $product->getPrice() . " руб.<br>";
            echo "Количество: $quantity<br>";
            echo "Сумма: " . ($product->getPrice() * $quantity) . " руб.<br>";
            echo "<a href='/remove?id=" . $product->getId() . "'>Удалить</a>";
            echo "</div><hr>";
        }

        $total = 0;
        foreach ($cart->getItems() as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }
        echo "<h3>Итого: $total руб.</h3>";
    }

    echo "<a href='/clear'>Очистить корзину</a><br><br>";
    echo "<a href='/'>Назад</a>";

} elseif ($path === '/remove') {

    $id = $_GET['id'] ?? null;
    if ($id !== null) {
        $cart->remove((int)$id);

        unset($_SESSION['cart'][$id]);
    }

    header("Location: /cart");
    exit;

} elseif ($path === '/clear') {

    $cart->clear();
    $_SESSION['cart'] = [];

    header("Location: /cart");
    exit;

} else {
    http_response_code(404);
    echo "404 - страница не найдена";
}
