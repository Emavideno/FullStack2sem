<?php

require_once 'Product.php';

$data = [
    ['id' => 1, 'title' => 'Телефон', 'price' => 19999.5],
    ['id' => 2, 'title' => 'Ноутбук', 'price' => 75999.99],
    ['id' => 3, 'title' => 'Наушники', 'price' => 4999],
];

$products = [];

foreach ($data as $item) {
    $products[] = new Product(
        $item['id'],
        $item['title'],
        $item['price']
    );
}

?>

<h1>Список товаров</h1>

<ul>
    <?php foreach ($products as $product): ?>
        <li>
            <?= htmlspecialchars($product->getTitle()) ?> —
            <?= $product->getFormattedPrice() ?>
        </li>
    <?php endforeach; ?>
</ul>
