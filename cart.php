<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="static/styles/styles_for_cart.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap">
    <title>Корзина</title>
</head>
<body>
<header>
    <a href="index.php">
        <img src="static/img/logo_one.png" alt="High Score Logo" class="logo">
    </a>
    <h1>Корзина</h1>
    <div class="menu-icon" onclick="toggleMenu()">☰</div>
</header>
<nav class="menu" id="mobileMenu">
    <a href="index.php">Главная страница</a>
    <a href="items.php">Каталог</a>
    <a href="like_items.php">Понравившиеся</a>
    <p></p>
</nav>
<div class="container">
    <?php if (count($cartItems) > 0): ?>
        <?php
        $totalPrice = 0;
        foreach ($cartItems as $item):
            $itemTotal = $item['quantity'] * $item['price'];
            $totalPrice += $itemTotal;
            ?>
            <div class="cart-item" data-item-id="<?= $item['id'] ?>">
                <div class="cart-item-details">
                    <img src="<?= $item['image_url'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="order-image">
                    <div class="item-details">
                        <p>
                            <strong>Товар: </strong><?= htmlspecialchars($item['manufacturer'])?> <?= htmlspecialchars($item['name']) ?> <br>
                            <strong>Размер:</strong> <?= htmlspecialchars($item['size']) ?>
                        </p>
                        <p><strong>Количество:</strong>
                            <button class="quantity-button" onclick="changeQuantity(this, -1)">-</button>
                            <span class="quantity-display"><?= htmlspecialchars($item['quantity']) ?></span>
                            <button class="quantity-button" onclick="changeQuantity(this, 1)">+</button>
                        </p>
                        <p>
                            <strong>Цена:</strong> <?= htmlspecialchars($item['price']) ?> ₽<br>
                            <strong>Общая цена:</strong> <span class="item-total-price"><?= number_format($itemTotal, 2) ?></span> ₽
                        </p>
                        <form method="POST" action="remove_item.php">
                            <input type="hidden" name="cart_item_id" value="<?= htmlspecialchars($item['id']) ?>">
                            <button type="submit" class="remove-item-button">Удалить</button>
                        </form>
                    </div>


                </div>
            </div>
        <?php endforeach; ?>
        <?php $totalPriceWithDelivery = $totalPrice + $deliveryFee; ?>
        <div class="cart-total">
            <strong>Доставка: </strong>500₽
            <h3>Общая сумма: <span id="total-price"><?= number_format($totalPriceWithDelivery, 2) ?></span> ₽</h3>
        </div>
        <div class="cart-buttons">
            <button class="notify-button" onclick="window.location.href = 'pay.php'">Оплатить</button>
            <form method="POST" action="clear_cart.php">
                <button type="submit" class="clear-cart-button">Очистить корзину</button>
            </form>
        </div>
    <?php else: ?>
        <div class="zero-cart">
            <p>Ваша корзина пуста.</p>
            <button class="start-shopping" onclick="window.location.href='items.php'">Начать покупки</button>
        </div>

    <?php endif; ?>
</div>

<div class="overlay" id="menuOverlay" onclick="toggleMenu()"></div>
<script src="static/scripts/menu.js"></script>
<script src="static/scripts/script_for_cart.js"></script>
<script src="https://telegram.org/js/telegram-web-app.js"></script>
<script src="static/scripts/telegram_back.js"></script>

</body>
</html>