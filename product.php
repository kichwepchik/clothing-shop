<?php
session_start();
global $pdo;
require 'static/app/database/connect.php';

$product = [];
$sizes = [];
$images = [];
$color = '';
$isFavorite = false;

function redirectToError($message) {
    header("Location: error.php?message=" . urlencode($message));
    exit;
}

if (isset($_GET['id'])) {
    $productId = intval($_GET['id']);
    $userId = $_SESSION['user_id']; // предположим, что ID пользователя хранится в сессии

    try {
        // Извлечение данных о продукте
        $sql = 'SELECT p.id, p.name, p.description, p.price, p.image_url, c.color_name, p.manufacturer 
                FROM products p
                JOIN product_colors pc ON p.id = pc.product_id
                JOIN colors c ON pc.color_id = c.id
                WHERE p.id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            redirectToError("Товар не найден.");
        }

        $color = $product['color_name'];

        // Проверка, находится ли товар в избранном
        $sql = 'SELECT * FROM like_items WHERE user_id = :user_id AND product_id = :product_id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'product_id' => $productId
        ]);
        $isFavorite = $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;

        // Извлечение доступных размеров
        $sql = 'SELECT s.id, s.size FROM sizes s
                JOIN product_sizes ps ON s.id = ps.size_id
                WHERE ps.product_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Извлечение изображений
        $sql = 'SELECT image_url FROM product_images WHERE product_id = :id';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $productId]);
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        redirectToError("Ошибка при выполнении запроса: " . $e->getMessage());
    }
} else {
    redirectToError("ID товара не указан.");
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="static/styles/styles_for_product.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-solid-rounded/css/uicons-solid-rounded.css'>
    <link rel='stylesheet' href='https://cdn-uicons.flaticon.com/2.4.2/uicons-bold-rounded/css/uicons-bold-rounded.css'>
    <title>Информация о товаре</title>
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="index.php">Главная страница</a>
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="container">
    <h2 class="product-name"><?= htmlspecialchars($product['manufacturer']) ?> <?= htmlspecialchars($product['name']) ?></h2>
    <div class="product-details">
        <div class="swiper-container">
            <div class="heart-icon-container">
                <i class="<?= $isFavorite ? 'fi fi-sr-heart' : 'fi fi-br-heart' ?>" onclick="addToFavorites(<?= htmlspecialchars($product['id']) ?>, this)"></i>
            </div>
            <div class="swiper-wrapper">
                <?php foreach ($images as $image): ?>
                    <div class="swiper-slide">
                        <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="Product Image" class="carousel-image">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        <p class="product-price">Стоимость: <?= htmlspecialchars($product['price']) ?> ₽</p>
        <p class="product-description"><?= htmlspecialchars($product['description']) ?></p>
        <p class="product-color">Цвет: <?= htmlspecialchars($color) ?></p>
        <div class="delivery">
            <p>Доставка СДЭК</p>
            <div class="delivery-option checked">
                <input type="radio" id="standard" name="delivery" value="standard" checked>
                <label for="standard">Стандартная 500₽<br> до 20 дней</label>
            </div>
        </div>
        <form id="add-to-cart-form" action="add_to_cart.php" method="post">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
            <h3>Доступные размеры</h3>
            <a href="#" class="size-memory" id="openSizePopup">Памятка размеров</a>
            <div class="product-sizes">
                <div class="dropdown">
                    <button type="button" class="dropbtn" onclick="toggleDropdown('kids-sizes')">Детские</button>
                    <div id="kids-sizes" class="dropdown-content">
                        <?php foreach ($sizes as $size): ?>
                            <?php if (
                                ($size['size'] >= 27 && $size['size'] <= 38) ||
                                in_array($size['id'], [44, 45, 46, 47, 48, 49])
                            ): ?>
                                <div class="size-option" data-size-id="<?= $size['id'] ?>">
                                    <?= htmlspecialchars($size['size']) ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="dropdown">
                    <button type="button" class="dropbtn" onclick="toggleDropdown('adult-sizes')">Взрослые</button>
                    <div id="adult-sizes" class="dropdown-content">
                        <?php foreach ($sizes as $size): ?>
                            <?php if (
                                ($size['size'] > 38 && $size['size'] <= 48) ||
                                in_array($size['id'], [44, 45, 46, 47, 48, 49])
                            ): ?>
                                <div class="size-option" data-size-id="<?= $size['id'] ?>">
                                    <?= htmlspecialchars($size['size']) ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" name="size_id" id="selected-size" required>
            </div>
            <button type="submit" class="add-to-cart-btn">Добавить в корзину</button>
        </form>
    </div>
</div>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<div class="full-screen-image" id="fullScreenImage">
    <img src="" alt="Full Screen Image" id="fullScreenImg">
    <span class="close" id="closeFullScreen">×</span>
</div>
<div class="popup-overlay" id="popupOverlay"></div>
<div class="popup" id="popup">
    <p>Внимание! Вы не выбрали размер</p>
</div>
<div class="size-popup-overlay" id="sizePopupOverlay"></div>
<div class="size-popup" id="sizePopup">
    <div class="size-popup-content">
        <span class="close-size-popup" id="closeSizePopup">×</span>
        <img src="static/img/table_size.png" alt="Size Chart" class="size-chart-image">
    </div>
</div>

<script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
<script src="static/scripts/menu.js"></script>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/script_for_product.js"></script>
<script src="static/scripts/add_to_favorite.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="static/scripts/ajax_script_for_product.js"></script>
<script src="static/scripts/dropdown_for_product.js"></script>
</body>
</html>
