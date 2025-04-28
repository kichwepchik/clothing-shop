<?php
global $pdo;
session_start();
require 'static/app/database/connect.php';

$user_id = $_SESSION['user_id']; // Предполагается, что user_id хранится в сессии

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_favorites'])) {
    // Очистка списка "понравившихся"
    $stmt = $pdo->prepare('DELETE FROM like_items WHERE user_id = ?');
    $stmt->execute([$user_id]);
}

$stmt = $pdo->prepare('
    SELECT p.id, p.manufacturer, p.name, p.description, p.price, p.image_url
    FROM products p
    JOIN like_items l ON p.id = l.product_id
    WHERE l.user_id = ?
');
$stmt->execute([$user_id]);
$liked_products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/styles/styles_for_like_items.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <title>like_items</title>
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <h1>Избранное</h1>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="index.php">Главная страница</a>
    <a href="items.php">Каталог</a>
    <a href="cart.php">Корзина</a>

    <p></p>
</nav>
<div class="container">
    <div class="favorite_items">
        <?php if ($liked_products): ?>
            <div class="like-block">
                <?php foreach ($liked_products as $product): ?>
                    <div class="like_item" onclick="window.location.href='product.php?id=<?= htmlspecialchars($product['id']) ?>'">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="image_url">
                        <div class="favorite-info">
                            <h2><?php echo htmlspecialchars($product['manufacturer']); ?> <?php echo htmlspecialchars($product['name']); ?></h2>
                            <p>Цена: <?php echo htmlspecialchars($product['price']); ?> ₽.</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
        <p>У вас нет понравившихся товаров.</p>
        <?php endif; ?>
    </div>
    <form method="post">
        <button type="submit" name="clear_favorites" class="clear-btn">Очистить избранные</button>
    </form>
</div>
<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>
<script src="static/scripts/menu.js"></script>
</body>
</html>
