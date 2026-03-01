<?php

$products = [
    [
        'id' => 0,
        'type' => 'book',
        'name' => 'Treasure Island',
        'price' => 4544,
        'tags' => ['high rating', '6+', 'all']
    ],
    [
        'id' => 1,
        'type' => 'book',
        'name' => 'Twenty-sixth',
        'price' => 699,
        'tags' => ['high rating', 'bestseller', 'all']
    ],
    [
        'id' => 2,
        'type' => 'book',
        'name' => 'The Art of War',
        'price' => 299,
        'tags' => ['high rating', 'classic', 'all']
    ],
    [
        'id' => 3,
        'type' => 'book',
        'name' => '1984',
        'price' => 349,
        'tags' => ['high rating', '16+', 'all']
    ],
    [
        'id' => 4,
        'type' => 'book',
        'name' => 'The Great Gatsby',
        'price' => 309,
        'tags' => ['high rating', '12+', 'all']
    ],
    [
        'id' => 5,
        'type' => 'book',
        'name' => 'Play Back',
        'price' => 929,
        'tags' => ['exclusive', '18+', 'all']
    ],
    [
        'id' => 6,
        'type' => 'book',
        'name' => 'Count Averin. Sorcerer of the Russian Empire',
        'price' => 799,
        'tags' => ['weekly top', '16+', 'all']
    ],
    [
        'id' => 7,
        'type' => 'book',
        'name' => 'Detective Stories of the Meiji Period',
        'price' => 369,
        'tags' => ['bestseller', '16+', 'all']
    ],
    [
        'id' => 8,
        'type' => 'book',
        'name' => 'Unworthy Man',
        'price' => 319,
        'tags' => ['bestseller', '16+', 'all']
    ],
    [
        'id' => 9,
        'type' => 'book',
        'name' => 'Wuthering Heights',
        'price' => 1149,
        'tags' => ['bestseller', '16+', 'all']
    ],
    [
        'id' => 10,
        'type' => 'book',
        'name' => 'A Bad Joke, a Miniature',
        'price' => 499,
        'tags' => ['exclusive', '16+', 'all']
    ],
    [
        'id' => 11,
        'type' => 'notebook',
        'name' => 'Notebook. St. Petersburg. White Nights. Conversation',
        'price' => 349,
        'tags' => ['exclusive', '0+', 'all']
    ],
    [
        'id' => 12,
        'type' => 'notebook',
        'name' => 'Your Life, Your Rules',
        'price' => 140,
        'tags' => ['high rating', '12+', 'all']
    ],
    [
        'id' => 13,
        'type' => 'notebook',
        'name' => 'My Light, My Mirror',
        'price' => 191,
        'tags' => ['exclusive', '0+', 'all']
    ],
];

function shieldInputString(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function validatePositiveInt($value) {
    if (!isset($value) || !is_numeric($value) || $value < 1) {
        return 1;
    }
    $int = (int)$value;
    
    return $int;
}

$type = shieldInputString($_GET['type'] ?? 'book');
$minPrice = $_GET['min'] ?? 0;
$maxPrice = $_GET['max'] ?? PHP_INT_MAX;
$sort = shieldInputString($_GET['sort'] ?? 'all');
$dir = shieldInputString($_GET['dir'] ?? 'asc');
$page = validatePositiveInt($_GET['page'] ?? 1);
$perPage = validatePositiveInt($_GET['perPage'] ?? 1);

function filterByPreferences($product, $type, $minPrice, $maxPrice, $sortTag): bool  {
    if ($product['type'] === $type && $minPrice <= $product['price'] && $product['price'] <= $maxPrice && in_array($sortTag, $product['tags'])) {
        return true;
    }

    return false;
}

$filteredProducts = [];
foreach ($products as $product) {
    if (filterByPreferences($product, $type, $minPrice, $maxPrice, $sort))
        $filteredProducts[] = $product;
}

if ($dir === 'desc')
    $filteredProducts = array_reverse($filteredProducts);

if (count($filteredProducts) < $perPage) {
    $perPage = validatePositiveInt(($filteredProducts));
    echo '<h1> Невозможно отобразить товары! </h1>';
}

$amountOfFilteredProducts = count($filteredProducts);
$amountOfNeededProducts = $perPage * $page;

if (count($filteredProducts) < $amountOfNeededProducts) {
    echo '<h1> Количество товаров не хватает на эту страницу c таким же наполнением! </h1>';
}
    
?>

<h1>
    Страница Номер <?=$page ?> <br>
    Показывается <?=$perPage ?> товаров
    <ul>
        <?php 
            $startIndex = $perPage * ($page - 1);
            $endIndex = min($startIndex + $perPage, $amountOfFilteredProducts);

            if ($startIndex >= $amountOfFilteredProducts) {
                echo '<li> Пусто! </li>';
            } else {
                for ($i = $startIndex; $i < $endIndex; $i++) {
                    echo "<li> {$filteredProducts[$i]['name']} </li>";
                }
            } ?>
    </ul>
</h1>

<div>
    <a href="?<?= http_build_query(['page' => max(1, $page - 1)] + $_GET) ?>">← Назад</a>
    <a href="?<?= http_build_query(['page' => min(ceil($amountOfFilteredProducts / $perPage), $page + 1)] + $_GET) ?>">Дальше →</a>
</div>

<h1> Удобные ссылки: </h1>
<ul>
    <h2>Базовые тесты</h2>
    <li><a href="http://127.0.0.1:11228/"> Все товары (по умолчанию) </a></li>
    <li><a href="http://127.0.0.1:11228/?type=notebook"> Только блокноты </a></li>
    <li><a href="http://127.0.0.1:11228/?type=magazine"> Несуществующий тип </a></li>
    
    <h2>Фильтрация по цене</h2>
    <li><a href="http://127.0.0.1:11228/?min=300&max=500"> Цена от 300 до 500 </a></li>
    <li><a href="http://127.0.0.1:11228/?min=1000"> Цена выше 1000 </a></li>
    <li><a href="http://127.0.0.1:11228/?max=200"> Цена ниже 200 </a></li>
    <li><a href="http://127.0.0.1:11228/?min=10000&max=20000"> Диапазон без товаров </a></li>
    
    <h2>Фильтрация по тегам</h2>
    <li><a href="http://127.0.0.1:11228/?sort=high+rating"> Тег: high rating </a></li>
    <li><a href="http://127.0.0.1:11228/?sort=bestseller"> Тег: bestseller </a></li>
    <li><a href="http://127.0.0.1:11228/?sort=16%2B"> Тег: 16+ </a></li>
    <li><a href="http://127.0.0.1:11228/?sort=0%2B"> Тег: 0+ </a></li>
    <li><a href="http://127.0.0.1:11228/?sort=nonexistent"> Несуществующий тег </a></li>
    
    <h2>Комбинации фильтров</h2>
    <li><a href="http://127.0.0.1:11228/?type=book&sort=bestseller&min=300&max=400"> Книги-бестселлеры 300-400 </a></li>
    <li><a href="http://127.0.0.1:11228/?type=notebook&sort=0%2B&min=100&max=200"> Блокноты 0+ 100-200 </a></li>
    
    <h2>Сортировка</h2>
    <li><a href="http://127.0.0.1:11228/?dir=asc"> Сортировка по возрастанию </a></li>
    <li><a href="http://127.0.0.1:11228/?dir=desc"> Сортировка по убыванию </a></li>
    
    <h2>Параметры страницы</h2>
    <li><a href="http://127.0.0.1:11228/?perPage=5"> 5 товаров на странице </a></li>
    <li><a href="http://127.0.0.1:11228/?perPage=10"> 10 товаров на странице </a></li>
    <li><a href="http://127.0.0.1:11228/?page=2"> Страница 2 </a></li>
    <li><a href="http://127.0.0.1:11228/?page=3"> Страница 3 </a></li>
    
    <h2>Граничные случаи</h2>
    <li><a href="http://127.0.0.1:11228/?page=0"> Нулевая страница </a></li>
    <li><a href="http://127.0.0.1:11228/?perPage=0"> Ноль товаров </a></li>
    <li><a href="http://127.0.0.1:11228/?min=abc"> Некорректная цена </a></li>
    <li><a href="http://127.0.0.1:11228/?page=abc"> Некорректная страница </a></li>
</ul>